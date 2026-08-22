<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Add Course';
$basePath = '../';
$activePage = 'courses';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Academic Setup</p>
        <h2>Add New Course</h2>
    </div>
    <a class="button muted" href="index.php">Back to Courses</a>
</section>

<form class="form-card" method="post" action="store.php">
    <div class="grid-2">
        <label>
            <span>Course Code (e.g. SE201, CS101)</span>
            <input type="text" name="course_code" placeholder="SE201" required>
        </label>

        <label>
            <span>Course Name</span>
            <input type="text" name="course_name" placeholder="Software Architecture" required>
        </label>
    </div>

    <div class="form-actions">
        <button class="button primary" type="submit">Save Course</button>
        <a class="button muted" href="index.php">Cancel</a>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>