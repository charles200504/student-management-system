<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('create.php');
}

[$errors, $student] = validate_student_input($_POST);
$courses = get_courses($pdo);

if ($errors) {
    $pageTitle = 'Add Student';
    $basePath = '../';
    $activePage = 'students';
    $formAction = 'store.php';
    $buttonText = 'Save Student';
    require_once '../includes/header.php';
    require '_form.php';
    require_once '../includes/footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO students
        (student_no, first_name, last_name, email, phone, date_of_birth,
         gender, address, course_id, enrollment_date, status)
        VALUES
        (:student_no, :first_name, :last_name, :email, :phone, :date_of_birth,
         :gender, :address, :course_id, :enrollment_date, :status)'
    );

    $stmt->execute([
        'student_no' => $student['student_no'],
        'first_name' => $student['first_name'],
        'last_name' => $student['last_name'],
        'email' => $student['email'],
        'phone' => $student['phone'] ?: null,
        'date_of_birth' => $student['date_of_birth'] ?: null,
        'gender' => $student['gender'],
        'address' => $student['address'] ?: null,
        'course_id' => $student['course_id'],
        'enrollment_date' => $student['enrollment_date'],
        'status' => $student['status'],
    ]);

    redirect('index.php?created=1');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $errors['database'] = 'Student number or email already exists.';
        $pageTitle = 'Add Student';
        $basePath = '../';
        $activePage = 'students';
        $formAction = 'store.php';
        $buttonText = 'Save Student';
        require_once '../includes/header.php';
        require '_form.php';
        require_once '../includes/footer.php';
        exit;
    }

    throw $e;
}