<?php
/**
 * LIBAAS CO. — Homepage
 */
require_once __DIR__ . '/../config/db.php';

$page_title = 'Home';

// Featured products (latest 8 featured items)
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image
    FROM products p
    WHERE p.active = 1 AND p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 8
");
$stmt->execute();
$featured = $stmt->fetchAll();

// If no featured, get latest 8
if (empty($featured)) {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image
        FROM products p
        WHERE p.active = 1
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $stmt->execute();
    $featured = $stmt->fetchAll();
}

// Category counts
$catCounts = $pdo->query("SELECT category, COUNT(*) as cnt FROM products WHERE active = 1 GROUP BY category")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <div class="hero-label">Spring / Summer 2026</div>
        <h1 class="hero-title">Define Your <span>Style</span></h1>
        <p class="hero-desc">
            Premium clothing crafted for the modern individual. Discover pieces that speak to who you are.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= base_url('public/products.php') ?>" class="btn-brand">Shop Now</a>
            <a href="<?= base_url('public/products.php?category=Men') ?>" class="btn-brand-outline">Men's Collection</a>
        </div>
    </div>
</section>

<!-- Category Section -->
<section class="category-section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <p class="section-subtitle">Find your perfect style</p>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a href="<?= base_url('public/products.php?category=Men') ?>" class="category-card d-block">
                    <div class="placeholder-img" style="position:absolute;top:0;left:0;width:100%;height:100%;">
                        <span>Men's Collection</span>
                    </div>
                    <div class="overlay"></div>
                    <div class="card-body">
                        <h3 class="card-title">Men</h3>
                        <span class="card-link">Shop Collection →</span>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="<?= base_url('public/products.php?category=Women') ?>" class="category-card d-block">
                    <div class="placeholder-img" style="position:absolute;top:0;left:0;width:100%;height:100%;">
                        <span>Women's Collection</span>
                    </div>
                    <div class="overlay"></div>
                    <div class="card-body">
                        <h3 class="card-title">Women</h3>
                        <span class="card-link">Shop Collection →</span>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-12">
                <a href="<?= base_url('public/products.php?category=Kids') ?>" class="category-card d-block">
                    <div class="placeholder-img" style="position:absolute;top:0;left:0;width:100%;height:100%;">
                        <span>Kids' Collection</span>
                    </div>
                    <div class="overlay"></div>
                    <div class="card-body">
                        <h3 class="card-title">Kids</h3>
                        <span class="card-link">Shop Collection →</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-section">
    <div class="container">
        <h2 class="section-title">Featured Collection</h2>
        <p class="section-subtitle">Handpicked for you</p>

        <?php if (empty($featured)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No products yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
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

            <div class="text-center mt-5">
                <a href="<?= base_url('public/products.php') ?>" class="btn-brand-dark">View All Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Brand Promise -->
<section style="padding:4rem 0;background:var(--color-bg);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <i class="bi bi-truck" style="font-size:2rem;color:var(--color-accent);"></i>
                <h6 style="margin-top:0.75rem;font-size:0.85rem;font-family:var(--font-primary);font-weight:600;">Free Shipping</h6>
                <p style="font-size:0.8rem;color:var(--color-text-light);margin:0;">On orders above Rs. 3,000</p>
            </div>
            <div class="col-md-3 col-6">
                <i class="bi bi-shield-check" style="font-size:2rem;color:var(--color-accent);"></i>
                <h6 style="margin-top:0.75rem;font-size:0.85rem;font-family:var(--font-primary);font-weight:600;">Quality Assured</h6>
                <p style="font-size:0.8rem;color:var(--color-text-light);margin:0;">Premium fabrics only</p>
            </div>
            <div class="col-md-3 col-6">
                <i class="bi bi-arrow-repeat" style="font-size:2rem;color:var(--color-accent);"></i>
                <h6 style="margin-top:0.75rem;font-size:0.85rem;font-family:var(--font-primary);font-weight:600;">Easy Returns</h6>
                <p style="font-size:0.8rem;color:var(--color-text-light);margin:0;">7-day return policy</p>
            </div>
            <div class="col-md-3 col-6">
                <i class="bi bi-cash-stack" style="font-size:2rem;color:var(--color-accent);"></i>
                <h6 style="margin-top:0.75rem;font-size:0.85rem;font-family:var(--font-primary);font-weight:600;">COD Available</h6>
                <p style="font-size:0.8rem;color:var(--color-text-light);margin:0;">Cash on delivery</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
