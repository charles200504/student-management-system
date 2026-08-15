<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Students';
$basePath = '../';
$activePage = 'students';

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$allowedStatuses = ['Active', 'Inactive', 'Graduated'];

$sql = 'SELECT s.*, c.course_code, c.course_name
        FROM students s
        INNER JOIN courses c ON c.id = s.course_id';
$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(s.student_no LIKE :student_no
                 OR s.first_name LIKE :first_name
                 OR s.last_name LIKE :last_name
                 OR s.email LIKE :email
                 OR c.course_name LIKE :course_name)';
    $searchTerm = '%' . $search . '%';
    $params['student_no'] = $searchTerm;
    $params['first_name'] = $searchTerm;
    $params['last_name'] = $searchTerm;
    $params['email'] = $searchTerm;
    $params['course_name'] = $searchTerm;
}

if (in_array($status, $allowedStatuses, true)) {
    $where[] = 's.status = :status';
    $params['status'] = $status;
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY s.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$messages = [
    'created' => 'Student added successfully.',
    'updated' => 'Student updated successfully.',
    'deleted' => 'Student deleted successfully.',
];

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Manage Records</p>
        <h2>Students</h2>
    </div>
    <a class="button primary" href="create.php">Add Student</a>
</section>

<?php foreach ($messages as $key => $message): ?>
    <?php if (isset($_GET[$key])): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<section class="panel">
    <form class="toolbar" method="get" action="index.php">
        <label>
            <span>Search</span>
            <input type="text" name="search" value="<?= e($search) ?>"
                   placeholder="Name, student number, email, or course">
        </label>
        <label>
            <span>Status</span>
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach ($allowedStatuses as $item): ?>
                    <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>>
                        <?= e($item) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Filter</button>
        <a class="button muted" href="index.php">Reset</a>
    </form>
</section>

<section class="panel">
    <?php if (!$students): ?>
        <p class="empty">No student records found.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student['student_no']) ?></td>
                            <td><?= e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td><?= e($student['email']) ?></td>
                            <td><?= e($student['course_code']) ?></td>
                            <td><span class="status"><?= e($student['status']) ?></span></td>
                            <td class="actions">
                                <a href="show.php?id=<?= e($student['id']) ?>">View</a>
                                <a href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                <form method="post" action="delete.php"
                                      onsubmit="return confirm('Delete this student?');">
                                    <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                    <button type="submit" class="link danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>