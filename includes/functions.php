<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function get_logged_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
    return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? 'student') === 'admin';
}

function get_courses(PDO $pdo): array {
    $stmt = $pdo->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC');
    return $stmt->fetchAll();
}

function get_student_record_for_user(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare('
        SELECT s.*, c.course_name, c.course_code 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id
        WHERE s.email = (SELECT email FROM users WHERE id = :uid)
        LIMIT 1
    ');
    $stmt->execute(['uid' => $userId]);
    return $stmt->fetch() ?: null;
}

function find_student(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare('
        SELECT s.*, c.course_name, c.course_code 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        WHERE s.id = :id
    ');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}

function calculate_and_update_gpa(PDO $pdo, int $studentId): float {
    $stmt = $pdo->prepare('SELECT grade, credits FROM student_modules WHERE student_id = :sid');
    $stmt->execute(['sid' => $studentId]);
    $modules = $stmt->fetchAll();

    if (!$modules) {
        $upd = $pdo->prepare('UPDATE students SET gpa = 0.00 WHERE id = :sid');
        $upd->execute(['sid' => $studentId]);
        return 0.00;
    }

    $pointsMap = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'F' => 0.0
    ];

    $totalPoints = 0.0;
    $totalCredits = 0;

    foreach ($modules as $m) {
        $cr = (int)($m['credits'] > 0 ? $m['credits'] : 20);
        $gr = trim((string)$m['grade']);
        $p = $pointsMap[$gr] ?? 0.0;

        $totalPoints += ($p * $cr);
        $totalCredits += $cr;
    }

    $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00;

    $upd = $pdo->prepare('UPDATE students SET gpa = :gpa WHERE id = :sid');
    $upd->execute(['gpa' => $gpa, 'sid' => $studentId]);

    return $gpa;
}