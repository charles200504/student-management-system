<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

if (!$student) {
    redirect('index.php');
}

$pageTitle = 'Smart ID Card - ' . $student['student_no'];
$basePath = '../';
$activePage = is_admin() ? 'students' : 'id_card';

$fullName = trim($student['first_name'] . ' ' . $student['last_name']);
$studentNo = trim((string)$student['student_no']);
$courseCode = trim((string)$student['course_code']);
$status = trim((string)$student['status']);

require_once '../includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div class="no-print" style="display: flex; justify-content: center; gap: 14px; margin-bottom: 24px;">
    <a href="show.php?id=<?= e((string)$student['id']) ?>" class="button muted">← Back to Profile</a>
    <button onclick="window.print()" class="button dark-btn">🖨️ Browser Print</button>
    <button onclick="downloadPdfCard()" class="button gold-btn">📥 Download PDF Card</button>
</div>

<div class="id-badge-container">
    <div class="smart-badge-card" id="smart-id-card">
        <div class="badge-top-row">
            <span class="badge-org">PLYMOUTH / NSBM AFFILIATE</span>
            <span class="badge-type">STUDENT IDENTITY</span>
        </div>

        <div class="smart-badge-body">
            <div class="badge-photo-box">
                <?php if (!empty($student['profile_pic'])): ?>
                    <img src="../assets/uploads/<?= e($student['profile_pic']) ?>" alt="Photo" class="id-profile-img">
                <?php else: ?>
                    <div class="id-profile-placeholder">
                        <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <span class="id-status-tag valid"><?= e($student['status']) ?></span>
            </div>

            <div class="badge-info-box">
                <div class="student-full-name"><?= e($fullName) ?></div>
                <div class="id-number-pill"><?= e($studentNo) ?></div>
                
                <div class="id-meta-list">
                    <div class="meta-row">
                        <span class="lbl">PROGRAM</span>
                        <span class="val"><?= e($courseCode) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="lbl">ISSUED</span>
                        <span class="val"><?= date('M Y', strtotime($student['enrollment_date'] ?? 'now')) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="lbl">EXPIRES</span>
                        <span class="val"><?= date('M Y', strtotime(($student['enrollment_date'] ?? 'now') . ' +3 years')) ?></span>
                    </div>
                </div>
            </div>

            <!-- Instant-Scan QR Container -->
            <div class="badge-qr-box" style="background: #ffffff; padding: 6px; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 96px;">
                <div id="qrcode-canvas" style="display: flex; justify-content: center; align-items: center;"></div>
                <span class="qr-label" style="font-size: 8px; font-weight: 800; color: #0f172a; margin-top: 4px;">SCAN TO VERIFY</span>
            </div>
        </div>

        <div class="smart-badge-bottom">
            <span>Official Digital Identification &bull; Issued by Administration</span>
            <div class="auth-bar"></div>
        </div>
    </div>
</div>

<script>
// Lightweight payload: clean, fast, and 100% recognized by iOS and Android camera apps
const quickScanPayload = "STUDENTSYS VERIFIED | ID: <?= addslashes($studentNo) ?> | Name: <?= addslashes($fullName) ?> | Course: <?= addslashes($courseCode) ?> | Status: <?= addslashes($status) ?>";

const qrContainer = document.getElementById("qrcode-canvas");
qrContainer.innerHTML = "";

new QRCode(qrContainer, {
    text: quickScanPayload,
    width: 88,
    height: 88,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.L
});

function downloadPdfCard() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: [85.6, 53.98]
    });

    doc.setFillColor(15, 23, 42);
    doc.roundedRect(2, 2, 81.6, 49.98, 3, 3, 'F');

    doc.setFont('helvetica', 'bold');
    doc.setTextColor(245, 158, 11);
    doc.setFontSize(7);
    doc.text('PLYMOUTH / NSBM AFFILIATE', 6, 8);

    doc.setFont('helvetica', 'normal');
    doc.setTextColor(148, 163, 184);
    doc.setFontSize(5.5);
    doc.text('STUDENT IDENTITY', 58, 8);

    doc.setDrawColor(51, 65, 85);
    doc.line(6, 10, 79, 10);

    doc.setFillColor(30, 41, 59);
    doc.roundedRect(6, 14, 18, 22, 2, 2, 'F');
    doc.setTextColor(245, 158, 11);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.text('<?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>', 11, 27);

    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(8);
    doc.text('<?= e($fullName) ?>', 28, 18);

    doc.setTextColor(245, 158, 11);
    doc.setFontSize(7);
    doc.text('<?= e($studentNo) ?>', 28, 23);

    doc.setFont('helvetica', 'normal');
    doc.setTextColor(148, 163, 184);
    doc.setFontSize(6);
    doc.text('Program: <?= e($courseCode) ?>', 28, 28);
    doc.text('Status: <?= e($status) ?>', 28, 32);

    try {
        const qrCanvas = document.querySelector('#qrcode-canvas canvas') || document.querySelector('#qrcode-canvas img');
        if (qrCanvas) {
            const qrDataUrl = qrCanvas.toDataURL ? qrCanvas.toDataURL("image/png") : qrCanvas.src;
            doc.addImage(qrDataUrl, 'PNG', 58, 14, 21, 21);
        }
    } catch(e) {}

    doc.setFontSize(4.5);
    doc.setTextColor(100, 116, 139);
    doc.text('Official Digital ID • StudentSys Portal', 6, 48);

    doc.save('StudentID_<?= e($studentNo) ?>.pdf');
}
</script>

<?php require_once '../includes/footer.php'; ?>