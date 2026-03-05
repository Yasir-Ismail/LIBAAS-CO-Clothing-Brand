<?php
/**
 * LIBAAS CO. — Product Listing Page
 * Supports category filtering and search
 */
require_once __DIR__ . '/../config/db.php';

// Filter params
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$page_title = $category ?: 'All Products';

// Build query
$where = ['p.active = 1'];
$params = [];

if ($category && in_array($category, ['Men', 'Women', 'Kids'])) {
    $where[] = 'p.category = ?';
    $params[] = $category;
}

if ($search) {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image
    FROM products p
    WHERE $whereClause
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?= $category ? e($category) . "'s Collection" : 'All Products' ?></h1>
        <p><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> found</p>
    </div>
</div>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('public/index.php') ?>">Home</a></li>
                <?php if ($category): ?>
                    <li class="breadcrumb-item"><a href="<?= base_url('public/products.php') ?>">Shop</a></li>
                    <li class="breadcrumb-item active"><?= e($category) ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item active">Shop</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<div class="container" style="padding:2rem 0 5rem;">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-pills">
            <a href="<?= base_url('public/products.php') ?>" class="filter-pill <?= !$category ? 'active' : '' ?>">All</a>
            <a href="<?= base_url('public/products.php?category=Men') ?>" class="filter-pill <?= $category === 'Men' ? 'active' : '' ?>">Men</a>
            <a href="<?= base_url('public/products.php?category=Women') ?>" class="filter-pill <?= $category === 'Women' ? 'active' : '' ?>">Women</a>
            <a href="<?= base_url('public/products.php?category=Kids') ?>" class="filter-pill <?= $category === 'Kids' ? 'active' : '' ?>">Kids</a>
        </div>
        <form action="" method="GET" class="d-flex gap-2" style="max-width:300px;">
            <?php if ($category): ?>
                <input type="hidden" name="category" value="<?= e($category) ?>">
            <?php endif; ?>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search..." value="<?= e($search) ?>" style="border-radius:50px;padding:0.4rem 1rem;font-size:0.85rem;">
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search" style="font-size:3rem;color:var(--color-border);"></i>
            <h3 style="margin-top:1rem;">No products found</h3>
            <p class="text-muted">Try a different category or search term.</p>
            <a href="<?= base_url('public/products.php') ?>" class="btn-brand-dark" style="margin-top:1rem;">View All Products</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?= base_url('uploads/' . e($product['image'])) ?>" alt="<?= e($product['name']) ?>">
                        <?php else: ?>
                            <div class="placeholder-img"><span><?= e($product['name']) ?></span></div>
                        <?php endif; ?>
                        <div class="product-actions">
                            <a href="<?= base_url('public/product.php?id=' . $product['id']) ?>" class="btn btn-dark btn-sm">View Details</a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?= e($product['category']) ?></div>
                        <div class="product-name">
                            <a href="<?= base_url('public/product.php?id=' . $product['id']) ?>">
                                <?= e($product['name']) ?>
                            </a>
                        </div>
                        <div class="product-price"><?= format_price($product['price']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
