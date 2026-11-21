-- ТОРГОВЛЯ. MySQL 8.0.16+. Запускать на новой базе.
-- Импорт заменяет php artisan migrate. Данные не добавляются.
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS trade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE trade;

CREATE TABLE departments (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(30) NOT NULL UNIQUE,
 name VARCHAR(255) NOT NULL,
 manager_name VARCHAR(255) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 daily_plan DECIMAL(14,2) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 CONSTRAINT chk_plan CHECK (daily_plan >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 sku VARCHAR(50) NOT NULL UNIQUE,
 name VARCHAR(255) NOT NULL,
 unit VARCHAR(30) NOT NULL,
 retail_price DECIMAL(14,2) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 CONSTRAINT chk_price CHECK (retail_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 department_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NOT NULL,
 sale_date DATE NOT NULL,
 quantity DECIMAL(12,3) NOT NULL,
 unit_price DECIMAL(14,2) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 KEY sales_department_id_sale_date_index (department_id, sale_date),
 KEY sales_product_id_sale_date_index (product_id, sale_date),
 CONSTRAINT sales_department_id_foreign FOREIGN KEY (department_id)
  REFERENCES departments (id) ON DELETE RESTRICT,
 CONSTRAINT sales_product_id_foreign FOREIGN KEY (product_id)
  REFERENCES products (id) ON DELETE RESTRICT,
 CONSTRAINT chk_sale CHECK (quantity > 0 AND unit_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Служебная запись обязательна для транзакций API.
CREATE TABLE trade_locks (
 id INT UNSIGNED NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO trade_locks (id) VALUES (1);

-- Минимум одну продажу у отдела обеспечивает API, а не внешний ключ.
-- Создавайте отдел через POST /api/departments вместе с initial_sale.
-- Прямые изменения SQL могут обойти это правило приложения.