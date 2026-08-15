<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

$pageTitle = 'View Student';
$basePath = '../';
$activePage = 'students';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Student Profile</p>
        <h2><?= $student ? e($student['first_name'] . ' ' . $student['last_name']) : 'Student Not Found' ?></h2>
    </div>
    <a class="button muted" href="index.php">Back to Students</a>
</section>

<?php if (!$student): ?>
    <div class="alert error">The requested student record could not be found.</div>
<?php else: ?>
    <section class="panel details">
        <dl>
            <dt>Student Number</dt>
            <dd><?= e($student['student_no']) ?></dd>

            <dt>Name</dt>
            <dd><?= e($student['first_name'] . ' ' . $student['last_name']) ?></dd>

            <dt>Email</dt>
            <dd><?= e($student['email']) ?></dd>

            <dt>Phone</dt>
            <dd><?= e($student['phone'] ?: 'Not provided') ?></dd>

            <dt>Date of Birth</dt>
            <dd><?= e($student['date_of_birth'] ?: 'Not provided') ?></dd>

            <dt>Gender</dt>
            <dd><?= e($student['gender']) ?></dd>

            <dt>Address</dt>
            <dd><?= e($student['address'] ?: 'Not provided') ?></dd>

            <dt>Course</dt>
            <dd><?= e($student['course_code'] . ' - ' . $student['course_name']) ?></dd>

            <dt>Enrollment Date</dt>
            <dd><?= e($student['enrollment_date']) ?></dd>

            <dt>Status</dt>
            <dd><span class="status"><?= e($student['status']) ?></span></dd>
        </dl>

        <div class="form-actions">
            <a class="button primary" href="edit.php?id=<?= e($student['id']) ?>">Edit Student</a>
            <form method="post" action="delete.php"
                  onsubmit="return confirm('Delete this student?');">
                <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                <button type="submit" class="button danger">Delete Student</button>
            </form>
        </div>
    </section>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>