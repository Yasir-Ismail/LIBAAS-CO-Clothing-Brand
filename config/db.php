<?php
/**
 * LIBAAS CO. — Database Configuration
 * Works on XAMPP / WAMP (localhost)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'libaas_co');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'LIBAAS CO.');
define('SITE_URL',  'http://localhost/clothing-brand');
define('CURRENCY',  'Rs.');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection (PDO)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please make sure MySQL is running and the database 'libaas_co' exists.");
}

/**
 * Helper: get base URL path
 */
function base_url(string $path = ''): string {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Helper: escape output
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper: redirect
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Helper: format price
 */
function format_price($amount): string {
    return CURRENCY . ' ' . number_format((float)$amount, 0);
}

/**
 * Flash message system
 */
function set_flash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

function require_admin_login(): void {
    if (!is_admin_logged_in()) {
        redirect(base_url('admin/login.php'));
    }
}
