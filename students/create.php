<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Add Student';
$basePath = '../';
$activePage = 'students';
$errors = [];
$courses = get_courses($pdo);
$student = [
    'gender' => 'Other',
    'status' => 'Active',
    'enrollment_date' => date('Y-m-d'),
];
$formAction = 'store.php';
$buttonText = 'Save Student';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Create Record</p>
        <h2>Add Student</h2>
    </div>
    <a class="button muted" href="index.php">Back to Students</a>
</section>

<?php require '_form.php'; ?>

<?php require_once '../includes/footer.php'; ?>