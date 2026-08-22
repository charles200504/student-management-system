<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('create.php');
}

$course_code = strtoupper(trim((string)($_POST['course_code'] ?? '')));
$course_name = trim((string)($_POST['course_name'] ?? ''));

if ($course_code === '' || $course_name === '') {
    redirect('create.php');
}

try {
    $stmt = $pdo->prepare('INSERT INTO courses (course_code, course_name) VALUES (:course_code, :course_name)');
    $stmt->execute([
        'course_code' => $course_code,
        'course_name' => $course_name,
    ]);

    redirect('index.php?created=1');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo "<script>alert('Course code already exists!'); window.location.href='create.php';</script>";
        exit;
    }
    throw $e;
}