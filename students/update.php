<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    redirect('index.php');
}

$currentStudent = find_student($pdo, $id);
if (!$currentStudent) {
    redirect('index.php');
}

$uploadFileDir = '../assets/uploads/';
$profilePic = $currentStudent['profile_pic'];

// 1. Handle Photo Removal Checkbox
if (isset($_POST['remove_profile_pic']) && $_POST['remove_profile_pic'] === '1') {
    if (!empty($profilePic) && file_exists($uploadFileDir . $profilePic)) {
        unlink($uploadFileDir . $profilePic);
    }
    $profilePic = null;
}

$data = [
    'id'              => $id,
    'student_no'      => trim((string) ($_POST['student_no'] ?? '')),
    'first_name'      => trim((string) ($_POST['first_name'] ?? '')),
    'last_name'       => trim((string) ($_POST['last_name'] ?? '')),
    'email'           => trim((string) ($_POST['email'] ?? '')),
    'phone'           => trim((string) ($_POST['phone'] ?? '')),
    'date_of_birth'   => trim((string) ($_POST['date_of_birth'] ?? '')) ?: null,
    'gender'          => trim((string) ($_POST['gender'] ?? 'Other')),
    'address'         => trim((string) ($_POST['address'] ?? '')),
    'profile_pic'     => $profilePic,
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

// 2. Handle New Photo Upload (Replaces old photo if provided)
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileSize = $_FILES['profile_pic']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileExtension, $allowedExtensions, true)) {
        $errors[] = 'Invalid file type. Only JPG, PNG, and WEBP formats are permitted.';
    } elseif ($fileSize > 2 * 1024 * 1024) {
        $errors[] = 'Uploaded image exceeds 2MB limit.';
    } else {
        $newFileName = 'student_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $destPath = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            if (!empty($currentStudent['profile_pic']) && file_exists($uploadFileDir . $currentStudent['profile_pic'])) {
                unlink($uploadFileDir . $currentStudent['profile_pic']);
            }
            $data['profile_pic'] = $newFileName;
        } else {
            $errors[] = 'Failed to upload photo.';
        }
    }
}

if ($errors) {
    $pageTitle = 'Edit Student';
    $basePath = '../';
    $activePage = 'students';
    $student = $data;
    require_once '../includes/header.php';
    echo '<form class="form-card" method="post" action="update.php" enctype="multipart/form-data">';
    echo '<input type="hidden" name="id" value="' . e((string)$id) . '">';
    require_once '_form.php';
    echo '</form>';
    require_once '../includes/footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare('
        UPDATE students SET
            student_no = :student_no,
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            phone = :phone,
            date_of_birth = :date_of_birth,
            gender = :gender,
            address = :address,
            profile_pic = :profile_pic,
            course_id = :course_id,
            academic_year = :academic_year,
            academic_status = :academic_status,
            gpa = :gpa,
            enrollment_date = :enrollment_date,
            status = :status
        WHERE id = :id
    ');
    $stmt->execute($data);
    redirect('index.php?updated=1');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $errors[] = 'Student number or Email is already used by another record.';
        $pageTitle = 'Edit Student';
        $basePath = '../';
        $activePage = 'students';
        $student = $data;
        require_once '../includes/header.php';
        echo '<form class="form-card" method="post" action="update.php" enctype="multipart/form-data">';
        echo '<input type="hidden" name="id" value="' . e((string)$id) . '">';
        require_once '_form.php';
        echo '</form>';
        require_once '../includes/footer.php';
        exit;
    }
    throw $e;
}