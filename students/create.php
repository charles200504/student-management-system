<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Strict Admin-Only Guard
require_admin();

$errors = [];
$coursesStmt = $pdo->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC');
$courses = $coursesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNo = strtoupper(trim((string)($_POST['student_no'] ?? '')));
    $firstName = trim((string)($_POST['first_name'] ?? ''));
    $lastName = trim((string)($_POST['last_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $dob = $_POST['date_of_birth'] !== '' ? $_POST['date_of_birth'] : null;
    $gender = $_POST['gender'] ?? 'Other';
    $courseId = (int)($_POST['course_id'] ?? 0);
    $academicYear = trim((string)($_POST['academic_year'] ?? 'Year 1, Sem 1'));
    $status = $_POST['status'] ?? 'Active';
    $address = trim((string)($_POST['address'] ?? ''));
    $enrollmentDate = $_POST['enrollment_date'] !== '' ? $_POST['enrollment_date'] : date('Y-m-d');

    // Basic Validation
    if ($studentNo === '') $errors[] = 'Student number is required.';
    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '') $errors[] = 'Last name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($courseId <= 0) $errors[] = 'Please select a valid academic course.';

    // Check Duplicate Student No & Email
    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM students WHERE student_no = :sno OR email = :email LIMIT 1');
        $chk->execute(['sno' => $studentNo, 'email' => $email]);
        if ($chk->fetch()) {
            $errors[] = 'A student with this ID number or Email address already exists.';
        }
    }

    // Handle Profile Image Upload
    $profilePic = null;
    if (!$errors && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed, true)) {
            $newFileName = 'student_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = '../assets/uploads/' . $newFileName;
            if (move_uploaded_file($fileTmp, $dest)) {
                $profilePic = $newFileName;
            }
        }
    }

    // Insert Student Record
    if (!$errors) {
        $sql = 'INSERT INTO students (student_no, first_name, last_name, email, phone, date_of_birth, gender, course_id, academic_year, status, address, enrollment_date, profile_pic) 
                VALUES (:sno, :fn, :ln, :email, :phone, :dob, :gender, :cid, :ayear, :status, :addr, :edate, :pic)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'sno' => $studentNo,
            'fn' => $firstName,
            'ln' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'dob' => $dob,
            'gender' => $gender,
            'cid' => $courseId,
            'ayear' => $academicYear,
            'status' => $status,
            'addr' => $address,
            'edate' => $enrollmentDate,
            'pic' => $profilePic
        ]);

        $newStudentId = (int)$pdo->lastInsertId();

        // Auto-seed default course modules
        $defaultModules = [
            ['code' => 'SE201', 'name' => 'Object-Oriented Analysis & Design', 'grade' => 'A', 'credits' => 20],
            ['code' => 'SE202', 'name' => 'Software Testing & Quality Assurance', 'grade' => 'A', 'credits' => 20],
            ['code' => 'SE203', 'name' => 'Enterprise Application Development', 'grade' => 'A-', 'credits' => 20],
            ['code' => 'SE204', 'name' => 'Cloud Computing & DevOps Architecture', 'grade' => 'A', 'credits' => 20]
        ];

        $insMod = $pdo->prepare('INSERT INTO student_modules (student_id, module_code, module_name, grade, credits) VALUES (:sid, :code, :name, :grade, :credits)');
        foreach ($defaultModules as $dm) {
            $insMod->execute([
                'sid' => $newStudentId,
                'code' => $dm['code'],
                'name' => $dm['name'],
                'grade' => $dm['grade'],
                'credits' => $dm['credits']
            ]);
        }

        calculate_and_update_gpa($pdo, $newStudentId);
        redirect('show.php?id=' . $newStudentId);
    }
}

$pageTitle = 'Register New Student';
$basePath = '../';
$activePage = 'students';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Administration</p>
        <h2>Register New Student</h2>
    </div>
    <a href="index.php" class="button muted">← Back to Directory</a>
</section>

<?php if ($errors): ?>
    <div class="alert error">
        <?php foreach ($errors as $err): ?>
            <div>• <?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="panel form-card">
    <form method="post" enctype="multipart/form-data">
        <div class="grid-2">
            <label>
                <span>Student ID / Number *</span>
                <input type="text" name="student_no" placeholder="e.g. STU009" value="<?= e($_POST['student_no'] ?? '') ?>" required>
            </label>

            <label>
                <span>Enrolled Degree Course *</span>
                <select name="course_id" required>
                    <option value="">-- Select Degree --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= e((string)$c['id']) ?>" <?= (isset($_POST['course_id']) && (int)$_POST['course_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= e($c['course_code']) ?> - <?= e($c['course_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>First Name *</span>
                <input type="text" name="first_name" placeholder="First Name" value="<?= e($_POST['first_name'] ?? '') ?>" required>
            </label>

            <label>
                <span>Last Name *</span>
                <input type="text" name="last_name" placeholder="Last Name" value="<?= e($_POST['last_name'] ?? '') ?>" required>
            </label>

            <label>
                <span>Institutional / Personal Email *</span>
                <input type="email" name="email" placeholder="student@domain.com" value="<?= e($_POST['email'] ?? '') ?>" required>
            </label>

            <label>
                <span>Phone Number</span>
                <input type="text" name="phone" placeholder="+94 7X XXX XXXX" value="<?= e($_POST['phone'] ?? '') ?>">
            </label>

            <label>
                <span>Academic Year / Semester</span>
                <input type="text" name="academic_year" placeholder="Year 2, Sem 1" value="<?= e($_POST['academic_year'] ?? 'Year 1, Sem 1') ?>">
            </label>

            <label>
                <span>Status</span>
                <select name="status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Graduated">Graduated</option>
                </select>
            </label>

            <label>
                <span>Gender</span>
                <select name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </label>

            <label>
                <span>Date of Birth</span>
                <input type="date" name="date_of_birth" value="<?= e($_POST['date_of_birth'] ?? '') ?>">
            </label>

            <label>
                <span>Enrollment Date</span>
                <input type="date" name="enrollment_date" value="<?= e($_POST['enrollment_date'] ?? date('Y-m-d')) ?>">
            </label>

            <label>
                <span>Profile Photo</span>
                <input type="file" name="profile_pic" accept="image/*">
            </label>
        </div>

        <label style="margin-top: 16px;">
            <span>Residential Address</span>
            <textarea name="address" rows="3" placeholder="Street, City, Postal Code"><?= e($_POST['address'] ?? '') ?></textarea>
        </label>

        <div class="form-actions">
            <button type="submit" class="button gold-btn">Save Student Record</button>
            <a href="index.php" class="button muted">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>