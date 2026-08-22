<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function get_logged_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Admin',
        'email' => $_SESSION['user_email'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? '',
    ];
}

function get_courses(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, course_code, course_name FROM courses ORDER BY course_name ASC');
    return $stmt->fetchAll();
}

function find_student(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('
        SELECT s.*, c.course_code, c.course_name
        FROM students s
        INNER JOIN courses c ON c.id = s.course_id
        WHERE s.id = :id
        LIMIT 1
    ');
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();
    return $student ?: null;
}

function calculate_and_update_gpa(PDO $pdo, int $studentId): float
{
    $gradeScale = [
        'A+' => 4.00, 'A'  => 4.00, 'A-' => 3.70,
        'B+' => 3.30, 'B'  => 3.00, 'B-' => 2.70,
        'C+' => 2.30, 'C'  => 2.00, 'C-' => 1.70,
        'D'  => 1.00, 'F'  => 0.00
    ];

    $stmt = $pdo->prepare('SELECT grade, credits FROM student_modules WHERE student_id = :id');
    $stmt->execute(['id' => $studentId]);
    $modules = $stmt->fetchAll();

    if (!$modules) {
        return 0.00;
    }

    $totalPoints = 0.0;
    $totalCredits = 0;

    foreach ($modules as $mod) {
        $grade = strtoupper(trim($mod['grade']));
        $credits = (int) $mod['credits'];
        $point = $gradeScale[$grade] ?? 0.00;

        $totalPoints += ($point * $credits);
        $totalCredits += $credits;
    }

    $calculatedGPA = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00;

    // Determine Academic Standing dynamically
    $status = 'Good Standing';
    if ($calculatedGPA >= 3.70) {
        $status = "Dean's List";
    } elseif ($calculatedGPA < 2.00) {
        $status = 'On Probation';
    }

    // Persist new GPA and Standing in database
    $upd = $pdo->prepare('UPDATE students SET gpa = :gpa, academic_status = :status WHERE id = :id');
    $upd->execute([
        'gpa' => $calculatedGPA,
        'status' => $status,
        'id' => $studentId
    ]);

    return $calculatedGPA;
}