<?php
/**
 * LIBAAS CO. — Database Installer & Seed Script
 * 
 * Run this once to create the database, tables, admin user, and seed products.
 * Access: http://localhost/clothing-brand/database/seed.php
 */

// DB credentials (same as config)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'libaas_co';

echo "<pre style='font-family:monospace;background:#111;color:#0f0;padding:2rem;'>\n";
echo "═══════════════════════════════════════\n";
echo "  LIBAAS CO. — Database Setup\n";
echo "═══════════════════════════════════════\n\n";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "[✓] Database '$dbname' created/verified\n";

    $pdo->exec("USE `$dbname`");

    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            name        VARCHAR(255)    NOT NULL,
            description TEXT,
            price       DECIMAL(10,2)   NOT NULL,
            category    ENUM('Men','Women','Kids') NOT NULL DEFAULT 'Men',
            color       VARCHAR(100)    DEFAULT NULL,
            featured    TINYINT(1)      NOT NULL DEFAULT 0,
            active      TINYINT(1)      NOT NULL DEFAULT 1,
            created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_featured (featured),
            INDEX idx_active   (active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "[✓] Table 'products' created\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_images (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            product_id  INT             NOT NULL,
            image_path  VARCHAR(500)    NOT NULL,
            is_primary  TINYINT(1)      NOT NULL DEFAULT 0,
            sort_order  INT             NOT NULL DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "[✓] Table 'product_images' created\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_sizes (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            product_id      INT             NOT NULL,
            size            ENUM('S','M','L','XL') NOT NULL,
            stock_quantity  INT             NOT NULL DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY uk_product_size (product_id, size),
            INDEX idx_stock (stock_quantity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "[✓] Table 'product_sizes' created\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            customer_name   VARCHAR(255)    NOT NULL,
            phone           VARCHAR(20)     NOT NULL,
            address         TEXT            NOT NULL,
            city            VARCHAR(100)    NOT NULL,
            total_amount    DECIMAL(10,2)   NOT NULL,
            status          ENUM('Pending','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
            created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
            updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status     (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "[✓] Table 'orders' created\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            order_id    INT             NOT NULL,
            product_id  INT             NOT NULL,
            size        ENUM('S','M','L','XL') NOT NULL,
            color       VARCHAR(100)    DEFAULT NULL,
            quantity    INT             NOT NULL,
            price       DECIMAL(10,2)   NOT NULL,
            FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE RESTRICT,
            INDEX idx_order   (order_id),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "[✓] Table 'order_items' created\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            username    VARCHAR(100) NOT NULL UNIQUE,
            password    VARCHAR(255) NOT NULL,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "[✓] Table 'admin_users' created\n";

    // ────────────────────────────────────────────
    // Create Admin User
    // ────────────────────────────────────────────
    $admin_check = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($admin_check == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $hashed_password]);
        echo "[✓] Admin user created (username: admin, password: admin123)\n";
    } else {
        echo "[~] Admin user already exists\n";
    }

    // ────────────────────────────────────────────
    // Seed Products
    // ────────────────────────────────────────────
    $product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($product_count == 0) {
        echo "\n--- Seeding Products ---\n";

        $products = [
            [
                'name' => 'Classic Oxford Shirt',
                'description' => 'A timeless Oxford button-down shirt crafted from premium cotton. Perfect for both formal and casual occasions. Features reinforced collar, mother-of-pearl buttons, and a tailored fit that flatters every body type.',
                'price' => 3490,
                'category' => 'Men',
                'color' => 'White, Light Blue, Navy',
                'featured' => 1,
                'sizes' => ['S' => 15, 'M' => 25, 'L' => 20, 'XL' => 10],
            ],
            [
                'name' => 'Slim Fit Chinos',
                'description' => 'Modern slim-fit chinos in premium stretch cotton. These versatile trousers transition seamlessly from office to evening. Features a comfortable waistband and clean tailoring.',
                'price' => 2990,
                'category' => 'Men',
                'color' => 'Khaki, Black, Olive',
                'featured' => 1,
                'sizes' => ['S' => 12, 'M' => 20, 'L' => 18, 'XL' => 8],
            ],
            [
                'name' => 'Premium Polo T-Shirt',
                'description' => 'Elevated casual wear with our signature polo. Made from piqué cotton with a soft hand feel. Ribbed collar and embroidered logo detail.',
                'price' => 1990,
                'category' => 'Men',
                'color' => 'Black, White, Maroon',
                'featured' => 1,
                'sizes' => ['S' => 20, 'M' => 30, 'L' => 25, 'XL' => 15],
            ],
            [
                'name' => 'Wool Blend Blazer',
                'description' => 'Sophisticated single-breasted blazer in premium wool blend. Half-canvassed construction with natural shoulder and slim silhouette. Perfect for the modern professional.',
                'price' => 8990,
                'category' => 'Men',
                'color' => 'Charcoal, Navy',
                'featured' => 1,
                'sizes' => ['S' => 5, 'M' => 10, 'L' => 8, 'XL' => 3],
            ],
            [
                'name' => 'Floral Wrap Dress',
                'description' => 'Elegant wrap dress with a beautiful floral print. Crafted from flowing viscose fabric. Features a flattering V-neckline, adjustable wrap tie, and midi length.',
                'price' => 4490,
                'category' => 'Women',
                'color' => 'Floral Blue, Floral Pink',
                'featured' => 1,
                'sizes' => ['S' => 18, 'M' => 22, 'L' => 15, 'XL' => 8],
            ],
            [
                'name' => 'Cotton A-Line Kurta',
                'description' => 'Beautiful handcrafted kurta in pure cotton with delicate embroidery. Comfortable yet stylish, perfect for everyday elegance. Features side slits and mandarin collar.',
                'price' => 2790,
                'category' => 'Women',
                'color' => 'White, Sage Green, Dusty Pink',
                'featured' => 1,
                'sizes' => ['S' => 20, 'M' => 25, 'L' => 18, 'XL' => 12],
            ],
            [
                'name' => 'High-Waist Palazzo Pants',
                'description' => 'Effortlessly chic palazzo pants in premium crepe fabric. High-waisted design with wide leg for maximum comfort and style. Pairs beautifully with fitted tops.',
                'price' => 2490,
                'category' => 'Women',
                'color' => 'Black, Off-White, Burgundy',
                'featured' => 0,
                'sizes' => ['S' => 14, 'M' => 20, 'L' => 16, 'XL' => 10],
            ],
            [
                'name' => 'Silk Blend Scarf Top',
                'description' => 'Luxurious sleeveless top in silk-blend fabric. Delicate draping at the neckline creates an effortlessly elegant look. Perfect for evening wear or special occasions.',
                'price' => 3290,
                'category' => 'Women',
                'color' => 'Champagne, Black, Emerald',
                'featured' => 1,
                'sizes' => ['S' => 10, 'M' => 15, 'L' => 12, 'XL' => 5],
            ],
            [
                'name' => 'Kids Graphic Tee',
                'description' => 'Fun and comfortable graphic t-shirt for kids. Made from 100% soft cotton with vibrant, fade-resistant prints. Easy to wash, hard to wear out.',
                'price' => 990,
                'category' => 'Kids',
                'color' => 'Red, Blue, Yellow',
                'featured' => 1,
                'sizes' => ['S' => 25, 'M' => 30, 'L' => 20, 'XL' => 10],
            ],
            [
                'name' => 'Kids Denim Joggers',
                'description' => 'Comfortable denim joggers with elastic waistband. Soft stretch denim that allows free movement. Perfect for active kids who want to look cool.',
                'price' => 1490,
                'category' => 'Kids',
                'color' => 'Blue Denim, Black',
                'featured' => 0,
                'sizes' => ['S' => 15, 'M' => 20, 'L' => 15, 'XL' => 8],
            ],
            [
                'name' => 'Kids Party Dress',
                'description' => 'Adorable party dress for special occasions. Features tulle overlay, satin ribbon waistband, and back zip closure. Makes every little girl feel like a princess.',
                'price' => 2290,
                'category' => 'Kids',
                'color' => 'Pink, Lavender',
                'featured' => 1,
                'sizes' => ['S' => 12, 'M' => 18, 'L' => 10, 'XL' => 5],
            ],
            [
                'name' => 'Heavyweight Hoodie',
                'description' => 'Premium heavyweight hoodie in brushed-back fleece. Oversized fit with ribbed cuffs and hem. The ultimate comfort piece that doesnt compromise on style.',
                'price' => 3990,
                'category' => 'Men',
                'color' => 'Black, Grey Melange, Forest Green',
                'featured' => 0,
                'sizes' => ['S' => 10, 'M' => 18, 'L' => 15, 'XL' => 7],
            ],
        ];

        $productStmt = $pdo->prepare("INSERT INTO products (name, description, price, category, color, featured) VALUES (?, ?, ?, ?, ?, ?)");
        $sizeStmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES (?, ?, ?)");

        foreach ($products as $p) {
            $productStmt->execute([$p['name'], $p['description'], $p['price'], $p['category'], $p['color'], $p['featured']]);
            $pid = $pdo->lastInsertId();

            foreach ($p['sizes'] as $size => $qty) {
                $sizeStmt->execute([$pid, $size, $qty]);
            }

            echo "[✓] Product: {$p['name']} (ID: $pid)\n";
        }
    } else {
        echo "[~] Products already exist ($product_count found), skipping seed\n";
    }

    // ────────────────────────────────────────────
    // Create uploads directory
    // ────────────────────────────────────────────
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
        echo "\n[✓] Uploads directory created\n";
    } else {
        echo "\n[~] Uploads directory already exists\n";
    }

    echo "\n═══════════════════════════════════════\n";
    echo "  ✅ SETUP COMPLETE!\n";
    echo "═══════════════════════════════════════\n\n";
    echo "Admin Login:\n";
    echo "  URL:      http://localhost/clothing-brand/admin/login.php\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    echo "Store:\n";
    echo "  URL: http://localhost/clothing-brand/public/index.php\n\n";
    echo "⚠ Delete this file after setup for security!\n";

} catch (PDOException $e) {
    echo "\n[✗] ERROR: " . $e->getMessage() . "\n";
    echo "\nMake sure MySQL is running in XAMPP/WAMP.\n";
}

echo "</pre>";
