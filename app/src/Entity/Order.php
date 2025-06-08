<?php

declare(strict_types=1);

namespace App\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity(table: 'orders')]
#[Index(columns: ['order_year', 'order_month', 'store_storeId','total_price','order_id'], name: 'idx_covering_orders_report')]
#[Index(columns: ['order_date', 'store_id', 'product_productId','total_price'], name: 'idx_orders_report_top_categories')]


class Order
{
    #[Column(type: 'bigPrimary')]
    public int $orderId;
    
    #[Column(type: 'bigInteger')]
    public int $customerId;
    
    #[Column(type: 'bigInteger')]
    public int $productId;
    
    #[Column(type: 'integer')]
    public int $quantity;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public float $unitPrice;

    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public float $totalPrice;
    
    #[Column(type: 'date')]
    public \DateTimeInterface $orderDate;
    
    #[Column(type: 'bigInteger')]
    public int $storeId;

    #[Column(type: 'integer', typecast: 'int', name: 'order_year', custom: [
        'generated' => 'ALWAYS',
        'as' => '(YEAR(order_date))',
        'stored' => true
    ])]
    public int $orderYear;

    #[Column(type: 'integer', typecast: 'int', name: 'order_month', custom: [
        'generated' => 'ALWAYS',
        'as' => '(MONTH(order_date))',
        'stored' => true
    ])]
    public int $orderMonth;
    
    #[BelongsTo(target: Store::class, fkAction: 'CASCADE')]
    public Store $store;
    
    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'productId' => $this->productId,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'orderDate' => $this->orderDate->format('Y-m-d'),
            'storeId' => $this->storeId,
        ];
    }
}