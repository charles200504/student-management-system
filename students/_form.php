<?php
$courses = get_courses($pdo);
$student = $student ?? [
    'id' => '',
    'student_no' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'date_of_birth' => '',
    'gender' => 'Other',
    'address' => '',
    'profile_pic' => '',
    'course_id' => '',
    'academic_year' => 'Year 1, Sem 1',
    'academic_status' => 'Good Standing',
    'gpa' => '3.50',
    'enrollment_date' => date('Y-m-d'),
    'status' => 'Active',
];
$errors = $errors ?? [];
$isEdit = !empty($student['id']);
?>

<?php if ($errors): ?>
    <div class="alert error">
        <ul style="margin-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Real-time Duplicate Alert Container -->
<div id="duplicate-warning" class="alert error" style="display: none; margin-bottom: 20px;"></div>

<!-- Basic Demographic Info -->
<h3 style="font-size: 16px; margin-bottom: 14px; color: var(--primary-gold);">👤 Personal Details</h3>
<div class="grid-2">
    <label>
        <span>Student Number *</span>
        <input id="input_student_no" type="text" name="student_no" placeholder="STU004" value="<?= e($student['student_no']) ?>" required>
        <small id="status_student_no" style="font-size: 11px; margin-top: 4px; display: block;"></small>
    </label>

    <label>
        <span>Email Address *</span>
        <input id="input_email" type="email" name="email" placeholder="student@example.com" value="<?= e($student['email']) ?>" required>
        <small id="status_email" style="font-size: 11px; margin-top: 4px; display: block;"></small>
    </label>

    <label>
        <span>First Name *</span>
        <input id="input_first_name" type="text" name="first_name" placeholder="John" value="<?= e($student['first_name']) ?>" required>
    </label>

    <label>
        <span>Last Name *</span>
        <input id="input_last_name" type="text" name="last_name" placeholder="Doe" value="<?= e($student['last_name']) ?>" required>
        <small id="status_name" style="font-size: 11px; margin-top: 4px; display: block;"></small>
    </label>

    <label>
        <span>Phone Number</span>
        <input type="text" name="phone" placeholder="0771234567" value="<?= e($student['phone']) ?>">
    </label>

    <label>
        <span>Date of Birth</span>
        <input type="date" name="date_of_birth" value="<?= e($student['date_of_birth']) ?>">
    </label>

    <label>
        <span>Gender *</span>
        <select name="gender" required>
            <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                <option value="<?= e($gender) ?>" <?= $student['gender'] === $gender ? 'selected' : '' ?>>
                    <?= e($gender) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span>Enrollment Date *</span>
        <input type="date" name="enrollment_date" value="<?= e($student['enrollment_date']) ?>" required>
    </label>
</div>

