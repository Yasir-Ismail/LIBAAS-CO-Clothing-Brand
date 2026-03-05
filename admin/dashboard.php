<?php
/**
 * LIBAAS CO. — Admin Dashboard
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Dashboard';

// Stats
$total_products = $pdo->query("SELECT COUNT(*) FROM products WHERE active = 1")->fetchColumn();
$total_orders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$total_revenue  = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$low_stock      = $pdo->query("SELECT COUNT(*) FROM product_sizes WHERE stock_quantity > 0 AND stock_quantity <= 5")->fetchColumn();
$out_of_stock   = $pdo->query("SELECT COUNT(*) FROM product_sizes WHERE stock_quantity = 0")->fetchColumn();

// Recent orders
$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Dashboard</h1>
    <div style="font-size:0.85rem;color:var(--color-text-light);">
        Welcome, <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $total_products ?></div>
            <div class="stat-label">Products</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $total_orders ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-value" style="color:var(--color-warning);"><?= $pending_orders ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= format_price($total_revenue) ?></div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
    <div class="col-lg-1 col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-value" style="color:var(--color-warning);"><?= $low_stock ?></div>
            <div class="stat-label">Low Stock</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="stat-card">
            <div class="stat-value" style="color:var(--color-danger);"><?= $out_of_stock ?></div>
            <div class="stat-label">Out of Stock</div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div style="background:var(--color-bg);border-radius:var(--radius-md);padding:1.5rem;box-shadow:var(--shadow-sm);">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="font-family:var(--font-primary);font-size:1rem;font-weight:600;margin:0;">Recent Orders</h5>
        <a href="<?= base_url('admin/orders.php') ?>" style="font-size:0.8rem;color:var(--color-accent);font-weight:500;">View All →</a>
    </div>

    <?php if (empty($recent_orders)): ?>
        <p style="color:var(--color-text-light);font-size:0.9rem;">No orders yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>City</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><strong>#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= e($order['city']) ?></td>
                            <td><?= format_price($order['total_amount']) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($order['status']) ?>">
                                    <?= e($order['status']) ?>
                                </span>
                            </td>
                            <td style="font-size:0.85rem;color:var(--color-text-light);">
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/order_detail.php?id=' . $order['id']) ?>" 
                                   style="font-size:0.8rem;color:var(--color-accent);">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
