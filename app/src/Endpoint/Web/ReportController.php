<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use Psr\Http\Message\ResponseInterface;
use Spiral\Router\Annotation\Route;
use Cycle\Database\Database;
use Spiral\Http\Request\InputManager;
use Spiral\Prototype\Traits\PrototypeTrait;

class ReportController
{
    use PrototypeTrait;

    private InputManager $input;

    public function __construct(private Database $db, InputManager $inputManager)
    {
        $this->input = $inputManager;
    }

    #[Route(route: '/monthly-sales-by-region', name: 'monthly.sales.region', methods: 'GET')]
    public function monthlySalesByRegion(): ResponseInterface
    {

        $start = $this->input->query->get('start_date') ?? date("Y-m-d", strtotime("-12 months"));
        $end = $this->input->query->get('end_date') ?? date('Y-m-d');

        // $currentDate  = date("Y-m-d");
        // $startDate = date("Y-m-d", strtotime("-12 months", strtotime($currentDate)));
        // Old approch
        // $query = "
        //     SELECT
        //         YEAR(o.order_date) AS order_year,
        //         MONTH(o.order_date) AS order_month,
        //         s.region_id,
        //         SUM(o.total_price) AS total_sales_amount,
        //         COUNT(o.order_id) AS number_of_orders
        //     FROM orders o 
        //     JOIN stores s ON s.store_id = o.store_storeId
        //     WHERE o.order_date BETWEEN '" . $startDate . "' AND '" . $currentDate . "'
        //     GROUP BY order_year, order_month, s.region_id
        //     ORDER BY order_year DESC, order_month DESC, s.region_id;

        // ";

        // Pagination
        $page = max(1, (int)($this->input->query->get('page') ?? 1));
        $perPage = 1000;
        $offset = ($page - 1) * $perPage;

        $query = "
            SELECT
                o.order_year,
    			o.order_month,
                s.region_id,
                SUM(o.total_price) AS total_sales_amount,
                COUNT(o.order_id) AS number_of_orders
            FROM orders o FORCE INDEX (idx_covering_orders_report)
            JOIN stores s ON s.store_id = o.store_storeId
            WHERE o.order_date BETWEEN ? AND ?
            GROUP BY order_year, order_month, s.region_id
            ORDER BY order_year DESC, order_month DESC, s.region_id LIMIT ".$offset.", ".$perPage.";
        ";
       
        $result = $this->db->query($query, [$start, $end])->fetchAll();

        return $this->response->json(
            ['msg' => empty($result) ? 'No Data Found' : 'Data Found', 'data' => $result],
            200
        );
    }

    #[Route(route: '/top-categories-by-store', name: 'top.categories.store', methods: 'GET')]
    public function topCategoriesByStore(): ResponseInterface
    {
        $start = $this->input->query->get('start_date') ?? date('Y-m-01', strtotime('-3 months'));
        $end = $this->input->query->get('end_date') ?? date('Y-m-d');

        //         // old query
        //         $query = "
        //             SELECT
        //     o.store_id,
        //     p.category_id,
        //     SUM(o.unit_price * o.quantity) AS total_sales_amount,
        //     RANK() OVER (PARTITION BY o.store_id ORDER BY SUM(o.unit_price * o.quantity) DESC) AS rank_within_store,
        //     o.order_date
        // FROM orders o
        // JOIN products p ON p.product_id = o.product_productId
        //  WHERE o.order_date BETWEEN ? AND ?
        // GROUP BY o.store_id, p.category_id
        // ORDER BY o.store_id, rank_within_store;
        //         ";

        // Pagination
        $page = max(1, (int)($this->input->query->get('page') ?? 1));
        $perPage = 1000;
        $offset = ($page - 1) * $perPage;

        // optimized query
        $query = "
            WITH aggregated AS (
    SELECT
        o.store_id,
        p.category_id,
        SUM(o.total_price) AS total_sales_amount
    FROM orders o FORCE INDEX (idx_orders_report_top_categories)
    JOIN products p FORCE INDEX (idx_products_category_product)
      ON p.product_id = o.product_productId
    WHERE o.order_date BETWEEN ? AND ?
    GROUP BY o.store_id, p.category_id
)
SELECT
    store_id,
    category_id,
    total_sales_amount,
    RANK() OVER (PARTITION BY store_id ORDER BY total_sales_amount DESC) AS rank_within_store
FROM aggregated
ORDER BY store_id, rank_within_store  LIMIT ".$offset.", ".$perPage.";
";
       
        $result = $this->db->query($query, [$start, $end])->fetchAll();
        return $this->response->json(
            ['msg' => empty($result) ? 'No Data Found' : 'Data Found', 'data' => $result],
            200
        );
    }
}