<!-- Academic Information Section -->
<h3 style="font-size: 16px; margin: 24px 0 14px; color: var(--primary-gold);">🎓 Academic Information</h3>
<div class="grid-2">
    <label>
        <span>Current Course / Degree *</span>
        <select name="course_id" required>
            <option value="">Select a Degree Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= e((string)$course['id']) ?>"
                    <?= (string)$student['course_id'] === (string)$course['id'] ? 'selected' : '' ?>>
                    <?= e($course['course_code'] . ' - ' . $course['course_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span>Year / Semester *</span>
        <select name="academic_year" required>
            <?php 
            $years = ['Year 1, Sem 1', 'Year 1, Sem 2', 'Year 2, Sem 1', 'Year 2, Sem 2', 'Year 3, Sem 1', 'Year 3, Sem 2', 'Graduated'];
            foreach ($years as $yr): ?>
                <option value="<?= e($yr) ?>" <?= ($student['academic_year'] ?? '') === $yr ? 'selected' : '' ?>>
                    <?= e($yr) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span>Academic Standing *</span>
        <select name="academic_status" required>
            <?php foreach (["Good Standing", "Dean's List", "On Probation", "Suspended"] as $astat): ?>
                <option value="<?= e($astat) ?>" <?= ($student['academic_status'] ?? '') === $astat ? 'selected' : '' ?>>
                    <?= e($astat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span>Cumulative GPA (0.00 - 4.00) *</span>
        <input type="number" step="0.01" min="0" max="4.00" name="gpa" placeholder="3.50" value="<?= e((string)($student['gpa'] ?? '0.00')) ?>" required>
    </label>

    <label>
        <span>Enrollment Lifecycle Status *</span>
        <select name="status" required>
            <?php foreach (['Active', 'Inactive', 'Graduated'] as $status): ?>
                <option value="<?= e($status) ?>" <?= $student['status'] === $status ? 'selected' : '' ?>>
                    <?= e($status) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
</div>

<!-- Profile Picture Upload & Removal Box -->
<div class="panel" style="margin-top: 20px; border: 1px dashed var(--border-light); background: var(--bg-surface-elevated);">
    <label>
        <span>Student Profile Photo (JPG, PNG, WEBP - Max 2MB)</span>
        <input type="file" name="profile_pic" accept="image/jpeg,image/png,image/webp">
    </label>
    <?php if (!empty($student['profile_pic'])): ?>
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="../assets/uploads/<?= e($student['profile_pic']) ?>" alt="Current Profile" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-gold);">
                <span style="font-size: 13px; color: var(--text-muted);">Current photo attached. Select a file to replace it.</span>
            </div>
            
            <!-- Remove Photo Option Checkbox -->
            <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; color: #f87171; font-weight: 700; font-size: 13px; margin: 0;">
                <input type="checkbox" name="remove_profile_pic" value="1" style="width: auto; margin: 0; cursor: pointer;">
                <span>🗑️ Remove Photo</span>
            </label>
        </div>
    <?php endif; ?>
</div>

<label style="margin-top: 18px;">
    <span>Residential Address</span>
    <textarea name="address" rows="2" placeholder="Enter student residential address..."><?= e($student['address']) ?></textarea>
</label>

<div class="form-actions">
    <button id="submit_btn" class="button gold-btn" type="submit"><?= $isEdit ? 'Update Student Profile' : 'Save & Register Student' ?></button>
    <a class="button muted" href="index.php">Cancel</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const studentId = '<?= e((string)$student['id']) ?>';
    const studentNoInput = document.getElementById('input_student_no');
    const emailInput = document.getElementById('input_email');
    const fNameInput = document.getElementById('input_first_name');
    const lNameInput = document.getElementById('input_last_name');

    const statusStudentNo = document.getElementById('status_student_no');
    const statusEmail = document.getElementById('status_email');
    const statusName = document.getElementById('status_name');
    const submitBtn = document.getElementById('submit_btn');

    let hasDuplicateId = false;
    let hasDuplicateEmail = false;

    function updateSubmitState() {
        if (hasDuplicateId || hasDuplicateEmail) {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    }

    async function checkDuplicate(field, value, statusElement) {
        if (!value.trim()) {
            statusElement.innerText = '';
            return false;
        }
        try {
            const res = await fetch(`check_duplicate.php?field=${field}&value=${encodeURIComponent(value)}&exclude_id=${studentId}`);
            const data = await res.json();
            if (data.exists) {
                statusElement.innerText = data.message;
                statusElement.style.color = '#ef4444';
                return true;
            } else {
                statusElement.innerText = '✓ Available';
                statusElement.style.color = '#10b981';
                return false;
            }
        } catch (e) {
            return false;
        }
    }

    async function checkNameDuplicate() {
        const fn = fNameInput.value.trim();
        const ln = lNameInput.value.trim();
        if (fn && ln) {
            try {
                const res = await fetch(`check_duplicate.php?field=name&first_name=${encodeURIComponent(fn)}&last_name=${encodeURIComponent(ln)}&exclude_id=${studentId}`);
                const data = await res.json();
                if (data.exists) {
                    statusName.innerText = data.message;
                    statusName.style.color = '#f59e0b';
                } else {
                    statusName.innerText = '';
                }
            } catch (e) {}
        } else {
            statusName.innerText = '';
        }
    }

    studentNoInput.addEventListener('blur', async () => {
        hasDuplicateId = await checkDuplicate('student_no', studentNoInput.value, statusStudentNo);
        updateSubmitState();
    });

    emailInput.addEventListener('blur', async () => {
        hasDuplicateEmail = await checkDuplicate('email', emailInput.value, statusEmail);
        updateSubmitState();
    });

    fNameInput.addEventListener('blur', checkNameDuplicate);
    lNameInput.addEventListener('blur', checkNameDuplicate);
});
</script>