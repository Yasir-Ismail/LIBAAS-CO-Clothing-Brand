<?php
/**
 * LIBAAS CO. — Admin Logout
 */
require_once __DIR__ . '/../config/db.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

redirect(base_url('admin/login.php'));
