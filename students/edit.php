<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int) ($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

if (!$student) {
    redirect('index.php');
}

$pageTitle = 'Edit Student';
$basePath = '../';
$activePage = 'students';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Update Profile</p>
        <h2>Edit: <?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
    </div>
    <a class="button muted" href="index.php">Back to Students</a>
</section>

<form class="form-card" method="post" action="update.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= e((string)$student['id']) ?>">
    <?php require_once '_form.php'; ?>
</form>

<?php require_once '../includes/footer.php'; ?>