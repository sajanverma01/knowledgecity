<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmDefaultBaea8475f53fb941e7457e8d11dbd0ef extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('stores')
        ->addColumn('store_id', 'bigPrimary', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('region_id', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('store_name', 'string', ['nullable' => false, 'defaultValue' => null, 'length' => 200, 'size' => 255])
        ->setPrimaryKeys(['store_id'])
        ->create();
        $this->table('products')
        ->addColumn('product_id', 'bigPrimary', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('category_id', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('product_name', 'string', ['nullable' => false, 'defaultValue' => null, 'length' => 200, 'size' => 255])
        ->setPrimaryKeys(['product_id'])
        ->create();
        $this->table('orders')
        ->addColumn('order_id', 'bigPrimary', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('customer_id', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('product_id', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('quantity', 'integer', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('unit_price', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 10, 'scale' => 2])
        ->addColumn('order_date', 'date', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('store_id', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('store_storeId', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('product_productId', 'bigInteger', ['nullable' => false, 'defaultValue' => null])
        ->addIndex(['store_storeId'], ['name' => 'orders_index_store_storeid_6790d290a19b9', 'unique' => false])
        ->addIndex(['product_productId'], ['name' => 'orders_index_product_productid_6790d290a1b17', 'unique' => false])
        ->addForeignKey(['store_storeId'], 'stores', ['store_id'], [
            'name' => 'orders_store_storeId_fk',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->addForeignKey(['product_productId'], 'products', ['product_id'], [
            'name' => 'orders_product_productId_fk',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['order_id'])
        ->create();
    }

    public function down(): void
    {
        $this->table('orders')->drop();
        $this->table('products')->drop();
        $this->table('stores')->drop();
    }
}
