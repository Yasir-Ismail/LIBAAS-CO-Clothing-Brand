<?php
/**
 * LIBAAS CO. — Admin Login
 */
require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    redirect(base_url('admin/dashboard.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect(base_url('admin/dashboard.php'));
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body style="background:var(--color-bg-dark);display:flex;align-items:center;justify-content:center;min-height:100vh;">

<div style="width:100%;max-width:400px;padding:2rem;">
    <div style="text-align:center;margin-bottom:2rem;">
        <div style="font-family:var(--font-heading);font-size:1.6rem;font-weight:700;letter-spacing:0.15em;color:#fff;">
            LIBAAS <span style="color:var(--color-accent);">CO.</span>
        </div>
        <div style="font-size:0.75rem;letter-spacing:0.15em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-top:0.5rem;">Admin Panel</div>
    </div>

    <div style="background:var(--color-bg);border-radius:var(--radius-md);padding:2rem;box-shadow:var(--shadow-lg);">
        <h2 style="font-family:var(--font-primary);font-size:1.1rem;font-weight:600;margin-bottom:1.5rem;">Sign In</h2>

        <?php if ($error): ?>
            <div class="alert-brand error" style="margin-bottom:1rem;">
                <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Username</label>
                <input type="text" class="form-control" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus
                       style="padding:0.75rem 1rem;border:2px solid var(--color-border);border-radius:var(--radius-sm);">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Password</label>
                <input type="password" class="form-control" name="password" required
                       style="padding:0.75rem 1rem;border:2px solid var(--color-border);border-radius:var(--radius-sm);">
            </div>
            <button type="submit" class="btn-brand w-100" style="text-align:center;margin-top:0.5rem;">
                Sign In
            </button>
        </form>
    </div>

    <div style="text-align:center;margin-top:1.5rem;">
        <a href="<?= base_url('public/index.php') ?>" style="font-size:0.8rem;color:rgba(255,255,255,0.4);">← Back to Store</a>
    </div>
</div>

</body>
</html>
