# MySQL Report Optimization with Large Data Seeding

## Overview

This project demonstrates the optimization of SQL queries on a dataset of 1 million `orders` using indexing, schema adjustments, and precomputed values. It also includes a custom seeding command to populate the database efficiently while testing performance improvements.

---

## 🛠 Composer Setup

Before starting composer install not getting install due to php version 8.5 configured. php 8.5 version is not exits i have set php version 8.3 to install packages 

---

## 📦 Setup Tables and Seeding Large Data

Custom seeding command for inserting **1 million records**:

```bash
sudo php app.php cycle:migrate 
```
    
```bash
php -d memory_limit=8G app.php seed:database
```

**API URL
```bash
`/monthly-sales-by-region?start_date=2025-01-01&end_date=2025-12-31&page=1`
```

```bash
`/top-categories-by-store?start_date=2025-01-01&end_date=2025-03-30&page=1`
```

**Seed Command File:**
`app/src/Endpoint/Console/SeedDatabaseCommand.php`

Indexes were temporarily removed during insertion to boost performance, then recreated afterward in Data Seeding.

---

## 📊 Report 1: Monthly Sales by Region

### 😌 Initial (Unoptimized) Query

```sql
SELECT
    YEAR(o.order_date) AS order_year,
    MONTH(o.order_date) AS order_month,
    s.region_id,
    SUM(o.unit_price * o.quantity) AS total_sales_amount,
    COUNT(o.order_id) AS number_of_orders
FROM orders o
JOIN stores s ON s.store_id = o.store_storeId
WHERE o.order_date BETWEEN ? AND ?
GROUP BY order_year, order_month, s.region_id
ORDER BY order_year DESC, order_month DESC, s.region_id;
```

### ⚠️ Issues 

Check queries plan using EXPLAIN KEYWORD

* No indexes on `order_date`, `store_storeId`
* `YEAR()` and `MONTH()` and `o.unit_price * o.quantity` calculated at runtime
* Full table scans and filesorts
* No Limit Data using pagination (Pagination is required for send fast api response)

---

### ✅ Schema Changes

* Added columns: `order_year`, `order_month`, `total_price` (precomputed during insertion)
* Index added:

  ```sql
  KEY `idx_covering_orders_report` (`order_year`, `order_month`, `store_storeId`, `total_price`, `order_id`)
  ```

---

### 🚀 Optimized Query

```sql
SELECT
    o.order_year,
    o.order_month,
    s.region_id,
    SUM(o.total_price) AS total_sales_amount,
    COUNT(o.order_id) AS number_of_orders
FROM orders o FORCE INDEX (idx_covering_orders_report)
JOIN stores s ON s.store_id = o.store_storeId
WHERE o.order_date BETWEEN ? AND ?
GROUP BY o.order_year, o.order_month, s.region_id
ORDER BY o.order_year DESC, o.order_month DESC, s.region_id;
```

### ⏱ Performance

| Version      | Execution Time |
| ------------ | -------------- |
| Before Index | 7.19 seconds   |
| After Index  | 431 ms         |

📷 Before: Before Optimization 
(https://prnt.sc/IGCzQFax_InC)

📷 After:  After Optimization 
(https://prnt.sc/QNFNk2DlUy7Y)

---

## 📊 Report 2: Top Categories by Store

### 😌 Initial (Unoptimized) Query

```sql
SELECT
    o.store_id,
    p.category_id,
    SUM(o.unit_price * o.quantity) AS total_sales_amount,
    RANK() OVER (PARTITION BY o.store_id ORDER BY SUM(o.unit_price * o.quantity) DESC) AS rank_within_store,
    o.order_date
FROM orders o
JOIN products p ON p.product_id = o.product_productId
WHERE o.order_date BETWEEN ? AND ?
GROUP BY o.store_id, p.category_id
ORDER BY o.store_id, rank_within_store;
```

### ⚠️ Issues

* Runtime multiplication `unit_price * quantity`
* No indexes on `order_date`, `store_id`, `product_productId`
* Costly window function and GROUP BY with JOIN
* No Limit Data using pagination (Pagination is required for send fast api response)

---

### ✅ Schema Changes

* Added column: `total_price` (precomputed during insertion)
* Indexes added:

  ```sql
  -- On orders table
  KEY `idx_orders_report_top_categories` (`order_date`, `store_id`, `product_productId`, `total_price`),

  -- On products table
  KEY `idx_products_category_product` (`product_id`, `category_id`)
  ```

---

### 🚀 Optimized Query

```sql
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
ORDER BY store_id, rank_within_store;
```

### ⏱ Performance

| Version      | Execution Time |
| ------------ | -------------- |
| Before Index | 7.25 seconds   |
| After Index  | 520 ms         |

📷 Before: Before Optimization   
(https://prnt.sc/XEPkzL9hNbob)

📷 After:  After Optimization 
(https://prnt.sc/-6-wVMeO8qer)

---

## 🧠 Outcome

* Precomputing expensive expressions (like `YEAR()`, `MONTH()`, `unit_price * quantity`) significantly reduces runtime.
* Smart indexing strategies, especially covering indexes, improve performance drastically.
* Avoiding indexes during large bulk inserts helps avoid unnecessary I/O overhead.
* Use `FORCE INDEX` when needed to guide the query planner.
* We have use cache for report result for 24 hours

# We can further improve 

* We can use Redis Cache
* We can use Amazon RDS (seperate service for handle database)
* We can store pre-calculated reports in different tables
* We can generage these report over the night using cron job when traffic is low
* We can create Multiple replica of Database if getting read request getting more on single system
