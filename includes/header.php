<?php
/**
 * LIBAAS CO. — Shared Header Include
 * Included by all public-facing pages
 */

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/db.php';
}

// Cart count for badge
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Current page for nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' — ' . SITE_NAME : SITE_NAME . ' — Premium Clothing' ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand navbar-brand-custom" href="<?= base_url('public/index.php') ?>">
            LIBAAS <span>CO.</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" href="<?= base_url('public/index.php') ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'products.php' && (!isset($_GET['category']) || $_GET['category'] === '')) ? 'active' : '' ?>" href="<?= base_url('public/products.php') ?>">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'products.php' && isset($_GET['category']) && $_GET['category'] === 'Men') ? 'active' : '' ?>" href="<?= base_url('public/products.php?category=Men') ?>">Men</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'products.php' && isset($_GET['category']) && $_GET['category'] === 'Women') ? 'active' : '' ?>" href="<?= base_url('public/products.php?category=Women') ?>">Women</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'products.php' && isset($_GET['category']) && $_GET['category'] === 'Kids') ? 'active' : '' ?>" href="<?= base_url('public/products.php?category=Kids') ?>">Kids</a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item cart-badge">
                    <a class="nav-link" href="<?= base_url('public/cart.php') ?>">
                        <i class="bi bi-bag"></i> Cart
                        <span class="badge cart-count" style="<?= $cart_count > 0 ? '' : 'display:none' ?>"><?= $cart_count ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
