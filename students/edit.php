<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

require_admin();

$id = (int)($_GET['id'] ?? 0);
$student = $id > 0 ? find_student($pdo, $id) : null;

if (!$student) {
    redirect('index.php');
}

$courses = get_courses($pdo);
$errors = [];

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
    $enrollmentDate = $_POST['enrollment_date'] !== '' ? $_POST['enrollment_date'] : $student['enrollment_date'];

    if ($studentNo === '') $errors[] = 'Student number is required.';
    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '') $errors[] = 'Last name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($courseId <= 0) $errors[] = 'Please select a valid academic course.';

    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM students WHERE (student_no = :sno OR email = :email) AND id != :id LIMIT 1');
        $chk->execute(['sno' => $studentNo, 'email' => $email, 'id' => $id]);
        if ($chk->fetch()) {
            $errors[] = 'Another student with this ID number or Email address already exists.';
        }
    }

    $profilePic = $student['profile_pic'];
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

    if (!$errors) {
        $sql = 'UPDATE students SET 
                student_no = :sno, 
                first_name = :fn, 
                last_name = :ln, 
                email = :email, 
                phone = :phone, 
                date_of_birth = :dob, 
                gender = :gender, 
                course_id = :cid, 
                academic_year = :ayear, 
                status = :status, 
                address = :addr, 
                enrollment_date = :edate, 
                profile_pic = :pic 
                WHERE id = :id';
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
            'pic' => $profilePic,
            'id' => $id
        ]);

        redirect('show.php?id=' . $id);
    }
}

$pageTitle = 'Edit Student - ' . $student['first_name'] . ' ' . $student['last_name'];
$basePath = '../';
$activePage = 'students';

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Update Profile</p>
        <h2>Edit: <?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
    </div>
    <a href="index.php" class="button muted">Back to Students</a>
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
                <input type="text" name="student_no" value="<?= e($_POST['student_no'] ?? $student['student_no']) ?>" required>
            </label>

            <label>
                <span>Enrolled Degree Course *</span>
                <select name="course_id" required>
                    <option value="">-- Select Degree --</option>
                    <?php foreach ($courses as $c): ?>
                        <?php $selected = (int)($_POST['course_id'] ?? $student['course_id']) === (int)$c['id'] ? 'selected' : ''; ?>
                        <option value="<?= e((string)$c['id']) ?>" <?= $selected ?>>
                            <?= e($c['course_code']) ?> - <?= e($c['course_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>First Name *</span>
                <input type="text" name="first_name" value="<?= e($_POST['first_name'] ?? $student['first_name']) ?>" required>
            </label>

            <label>
                <span>Last Name *</span>
                <input type="text" name="last_name" value="<?= e($_POST['last_name'] ?? $student['last_name']) ?>" required>
            </label>

            <label>
                <span>Institutional / Personal Email *</span>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? $student['email']) ?>" required>
            </label>

            <label>
                <span>Phone Number</span>
                <input type="text" name="phone" value="<?= e($_POST['phone'] ?? $student['phone']) ?>">
            </label>

            <label>
                <span>Academic Year / Semester</span>
                <input type="text" name="academic_year" value="<?= e($_POST['academic_year'] ?? ($student['academic_year'] ?? 'Year 1, Sem 1')) ?>">
            </label>

            <label>
                <span>Status</span>
                <select name="status">
                    <?php foreach (['Active', 'Inactive', 'Graduated'] as $st): ?>
                        <option value="<?= $st ?>" <?= ($_POST['status'] ?? $student['status']) === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>Gender</span>
                <select name="gender">
                    <?php foreach (['Male', 'Female', 'Other'] as $gen): ?>
                        <option value="<?= $gen ?>" <?= ($_POST['gender'] ?? $student['gender']) === $gen ? 'selected' : '' ?>><?= $gen ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>Date of Birth</span>
                <input type="date" name="date_of_birth" value="<?= e($_POST['date_of_birth'] ?? $student['date_of_birth']) ?>">
            </label>

            <label>
                <span>Enrollment Date</span>
                <input type="date" name="enrollment_date" value="<?= e($_POST['enrollment_date'] ?? $student['enrollment_date']) ?>">
            </label>

            <label>
                <span>Update Profile Photo</span>
                <input type="file" name="profile_pic" accept="image/*">
            </label>
        </div>

        <label style="margin-top: 16px;">
            <span>Residential Address</span>
            <textarea name="address" rows="3"><?= e($_POST['address'] ?? $student['address']) ?></textarea>
        </label>

        <div class="form-actions">
            <button type="submit" class="button gold-btn">Save Changes</button>
            <a href="show.php?id=<?= e((string)$student['id']) ?>" class="button muted">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>