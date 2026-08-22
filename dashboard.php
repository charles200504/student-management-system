<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';

$pageTitle = 'Analytics Dashboard';
$basePath = '';
$activePage = 'dashboard';

// Aggregate Analytics
$stats = $pdo->query('
    SELECT 
        COUNT(*) as total_students,
        SUM(CASE WHEN status = "Active" THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN status = "Graduated" THEN 1 ELSE 0 END) as graduated_count,
        SUM(CASE WHEN status = "Inactive" THEN 1 ELSE 0 END) as inactive_count
    FROM students
')->fetch();

$totalCourses = $pdo->query('SELECT COUNT(*) as c FROM courses')->fetch()['c'] ?? 0;

// Enrollment Distribution by Course (for Bar Chart)
$courseDist = $pdo->query('
    SELECT c.course_code, COUNT(s.id) as student_count
    FROM courses c
    LEFT JOIN students s ON s.course_id = c.id
    GROUP BY c.id
    ORDER BY student_count DESC
')->fetchAll();

// Recent 5 Student Registrations
$recentStudents = $pdo->query('
    SELECT s.*, c.course_code 
    FROM students s
    INNER JOIN courses c ON c.id = s.course_id
    ORDER BY s.created_at DESC
    LIMIT 5
')->fetchAll();

require_once 'includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">CONTROL CONSOLE</p>
        <h2>Executive Dashboard Overview</h2>
    </div>
    <div class="action-buttons">
        <a class="button gold-btn" href="students/create.php">➕ Register Student</a>
        <a class="button secondary" href="students/export.php">📥 Export CSV</a>
    </div>
</section>

<!-- KPI Metrics Grid -->
<section class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);">🎓</div>
        <div class="stat-content">
            <span class="stat-label">TOTAL ENROLLED</span>
            <span class="stat-number"><?= e($stats['total_students'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">⚡</div>
        <div class="stat-content">
            <span class="stat-label">ACTIVE STUDENTS</span>
            <span class="stat-number"><?= e($stats['active_count'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">🏆</div>
        <div class="stat-content">
            <span class="stat-label">GRADUATED ALUMNI</span>
            <span class="stat-number"><?= e($stats['graduated_count'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #ec4899; background: rgba(236, 72, 153, 0.1);">📚</div>
        <div class="stat-content">
            <span class="stat-label">TOTAL PROGRAMS</span>
            <span class="stat-number"><?= e($totalCourses) ?></span>
        </div>
    </div>
</section>

<!-- Interactive Analytics Charts -->
<section class="charts-grid">
    <div class="panel chart-box">
        <h3>📊 Enrollment Distribution by Course</h3>
        <canvas id="courseChart" height="200"></canvas>
    </div>
    <div class="panel chart-box">
        <h3>🎯 Student Status Ratio</h3>
        <canvas id="statusChart" height="200"></canvas>
    </div>
</section>

<!-- Recent Enrollments Table -->
<section class="panel table-card">
    <div class="panel-header-flex">
        <h3>⚡ Recent Student Registrations</h3>
        <a class="button gold-text-btn" href="students/index.php">View All Directory →</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>GPA</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$recentStudents): ?>
                    <tr><td colspan="7" style="text-align:center; color:#94a3b8;">No students enrolled yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentStudents as $student): ?>
                        <tr>
                            <td><span class="badge badge-id"><?= e($student['student_no']) ?></span></td>
                            <td><strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong></td>
                            <td><?= e($student['email']) ?></td>
                            <td><span class="badge badge-course"><?= e($student['course_code']) ?></span></td>
                            <td><strong style="color: var(--primary-gold);"><?= number_format((float)($student['gpa'] ?? 0.0), 2) ?></strong></td>
                            <td>
                                <span class="badge badge-status badge-<?= strtolower(e($student['status'])) ?>">
                                    <?= e($student['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a class="action-btn view-btn" href="students/show.php?id=<?= e($student['id']) ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = '#1e293b';

const courseLabels = <?= json_encode(array_column($courseDist, 'course_code')) ?>;
const courseCounts = <?= json_encode(array_map('intval', array_column($courseDist, 'student_count'))) ?>;

new Chart(document.getElementById('courseChart'), {
    type: 'bar',
    data: {
        labels: courseLabels,
        datasets: [{
            label: 'Students',
            data: courseCounts,
            backgroundColor: '#fbbf24',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: '#1e293b' } },
            x: { ticks: { color: '#94a3b8' }, grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Graduated', 'Inactive'],
        datasets: [{
            data: [
                <?= (int)($stats['active_count'] ?? 0) ?>,
                <?= (int)($stats['graduated_count'] ?? 0) ?>,
                <?= (int)($stats['inactive_count'] ?? 0) ?>
            ],
            backgroundColor: ['#10b981', '#fbbf24', '#ef4444'],
            borderColor: '#0f172a',
            borderWidth: 3,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { color: '#cbd5e1' } }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>