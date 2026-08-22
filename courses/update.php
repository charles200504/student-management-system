<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$course_code = strtoupper(trim((string)($_POST['course_code'] ?? '')));
$course_name = trim((string)($_POST['course_name'] ?? ''));

if ($id <= 0 || $course_code === '' || $course_name === '') {
    redirect('index.php');
}

try {
    $stmt = $pdo->prepare('
        UPDATE courses 
        SET course_code = :course_code, course_name = :course_name 
        WHERE id = :id
    ');
    $stmt->execute([
        'course_code' => $course_code,
        'course_name' => $course_name,
        'id' => $id,
    ]);

    redirect('index.php?updated=1');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo "<script>alert('Course code already exists!'); window.location.href='edit.php?id=$id';</script>";
        exit;
    }
    throw $e;
}