<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Student Directory';
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
    'created' => 'Student record registered successfully.',
    'updated' => 'Student record updated successfully.',
    'deleted' => 'Student record deleted successfully.',
];

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Directory</p>
        <h2>Enrolled Students</h2>
    </div>
    <div class="action-buttons">
        <a class="button secondary" href="export.php">📥 Export CSV</a>
        <a class="button gold-btn" href="create.php">➕ Register Student</a>
    </div>
</section>

<?php foreach ($messages as $key => $message): ?>
    <?php if (isset($_GET[$key])): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<section class="panel filter-panel">
    <form class="toolbar" method="get" action="index.php">
        <label>
            <span>Search Directory</span>
            <input type="text" name="search" value="<?= e($search) ?>"
                   placeholder="Search by name, ID, email, or course...">
        </label>
        <label>
            <span>Filter Status</span>
            <select name="status">
                <option value="">All Statuses</option>
                <?php foreach ($allowedStatuses as $item): ?>
                    <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>>
                        <?= e($item) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button primary" type="submit">Filter</button>
        <a class="button muted" href="index.php">Reset</a>
    </form>
</section>

<section class="panel table-card">
    <?php if (!$students): ?>
        <div style="padding:40px; text-align:center; color:#94a3b8;">
            <p>🔍 No matching student records found.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student No</th>
                        <th>Student Name</th>
                        <th>Email Address</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th class="actions">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><span class="badge badge-id"><?= e($student['student_no']) ?></span></td>
                            <td><strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong></td>
                            <td><?= e($student['email']) ?></td>
                            <td><span class="badge badge-course"><?= e($student['course_code']) ?></span></td>
                            <td>
                                <span class="badge badge-status badge-<?= strtolower(e($student['status'])) ?>">
                                    <?= e($student['status']) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a class="action-btn view-btn" href="show.php?id=<?= e($student['id']) ?>">View</a>
                                <a class="action-btn edit-btn" href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                <form method="post" action="delete.php"
                                      onsubmit="return confirm('Are you sure you want to remove this record?');">
                                    <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                    <button type="submit" class="action-btn delete-btn">Delete</button>
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