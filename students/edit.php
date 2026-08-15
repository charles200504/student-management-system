<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('index.php');
}

$student = find_student($pdo, $id);

if (!$student) {
    $pageTitle = 'Student Not Found';
    $basePath = '../';
    $activePage = 'students';
    require_once '../includes/header.php';
    echo '<div class="alert error">Student not found.</div>';
    echo '<p><a class="button muted" href="index.php">Back to Students</a></p>';
    require_once '../includes/footer.php';
    exit;
}

$pageTitle = 'Edit Student';
$basePath = '../';
$activePage = 'students';
$errors = [];
$courses = get_courses($pdo);
$formAction = 'update.php';
$buttonText = 'Update Student';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Update Record</p>
        <h2>Edit Student</h2>
    </div>
    <a class="button muted" href="index.php">Back to Students</a>
</section>

<?php require '_form.php'; ?>

<?php require_once '../includes/footer.php'; ?>