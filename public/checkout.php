<?php
/**
 * LIBAAS CO. — Checkout Page (COD Only)
 */
require_once __DIR__ . '/../config/db.php';

$page_title = 'Checkout';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    set_flash('error', 'Your cart is empty.');
    redirect(base_url('public/cart.php'));
}

$cart = $_SESSION['cart'];
$errors = [];

// ── PROCESS ORDER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['customer_name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');

    // Validation
    if (empty($name))    $errors[] = 'Name is required.';
    if (empty($phone))   $errors[] = 'Phone number is required.';
    if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Invalid phone number.';
    if (empty($address)) $errors[] = 'Address is required.';
    if (empty($city))    $errors[] = 'City is required.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Re-validate cart & calculate total
            $total = 0;
            $order_items = [];

            foreach ($cart as $key => $item) {
                // Re-check product and stock
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch();

                if (!$product) {
                    throw new Exception("Product '{$item['name']}' is no longer available.");
                }

                // Check stock with row lock
                $stmt = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ? AND size = ? FOR UPDATE");
                $stmt->execute([$item['product_id'], $item['size']]);
                $sizeRow = $stmt->fetch();

                if (!$sizeRow || $sizeRow['stock_quantity'] < $item['quantity']) {
                    $avail = $sizeRow ? $sizeRow['stock_quantity'] : 0;
                    throw new Exception("Not enough stock for '{$item['name']}' (Size: {$item['size']}). Only $avail left.");
                }

                $line_total = $product['price'] * $item['quantity'];
                $total += $line_total;

                $order_items[] = [
                    'product_id' => $item['product_id'],
                    'size'       => $item['size'],
                    'color'      => $item['color'],
                    'quantity'   => $item['quantity'],
                    'price'      => $product['price'],
                ];
            }

            // Add shipping
            $shipping = $total >= 3000 ? 0 : 200;
            $grand_total = $total + $shipping;

            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_name, phone, address, city, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')
            ");
            $stmt->execute([$name, $phone, $address, $city, $grand_total]);
            $order_id = $pdo->lastInsertId();

            // Insert order items & reduce stock
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, size, color, quantity, price) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stockStmt = $pdo->prepare("
                UPDATE product_sizes SET stock_quantity = stock_quantity - ? 
                WHERE product_id = ? AND size = ? AND stock_quantity >= ?
            ");

            foreach ($order_items as $oi) {
                $itemStmt->execute([
                    $order_id,
                    $oi['product_id'],
                    $oi['size'],
                    $oi['color'],
                    $oi['quantity'],
                    $oi['price'],
                ]);

                $stockStmt->execute([
                    $oi['quantity'],
                    $oi['product_id'],
                    $oi['size'],
                    $oi['quantity'],
                ]);

                // Verify stock was actually reduced
                if ($stockStmt->rowCount() === 0) {
                    throw new Exception("Stock update failed for product ID {$oi['product_id']}, size {$oi['size']}.");
                }
            }

            $pdo->commit();

            // Clear cart
            $_SESSION['cart'] = [];

            // Store order ID for confirmation page
            $_SESSION['last_order_id'] = $order_id;

            redirect(base_url('public/confirmation.php'));

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

// Recalculate totals for display
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 3000 ? 0 : 200;
$grand_total = $subtotal + $shipping;

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('public/index.php') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('public/cart.php') ?>">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
    </div>
</div>

<section class="checkout-section">
    <div class="container">
        <h1 style="font-size:1.8rem;margin-bottom:2rem;">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert-brand error">
                <i class="bi bi-exclamation-circle"></i>
                <ul style="margin:0.5rem 0 0;padding-left:1.2rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="checkoutForm" method="POST" class="checkout-form">
            <div class="row g-4">
                <!-- Customer Details -->
                <div class="col-lg-7">
                    <h5 style="font-family:var(--font-primary);font-size:1rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1.5rem;">
                        Delivery Details
                    </h5>

                    <div class="mb-3">
                        <label class="form-label" for="customer_name">Full Name *</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" 
                               value="<?= e($_POST['customer_name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= e($_POST['phone'] ?? '') ?>" required
                               placeholder="03XX-XXXXXXX">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="city">City *</label>
                        <input type="text" class="form-control" id="city" name="city" 
                               value="<?= e($_POST['city'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="address">Full Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?= e($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div style="background:rgba(201,169,110,0.1);padding:1rem;border-radius:var(--radius-sm);margin-top:1rem;">
                        <div class="d-flex align-items-center gap-2" style="font-size:0.9rem;font-weight:600;">
                            <i class="bi bi-cash-stack" style="color:var(--color-accent);"></i>
                            Cash on Delivery
                        </div>
                        <p style="font-size:0.8rem;color:var(--color-text-light);margin:0.5rem 0 0;">
                            Pay when your order arrives at your doorstep.
                        </p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-5">
                    <div class="order-summary-card">
                        <h5 style="font-family:var(--font-primary);font-size:1rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1.5rem;">
                            Order Summary
                        </h5>

                        <?php foreach ($cart as $item): ?>
                            <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--color-border);">
                                <div style="width:60px;height:75px;border-radius:var(--radius-sm);overflow:hidden;background:var(--color-border);flex-shrink:0;">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= base_url('uploads/' . e($item['image'])) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        <?= e($item['name']) ?>
                                    </div>
                                    <div style="font-size:0.8rem;color:var(--color-text-light);">
                                        Size: <?= e($item['size']) ?> &middot; Qty: <?= $item['quantity'] ?>
                                    </div>
                                </div>
                                <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;">
                                    <?= format_price($item['price'] * $item['quantity']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="summary-row" style="display:flex;justify-content:space-between;margin-bottom:0.5rem;font-size:0.9rem;">
                            <span>Subtotal</span>
                            <span><?= format_price($subtotal) ?></span>
                        </div>
                        <div class="summary-row" style="display:flex;justify-content:space-between;margin-bottom:0.5rem;font-size:0.9rem;">
                            <span>Shipping</span>
                            <span style="color:<?= $shipping === 0 ? 'var(--color-success)' : 'inherit' ?>;">
                                <?= $shipping === 0 ? 'Free' : format_price($shipping) ?>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding-top:1rem;border-top:2px solid var(--color-border);margin-top:0.5rem;font-weight:700;font-size:1.1rem;">
                            <span>Total</span>
                            <span><?= format_price($grand_total) ?></span>
                        </div>

                        <button type="submit" class="btn-brand w-100" style="margin-top:1.5rem;text-align:center;">
                            Place Order — <?= format_price($grand_total) ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
