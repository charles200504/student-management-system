<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function get_courses(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT id, course_code, course_name
         FROM courses
         ORDER BY course_name'
    );

    return $stmt->fetchAll();
}

function find_student(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT s.*, c.course_code, c.course_name
         FROM students s
         INNER JOIN courses c ON c.id = s.course_id
         WHERE s.id = :id'
    );

    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();

    return $student ?: null;
}

function is_valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function validate_student_input(array $input): array
{
    $clean = [
        'student_no' => strtoupper(trim((string) ($input['student_no'] ?? ''))),
        'first_name' => trim((string) ($input['first_name'] ?? '')),
        'last_name' => trim((string) ($input['last_name'] ?? '')),
        'email' => trim((string) ($input['email'] ?? '')),
        'phone' => trim((string) ($input['phone'] ?? '')),
        'date_of_birth' => trim((string) ($input['date_of_birth'] ?? '')),
        'gender' => trim((string) ($input['gender'] ?? 'Other')),
        'address' => trim((string) ($input['address'] ?? '')),
        'course_id' => (int) ($input['course_id'] ?? 0),
        'enrollment_date' => trim((string) ($input['enrollment_date'] ?? '')),
        'status' => trim((string) ($input['status'] ?? 'Active')),
    ];

    $errors = [];

    if ($clean['student_no'] === '') {
        $errors['student_no'] = 'Student number is required.';
    } elseif (!preg_match('/^[A-Z0-9-]{3,20}$/', $clean['student_no'])) {
        $errors['student_no'] = 'Use 3-20 letters, numbers, or hyphens.';
    }

    if ($clean['first_name'] === '') {
        $errors['first_name'] = 'First name is required.';
    }

    if ($clean['last_name'] === '') {
        $errors['last_name'] = 'Last name is required.';
    }

    if ($clean['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($clean['date_of_birth'] !== '' && !is_valid_date($clean['date_of_birth'])) {
        $errors['date_of_birth'] = 'Use the format YYYY-MM-DD.';
    }

    $allowedGenders = ['Male', 'Female', 'Other'];
    if (!in_array($clean['gender'], $allowedGenders, true)) {
        $errors['gender'] = 'Choose a valid gender.';
    }

    if ($clean['course_id'] <= 0) {
        $errors['course_id'] = 'Choose a course.';
    }

    if ($clean['enrollment_date'] === '') {
        $errors['enrollment_date'] = 'Enrollment date is required.';
    } elseif (!is_valid_date($clean['enrollment_date'])) {
        $errors['enrollment_date'] = 'Use the format YYYY-MM-DD.';
    }

    $allowedStatuses = ['Active', 'Inactive', 'Graduated'];
    if (!in_array($clean['status'], $allowedStatuses, true)) {
        $errors['status'] = 'Choose a valid status.';
    }

    return [$errors, $clean];
}

function field_value(array $data, string $key, string $default = ''): string
{
    return (string) ($data[$key] ?? $default);
}