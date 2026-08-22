<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    // Check if students are currently enrolled in this course
    $check = $pdo->prepare('SELECT COUNT(*) as student_count FROM students WHERE course_id = :id');
    $check->execute(['id' => $id]);
    $count = (int) $check->fetch()['student_count'];

    if ($count > 0) {
        redirect('index.php?error=has_students');
    }

    $stmt = $pdo->prepare('DELETE FROM courses WHERE id = :id');
    $stmt->execute(['id' => $id]);
    redirect('index.php?deleted=1');
}

redirect('index.php');