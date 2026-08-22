<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int) ($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

if (!$student) {
    redirect('index.php');
}

// Compute Dynamic Expiry Date (Default: 3 Academic Years from Enrollment)
$enrollDate = !empty($student['enrollment_date']) ? strtotime($student['enrollment_date']) : time();
$expiryTimestamp = strtotime('+3 years', $enrollDate);
$expiryDateFormatted = date('M Y', $expiryTimestamp);
$isExpired = time() > $expiryTimestamp || $student['status'] === 'Inactive';

// Public Verification QR Payload
$verifyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/student-management-system/students/verify.php?no=" . urlencode($student['student_no']);
$qrCodeApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=" . urlencode($verifyUrl);

$pageTitle = 'Smart ID Card - ' . $student['student_no'];
$basePath = '../';
$activePage = 'students';

require_once '../includes/header.php';
?>

<!-- HTML2PDF CDN Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="id-card-wrapper">
    <div class="no-print actions-bar">
        <a class="button muted" href="show.php?id=<?= e((string)$student['id']) ?>">⬅ Back to Profile</a>
        <button class="button dark-btn" onclick="window.print()">🖨 Browser Print</button>
        <button class="button gold-btn" id="btn-download-pdf">📥 Download PDF Card</button>
    </div>

    <!-- Printable & Exportable Smart ID Badge Element -->
    <div id="smart-id-card" class="id-badge-container">
        <!-- FRONT SIDE -->
        <div class="smart-badge-card front">
            <div class="smart-badge-top">
                <div class="badge-top-row">
                    <span class="badge-org">PLYMOUTH / NSBM AFFILIATE</span>
                    <span class="badge-type">STUDENT IDENTITY</span>
                </div>
                <div class="smart-chip"></div>
            </div>

            <div class="smart-badge-body">
                <div class="badge-photo-box">
                    <?php if (!empty($student['profile_pic'])): ?>
                        <img src="../assets/uploads/<?= e($student['profile_pic']) ?>" alt="Student Photo" class="id-profile-img">
                    <?php else: ?>
                        <div class="id-profile-placeholder">
                            <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <span class="id-status-tag <?= $isExpired ? 'expired' : 'valid' ?>">
                        <?= $isExpired ? 'EXPIRED' : 'VALID' ?>
                    </span>
                </div>

                <div class="badge-main-info">
                    <h3 class="student-full-name"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h3>
                    <p class="id-number-pill"><?= e($student['student_no']) ?></p>

                    <div class="id-meta-list">
                        <div class="meta-row">
                            <span class="lbl">PROGRAM</span>
                            <span class="val"><?= e($student['course_code']) ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="lbl">ISSUED</span>
                            <span class="val"><?= date('M Y', $enrollDate) ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="lbl">EXPIRES</span>
                            <span class="val" style="color: <?= $isExpired ? '#ef4444' : '#10b981' ?>;"><?= $expiryDateFormatted ?></span>
                        </div>
                    </div>
                </div>

                <div class="badge-qr-box">
                    <img src="<?= $qrCodeApiUrl ?>" alt="Scan to Verify QR" class="id-qr-img">
                    <span class="qr-label">SCAN TO VERIFY</span>
                </div>
            </div>

            <div class="smart-badge-bottom">
                <span class="auth-text">Official Digital Identification • Issued by Administration</span>
                <div class="auth-bar"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-download-pdf').addEventListener('click', function() {
    const cardElement = document.getElementById('smart-id-card');
    const opt = {
        margin:       10,
        filename:     'Student_ID_<?= e($student['student_no']) ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a5', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(cardElement).save();
});
</script>

<?php require_once '../includes/footer.php'; ?>