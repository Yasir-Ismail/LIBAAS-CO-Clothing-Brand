<?php
/**
 * LIBAAS CO. — Admin: Product List
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Products';

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // Delete images from disk
    $imgs = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $imgs->execute([$del_id]);
    foreach ($imgs->fetchAll() as $img) {
        $file = __DIR__ . '/../uploads/' . $img['image_path'];
        if (file_exists($file)) unlink($file);
    }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$del_id]);
    set_flash('success', 'Product deleted.');
    redirect(base_url('admin/products.php'));
}

// Fetch products
$products = $pdo->query("
    SELECT p.*, 
           (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image,
           (SELECT SUM(ps.stock_quantity) FROM product_sizes ps WHERE ps.product_id = p.id) AS total_stock
    FROM products p
    ORDER BY p.created_at DESC
")->fetchAll();

$flash_success = get_flash('success');

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Products</h1>
    <a href="<?= base_url('admin/product_add.php') ?>" class="btn-brand" style="padding:0.6rem 1.5rem;font-size:0.75rem;">
        <i class="bi bi-plus"></i> Add Product
    </a>
</div>

<?php if ($flash_success): ?>
    <div class="alert-brand success"><i class="bi bi-check-circle"></i> <?= e($flash_success) ?></div>
<?php endif; ?>

<?php if (empty($products)): ?>
    <div style="text-align:center;padding:3rem;color:var(--color-text-light);">
        <i class="bi bi-box-seam" style="font-size:3rem;"></i>
        <p style="margin-top:1rem;">No products yet. Add your first product!</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td style="width:60px;">
                            <div style="width:50px;height:60px;border-radius:4px;overflow:hidden;background:var(--color-bg-alt);">
                                <?php if ($p['image']): ?>
                                    <img src="<?= base_url('uploads/' . e($p['image'])) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                                <?php else: ?>
                                    <div class="placeholder-img" style="font-size:0.5rem;">IMG</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?= e($p['name']) ?></strong>
                            <div style="font-size:0.75rem;color:var(--color-text-light);">ID: <?= $p['id'] ?></div>
                        </td>
                        <td><?= e($p['category']) ?></td>
                        <td><?= format_price($p['price']) ?></td>
                        <td>
                            <?php
                            $total_stock = (int)$p['total_stock'];
                            if ($total_stock <= 0):
                            ?>
                                <span class="low-stock-badge">Out of Stock</span>
                            <?php elseif ($total_stock <= 10): ?>
                                <span class="low-stock-badge"><?= $total_stock ?> left</span>
                            <?php else: ?>
                                <?= $total_stock ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $p['featured'] ? '<i class="bi bi-star-fill" style="color:var(--color-accent);"></i>' : '<i class="bi bi-star" style="color:var(--color-border);"></i>' ?>
                        </td>
                        <td>
                            <a href="<?= base_url('admin/product_edit.php?id=' . $p['id']) ?>" style="font-size:0.8rem;color:var(--color-accent);margin-right:0.75rem;">Edit</a>
                            <a href="<?= base_url('admin/products.php?delete=' . $p['id']) ?>" 
                               style="font-size:0.8rem;color:var(--color-danger);"
                               onclick="return confirm('Delete this product? This cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
