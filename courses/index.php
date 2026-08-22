<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Courses Management';
$basePath = '../';
$activePage = 'courses';

$stmt = $pdo->query('
    SELECT c.*, COUNT(s.id) AS enrolled_count
    FROM courses c
    LEFT JOIN students s ON s.course_id = c.id
    GROUP BY c.id
    ORDER BY c.course_code ASC
');
$courses = $stmt->fetchAll();

$messages = [
    'created' => 'New academic course added successfully.',
    'updated' => 'Course record updated successfully.',
    'deleted' => 'Course deleted successfully.',
];

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Academic Programs</p>
        <h2>Courses Management</h2>
    </div>
    <a class="button gold-btn" href="create.php">➕ Add New Course</a>
</section>

<?php foreach ($messages as $key => $message): ?>
    <?php if (isset($_GET[$key])): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'has_students'): ?>
    <div class="alert error">
        ⚠️ Cannot delete course: Students are currently enrolled in this program. Reassign or delete those student records first.
    </div>
<?php endif; ?>

<section class="panel table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Enrolled Students</th>
                    <th>Date Added</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$courses): ?>
                    <tr><td colspan="5" style="text-align:center; color:#94a3b8;">No courses available.</td></tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><span class="badge badge-course"><?= e($c['course_code']) ?></span></td>
                            <td><strong><?= e($c['course_name']) ?></strong></td>
                            <td><span class="badge badge-id"><?= e($c['enrolled_count']) ?> Students</span></td>
                            <td><?= e(substr((string)$c['created_at'], 0, 10)) ?></td>
                            <td class="actions">
                                <a class="action-btn edit-btn" href="edit.php?id=<?= e($c['id']) ?>">Edit</a>
                                <form method="post" action="delete.php"
                                      onsubmit="return confirm('Are you sure you want to delete this course?');">
                                    <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                                    <button type="submit" class="action-btn delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>