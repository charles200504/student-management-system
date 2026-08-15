<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    redirect('index.php');
}

[$errors, $student] = validate_student_input($_POST);
$student['id'] = $id;
$courses = get_courses($pdo);

if ($errors) {
    $pageTitle = 'Edit Student';
    $basePath = '../';
    $activePage = 'students';
    $formAction = 'update.php';
    $buttonText = 'Update Student';
    require_once '../includes/header.php';
    require '_form.php';
    require_once '../includes/footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare(
        'UPDATE students
         SET student_no = :student_no,
             first_name = :first_name,
             last_name = :last_name,
             email = :email,
             phone = :phone,
             date_of_birth = :date_of_birth,
             gender = :gender,
             address = :address,
             course_id = :course_id,
             enrollment_date = :enrollment_date,
             status = :status
         WHERE id = :id'
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
        'id' => $student['id'],
    ]);

    redirect('index.php?updated=1');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $errors['database'] = 'Student number or email already exists.';
        $pageTitle = 'Edit Student';
        $basePath = '../';
        $activePage = 'students';
        $formAction = 'update.php';
        $buttonText = 'Update Student';
        require_once '../includes/header.php';
        require '_form.php';
        require_once '../includes/footer.php';
        exit;
    }

    throw $e;
}