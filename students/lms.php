<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

if (!$student) {
    $first = $pdo->query('SELECT id FROM students ORDER BY id ASC LIMIT 1')->fetch();
    if ($first) {
        redirect('lms.php?id=' . $first['id']);
    } else {
        redirect('index.php');
    }
}

$modulesStmt = $pdo->prepare('SELECT * FROM student_modules WHERE student_id = :id ORDER BY id ASC');
$modulesStmt->execute(['id' => $id]);
$modules = $modulesStmt->fetchAll();

$pageTitle = 'SLearn LMS - ' . $student['first_name'] . ' ' . $student['last_name'];
$basePath = '../';
$activePage = 'lms';

require_once '../includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div style="background: linear-gradient(135deg, #022c22 0%, #064e3b 100%); border: 1px solid #059669; border-radius: 14px; padding: 22px 28px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.2);">
    <div>
        <div style="color: #34d399; font-weight: 800; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase;">SLearn Institutional LMS Gateway</div>
        <h2 style="font-size: 24px; color: #ffffff; margin-top: 4px;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($student['student_no']) ?>)</h2>
        <p style="color: #a7f3d0; font-size: 13px; margin-top: 2px;"><?= e($student['course_name']) ?> &bull; <?= e($student['academic_year'] ?? 'Year 1, Sem 1') ?></p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="show.php?id=<?= e((string)$student['id']) ?>" class="button muted">← Back to Profile</a>
    </div>
</div>

<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">📚</div>
        <div class="stat-content">
            <span class="stat-label">REGISTERED MODULES</span>
            <span class="stat-number"><?= count($modules) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);">🎓</div>
        <div class="stat-content">
            <span class="stat-label">CURRENT GPA</span>
            <span class="stat-number"><?= number_format((float)($student['gpa'] ?? 0.0), 2) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="color: #38bdf8; background: rgba(56, 189, 248, 0.1);">⚡</div>
        <div class="stat-content">
            <span class="stat-label">SLEARN ACCESS STATUS</span>
            <span class="stat-number" style="font-size: 18px; color: #34d399;"><?= e($student['status']) ?></span>
        </div>
    </div>
</div>

<div class="panel table-card">
    <div class="panel-header-flex">
        <h3>📖 Active SLearn Modules & Digital Learning Materials</h3>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Module Code</th>
                    <th>Module Title</th>
                    <th>Credits</th>
                    <th>Grade Standing</th>
                    <th>Lecture Notes & Slides</th>
                    <th>Coursework & Submissions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$modules): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No active modules currently registered.</td></tr>
                <?php else: ?>
                    <?php foreach ($modules as $m): ?>
                        <tr>
                            <td><span class="badge badge-course"><?= e($m['module_code']) ?></span></td>
                            <td><strong style="color: #ffffff;"><?= e($m['module_name']) ?></strong></td>
                            <td><?= e((string)$m['credits']) ?> Credits</td>
                            <td><span class="badge badge-active"><?= e($m['grade']) ?></span></td>
                            <td>
                                <button type="button" class="action-btn view-btn" onclick="openSlidesModal('<?= e($m['module_code']) ?>', '<?= e(addslashes($m['module_name'])) ?>')" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                                    📁 Lecture Packs (PDF)
                                </button>
                            </td>
                            <td>
                                <a href="assignment_submission.php?student_id=<?= e((string)$student['id']) ?>&code=<?= urlencode($m['module_code']) ?>&name=<?= urlencode($m['module_name']) ?>" class="action-btn edit-btn" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                                    📝 Coursework & Submission ↗
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="slidesModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 100; backdrop-filter: blur(6px); place-items: center; padding: 20px;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: 16px; max-width: 620px; width: 100%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.6);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <div>
                <span id="modalModuleCode" class="badge badge-course"></span>
                <h3 id="modalModuleName" style="color: #ffffff; font-size: 18px; margin-top: 6px;"></h3>
            </div>
            <button onclick="closeModals()" style="background: none; border: none; color: var(--text-muted); font-size: 22px; cursor: pointer;">✕</button>
        </div>

        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Download formatted institutional lecture handouts & revision packs:</p>
        
        <div style="display: grid; gap: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-elevated); padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border);">
                <div>
                    <strong style="font-size: 13px; color: #f8fafc;">Part 1: Core Fundamentals & Theory</strong>
                    <div style="font-size: 11px; color: var(--text-muted);">Weeks 01–04 &bull; Official Academic PDF</div>
                </div>
                <button onclick="generateLecturePDF(1)" class="button gold-btn" style="padding: 6px 12px; font-size: 12px;">📥 Download PDF</button>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-elevated); padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border);">
                <div>
                    <strong style="font-size: 13px; color: #f8fafc;">Part 2: Advanced Design & Implementation</strong>
                    <div style="font-size: 11px; color: var(--text-muted);">Weeks 05–08 &bull; Official Academic PDF</div>
                </div>
                <button onclick="generateLecturePDF(2)" class="button gold-btn" style="padding: 6px 12px; font-size: 12px;">📥 Download PDF</button>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-elevated); padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border);">
                <div>
                    <strong style="font-size: 13px; color: #f8fafc;">Part 3: Industry Deployment & Testing</strong>
                    <div style="font-size: 11px; color: var(--text-muted);">Weeks 09–12 &bull; Official Academic PDF</div>
                </div>
                <button onclick="generateLecturePDF(3)" class="button gold-btn" style="padding: 6px 12px; font-size: 12px;">📥 Download PDF</button>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button onclick="closeModals()" class="button muted">Close</button>
        </div>
    </div>
