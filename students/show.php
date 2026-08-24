<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';
require_once '../includes/academic_intelligence.php';

$id = (int) ($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

if (!$student) {
    redirect('index.php');
}

// Admin-only module additions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    require_admin();
    $mCode = strtoupper(trim((string)$_POST['module_code']));
    $mName = trim((string)$_POST['module_name']);
    $mGrade = trim((string)$_POST['grade']);
    $mCredits = (int)($_POST['credits'] ?? 20);

    if ($mCode !== '' && $mName !== '') {
        $ins = $pdo->prepare('INSERT INTO student_modules (student_id, module_code, module_name, grade, credits) VALUES (:sid, :code, :name, :grade, :credits)');
        $ins->execute([
            'sid' => $id,
            'code' => $mCode,
            'name' => $mName,
            'grade' => $mGrade,
            'credits' => $mCredits
        ]);
        calculate_and_update_gpa($pdo, $id);
        redirect("show.php?id=$id&gpa_updated=1");
    }
}

// Admin-only module grade updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    require_admin();
    $moduleId = (int)$_POST['module_id'];
    $newGrade = trim((string)$_POST['grade']);
    
    $upd = $pdo->prepare('UPDATE student_modules SET grade = :grade WHERE id = :mid AND student_id = :sid');
    $upd->execute(['grade' => $newGrade, 'mid' => $moduleId, 'sid' => $id]);
    
    calculate_and_update_gpa($pdo, $id);
    redirect("show.php?id=$id&gpa_updated=1");
}

// Admin-only module deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_module'])) {
    require_admin();
    $moduleId = (int)$_POST['module_id'];
    $del = $pdo->prepare('DELETE FROM student_modules WHERE id = :mid AND student_id = :sid');
    $del->execute(['mid' => $moduleId, 'sid' => $id]);
    
    calculate_and_update_gpa($pdo, $id);
    redirect("show.php?id=$id&gpa_updated=1");
}

$student = find_student($pdo, $id);
$modulesStmt = $pdo->prepare('SELECT * FROM student_modules WHERE student_id = :id ORDER BY id DESC');
$modulesStmt->execute(['id' => $id]);
$modules = $modulesStmt->fetchAll();

$intelReport = generate_academic_intelligence_analysis($student, $modules);

$pageTitle = 'View Student Profile';
$basePath = '../';
$activePage = is_admin() ? 'students' : 'profile_view';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Student Profile & Academic Record</p>
        <h2><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
    </div>
    <div class="action-buttons">
        <a class="button dark-btn" href="lms.php?id=<?= e((string)$student['id']) ?>" style="border-color: #059669; color: #34d399;">
            🎓 Student SLearn Portal
        </a>
        <a class="button secondary" href="id_card.php?id=<?= e((string)$student['id']) ?>">🪪 Print ID Card</a>
        <?php if (is_admin()): ?>
            <a class="button gold-btn" href="edit.php?id=<?= e((string)$student['id']) ?>">✏️ Edit Profile</a>
            <a class="button muted" href="index.php">Back to Directory</a>
        <?php endif; ?>
    </div>
</section>

<?php if (isset($_GET['gpa_updated'])): ?>
    <div class="alert success">⚡ Cumulative GPA and Academic Intelligence auto-recalculated!</div>
<?php endif; ?>

<div class="panel" style="border: 1px solid var(--card-gold-border); background: linear-gradient(135deg, rgba(21, 29, 48, 0.9) 0%, rgba(15, 21, 35, 0.95) 100%); margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">📊</span>
            <h3 style="font-size: 16px; color: var(--primary-gold); font-weight: 800;">Data-Driven Academic Intelligence & Predictive Engine</h3>
        </div>
        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: var(--primary-gold); border: 1px solid var(--card-gold-border);">
            Analytics Active
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
        <div style="background: var(--bg-surface-elevated); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Predicted Degree Class</div>
            <div style="font-size: 16px; font-weight: 800; color: #ffffff; margin-top: 4px;"><?= e($intelReport['predicted_honor']) ?></div>
        </div>

        <div style="background: var(--bg-surface-elevated); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Academic Risk Metric</div>
            <div style="font-size: 16px; font-weight: 800; color: <?= e($intelReport['risk_color']) ?>; margin-top: 4px;"><?= e($intelReport['risk_level']) ?> Risk</div>
        </div>

        <div style="background: var(--bg-surface-elevated); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total Credits Earned</div>
            <div style="font-size: 16px; font-weight: 800; color: #38bdf8; margin-top: 4px;"><?= e((string)$intelReport['total_credits']) ?> CATS Credits</div>
        </div>
    </div>

    <div style="background: rgba(0, 0, 0, 0.2); padding: 14px; border-radius: 8px;">
        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">ADVISORY INSIGHTS & REMEDIATION ROADMAP:</div>
        <ul style="margin-left: 20px; font-size: 13px; color: #cbd5e1; line-height: 1.6;">
            <?php foreach ($intelReport['insights'] as $insight): ?>
                <li><?= e($insight) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="panel" style="display: flex; align-items: center; gap: 24px; margin-bottom: 24px;">
    <?php if (!empty($student['profile_pic'])): ?>
        <img src="../assets/uploads/<?= e($student['profile_pic']) ?>" alt="Student Photo" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-gold);">
    <?php else: ?>
        <div style="width: 90px; height: 90px; border-radius: 50%; background: var(--bg-surface-elevated); border: 2px solid var(--border); display: grid; place-items: center; font-size: 30px; font-weight: 800; color: var(--primary-gold);">
            <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
        </div>
    <?php endif; ?>

    <div>
        <h2 style="font-size: 22px; margin-bottom: 4px;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:6px;">
            <span class="badge badge-id"><?= e($student['student_no']) ?></span>
            <span class="badge badge-course"><?= e($student['course_code']) ?> - <?= e($student['course_name']) ?></span>
            <span class="badge badge-status badge-<?= strtolower(e($student['status'])) ?>"><?= e($student['status']) ?></span>
        </div>
    </div>
