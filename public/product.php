<?php
/**
 * LIBAAS CO. — Product Detail Page
 * Size-based stock logic, image gallery, add to cart
 */
require_once __DIR__ . '/../config/db.php';

// Validate product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    redirect(base_url('public/products.php'));
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect(base_url('public/products.php'));
}

$page_title = $product['name'];

// Fetch images
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

// Fetch sizes with stock
$stmt = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size, 'S', 'M', 'L', 'XL')");
$stmt->execute([$id]);
$sizes = $stmt->fetchAll();

// Colors — split comma-separated from product
$colors = [];
if (!empty($product['color'])) {
    $colors = array_map('trim', explode(',', $product['color']));
}

// Primary image
$primary_image = null;
foreach ($images as $img) {
    if ($img['is_primary']) {
        $primary_image = $img;
        break;
    }
}
if (!$primary_image && !empty($images)) {
    $primary_image = $images[0];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('public/index.php') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('public/products.php') ?>">Shop</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('public/products.php?category=' . e($product['category'])) ?>"><?= e($product['category']) ?></a></li>
                <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Product Detail -->
<section class="product-detail-section">
    <div class="container">
        <div class="row">
            <!-- Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image">
                        <?php if ($primary_image): ?>
                            <img id="mainProductImage" src="<?= base_url('uploads/' . e($primary_image['image_path'])) ?>" alt="<?= e($product['name']) ?>">
                        <?php else: ?>
                            <div class="placeholder-img" style="height:100%;"><span><?= e($product['name']) ?></span></div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($images) > 1): ?>
                        <div class="thumbnails">
                            <?php foreach ($images as $i => $img): ?>
                                <div class="thumb <?= $i === 0 ? 'active' : '' ?>">
                                    <img src="<?= base_url('uploads/' . e($img['image_path'])) ?>" alt="">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Meta -->
            <div class="col-lg-6">
                <div class="product-meta">
                    <div class="product-category-tag"><?= e($product['category']) ?></div>
                    <h1 class="product-title"><?= e($product['name']) ?></h1>
                    <div class="product-price-large"><?= format_price($product['price']) ?></div>

                    <?php if ($product['description']): ?>
                        <div class="product-description"><?= nl2br(e($product['description'])) ?></div>
                    <?php endif; ?>

                    <form id="addToCartForm" action="<?= base_url('public/cart_action.php') ?>" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="selectedSize" id="selectedSize" value="">
                        <input type="hidden" id="maxQty" value="0">

                        <!-- Size Selector -->
                        <div class="size-selector">
                            <label>Select Size</label>
                            <div class="size-options">
                                <?php foreach ($sizes as $s): ?>
                                    <button type="button" 
                                            class="size-btn <?= $s['stock_quantity'] <= 0 ? 'disabled' : '' ?>"
                                            data-size="<?= e($s['size']) ?>"
                                            data-stock="<?= (int)$s['stock_quantity'] ?>"
                                            <?= $s['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                        <?= e($s['size']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Stock Display -->
                        <div id="stockDisplay" class="stock-info" style="display:none;"></div>

                        <!-- Color Selector -->
                        <?php if (!empty($colors)): ?>
                            <div class="color-selector">
                                <label>Color</label>
                                <select name="color" class="form-select" style="max-width:200px;">
                                    <?php foreach ($colors as $c): ?>
                                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="color" value="Default">
                        <?php endif; ?>

                        <!-- Quantity -->
                        <div class="quantity-selector">
                            <label>Quantity</label>
                            <div class="qty-control">
                                <button type="button" class="qty-minus">−</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="99" readonly>
                                <button type="button" class="qty-plus">+</button>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <button type="submit" id="addToCartBtn" class="btn-brand w-100" disabled>
                            <i class="bi bi-bag-plus"></i> &nbsp;Select a size first
                        </button>
                    </form>

                    <!-- Extra Info -->
                    <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--color-border);">
                        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:0.85rem;color:var(--color-text-light);">
                            <i class="bi bi-truck"></i> Free shipping on orders above Rs. 3,000
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:0.85rem;color:var(--color-text-light);">
                            <i class="bi bi-arrow-repeat"></i> 7-day easy returns
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:0.85rem;color:var(--color-text-light);">
                            <i class="bi bi-cash-stack"></i> Cash on Delivery available
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Extra JS: Update button text when size selected -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sizeBtns = document.querySelectorAll('.size-btn:not(.disabled)');
    const addBtn = document.getElementById('addToCartBtn');
    
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const stock = parseInt(this.dataset.stock);
            if (stock > 0 && addBtn) {
                addBtn.innerHTML = '<i class="bi bi-bag-plus"></i> &nbsp;Add to Cart';
            } else if (addBtn) {
                addBtn.innerHTML = '<i class="bi bi-x-circle"></i> &nbsp;Out of Stock';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
