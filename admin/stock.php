<?php
/**
 * LIBAAS CO. — Admin: Stock Management
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Stock Management';

// Handle inline stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $size_id = (int)$_POST['size_id'];
    $new_qty = max(0, (int)$_POST['new_qty']);

    $stmt = $pdo->prepare("UPDATE product_sizes SET stock_quantity = ? WHERE id = ?");
    $stmt->execute([$new_qty, $size_id]);
    set_flash('success', 'Stock updated.');
    redirect(base_url('admin/stock.php') . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : ''));
}

$filter = $_GET['filter'] ?? '';

$query = "
    SELECT ps.*, p.name as product_name, p.category, p.active
    FROM product_sizes ps
    JOIN products p ON ps.product_id = p.id
    WHERE p.active = 1
";

if ($filter === 'low') {
    $query .= " AND ps.stock_quantity > 0 AND ps.stock_quantity <= 5";
} elseif ($filter === 'out') {
    $query .= " AND ps.stock_quantity = 0";
}

$query .= " ORDER BY ps.stock_quantity ASC, p.name ASC";

$stock_items = $pdo->query($query)->fetchAll();
$flash_success = get_flash('success');

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Stock Management</h1>
</div>

<?php if ($flash_success): ?>
    <div class="alert-brand success"><i class="bi bi-check-circle"></i> <?= e($flash_success) ?></div>
<?php endif; ?>

<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="<?= base_url('admin/stock.php') ?>" class="filter-pill <?= !$filter ? 'active' : '' ?>">All</a>
    <a href="<?= base_url('admin/stock.php?filter=low') ?>" class="filter-pill <?= $filter === 'low' ? 'active' : '' ?>">Low Stock (≤5)</a>
    <a href="<?= base_url('admin/stock.php?filter=out') ?>" class="filter-pill <?= $filter === 'out' ? 'active' : '' ?>">Out of Stock</a>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Size</th>
                <th>Current Stock</th>
                <th>Status</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stock_items as $item): ?>
                <tr>
                    <td><strong><?= e($item['product_name']) ?></strong></td>
                    <td><?= e($item['category']) ?></td>
                    <td><span style="font-weight:600;"><?= e($item['size']) ?></span></td>
                    <td>
                        <?php if ($item['stock_quantity'] == 0): ?>
                            <span class="low-stock-badge">Out of Stock</span>
                        <?php elseif ($item['stock_quantity'] <= 5): ?>
                            <span class="low-stock-badge"><?= $item['stock_quantity'] ?> left</span>
                        <?php else: ?>
                            <?= $item['stock_quantity'] ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['stock_quantity'] == 0): ?>
                            <span style="color:var(--color-danger);font-size:0.8rem;font-weight:600;">⚠ Restock needed</span>
                        <?php elseif ($item['stock_quantity'] <= 5): ?>
                            <span style="color:#e65100;font-size:0.8rem;font-weight:600;">⚠ Low</span>
                        <?php else: ?>
                            <span style="color:var(--color-success);font-size:0.8rem;">✓ OK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-1" style="max-width:150px;">
                            <input type="hidden" name="update_stock" value="1">
                            <input type="hidden" name="size_id" value="<?= $item['id'] ?>">
                            <input type="number" class="form-control form-control-sm" name="new_qty" 
                                   value="<?= $item['stock_quantity'] ?>" min="0" style="width:70px;">
                            <button type="submit" class="btn btn-dark btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
