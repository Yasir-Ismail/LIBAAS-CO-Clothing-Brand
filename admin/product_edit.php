<?php
/**
 * LIBAAS CO. — Admin: Edit Product
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Edit Product';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect(base_url('admin/products.php'));

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) redirect(base_url('admin/products.php'));

// Fetch sizes
$stmt = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ?");
$stmt->execute([$id]);
$sizes_db = $stmt->fetchAll();
$sizes_map = [];
foreach ($sizes_db as $s) {
    $sizes_map[$s['size']] = $s['stock_quantity'];
}

// Fetch images
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

$errors = [];

// Handle image delete
if (isset($_GET['delete_image'])) {
    $img_id = (int)$_GET['delete_image'];
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->execute([$img_id, $id]);
    $img = $stmt->fetch();
    if ($img) {
        $file = __DIR__ . '/../uploads/' . $img['image_path'];
        if (file_exists($file)) unlink($file);
        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$img_id]);
    }
    redirect(base_url("admin/product_edit.php?id=$id"));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $category    = $_POST['category'] ?? 'Men';
    $color       = trim($_POST['color'] ?? '');
    $featured    = isset($_POST['featured']) ? 1 : 0;

    $sizes = [];
    foreach (['S', 'M', 'L', 'XL'] as $s) {
        $stock = (int)($_POST["stock_$s"] ?? 0);
        if ($stock < 0) $stock = 0;
        $sizes[$s] = $stock;
    }

    if (empty($name))  $errors[] = 'Product name is required.';
    if ($price <= 0)    $errors[] = 'Price must be greater than 0.';
    if (!in_array($category, ['Men','Women','Kids'])) $errors[] = 'Invalid category.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update product
            $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category=?, color=?, featured=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $category, $color, $featured, $id]);

            // Update sizes (upsert)
            $sizeStmt = $pdo->prepare("
                INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE stock_quantity = VALUES(stock_quantity)
            ");
            foreach ($sizes as $size => $stock) {
                $sizeStmt->execute([$id, $size, $stock]);
            }

            // Handle new image uploads
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            if (!empty($_FILES['images']['name'][0])) {
                $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                $existing_count = count($images);

                $file_count = count($_FILES['images']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                        if (!in_array($ext, $allowed)) continue;
                        if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) continue;

                        $filename = 'product_' . $id . '_' . uniqid() . '.' . $ext;
                        $target = $upload_dir . $filename;

                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target)) {
                            $is_primary = ($existing_count === 0 && $i === 0) ? 1 : 0;
                            $imgStmt->execute([$id, $filename, $is_primary, $existing_count + $i]);
                        }
                    }
                }
            }

            $pdo->commit();
            set_flash('success', "Product updated successfully!");
            redirect(base_url('admin/products.php'));

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Update failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Edit Product</h1>
    <a href="<?= base_url('admin/products.php') ?>" style="font-size:0.85rem;color:var(--color-text-light);">← Back to Products</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert-brand error">
        <ul style="margin:0;padding-left:1.2rem;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div style="background:var(--color-bg);border-radius:var(--radius-md);padding:2rem;box-shadow:var(--shadow-sm);max-width:800px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Product Name *</label>
                <input type="text" class="form-control" name="name" value="<?= e($product['name']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Category *</label>
                <select class="form-select" name="category">
                    <?php foreach (['Men','Women','Kids'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $product['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Price (Rs.) *</label>
                <input type="number" class="form-control" name="price" step="1" min="1" value="<?= (int)$product['price'] ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Colors</label>
                <input type="text" class="form-control" name="color" value="<?= e($product['color']) ?>" placeholder="Black, White, Navy">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="featured" id="featured" <?= $product['featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="featured" style="font-size:0.85rem;">Featured Product</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Description</label>
                <textarea class="form-control" name="description" rows="4"><?= e($product['description']) ?></textarea>
            </div>

            <!-- Size & Stock -->
            <div class="col-12">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Size & Stock *</label>
                <div class="row g-2">
                    <?php foreach (['S', 'M', 'L', 'XL'] as $s): ?>
                        <div class="col-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="font-weight:600;width:40px;justify-content:center;"><?= $s ?></span>
                                <input type="number" class="form-control" name="stock_<?= $s ?>" min="0" value="<?= $sizes_map[$s] ?? 0 ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Existing Images -->
            <?php if (!empty($images)): ?>
                <div class="col-12">
                    <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Current Images</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php foreach ($images as $img): ?>
                            <div style="position:relative;width:80px;height:100px;border-radius:4px;overflow:hidden;border:1px solid var(--color-border);">
                                <img src="<?= base_url('uploads/' . e($img['image_path'])) ?>" style="width:100%;height:100%;object-fit:cover;">
                                <?php if ($img['is_primary']): ?>
                                    <span style="position:absolute;top:2px;left:2px;background:var(--color-accent);color:#fff;font-size:0.5rem;padding:1px 4px;border-radius:2px;">PRIMARY</span>
                                <?php endif; ?>
                                <a href="<?= base_url("admin/product_edit.php?id=$id&delete_image=" . $img['id']) ?>" 
                                   style="position:absolute;top:2px;right:2px;background:var(--color-danger);color:#fff;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.6rem;"
                                   onclick="return confirm('Delete this image?')">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upload New Images -->
            <div class="col-12">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Upload New Images</label>
                <input type="file" class="form-control" name="images[]" multiple accept="image/*">
            </div>

            <div class="col-12" style="margin-top:1rem;">
                <button type="submit" class="btn-brand" style="padding:0.7rem 2rem;">
                    <i class="bi bi-check-circle"></i> Update Product
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
