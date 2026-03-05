<?php
/**
 * LIBAAS CO. — Admin Header
 */
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/db.php';
}

require_admin_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' — Admin' : 'Admin Panel' ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <div class="brand">
        LIBAAS CO.
        <small>Admin Panel</small>
    </div>
    <ul class="nav-menu">
        <li>
            <a href="<?= base_url('admin/dashboard.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-grid"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/products.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Products
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/product_add.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'product_add.php' ? 'active' : '' ?>">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/orders.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Orders
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/stock.php') ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'stock.php' ? 'active' : '' ?>">
                <i class="bi bi-clipboard-data"></i> Stock
            </a>
        </li>
        <li>
            <a href="<?= base_url('admin/logout.php') ?>">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </li>
    </ul>
</aside>

<!-- Admin Main Content -->
<main class="admin-main">
