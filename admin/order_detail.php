<?php
/**
 * LIBAAS CO. — Admin: Order Detail
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Order Detail';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect(base_url('admin/orders.php'));

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    if (in_array($new_status, ['Pending', 'Shipped', 'Delivered', 'Cancelled'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        set_flash('success', 'Order status updated to ' . $new_status);
        redirect(base_url('admin/order_detail.php?id=' . $id));
    }
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) redirect(base_url('admin/orders.php'));

// Fetch items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name,
           (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = oi.product_id AND pi.is_primary = 1 LIMIT 1) AS image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$flash_success = get_flash('success');

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Order #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h1>
    <a href="<?= base_url('admin/orders.php') ?>" style="font-size:0.85rem;color:var(--color-text-light);">← Back to Orders</a>
</div>

<?php if ($flash_success): ?>
    <div class="alert-brand success"><i class="bi bi-check-circle"></i> <?= e($flash_success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Order Info -->
    <div class="col-lg-8">
        <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:1.5rem;box-shadow:var(--shadow-sm);margin-bottom:1.5rem;">
            <h5 style="font-family:var(--font-primary);font-size:0.9rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1rem;">Order Items</h5>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Color</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td style="width:50px;">
                                <div style="width:40px;height:50px;border-radius:4px;overflow:hidden;background:var(--color-bg-alt);">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= base_url('uploads/' . e($item['image'])) ?>" style="width:100%;height:100%;object-fit:cover;">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?= e($item['product_name']) ?></strong></td>
                            <td><?= e($item['size']) ?></td>
                            <td><?= e($item['color'] ?: '-') ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= format_price($item['price']) ?></td>
                            <td style="font-weight:600;"><?= format_price($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status -->
        <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:1.5rem;box-shadow:var(--shadow-sm);margin-bottom:1.5rem;">
            <h5 style="font-family:var(--font-primary);font-size:0.9rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1rem;">Status</h5>

            <div style="margin-bottom:1rem;">
                <span class="status-badge <?= strtolower($order['status']) ?>" style="font-size:0.8rem;padding:0.4rem 1rem;">
                    <?= e($order['status']) ?>
                </span>
            </div>

            <form method="POST">
                <div class="input-group input-group-sm">
                    <select class="form-select" name="status">
                        <?php foreach (['Pending', 'Shipped', 'Delivered', 'Cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-dark btn-sm">Update</button>
                </div>
            </form>
        </div>

        <!-- Customer -->
        <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:1.5rem;box-shadow:var(--shadow-sm);margin-bottom:1.5rem;">
            <h5 style="font-family:var(--font-primary);font-size:0.9rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1rem;">Customer</h5>
            <p style="margin:0;line-height:1.8;font-size:0.9rem;">
                <strong><?= e($order['customer_name']) ?></strong><br>
                <i class="bi bi-telephone"></i> <?= e($order['phone']) ?><br>
                <i class="bi bi-geo-alt"></i> <?= e($order['address']) ?><br>
                <i class="bi bi-building"></i> <?= e($order['city']) ?>
            </p>
        </div>

        <!-- Summary -->
        <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:1.5rem;box-shadow:var(--shadow-sm);">
            <h5 style="font-family:var(--font-primary);font-size:0.9rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1rem;">Summary</h5>
            <div style="display:flex;justify-content:space-between;font-size:0.9rem;margin-bottom:0.5rem;">
                <span>Payment</span>
                <span>COD</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;padding-top:0.75rem;border-top:2px solid var(--color-border);">
                <span>Total</span>
                <span><?= format_price($order['total_amount']) ?></span>
            </div>
            <div style="font-size:0.75rem;color:var(--color-text-light);margin-top:0.5rem;">
                Placed on <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
