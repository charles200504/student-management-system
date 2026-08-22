<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('index.php');
}

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('index.php');
}

$pageTitle = 'Edit Course - ' . $course['course_code'];
$basePath = '../';
$activePage = 'courses';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Academic Setup</p>
        <h2>Edit Course: <?= e($course['course_code']) ?></h2>
    </div>
    <a class="button muted" href="index.php">Back to Courses</a>
</section>

<form class="form-card" method="post" action="update.php">
    <input type="hidden" name="id" value="<?= e((string)$course['id']) ?>">
    
    <div class="grid-2">
        <label>
            <span>Course Code *</span>
            <input type="text" name="course_code" value="<?= e($course['course_code']) ?>" required>
        </label>

        <label>
            <span>Course Name *</span>
            <input type="text" name="course_name" value="<?= e($course['course_name']) ?>" required>
        </label>
    </div>

    <div class="form-actions">
        <button class="button gold-btn" type="submit">Update Course</button>
        <a class="button muted" href="index.php">Cancel</a>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>