<?php
/**
 * LIBAAS CO. — Order Confirmation Page
 */
require_once __DIR__ . '/../config/db.php';

$page_title = 'Order Confirmed';

// Get order from session
$order_id = $_SESSION['last_order_id'] ?? 0;

if (!$order_id) {
    redirect(base_url('public/index.php'));
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect(base_url('public/index.php'));
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Clear the session reference
unset($_SESSION['last_order_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<section class="confirmation-section" style="padding:4rem 0 5rem;">
    <div class="container" style="max-width:600px;">
        <div class="check-icon">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 style="font-family:var(--font-heading);font-size:2rem;margin-bottom:0.75rem;">Order Confirmed!</h1>
        <p style="color:var(--color-text-light);margin-bottom:2rem;">
            Thank you for your order. We'll deliver it to your doorstep soon.
        </p>

        <div style="background:var(--color-bg-alt);border-radius:var(--radius-md);padding:2rem;text-align:left;margin-bottom:2rem;">
            <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom:1px solid var(--color-border);">
                <div>
                    <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-light);">Order Number</div>
                    <div style="font-weight:700;font-size:1.2rem;">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-light);">Date</div>
                    <div style="font-weight:600;"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                </div>
            </div>

            <h6 style="font-family:var(--font-primary);font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1rem;">Items Ordered</h6>

            <?php foreach ($items as $item): ?>
                <div class="d-flex justify-content-between mb-2" style="font-size:0.9rem;">
                    <div>
                        <?= e($item['product_name']) ?>
                        <span style="color:var(--color-text-light);font-size:0.8rem;">
                            (<?= e($item['size']) ?>) × <?= $item['quantity'] ?>
                        </span>
                    </div>
                    <div style="font-weight:600;"><?= format_price($item['price'] * $item['quantity']) ?></div>
                </div>
            <?php endforeach; ?>

            <div style="display:flex;justify-content:space-between;padding-top:1rem;border-top:2px solid var(--color-border);margin-top:1rem;font-weight:700;font-size:1.1rem;">
                <span>Total</span>
                <span><?= format_price($order['total_amount']) ?></span>
            </div>

            <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--color-border);">
                <h6 style="font-family:var(--font-primary);font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;">Delivery Details</h6>
                <p style="font-size:0.9rem;margin:0;line-height:1.8;">
                    <strong><?= e($order['customer_name']) ?></strong><br>
                    <?= e($order['phone']) ?><br>
                    <?= e($order['address']) ?><br>
                    <?= e($order['city']) ?>
                </p>
            </div>

            <div style="margin-top:1rem;padding:0.75rem 1rem;background:rgba(201,169,110,0.1);border-radius:var(--radius-sm);font-size:0.85rem;">
                <i class="bi bi-cash-stack" style="color:var(--color-accent);"></i> 
                <strong>Payment:</strong> Cash on Delivery — <?= format_price($order['total_amount']) ?>
            </div>
        </div>

        <a href="<?= base_url('public/products.php') ?>" class="btn-brand">Continue Shopping</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
