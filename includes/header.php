<?php
$pageTitle = $pageTitle ?? 'Student Management System';
$basePath = $basePath ?? '';
$activePage = $activePage ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e($basePath) ?>assets/css/styles.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div>
            <p class="eyebrow">PUSL2021 Coursework Project</p>
            <h1>Student Management System</h1>
        </div>
        <nav class="nav">
            <a class="<?= $activePage === 'students' ? 'active' : '' ?>"
               href="<?= e($basePath) ?>students/index.php">Students</a>
            <a href="<?= e($basePath) ?>students/create.php">Add Student</a>
        </nav>
    </div>
</header>
<main class="container main-content">