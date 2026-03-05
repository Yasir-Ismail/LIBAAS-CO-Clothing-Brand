<?php
/**
 * LIBAAS CO. — Cart Action Handler
 * Handles: add, remove, update cart items
 * Returns JSON for AJAX, or redirects for regular form posts
 */
require_once __DIR__ . '/../config/db.php';

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Also treat fetch API calls as AJAX
if (!$is_ajax && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    $is_ajax = true;
}

$action = $_POST['action'] ?? '';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function json_response(array $data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function get_cart_count(): int {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

// ─── ADD TO CART ───
if ($action === 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $size = trim($_POST['selectedSize'] ?? '');
    $color = trim($_POST['color'] ?? 'Default');
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    // Validate
    if ($product_id <= 0 || !$size) {
        if ($is_ajax) {
            json_response(['success' => false, 'message' => 'Please select a size.']);
        }
        set_flash('error', 'Please select a size.');
        redirect(base_url('public/product.php?id=' . $product_id));
    }

    // Check valid size values
    if (!in_array($size, ['S', 'M', 'L', 'XL'])) {
        if ($is_ajax) {
            json_response(['success' => false, 'message' => 'Invalid size selected.']);
        }
        set_flash('error', 'Invalid size.');
        redirect(base_url('public/product.php?id=' . $product_id));
    }

    // Get product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        if ($is_ajax) {
            json_response(['success' => false, 'message' => 'Product not found.']);
        }
        set_flash('error', 'Product not found.');
        redirect(base_url('public/products.php'));
    }

    // Check stock for selected size
    $stmt = $pdo->prepare("SELECT stock_quantity FROM product_sizes WHERE product_id = ? AND size = ?");
    $stmt->execute([$product_id, $size]);
    $sizeRow = $stmt->fetch();

    if (!$sizeRow || $sizeRow['stock_quantity'] <= 0) {
        if ($is_ajax) {
            json_response(['success' => false, 'message' => 'This size is out of stock.']);
        }
        set_flash('error', 'Selected size is out of stock.');
        redirect(base_url('public/product.php?id=' . $product_id));
    }

    $available_stock = $sizeRow['stock_quantity'];

    // Cart key = product_id + size + color (unique combo)
    $cart_key = $product_id . '_' . $size . '_' . $color;

    // Check existing cart quantity for this combo
    $existing_qty = isset($_SESSION['cart'][$cart_key]) ? $_SESSION['cart'][$cart_key]['quantity'] : 0;
    $new_total_qty = $existing_qty + $quantity;

    // Prevent exceeding stock
    if ($new_total_qty > $available_stock) {
        $can_add = $available_stock - $existing_qty;
        if ($can_add <= 0) {
            if ($is_ajax) {
                json_response(['success' => false, 'message' => "Maximum stock reached for this size. Only $available_stock available."]);
            }
            set_flash('error', "Maximum stock reached. Only $available_stock available.");
            redirect(base_url('public/product.php?id=' . $product_id));
        }
        $quantity = $can_add;
        $new_total_qty = $available_stock;
    }

    // Add/update cart
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] = $new_total_qty;
    } else {
        // Get primary image
        $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC LIMIT 1");
        $stmt->execute([$product_id]);
        $img = $stmt->fetch();

        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'name'       => $product['name'],
            'price'      => $product['price'],
            'size'       => $size,
            'color'      => $color,
            'quantity'   => $quantity,
            'image'      => $img ? $img['image_path'] : null,
            'max_stock'  => $available_stock,
        ];
    }

    if ($is_ajax) {
        json_response([
            'success'    => true,
            'message'    => "{$product['name']} (Size: $size) added to cart!",
            'cart_count' => get_cart_count(),
        ]);
    }
    set_flash('success', "{$product['name']} added to cart!");
    redirect(base_url('public/cart.php'));
}

// ─── REMOVE FROM CART ───
if ($action === 'remove') {
    $key = $_POST['key'] ?? '';

    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }

    if ($is_ajax) {
        json_response([
            'success'    => true,
            'message'    => 'Item removed from cart.',
            'cart_count' => get_cart_count(),
        ]);
    }
    set_flash('success', 'Item removed from cart.');
    redirect(base_url('public/cart.php'));
}

// ─── UPDATE QUANTITY ───
if ($action === 'update') {
    $key = $_POST['key'] ?? '';
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if (isset($_SESSION['cart'][$key])) {
        $item = $_SESSION['cart'][$key];

        // Re-check stock before updating
        $stmt = $pdo->prepare("SELECT stock_quantity FROM product_sizes WHERE product_id = ? AND size = ?");
        $stmt->execute([$item['product_id'], $item['size']]);
        $sizeRow = $stmt->fetch();

        if ($sizeRow) {
            $maxStock = $sizeRow['stock_quantity'];
            if ($quantity > $maxStock) {
                if ($is_ajax) {
                    json_response(['success' => false, 'message' => "Only $maxStock items available in this size."]);
                }
                set_flash('error', "Only $maxStock available.");
                redirect(base_url('public/cart.php'));
            }
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            $_SESSION['cart'][$key]['max_stock'] = $maxStock;
        }
    }

    if ($is_ajax) {
        json_response([
            'success'    => true,
            'message'    => 'Cart updated.',
            'cart_count' => get_cart_count(),
        ]);
    }
    redirect(base_url('public/cart.php'));
}

// ─── CLEAR CART ───
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    if ($is_ajax) {
        json_response(['success' => true, 'message' => 'Cart cleared.', 'cart_count' => 0]);
    }
    redirect(base_url('public/cart.php'));
}

// Default: redirect to cart
redirect(base_url('public/cart.php'));
