<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/auth_check.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_academic_export_' . date('Y-m-d_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Student ID',
    'Student Number',
    'Full Name',
    'Email',
    'Current Course',
    'Year / Semester',
    'Academic Status',
    'Cumulative GPA',
    'Lifecycle Status',
    'Enrollment Date'
]);

$sql = 'SELECT s.id, s.student_no, CONCAT(s.first_name, " ", s.last_name) AS full_name, s.email,
               CONCAT(c.course_code, " - ", c.course_name) AS course,
               s.academic_year, s.academic_status, s.gpa, s.status, s.enrollment_date
        FROM students s
        INNER JOIN courses c ON c.id = s.course_id
        ORDER BY s.id ASC';

$stmt = $pdo->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;