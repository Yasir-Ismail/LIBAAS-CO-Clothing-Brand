<?php
/**
 * LIBAAS CO. — Admin: Add Product
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Add Product';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $category    = $_POST['category'] ?? 'Men';
    $color       = trim($_POST['color'] ?? '');
    $featured    = isset($_POST['featured']) ? 1 : 0;

    // Sizes & stock
    $sizes = [];
    foreach (['S', 'M', 'L', 'XL'] as $s) {
        $stock = (int)($_POST["stock_$s"] ?? 0);
        if ($stock < 0) $stock = 0;
        $sizes[$s] = $stock;
    }

    // Validation
    if (empty($name))                         $errors[] = 'Product name is required.';
    if ($price <= 0)                           $errors[] = 'Price must be greater than 0.';
    if (!in_array($category, ['Men','Women','Kids'])) $errors[] = 'Invalid category.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert product
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, color, featured) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category, $color, $featured]);
            $product_id = $pdo->lastInsertId();

            // Insert sizes
            $sizeStmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES (?, ?, ?)");
            foreach ($sizes as $size => $stock) {
                $sizeStmt->execute([$product_id, $size, $stock]);
            }

            // Handle image uploads
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!empty($_FILES['images']['name'][0])) {
                $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                
                $file_count = count($_FILES['images']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                        if (!in_array($ext, $allowed)) continue;

                        // Limit file size (5MB)
                        if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) continue;

                        $filename = 'product_' . $product_id . '_' . uniqid() . '.' . $ext;
                        $target = $upload_dir . $filename;

                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target)) {
                            $is_primary = ($i === 0) ? 1 : 0;
                            $imgStmt->execute([$product_id, $filename, $is_primary, $i]);
                        }
                    }
                }
            }

            $pdo->commit();
            set_flash('success', "Product '$name' added successfully!");
            redirect(base_url('admin/products.php'));

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to add product: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-header">
    <h1>Add Product</h1>
    <a href="<?= base_url('admin/products.php') ?>" style="font-size:0.85rem;color:var(--color-text-light);">
        ← Back to Products
    </a>
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
            <!-- Name -->
            <div class="col-md-8">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Product Name *</label>
                <input type="text" class="form-control" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
            </div>

            <!-- Category -->
            <div class="col-md-4">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Category *</label>
                <select class="form-select" name="category">
                    <?php foreach (['Men','Women','Kids'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Price -->
            <div class="col-md-4">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Price (Rs.) *</label>
                <input type="number" class="form-control" name="price" step="1" min="1" value="<?= e($_POST['price'] ?? '') ?>" required>
            </div>

            <!-- Color -->
            <div class="col-md-4">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Colors</label>
                <input type="text" class="form-control" name="color" value="<?= e($_POST['color'] ?? '') ?>" placeholder="Black, White, Navy">
                <small class="text-muted">Comma-separated</small>
            </div>

            <!-- Featured -->
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="featured" id="featured" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="featured" style="font-size:0.85rem;">Featured Product</label>
                </div>
            </div>

            <!-- Description -->
            <div class="col-12">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Description</label>
                <textarea class="form-control" name="description" rows="4"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Size & Stock -->
            <div class="col-12">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Size & Stock *</label>
                <div class="row g-2">
                    <?php foreach (['S', 'M', 'L', 'XL'] as $s): ?>
                        <div class="col-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="font-weight:600;width:40px;justify-content:center;"><?= $s ?></span>
                                <input type="number" class="form-control" name="stock_<?= $s ?>" min="0" value="<?= e($_POST["stock_$s"] ?? '0') ?>" placeholder="Qty">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Images -->
            <div class="col-12">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Product Images</label>
                <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                <small class="text-muted">First image will be set as primary. Max 5MB each. JPG, PNG, WebP accepted.</small>
            </div>

            <!-- Submit -->
            <div class="col-12" style="margin-top:1rem;">
                <button type="submit" class="btn-brand" style="padding:0.7rem 2rem;">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
