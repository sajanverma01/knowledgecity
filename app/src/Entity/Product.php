<?php

declare(strict_types=1);

namespace App\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity(table: 'products')]
#[Index(columns: ['product_id', 'category_id'], name: 'idx_products_category_product')]
class Product
{
    #[Column(type: 'bigPrimary')]
    private int $productId;
    
    #[Column(type: 'bigInteger')]
    private int $categoryId;
    
    #[Column(type: 'string', length: 200)]
    private string $productName;
    
    #[HasMany(target: Order::class)]
    private array $orders;
}