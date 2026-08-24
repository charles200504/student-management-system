<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$user = get_logged_user();
$student = get_student_record_for_user($pdo, (int)$user['id']);

if ($student) {
    redirect('id_card.php?id=' . $student['id']);
} else {
    $first = $pdo->query('SELECT id FROM students ORDER BY id ASC LIMIT 1')->fetch();
    if ($first) {
        redirect('id_card.php?id=' . $first['id']);
    } else {
        redirect('index.php');
    }
}