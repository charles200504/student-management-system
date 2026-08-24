<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$errors = [];

if (is_logged_in()) {
    redirect('../dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'student';

    if ($name === '') $errors[] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long.';
    if (!in_array($role, ['admin', 'student'], true)) $role = 'student';

    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $chk->execute(['email' => $email]);
        if ($chk->fetch()) {
            $errors[] = 'This email address is already registered.';
        }
    }

    if (!$errors) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $ins = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :pass, :role)');
        $ins->execute([
            'name' => $name,
            'email' => $email,
            'pass' => $hashed,
            'role' => $role
        ]);

        $newUserId = (int)$pdo->lastInsertId();

        // Auto-login session setup
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user'] = [
            'id' => $newUserId,
            'name' => $name,
            'email' => $email,
            'avatar' => null,
            'role' => $role
        ];

        if ($role === 'admin') {
            redirect('../dashboard.php');
        } else {
            redirect('../students/my_profile.php');
        }
    }
}

$pageTitle = 'Create Account - StudentSys';
$basePath = '../';
$activePage = 'register';

require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="brand-logo" style="margin: 0 auto 12px; width: 44px; height: 44px; font-size: 22px;">⚡</div>
            <h2>Create Account</h2>
            <p>Register as an Administrator or a Student</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert error" style="margin-bottom: 16px;">
                <?php foreach ($errors as $err): ?>
                    <div>• <?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="auth-form">
            <label>
                <span>Account Role *</span>
                <select name="role" required style="font-weight: bold; color: var(--primary-gold);">
                    <option value="student" <?= (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : '' ?>>🎓 Student Account</option>
                    <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>>🛡️ Faculty Administrator</option>
                </select>
            </label>

            <label>
                <span>Full Name *</span>
                <input type="text" name="name" placeholder="e.g. John Doe" value="<?= e($_POST['name'] ?? '') ?>" required>
            </label>

            <label>
                <span>Email Address *</span>
                <input type="email" name="email" placeholder="e.g. user@nsbm.ac.lk" value="<?= e($_POST['email'] ?? '') ?>" required>
            </label>

            <label>
                <span>Password (min. 6 characters) *</span>
                <input type="password" name="password" placeholder="••••••••" required>
            </label>

            <button type="submit" class="button gold-btn auth-submit">Complete Registration</button>
        </form>

        <div class="auth-footer">
            Already registered? <a href="login.php" class="auth-link-bold">Sign In here</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>