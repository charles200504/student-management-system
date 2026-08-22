<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Register Student';
$basePath = '../';
$activePage = 'students';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">CREATE RECORD</p>
        <h2>Register Student</h2>
    </div>
    <a class="button muted" href="index.php">Back to Students</a>
</section>

<form class="form-card" method="post" action="store.php" enctype="multipart/form-data">
    <?php require_once '_form.php'; ?>
</form>

<?php require_once '../includes/footer.php'; ?>