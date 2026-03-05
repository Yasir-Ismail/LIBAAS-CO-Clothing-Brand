<?php
/**
 * LIBAAS CO. — Cart Page
 */
require_once __DIR__ . '/../config/db.php';

$page_title = 'Shopping Cart';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$subtotal = 0;

// Recalculate prices from DB for security (prevent price tampering)
foreach ($cart as $key => &$item) {
    $stmt = $pdo->prepare("SELECT price, name, active FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $prod = $stmt->fetch();

    if (!$prod || !$prod['active']) {
        // Remove invalid products from cart
        unset($_SESSION['cart'][$key]);
        continue;
    }

    // Refresh price from DB
    $item['price'] = $prod['price'];
    $item['name'] = $prod['name'];
    $_SESSION['cart'][$key]['price'] = $prod['price'];
    $_SESSION['cart'][$key]['name'] = $prod['name'];

    // Refresh stock info
    $stmt2 = $pdo->prepare("SELECT stock_quantity FROM product_sizes WHERE product_id = ? AND size = ?");
    $stmt2->execute([$item['product_id'], $item['size']]);
    $sizeRow = $stmt2->fetch();
    if ($sizeRow) {
        $item['max_stock'] = $sizeRow['stock_quantity'];
        $_SESSION['cart'][$key]['max_stock'] = $sizeRow['stock_quantity'];
        // Cap quantity to available stock
        if ($item['quantity'] > $sizeRow['stock_quantity']) {
            $item['quantity'] = $sizeRow['stock_quantity'];
            $_SESSION['cart'][$key]['quantity'] = $sizeRow['stock_quantity'];
        }
    }

    $subtotal += $item['price'] * $item['quantity'];
}
unset($item);

$cart = $_SESSION['cart'];

// Flash messages
$flash_success = get_flash('success');
$flash_error = get_flash('error');

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('public/index.php') ?>">Home</a></li>
                <li class="breadcrumb-item active">Cart</li>
            </ol>
        </nav>
    </div>
</div>

<section class="cart-section">
    <div class="container">
        <h1 style="font-size:1.8rem;margin-bottom:2rem;">Shopping Cart</h1>

        <?php if ($flash_success): ?>
            <div class="alert-brand success"><i class="bi bi-check-circle"></i> <?= e($flash_success) ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert-brand error"><i class="bi bi-exclamation-circle"></i> <?= e($flash_error) ?></div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="cart-empty">
                <i class="bi bi-bag"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <a href="<?= base_url('public/products.php') ?>" class="btn-brand">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th colspan="2">Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $key => $item): ?>
                                <tr>
                                    <td style="width:100px;">
                                        <div class="cart-item-image">
                                            <?php if ($item['image']): ?>
                                                <img src="<?= base_url('uploads/' . e($item['image'])) ?>" alt="">
                                            <?php else: ?>
                                                <div class="placeholder-img" style="font-size:0.6rem;">IMG</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cart-item-details">
                                            <div class="item-name">
                                                <a href="<?= base_url('public/product.php?id=' . $item['product_id']) ?>">
                                                    <?= e($item['name']) ?>
                                                </a>
                                            </div>
                                            <div class="item-variant">
                                                Size: <?= e($item['size']) ?>
                                                <?php if ($item['color'] && $item['color'] !== 'Default'): ?>
                                                    &middot; Color: <?= e($item['color']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= format_price($item['price']) ?></td>
                                    <td>
                                        <input type="number" 
                                               class="form-control cart-qty-input" 
                                               data-key="<?= e($key) ?>"
                                               data-old-qty="<?= $item['quantity'] ?>"
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               max="<?= $item['max_stock'] ?? 99 ?>"
                                               style="width:70px;text-align:center;">
                                    </td>
                                    <td style="font-weight:600;">
                                        <?= format_price($item['price'] * $item['quantity']) ?>
                                    </td>
                                    <td>
                                        <button class="remove-btn remove-from-cart" data-key="<?= e($key) ?>" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top:1.5rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                        <a href="<?= base_url('public/products.php') ?>" class="btn-brand-dark" style="padding:0.6rem 1.5rem;font-size:0.75rem;">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?= format_price($subtotal) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span style="color:var(--color-success);">
                                <?= $subtotal >= 3000 ? 'Free' : format_price(200) ?>
                            </span>
                        </div>
                        <?php $shipping = $subtotal >= 3000 ? 0 : 200; ?>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?= format_price($subtotal + $shipping) ?></span>
                        </div>
                        <?php if ($subtotal < 3000): ?>
                            <p style="font-size:0.8rem;color:var(--color-text-light);margin-top:0.75rem;">
                                Add <?= format_price(3000 - $subtotal) ?> more for free shipping!
                            </p>
                        <?php endif; ?>
                        <a href="<?= base_url('public/checkout.php') ?>" class="btn-brand w-100" style="margin-top:1rem;text-align:center;">
                            Proceed to Checkout
                        </a>
                        <p style="font-size:0.75rem;color:var(--color-text-light);text-align:center;margin-top:0.75rem;">
                            <i class="bi bi-cash-stack"></i> Cash on Delivery
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
