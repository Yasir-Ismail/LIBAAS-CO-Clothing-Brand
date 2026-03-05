<?php
/**
 * LIBAAS CO. — Admin: Orders List
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Orders';

// Filter
$status_filter = $_GET['status'] ?? '';
$where = '';
$params = [];

if ($status_filter && in_array($status_filter, ['Pending', 'Shipped', 'Delivered', 'Cancelled'])) {
    $where = 'WHERE status = ?';
    $params[] = $status_filter;
}

$orders = $pdo->prepare("SELECT * FROM orders $where ORDER BY created_at DESC");
$orders->execute($params);
$orders = $orders->fetchAll();

// Counts
$counts = $pdo->query("
    SELECT status, COUNT(*) as cnt 
    FROM orders 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$total_count = array_sum($counts);

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Orders</h1>
    <div style="font-size:0.85rem;color:var(--color-text-light);"><?= $total_count ?> total orders</div>
</div>

<!-- Status Filters -->
<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="<?= base_url('admin/orders.php') ?>" class="filter-pill <?= !$status_filter ? 'active' : '' ?>">
        All (<?= $total_count ?>)
    </a>
    <?php foreach (['Pending', 'Shipped', 'Delivered', 'Cancelled'] as $s): ?>
        <a href="<?= base_url('admin/orders.php?status=' . $s) ?>" class="filter-pill <?= $status_filter === $s ? 'active' : '' ?>">
            <?= $s ?> (<?= $counts[$s] ?? 0 ?>)
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($orders)): ?>
    <div style="text-align:center;padding:3rem;color:var(--color-text-light);">
        <i class="bi bi-receipt" style="font-size:3rem;"></i>
        <p style="margin-top:1rem;">No orders found.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): 
                    $item_count = $pdo->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
                    $item_count->execute([$order['id']]);
                    $qty = $item_count->fetchColumn();
                ?>
                    <tr>
                        <td><strong>#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= e($order['customer_name']) ?></td>
                        <td style="font-size:0.85rem;"><?= e($order['phone']) ?></td>
                        <td><?= e($order['city']) ?></td>
                        <td><?= $qty ?> items</td>
                        <td style="font-weight:600;"><?= format_price($order['total_amount']) ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($order['status']) ?>">
                                <?= e($order['status']) ?>
                            </span>
                        </td>
                        <td style="font-size:0.8rem;color:var(--color-text-light);">
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

<?php require_once __DIR__ . '/admin_footer.php'; ?>
