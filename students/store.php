<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('create.php');
}

$data = [
    'student_no'      => trim((string) ($_POST['student_no'] ?? '')),
    'first_name'      => trim((string) ($_POST['first_name'] ?? '')),
    'last_name'       => trim((string) ($_POST['last_name'] ?? '')),
    'email'           => trim((string) ($_POST['email'] ?? '')),
    'phone'           => trim((string) ($_POST['phone'] ?? '')),
    'date_of_birth'   => trim((string) ($_POST['date_of_birth'] ?? '')) ?: null,
    'gender'          => trim((string) ($_POST['gender'] ?? 'Other')),
    'address'         => trim((string) ($_POST['address'] ?? '')),
    'profile_pic'     => null,
    'course_id'       => (int) ($_POST['course_id'] ?? 0),
    'academic_year'   => trim((string) ($_POST['academic_year'] ?? 'Year 1, Sem 1')),
    'academic_status' => trim((string) ($_POST['academic_status'] ?? 'Good Standing')),
    'gpa'             => (float) ($_POST['gpa'] ?? 0.0),
    'enrollment_date' => trim((string) ($_POST['enrollment_date'] ?? '')),
    'status'          => trim((string) ($_POST['status'] ?? 'Active')),
];

$errors = [];

if ($data['student_no'] === '') $errors[] = 'Student number is required.';
if ($data['first_name'] === '') $errors[] = 'First name is required.';
if ($data['last_name'] === '') $errors[] = 'Last name is required.';
if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($data['course_id'] <= 0) $errors[] = 'Please select a valid course.';
if ($data['enrollment_date'] === '') $errors[] = 'Enrollment date is required.';

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileSize = $_FILES['profile_pic']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileExtension, $allowedExtensions, true)) {
        $errors[] = 'Invalid file type. Only JPG, PNG, and WEBP formats are permitted.';
    } elseif ($fileSize > 2 * 1024 * 1024) {
        $errors[] = 'Uploaded image exceeds the 2MB size limit.';
    } else {
        $newFileName = 'student_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $uploadFileDir = '../assets/uploads/';
        $destPath = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $data['profile_pic'] = $newFileName;
        } else {
            $errors[] = 'Failed to move uploaded photo to storage directory.';
        }
    }
}

if ($errors) {
    $pageTitle = 'Register Student';
    $basePath = '../';
    $activePage = 'students';
    $student = $data;
    require_once '../includes/header.php';
    echo '<section class="page-heading"><h2>Register Student</h2></section>';
    echo '<form class="form-card" method="post" action="store.php" enctype="multipart/form-data">';
    require_once '_form.php';
    echo '</form>';
    require_once '../includes/footer.php';
    exit;
}

try {
    // 1. Insert Student Record
    $stmt = $pdo->prepare('
        INSERT INTO students (
            student_no, first_name, last_name, email, phone,
            date_of_birth, gender, address, profile_pic, course_id,
            academic_year, academic_status, gpa, enrollment_date, status
        ) VALUES (
            :student_no, :first_name, :last_name, :email, :phone,
            :date_of_birth, :gender, :address, :profile_pic, :course_id,
            :academic_year, :academic_status, :gpa, :enrollment_date, :status
        )
    ');
    $stmt->execute($data);
    $newStudentId = (int)$pdo->lastInsertId();

    // 2. Automatically Copy Default Modules From The Selected Course
    $currStmt = $pdo->prepare('SELECT module_code, module_name, credits FROM course_curriculum WHERE course_id = :cid');
    $currStmt->execute(['cid' => $data['course_id']]);
    $defaultModules = $currStmt->fetchAll();

    if ($defaultModules) {
        $insertModule = $pdo->prepare('
            INSERT INTO student_modules (student_id, module_code, module_name, grade, credits)
            VALUES (:sid, :code, :name, "A", :credits)
        ');
        foreach ($defaultModules as $mod) {
            $insertModule->execute([
                'sid'     => $newStudentId,
                'code'    => $mod['module_code'],
                'name'    => $mod['module_name'],
                'credits' => $mod['credits']
            ]);
        }
    }

    redirect('index.php?created=1');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $errors[] = 'Student number or Email already exists in the system.';
        $pageTitle = 'Register Student';
        $basePath = '../';
        $activePage = 'students';
        $student = $data;
        require_once '../includes/header.php';
        echo '<form class="form-card" method="post" action="store.php" enctype="multipart/form-data">';
        require_once '_form.php';
        echo '</form>';
        require_once '../includes/footer.php';
        exit;
    }
    throw $e;
}