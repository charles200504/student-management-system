<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$studentId = (int)($_GET['student_id'] ?? 0);
$moduleCode = trim((string)($_GET['code'] ?? 'SE201'));
$moduleName = trim((string)($_GET['name'] ?? 'Enterprise Software Development'));

$student = $studentId > 0 ? find_student($pdo, $studentId) : null;
if (!$student) {
    redirect('index.php');
}

if (!isset($_SESSION['submissions'])) {
    $_SESSION['submissions'] = [];
}
$subKey = "{$studentId}_{$moduleCode}";
$currentSub = $_SESSION['submissions'][$subKey] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $uploadedFile = $_FILES['submission_file']['name'] ?? 'Assignment_Submission.zip';
    $comments = trim((string)($_POST['submission_comments'] ?? ''));
    $gitLink = trim((string)($_POST['github_link'] ?? 'https://github.com/'));

    $_SESSION['submissions'][$subKey] = [
        'status' => 'Submitted for grading',
        'file' => $uploadedFile,
        'comments' => $comments,
        'git_link' => $gitLink,
        'submitted_at' => date('d F Y, g:i A')
    ];
    redirect("assignment_submission.php?student_id={$studentId}&code=" . urlencode($moduleCode) . "&name=" . urlencode($moduleName) . "&submitted=1");
}

if (isset($_GET['remove']) && $_GET['remove'] === '1') {
    unset($_SESSION['submissions'][$subKey]);
    redirect("assignment_submission.php?student_id={$studentId}&code=" . urlencode($moduleCode) . "&name=" . urlencode($moduleName));
}

$pageTitle = "{$moduleCode}: Assignment 01 Submission";
$basePath = '../';
$activePage = 'lms';

require_once '../includes/header.php';
?>

