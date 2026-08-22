<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$field = trim((string)($_GET['field'] ?? ''));
$value = trim((string)($_GET['value'] ?? ''));
$excludeId = (int)($_GET['exclude_id'] ?? 0);

$response = [
    'exists' => false,
    'message' => ''
];

if ($field === 'student_no' && $value !== '') {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name FROM students WHERE student_no = :val AND id != :ex LIMIT 1');
    $stmt->execute(['val' => $value, 'ex' => $excludeId]);
    $match = $stmt->fetch();
    if ($match) {
        $response['exists'] = true;
        $response['message'] = "⚠️ Student Number '{$value}' is already assigned to {$match['first_name']} {$match['last_name']}.";
    }
} elseif ($field === 'email' && $value !== '') {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name FROM students WHERE email = :val AND id != :ex LIMIT 1');
    $stmt->execute(['val' => $value, 'ex' => $excludeId]);
    $match = $stmt->fetch();
    if ($match) {
        $response['exists'] = true;
        $response['message'] = "⚠️ Email '{$value}' is already registered under {$match['first_name']} {$match['last_name']}.";
    }
} elseif ($field === 'name') {
    $firstName = trim((string)($_GET['first_name'] ?? ''));
    $lastName = trim((string)($_GET['last_name'] ?? ''));
    if ($firstName !== '' && $lastName !== '') {
        $stmt = $pdo->prepare('SELECT id, student_no, email FROM students WHERE LOWER(first_name) = LOWER(:fn) AND LOWER(last_name) = LOWER(:ln) AND id != :ex LIMIT 1');
        $stmt->execute(['fn' => $firstName, 'ln' => $lastName, 'ex' => $excludeId]);
        $match = $stmt->fetch();
        if ($match) {
            $response['exists'] = true;
            $response['message'] = "💡 Note: A student named '{$firstName} {$lastName}' already exists ({$match['student_no']} - {$match['email']}).";
        }
    }
}

echo json_encode($response);
exit;