<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmDefault4396c1f30631b217643adef0ff8f960a extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('products')
        ->addColumn('product_id', 'bigPrimary', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => true,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('category_id', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('product_name', 'string', ['nullable' => false, 'defaultValue' => null, 'length' => 200, 'size' => 255])
        ->addIndex(['product_id', 'category_id'], ['name' => 'idx_products_category_product', 'unique' => false])
        ->setPrimaryKeys(['product_id'])
        ->create();
        $this->table('stores')
        ->addColumn('store_id', 'bigPrimary', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => true,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('region_id', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('store_name', 'string', ['nullable' => false, 'defaultValue' => null, 'length' => 200, 'size' => 255])
        ->setPrimaryKeys(['store_id'])
        ->create();
        $this->table('orders')
        ->addColumn('order_id', 'bigPrimary', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => true,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('customer_id', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('product_id', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('quantity', 'integer', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 11,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('unit_price', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 10, 'scale' => 2])
        ->addColumn('total_price', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 10, 'scale' => 2])
        ->addColumn('order_date', 'date', ['nullable' => false, 'defaultValue' => null])
        ->addColumn('store_id', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('order_year', 'integer', [
            'nullable' => false,
            'defaultValue' => null,
            'custom' => ['generated' => 'ALWAYS', 'as' => '(YEAR(order_date))', 'stored' => true],
            'size' => 11,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('order_month', 'integer', [
            'nullable' => false,
            'defaultValue' => null,
            'custom' => ['generated' => 'ALWAYS', 'as' => '(MONTH(order_date))', 'stored' => true],
            'size' => 11,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('product_productId', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('store_storeId', 'bigInteger', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 20,
            'autoIncrement' => false,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addIndex(['product_productId'], ['name' => 'orders_index_product_productid_684518fd0c26f', 'unique' => false])
        ->addIndex(['store_storeId'], ['name' => 'orders_index_store_storeid_684518fd0c342', 'unique' => false])
        ->addIndex(['order_year', 'order_month', 'store_storeId', 'total_price', 'order_id'], [
            'name' => 'idx_covering_orders_report',
            'unique' => false,
        ])
        ->addIndex(['order_date', 'store_id', 'product_productId', 'total_price'], [
            'name' => 'idx_orders_report_top_categories',
            'unique' => false,
        ])
        ->addForeignKey(['product_productId'], 'products', ['product_id'], [
            'name' => 'orders_foreign_product_productid_684518fd0c279',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->addForeignKey(['store_storeId'], 'stores', ['store_id'], [
            'name' => 'orders_foreign_store_storeid_684518fd0c349',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['order_id'])
        ->create();
        $this->table('users')
        ->addColumn('id', 'primary', [
            'nullable' => false,
            'defaultValue' => null,
            'size' => 11,
            'autoIncrement' => true,
            'unsigned' => false,
            'zerofill' => false,
        ])
        ->addColumn('username', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
        ->addColumn('email', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
        ->setPrimaryKeys(['id'])
        ->create();
    }

    public function down(): void
    {
        $this->table('users')->drop();
        $this->table('orders')->drop();
        $this->table('stores')->drop();
        $this->table('products')->drop();
    }
}
