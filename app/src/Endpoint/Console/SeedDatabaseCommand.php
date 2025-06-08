<?php

declare(strict_types=1);

namespace App\Endpoint\Console;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Cycle\Database\Database;

#[AsCommand(name: 'seed:database')]
final class SeedDatabaseCommand extends Command
{
    public function __invoke(): int
    {
        /** @var Database $db */
        $db = $this->container->get(Database::class);

        $stores = $db->table('stores');
        $products = $db->table('products');
        $orders = $db->table('orders');

        $regionCount = 100;
        $storesPerRegion = 10;
        $categories = 100;
        $productsPerCategory = 10;
        $totalOrders = 1000000;

        $this->writeln("Dropping indexes for performance...");
        $db->query('SET FOREIGN_KEY_CHECKS = 0');    
        $db->query('ALTER TABLE orders DROP INDEX IF EXISTS orders_index_store_storeid_684518fd0c342');
        $db->query('ALTER TABLE orders DROP INDEX IF EXISTS orders_index_product_productid_684518fd0c26f');
        $db->query('ALTER TABLE orders DROP INDEX IF EXISTS idx_covering_orders_report');
        $db->query('ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_report_top_categories');
        $db->query('ALTER TABLE products DROP INDEX IF EXISTS idx_products_category_product');
        // Seed stores
        $this->writeln("Seeding stores...");
        $storeId = 1;
        for ($r = 1; $r <= $regionCount; $r++) {
            for ($s = 0; $s < $storesPerRegion; $s++) {
                $stores->insert()->values([
                    'store_id' => $storeId,
                    'region_id' => $r,
                    'store_name' => "Store_$storeId"
                ])->run();
                $storeId++;
            }
        }

        // Seed products
        $this->writeln("Seeding products...");
        $productId = 1;
        for ($c = 1; $c <= $categories; $c++) {
            for ($p = 0; $p < $productsPerCategory; $p++) {
                $products->insert()->values([
                    'product_id' => $productId,
                    'category_id' => $c,
                    'product_name' => "Product_$productId"
                ])->run();
                $productId++;
            }
        }

        // Seed orders
        $this->writeln("Seeding orders (~1M rows)... this may take a while.");
        $batchSize = 1000;

        for ($i = 1; $i <= $totalOrders; $i++) {
            $productId = rand(1, $categories * $productsPerCategory);
            $storeId = rand(1, $regionCount * $storesPerRegion);
            $orderDate = date('Y-m-d', strtotime('-' . rand(0, 730) . ' days'));
            $unitPrice =number_format(rand(100, 10000) / 100, 2, '.', '');
            $qty = rand(1, 10);
            $orders->insert()->values([
                'customer_id' => rand(1, 100000),
                'product_id' => $productId,
                'quantity' =>$qty ,
                'unit_price' =>$unitPrice,
                'total_price' => $unitPrice * $qty,
                'order_date' => $orderDate,
                'order_year' => date('Y',strtotime($orderDate)),
                'order_month' => (int) date('m',strtotime($orderDate)),
                'store_id' => $storeId,
                'store_storeId' => $storeId,
                'product_productId' => $productId
            ])->run();

            if ($i % 10000 === 0) {
                $this->writeln("Inserted: $i");
            }
        }

        // Recreate indexes
        $this->writeln("Recreating indexes...");
        $db->query('CREATE INDEX orders_index_store_storeid_684518fd0c342 ON orders (store_storeId)');
        $db->query('CREATE INDEX orders_index_product_productid_684518fd0c26f ON orders (product_productId)');
        $db->query('CREATE INDEX idx_covering_orders_report ON orders (order_year,order_month,store_storeId,total_price,order_id)');
        $db->query('CREATE INDEX idx_orders_report_top_categories ON orders (order_date,store_id,product_productId,total_price)');
        $db->query('CREATE INDEX idx_products_category_product ON products (product_id,category_id)');
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
        $this->writeln('✅ Database seeding complete.');

        return self::SUCCESS;
    }
}
