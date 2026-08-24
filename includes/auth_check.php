<?php
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    $base = isset($basePath) ? $basePath : '../';
    redirect($base . 'auth/login.php');
}

function require_admin(): void {
    if (!is_admin()) {
        echo '<div style="max-width: 600px; margin: 80px auto; padding: 24px; background: #1e1b4b; border: 1px solid #6366f1; border-radius: 12px; color: #fff; font-family: sans-serif; text-align: center;">
            <h2 style="color: #f87171; font-size: 22px; margin-bottom: 8px;">Access Denied</h2>
            <p style="color: #cbd5e1; font-size: 14px;">Students are not authorized to perform administrative operations or register other students.</p>
            <a href="../students/lms.php" style="display: inline-block; margin-top: 16px; background: #f59e0b; color: #000; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold;">Return to My Portal</a>
        </div>';
        exit;
    }
}