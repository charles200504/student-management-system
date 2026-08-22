<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$studentNo = trim((string)($_GET['no'] ?? ''));
$student = null;

if ($studentNo !== '') {
    $stmt = $pdo->prepare('
        SELECT s.*, c.course_code, c.course_name 
        FROM students s 
        INNER JOIN courses c ON c.id = s.course_id 
        WHERE s.student_no = :no 
        LIMIT 1
    ');
    $stmt->execute(['no' => $studentNo]);
    $student = $stmt->fetch();
}

$pageTitle = 'Digital ID Verification';
$basePath = '../';
$activePage = '';

require_once '../includes/header.php';
?>

<div class="auth-container" style="min-height: calc(100vh - 200px);">
    <div class="auth-card" style="max-width: 540px; text-align: center;">
        <?php if ($student): ?>
            <?php 
                $enrollYear = (int)date('Y', strtotime($student['enrollment_date']));
                $expiryYear = $enrollYear + 3;
                $isExpired = date('Y') > $expiryYear || $student['status'] === 'Inactive';
            ?>
            <div style="width: 60px; height: 60px; border-radius: 50%; background: <?= $isExpired ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)' ?>; color: <?= $isExpired ? '#ef4444' : '#10b981' ?>; font-size: 28px; display: grid; place-items: center; margin: 0 auto 16px;">
                <?= $isExpired ? '⚠️' : '✓' ?>
            </div>

            <h2 style="font-size: 22px; margin-bottom: 4px;">Institutional Record Verified</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 24px;">National School of Business Management / Plymouth Affiliate</p>

            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <?php if (!empty($student['profile_pic'])): ?>
                    <img src="../assets/uploads/<?= e($student['profile_pic']) ?>" alt="Avatar" style="width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-gold);">
                <?php else: ?>
                    <div style="width: 85px; height: 85px; border-radius: 50%; background: var(--bg-surface-elevated); border: 2px solid var(--border); display: grid; place-items: center; font-size: 28px; font-weight: 800; color: var(--primary-gold);">
                        <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <h3 style="font-size: 18px; color: #ffffff;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h3>
            <p style="color: var(--primary-gold); font-weight: 700; font-size: 14px; margin-bottom: 16px;"><?= e($student['student_no']) ?></p>

            <div style="background: var(--bg-surface-elevated); border-radius: 12px; padding: 16px; text-align: left; margin-bottom: 20px; font-size: 13px; display: grid; gap: 8px;">
                <div><strong style="color: var(--text-muted);">Program:</strong> <span style="color: #ffffff;"><?= e($student['course_name']) ?> (<?= e($student['course_code']) ?>)</span></div>
                <div><strong style="color: var(--text-muted);">Lifecycle Status:</strong> <span class="badge badge-status badge-<?= strtolower(e($student['status'])) ?>"><?= e($student['status']) ?></span></div>
                <div><strong style="color: var(--text-muted);">Academic Standing:</strong> <span style="color: #ffffff;"><?= e($student['academic_status'] ?? 'Good Standing') ?></span></div>
                <div><strong style="color: var(--text-muted);">Valid Period:</strong> <span style="color: #ffffff;"><?= $enrollYear ?> – <?= $expiryYear ?> (<?= $isExpired ? 'EXPIRED' : 'ACTIVE' ?>)</span></div>
            </div>

            <div style="font-size: 11px; color: var(--text-muted);">Encrypted Verification Hash: SHA256:<?= hash('sha256', $student['student_no'] . $student['email']) ?></div>
        <?php else: ?>
            <div style="color: #ef4444; font-size: 40px; margin-bottom: 12px;">❌</div>
            <h2 style="font-size: 20px; margin-bottom: 8px;">Invalid Verification Token</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">No student records found matching this identifier or barcode signature.</p>
            <a href="../index.php" class="button muted">Return to Safety</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>