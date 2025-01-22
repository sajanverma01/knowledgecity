<?php

declare(strict_types=1);

namespace App\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(table: 'orders')]
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
    
    #[Column(type: 'date')]
    public \DateTimeInterface $orderDate;
    
    #[Column(type: 'bigInteger')]
    public int $storeId;
    
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