</div>

<script>
let currentActiveCode = '';
let currentActiveName = '';

const studentName = '<?= e(addslashes($student['first_name'] . ' ' . $student['last_name'])) ?>';
const studentNo = '<?= e($student['student_no']) ?>';
const studentDegree = '<?= e(addslashes($student['course_name'])) ?>';

function openSlidesModal(code, name) {
    currentActiveCode = code;
    currentActiveName = name;
    document.getElementById('modalModuleCode').innerText = code;
    document.getElementById('modalModuleName').innerText = name;
    document.getElementById('slidesModal').style.display = 'grid';
}

function closeModals() {
    document.getElementById('slidesModal').style.display = 'none';
}

function generateLecturePDF(part) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ format: 'a4', unit: 'mm' });

    doc.setFillColor(15, 23, 42);
    doc.rect(0, 0, 210, 36, 'F');

    doc.setFont('helvetica', 'bold');
    doc.setTextColor(245, 158, 11);
    doc.setFontSize(14);
    doc.text('NATIONAL SCHOOL OF BUSINESS MANAGEMENT / PLYMOUTH', 15, 15);

    doc.setFont('helvetica', 'normal');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(10);
    doc.text('FACULTY OF COMPUTING & APPLIED SCIENCES • SLEARN ACADEMIC HANDOUT', 15, 23);

    doc.setTextColor(15, 23, 42);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.text(`${currentActiveCode}: ${currentActiveName}`, 15, 48);

    doc.setFontSize(11);
    doc.setTextColor(100, 116, 139);
    doc.setFont('helvetica', 'normal');
    doc.text(`Enrolled Student: ${studentName} (${studentNo}) | Program: ${studentDegree}`, 15, 55);

    doc.setDrawColor(226, 232, 240);
    doc.line(15, 60, 195, 60);

    doc.setTextColor(30, 41, 59);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(13);

    let topicTitle = '';
    let bullets = [];

    if (part === 1) {
        topicTitle = 'PART 1: THEORETICAL FOUNDATIONS & ARCHITECTURE (WEEKS 01–04)';
        bullets = [
            '1. Module Syllabus & Assessment Overview: 20 Credits distribution, continuous assessments, and grading scheme.',
            '2. Domain Modeling & System Requirements: Functional analysis, architectural boundary definition, and data flows.',
            '3. Core Structural Principles: Decoupling business logic from user interfaces and persistence layers.',
            '4. Tutorial Practice: Analyzing entity schemas, key constraints, and relational integrity.'
        ];
    } else if (part === 2) {
        topicTitle = 'PART 2: ADVANCED ENGINEERING & IMPLEMENTATION (WEEKS 05–08)';
        bullets = [
            '1. Enterprise Patterns: Service Repository patterns, dependency management, and session state controllers.',
            '2. Data Persistence & Query Optimization: Relational indexing, prepared statements, and transaction atomicity.',
            '3. Security Framework: Defense against SQL injection, Cross-Site Scripting (XSS), and cryptographic password hashing.',
            '4. Laboratory Workshop: Practical implementation of modular handlers and dynamic schema execution.'
        ];
    } else {
        topicTitle = 'PART 3: TESTING, DEPLOYMENT & CASE STUDIES (WEEKS 09–12)';
        bullets = [
            '1. Quality Assurance & Defect Tracking: Black-box/white-box boundary testing and regression workflows.',
            '2. System Integration: Connecting web presentation front-ends with database management backends.',
            '3. Performance Profiling: Query load balancing, asset minification, and latency optimization.',
            '4. Final Project Revision: Viva preparation, documentation requirements, and code repository formatting.'
        ];
    }

    doc.text(topicTitle, 15, 72);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10.5);
    doc.setTextColor(51, 65, 85);

    let yPos = 84;
    bullets.forEach(item => {
        const splitText = doc.splitTextToSize(item, 180);
        doc.text(splitText, 15, yPos);
        yPos += splitText.length * 7 + 4;
    });

    doc.setFillColor(248, 250, 252);
    doc.setDrawColor(203, 213, 225);
    doc.roundedRect(15, yPos + 6, 180, 40, 3, 3, 'FD');

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(15, 23, 42);
    doc.text('Key Learning Outcomes:', 20, yPos + 16);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.setTextColor(71, 85, 105);
    doc.text('• Demonstrate technical proficiency in the relevant theoretical and programmatic paradigms.', 20, yPos + 24);
    doc.text('• Implement production-grade modules that satisfy enterprise standards for security and reliability.', 20, yPos + 32);
    doc.text('• Accurately analyze performance metrics, modular defect matrices, and academic grading benchmarks.', 20, yPos + 40);

    doc.setFontSize(8);
    doc.setTextColor(148, 163, 184);
    doc.text(`Official Course Material • Generated via StudentSys SLearn Portal • ${new Date().toLocaleDateString()}`, 15, 285);

    doc.save(`${currentActiveCode}_Lecture_Notes_Part${part}.pdf`);
}

window.onclick = function(event) {
    if (event.target === document.getElementById('slidesModal')) {
        closeModals();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>