<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$errors = [];

if (is_logged_in()) {
    redirect('../dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both your email address and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session with role
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'avatar' => $user['avatar'] ?? null,
                'role' => $user['role'] ?? 'student'
            ];

            // Role-based routing
            if (($user['role'] ?? 'student') === 'admin') {
                redirect('../dashboard.php');
            } else {
                redirect('../students/my_profile.php');
            }
        } else {
            $errors[] = 'Invalid email address or password combination.';
        }
    }
}

$pageTitle = 'Sign In - StudentSys';
$basePath = '../';
$activePage = 'login';

require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="brand-logo" style="margin: 0 auto 12px; width: 44px; height: 44px; font-size: 22px;">⚡</div>
            <h2>Sign In to Portal</h2>
            <p>Access your administrative or student academic workspace</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert error" style="margin-bottom: 16px;">
                <?php foreach ($errors as $err): ?>
                    <div>• <?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" class="auth-form">
            <label>
                <span>Institutional / Account Email</span>
                <input type="email" name="email" placeholder="e.g. admin@nsbm.ac.lk or student@nsbm.ac.lk" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" placeholder="••••••••" required>
            </label>

            <button type="submit" class="button gold-btn auth-submit">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php" class="auth-link-bold">Sign Up here</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>