</div>

<section class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);">📚</div>
        <div class="stat-content">
            <span class="stat-label">YEAR / SEMESTER</span>
            <span class="stat-number" style="font-size: 18px; margin-top: 4px;"><?= e($student['academic_year'] ?? 'Year 1, Sem 1') ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">🏆</div>
        <div class="stat-content">
            <span class="stat-label">AUTO-CALCULATED GPA</span>
            <span class="stat-number" style="color: #34d399; font-size: 24px; margin-top: 2px;"><?= number_format((float)($student['gpa'] ?? 0.0), 2) ?> / 4.00</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">⚡</div>
        <div class="stat-content">
            <span class="stat-label">ACADEMIC STANDING</span>
            <span class="stat-number" style="font-size: 18px; margin-top: 4px; color: #a78bfa;"><?= e($student['academic_status'] ?? 'Good Standing') ?></span>
        </div>
    </div>
</section>

<div style="display: grid; grid-template-columns: 1fr 1.4fr; gap: 24px;">
    <section class="panel details">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--primary-gold);">👤 Personal Record</h3>
        <dl>
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

            <dt>Enrolled Date</dt>
            <dd><?= e($student['enrollment_date']) ?></dd>
        </dl>
    </section>

    <section class="panel table-card">
        <div class="panel-header-flex">
            <h3>📖 Enrolled Modules & Grades</h3>
        </div>

        <?php if (is_admin()): ?>
            <form method="post" action="show.php?id=<?= e((string)$student['id']) ?>" style="padding: 16px 20px; background: var(--bg-surface-elevated); border-bottom: 1px solid var(--border); display: grid; grid-template-columns: 1fr 1.3fr 75px 80px auto; gap: 8px; align-items: center;">
                <input type="hidden" name="add_module" value="1">
                <input type="text" name="module_code" placeholder="Code (SE201)" required style="padding: 8px 10px; font-size: 12px;">
                <input type="text" name="module_name" placeholder="Module Title" required style="padding: 8px 10px; font-size: 12px;">
                <input type="number" name="credits" value="20" placeholder="Credits" required style="padding: 8px 10px; font-size: 12px;">
                <select name="grade" style="padding: 8px 8px; font-size: 12px;">
                    <option value="A+">A+ (4.0)</option>
                    <option value="A">A (4.0)</option>
                    <option value="A-">A- (3.7)</option>
                    <option value="B+">B+ (3.3)</option>
                    <option value="B">B (3.0)</option>
                    <option value="B-">B- (2.7)</option>
                    <option value="C+">C+ (2.3)</option>
                    <option value="C">C (2.0)</option>
                    <option value="F">F (0.0)</option>
                </select>
                <button type="submit" class="button gold-btn" style="padding: 8px 12px; font-size: 12px;">+ Add</button>
            </form>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Module</th>
                        <th>Credits</th>
                        <th>Grade</th>
                        <?php if (is_admin()): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$modules): ?>
                        <tr><td colspan="<?= is_admin() ? '5' : '4' ?>" style="text-align: center; color: var(--text-muted);">No academic modules recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($modules as $m): ?>
                            <tr>
                                <td><span class="badge badge-course"><?= e($m['module_code']) ?></span></td>
                                <td><strong><?= e($m['module_name']) ?></strong></td>
                                <td><?= e((string)$m['credits']) ?></td>
                                <td>
                                    <?php if (is_admin()): ?>
                                        <form method="post" action="show.php?id=<?= e((string)$student['id']) ?>" style="display:inline-flex; align-items:center; gap:4px;">
                                            <input type="hidden" name="update_grade" value="1">
                                            <input type="hidden" name="module_id" value="<?= e((string)$m['id']) ?>">
                                            <select name="grade" onchange="this.form.submit()" style="padding: 4px 6px; font-size: 12px; font-weight: bold; width: 75px;">
                                                <?php foreach (['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'F'] as $gr): ?>
                                                    <option value="<?= $gr ?>" <?= $m['grade'] === $gr ? 'selected' : '' ?>><?= $gr ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge badge-active"><?= e($m['grade']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if (is_admin()): ?>
                                    <td>
                                        <form method="post" action="show.php?id=<?= e((string)$student['id']) ?>" onsubmit="return confirm('Remove module?');" style="display:inline;">
                                            <input type="hidden" name="delete_module" value="1">
                                            <input type="hidden" name="module_id" value="<?= e((string)$m['id']) ?>">
                                            <button type="submit" class="action-btn delete-btn" style="padding: 4px 8px; font-size: 11px;">✕</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>