<div class="moodle-container">
    <!-- Assignment Brief Header -->
    <div class="moodle-card" style="border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h2 style="font-size: 22px; color: #ffffff;"><?= e($moduleCode) ?> - <?= e($moduleName) ?>: Assignment 01</h2>
            <a href="lms.php?id=<?= e((string)$student['id']) ?>" class="button muted" style="font-size: 12px; padding: 6px 14px;">← Back to SLearn LMS</a>
        </div>
        
        <div class="submission-req-list">
            <ul>
                <li>Introduction about your project architecture and system overview.</li>
                <li>Team composition and detailed member responsibility matrices.</li>
                <li>GitHub Repository link (Ensure valid branch and tag for <code>Assignment 01 - Milestone Release</code>).</li>
                <li>Deployment instructions (<code>How to run / XAMPP / Database import</code>).</li>
                <li>High-resolution user interface captures across all system routes.</li>
            </ul>
        </div>

        <div style="margin-top: 16px; padding: 12px 16px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">📄</span>
                <span style="font-weight: 700; color: #93c5fd; font-size: 14px;">Group Project Brief.docx</span>
            </div>
            <span style="font-size: 12px; color: var(--text-muted);">20 August 2026, 5:24 PM</span>
        </div>
    </div>

    <!-- Submission Status Table Box -->
    <div class="moodle-card" style="margin-top: 24px; border-radius: 12px;">
        <h3 style="font-size: 18px; color: #ffffff; margin-bottom: 18px; font-weight: 700;">Submission status</h3>

        <table class="moodle-status-table">
            <tbody>
                <tr>
                    <td class="lbl-col">Candidate / Student</td>
                    <td class="val-col">
                        <strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong> 
                        <span style="color: var(--primary-gold); margin-left: 6px;">(<?= e($student['student_no']) ?>)</span>
                    </td>
                </tr>
                <tr>
                    <td class="lbl-col">Submission status</td>
                    <td class="val-col">
                        <?php if ($currentSub): ?>
                            <span class="moodle-tag tag-success">✓ <?= e($currentSub['status']) ?></span>
                        <?php else: ?>
                            <span class="moodle-tag tag-muted">Nothing has been submitted for this assignment</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl-col">Grading status</td>
                    <td class="val-col">
                        <?php if ($currentSub): ?>
                            <span style="color: #fbbf24; font-weight: 700;">Pending Faculty Evaluation</span>
                        <?php else: ?>
                            <span style="color: var(--text-muted);">Not graded</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl-col">Due date</td>
                    <td class="val-col">Monday, 24 August 2026, 12:00 AM</td>
                </tr>
                <tr>
                    <td class="lbl-col">Time remaining</td>
                    <td class="val-col" style="color: #34d399; font-weight: 700;">11 hours 49 mins</td>
                </tr>
                <tr>
                    <td class="lbl-col">Last modified</td>
                    <td class="val-col"><?= $currentSub ? e($currentSub['submitted_at']) : '-' ?></td>
                </tr>
                <?php if ($currentSub): ?>
                <tr>
                    <td class="lbl-col">File submissions</td>
                    <td class="val-col">
                        <span style="color: #38bdf8; font-weight: 700;">📁 <?= e($currentSub['file']) ?></span>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">GitHub URL: <a href="<?= e($currentSub['git_link']) ?>" target="_blank" style="color: var(--primary-gold);"><?= e($currentSub['git_link']) ?></a></div>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="lbl-col">Submission comments</td>
                    <td class="val-col">
                        <span style="color: var(--text-muted); font-size: 13px;">
                            <?= $currentSub && !empty($currentSub['comments']) ? e($currentSub['comments']) : '▶ Comments (0)' ?>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="display: flex; justify-content: center; gap: 14px; margin-top: 28px;">
            <?php if ($currentSub): ?>
                <button type="button" onclick="document.getElementById('submissionModal').style.display='grid'" class="button gold-btn">✏️ Edit submission</button>
                <a href="assignment_submission.php?student_id=<?= e((string)$student['id']) ?>&code=<?= urlencode($moduleCode) ?>&name=<?= urlencode($moduleName) ?>&remove=1" onclick="return confirm('Remove your current submission?');" class="button danger">🗑️ Remove submission</a>
            <?php else: ?>
                <button type="button" onclick="document.getElementById('submissionModal').style.display='grid'" class="button gold-btn" style="padding: 12px 32px; font-size: 15px; font-weight: 800;">
                    Add submission
                </button>
            <?php endif; ?>
        </div>

        <?php if (!$currentSub): ?>
            <p style="text-align: center; color: var(--text-muted); font-size: 13px; margin-top: 14px;">You have not made a submission yet.</p>
        <?php endif; ?>
    </div>
</div>

<div id="submissionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100; backdrop-filter: blur(8px); place-items: center; padding: 20px;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: 16px; max-width: 600px; width: 100%; padding: 28px; box-shadow: 0 20px 50px rgba(0,0,0,0.7);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 18px;">
            <h3 style="color: #ffffff; font-size: 18px; font-weight: 800;">Upload Assignment Submission</h3>
            <button onclick="document.getElementById('submissionModal').style.display='none'" style="background: none; border: none; color: var(--text-muted); font-size: 22px; cursor: pointer;">✕</button>
        </div>

        <form method="post" enctype="multipart/form-data" action="assignment_submission.php?student_id=<?= e((string)$student['id']) ?>&code=<?= urlencode($moduleCode) ?>&name=<?= urlencode($moduleName) ?>">
            <input type="hidden" name="submit_assignment" value="1">

            <label style="margin-bottom: 14px;">
                <span>File Submission (ZIP, PDF, DOCX - Max 50MB) *</span>
                <input type="file" name="submission_file" required style="padding: 10px;">
            </label>

            <label style="margin-bottom: 14px;">
                <span>GitHub Repository URL *</span>
                <input type="url" name="github_link" placeholder="https://github.com/username/project-repo" required>
            </label>

            <label style="margin-bottom: 20px;">
                <span>Submission Notes / Member Contribution Summary</span>
                <textarea name="submission_comments" rows="3" placeholder="State group member responsibilities and special instructions..."></textarea>
            </label>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('submissionModal').style.display='none'" class="button muted">Cancel</button>
                <button type="submit" class="button gold-btn">Save Changes & Submit</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>