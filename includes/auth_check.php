<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    $base = isset($basePath) ? $basePath : '../';
    redirect($base . 'auth/login.php?error=unauthorized');
}