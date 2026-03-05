-- ============================================
-- LIBAAS CO. — Clothing Brand Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS libaas_co;
USE libaas_co;

-- --------------------------
-- Products Table
-- --------------------------
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255)    NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)   NOT NULL,
    category    ENUM('Men','Women','Kids') NOT NULL DEFAULT 'Men',
    color       VARCHAR(100)    DEFAULT NULL,
    featured    TINYINT(1)      NOT NULL DEFAULT 0,
    active      TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_featured (featured),
    INDEX idx_active   (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------
-- Product Images
-- --------------------------
CREATE TABLE product_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    product_id  INT             NOT NULL,
    image_path  VARCHAR(500)    NOT NULL,
    is_primary  TINYINT(1)      NOT NULL DEFAULT 0,
    sort_order  INT             NOT NULL DEFAULT 0,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------
-- Product Sizes & Stock
-- --------------------------
CREATE TABLE product_sizes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    product_id      INT             NOT NULL,
    size            ENUM('S','M','L','XL') NOT NULL,
    stock_quantity  INT             NOT NULL DEFAULT 0,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_product_size (product_id, size),
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------
-- Orders
-- --------------------------
CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_name   VARCHAR(255)    NOT NULL,
    phone           VARCHAR(20)     NOT NULL,
    address         TEXT            NOT NULL,
    city            VARCHAR(100)    NOT NULL,
    total_amount    DECIMAL(10,2)   NOT NULL,
    status          ENUM('Pending','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status     (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------
-- Order Items
-- --------------------------
CREATE TABLE order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT             NOT NULL,
    product_id  INT             NOT NULL,
    size        ENUM('S','M','L','XL') NOT NULL,
    color       VARCHAR(100)    DEFAULT NULL,
    quantity    INT             NOT NULL,
    price       DECIMAL(10,2)   NOT NULL,

    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE RESTRICT,
    INDEX idx_order   (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------
-- Admin Users
-- --------------------------
CREATE TABLE admin_users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------
-- Default Admin (password: admin123)
-- --------------------------
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQDr.pियush8K1p0dL1LXMIgoEDFrwO');
-- Note: run the seed.php to create the proper bcrypt hash
