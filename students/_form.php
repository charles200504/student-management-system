<?php
$genders = ['Male', 'Female', 'Other'];
$statuses = ['Active', 'Inactive', 'Graduated'];
?>

<?php if (!empty($errors['database'])): ?>
    <div class="alert error"><?= e($errors['database']) ?></div>
<?php endif; ?>

<form class="form-card" method="post" action="<?= e($formAction) ?>">
    <?php if (!empty($student['id'])): ?>
        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
    <?php endif; ?>

    <div class="grid-2">
        <label>
            <span>Student Number</span>
            <input type="text" name="student_no"
                   value="<?= e(field_value($student, 'student_no')) ?>"
                   placeholder="STU004">
            <?php if (!empty($errors['student_no'])): ?>
                <small class="error-text"><?= e($errors['student_no']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Email</span>
            <input type="email" name="email"
                   value="<?= e(field_value($student, 'email')) ?>"
                   placeholder="student@example.com">
            <?php if (!empty($errors['email'])): ?>
                <small class="error-text"><?= e($errors['email']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>First Name</span>
            <input type="text" name="first_name"
                   value="<?= e(field_value($student, 'first_name')) ?>">
            <?php if (!empty($errors['first_name'])): ?>
                <small class="error-text"><?= e($errors['first_name']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Last Name</span>
            <input type="text" name="last_name"
                   value="<?= e(field_value($student, 'last_name')) ?>">
            <?php if (!empty($errors['last_name'])): ?>
                <small class="error-text"><?= e($errors['last_name']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Phone</span>
            <input type="text" name="phone"
                   value="<?= e(field_value($student, 'phone')) ?>"
                   placeholder="0712345678">
        </label>

        <label>
            <span>Date of Birth</span>
            <input type="date" name="date_of_birth"
                   value="<?= e(field_value($student, 'date_of_birth')) ?>">
            <?php if (!empty($errors['date_of_birth'])): ?>
                <small class="error-text"><?= e($errors['date_of_birth']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Gender</span>
            <select name="gender">
                <?php foreach ($genders as $gender): ?>
                    <option value="<?= e($gender) ?>"
                        <?= field_value($student, 'gender', 'Other') === $gender ? 'selected' : '' ?>>
                        <?= e($gender) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['gender'])): ?>
                <small class="error-text"><?= e($errors['gender']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Course</span>
            <select name="course_id">
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course['id']) ?>"
                        <?= (int) field_value($student, 'course_id', '0') === (int) $course['id'] ? 'selected' : '' ?>>
                        <?= e($course['course_code'] . ' - ' . $course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['course_id'])): ?>
                <small class="error-text"><?= e($errors['course_id']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Enrollment Date</span>
            <input type="date" name="enrollment_date"
                   value="<?= e(field_value($student, 'enrollment_date', date('Y-m-d'))) ?>">
            <?php if (!empty($errors['enrollment_date'])): ?>
                <small class="error-text"><?= e($errors['enrollment_date']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            <span>Status</span>
            <select name="status">
                <?php foreach ($statuses as $item): ?>
                    <option value="<?= e($item) ?>"
                        <?= field_value($student, 'status', 'Active') === $item ? 'selected' : '' ?>>
                        <?= e($item) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['status'])): ?>
                <small class="error-text"><?= e($errors['status']) ?></small>
            <?php endif; ?>
        </label>
    </div>

    <label>
        <span>Address</span>
        <textarea name="address" rows="3"><?= e(field_value($student, 'address')) ?></textarea>
    </label>

    <div class="form-actions">
        <button class="button primary" type="submit"><?= e($buttonText) ?></button>
        <a class="button muted" href="index.php">Cancel</a>
    </div>
</form>