<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Student Management System';
$basePath = $basePath ?? '';
$activePage = $activePage ?? '';
$user = get_logged_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e($basePath) ?>assets/css/styles.css?v=<?= time() ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div class="brand">
            <div class="brand-logo">⚡</div>
            <div>
                <a href="<?= e($basePath) ?>index.php" class="brand-name">STUDENT<span>SYS</span></a>
                <p class="brand-sub">PUSL2021 Enterprise Portal</p>
            </div>
        </div>
        <nav class="nav">
            <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="<?= e($basePath) ?>index.php">Home</a>
            
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= e($basePath) ?>dashboard.php">Dashboard</a>
                    <a class="<?= $activePage === 'students' ? 'active' : '' ?>" href="<?= e($basePath) ?>students/index.php">Students</a>
                    <a class="<?= $activePage === 'courses' ? 'active' : '' ?>" href="<?= e($basePath) ?>courses/index.php">Courses</a>
                    <a class="nlearn-nav-btn <?= $activePage === 'lms' ? 'active' : '' ?>" href="<?= e($basePath) ?>students/lms.php">🎓 SLearn LMS</a>
                    <a class="button gold-btn-nav" href="<?= e($basePath) ?>students/create.php">➕ Register Student</a>
                <?php else: ?>
                    <a class="<?= $activePage === 'profile_view' ? 'active' : '' ?>" href="<?= e($basePath) ?>students/my_profile.php">👤 My Profile</a>
                    <a class="nlearn-nav-btn <?= $activePage === 'lms' ? 'active' : '' ?>" href="<?= e($basePath) ?>students/lms.php">🎓 My SLearn LMS</a>
                    <a class="<?= $activePage === 'id_card' ? 'active' : '' ?>" href="<?= e($basePath) ?>students/my_id_card.php">🪪 My Student ID</a>
                <?php endif; ?>
                
                <a href="<?= e($basePath) ?>auth/profile.php" class="user-profile-badge <?= $activePage === 'profile' ? 'active' : '' ?>" title="Edit Profile">
                    <div class="avatar-circle-wrapper">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= e($basePath) ?>assets/uploads/<?= e($user['avatar']) ?>" alt="Avatar" class="navbar-avatar">
                        <?php else: ?>
                            <span class="navbar-avatar-placeholder"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="user-greeting"><?= e($user['name'] ?? 'User') ?> (<?= ucfirst(e($user['role'] ?? 'student')) ?>)</span>
                </a>

                <a class="button danger-btn-nav" href="<?= e($basePath) ?>auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="nlearn-nav-btn" href="<?= e($basePath) ?>students/lms.php">🎓 SLearn LMS</a>
                <a class="<?= $activePage === 'login' ? 'active' : '' ?>" href="<?= e($basePath) ?>auth/login.php">Log In</a>
                <a class="button gold-btn-nav" href="<?= e($basePath) ?>auth/register.php">Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container main-content">