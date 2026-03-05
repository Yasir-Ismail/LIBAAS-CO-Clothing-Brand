# LIBAAS CO. — Premium Clothing Brand eCommerce

A full-featured clothing brand eCommerce system built with Core PHP, MySQL, Bootstrap 5, and vanilla JavaScript. No frameworks — just clean, professional code that runs on XAMPP/WAMP.

![PHP](https://img.shields.io/badge/PHP-Core-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap)

---

## Features

### Customer-Facing Store
- **Homepage** — Hero banner, featured products, category navigation
- **Product Listing** — Filter by Men/Women/Kids, search functionality
- **Product Detail** — Image gallery, size selector with real stock, color options
- **Cart System** — Session-based, AJAX add/remove/update, stock validation
- **Checkout** — COD only, full validation, transactional order processing
- **Order Confirmation** — Clean summary with order details

### Size-Based Stock (Core Logic)
- Each product has independent stock per size (S/M/L/XL)
- Sizes with 0 stock are disabled and cannot be added to cart
- Cart prevents exceeding available stock
- Checkout reduces stock atomically with row-level locking
- Real-time stock display on product detail page

### Admin Panel
- **Dashboard** — Stats (products, orders, revenue, stock alerts)
- **Product Management** — Add/edit/delete products, images, sizes, stock
- **Order Management** — View orders, update status (Pending/Shipped/Delivered/Cancelled)
- **Stock Management** — Inline stock editing, low-stock & out-of-stock filters

### Security
- PDO prepared statements (no SQL injection)
- Session-based authentication for admin
- Input validation & output escaping
- CSRF-safe form handling
- Upload security (type/size validation, PHP execution prevention)
- Negative stock prevention

---

## Tech Stack

| Layer     | Tech                    |
|-----------|------------------------|
| Frontend  | HTML5, CSS3, Bootstrap 5, Vanilla JS |
| Backend   | Core PHP (no framework) |
| Database  | MySQL (InnoDB, FK constraints) |
| Server    | XAMPP / WAMP            |

---

## Installation

### 1. Clone / Copy to web server root

```bash
# Copy to XAMPP htdocs
# Rename folder to 'clothing-brand'
C:\xampp\htdocs\clothing-brand\
```

### 2. Start XAMPP
- Start **Apache** and **MySQL** from XAMPP Control Panel

### 3. Run the installer
Open your browser and visit:
```
http://localhost/clothing-brand/database/seed.php
```

This will automatically:
- Create the `libaas_co` database
- Create all tables with proper relationships
- Create admin user
- Seed 12 sample products with sizes and stock
- Create the uploads directory

### 4. Access the site

| Page          | URL                                                   |
|---------------|-------------------------------------------------------|
| Store Home    | `http://localhost/clothing-brand/public/index.php`     |
| Admin Login   | `http://localhost/clothing-brand/admin/login.php`      |

### Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`

---

## Folder Structure

```
clothing-brand/
├── config/
│   └── db.php              # Database connection + helpers
├── public/
│   ├── index.php           # Homepage
│   ├── products.php        # Product listing
│   ├── product.php         # Product detail
│   ├── cart.php            # Cart page
│   ├── cart_action.php     # Cart AJAX handler
│   ├── checkout.php        # Checkout (COD)
│   └── confirmation.php    # Order confirmation
├── admin/
│   ├── login.php           # Admin login
│   ├── logout.php          # Admin logout
│   ├── dashboard.php       # Dashboard with stats
│   ├── products.php        # Product list
│   ├── product_add.php     # Add product
│   ├── product_edit.php    # Edit product
│   ├── orders.php          # Order list
│   ├── order_detail.php    # Order detail + status update
│   ├── stock.php           # Stock management
│   ├── admin_header.php    # Admin layout header
│   └── admin_footer.php    # Admin layout footer
├── includes/
│   ├── header.php          # Store header + navbar
│   └── footer.php          # Store footer
├── assets/
│   ├── css/style.css       # All custom styles
│   └── js/app.js           # Frontend JavaScript
├── uploads/                # Product images
├── database/
│   ├── schema.sql          # Raw SQL schema
│   └── seed.php            # Auto-installer
├── .htaccess               # Root redirect
└── README.md
```

## Database Schema

```
products ─── product_images (1:N)
         ─── product_sizes  (1:N, unique per size)
         ─── order_items    (1:N via orders)

orders   ─── order_items    (1:N)
```

**Tables**: `products`, `product_images`, `product_sizes`, `orders`, `order_items`, `admin_users`

---

## License

This project is for educational and portfolio purposes.