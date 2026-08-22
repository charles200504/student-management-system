<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$pageTitle = 'Login - Student Management System';
$basePath = '../';
$activePage = 'login';
$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Account created successfully! You can now log in.';
}
if (isset($_GET['reset'])) {
    $success = 'Password has been updated. Please log in with your new password.';
}
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'Please log in to access this portal section.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_avatar'] = $user['avatar'] ?? '';
            redirect('../index.php');
        } else {
            $error = 'Invalid email or password credentials.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access the academic control console</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="auth-form">
            <div class="form-group">
                <label for="email_input">Email Address</label>
                <input id="email_input" type="email" name="email" placeholder="admin@example.com" required autocomplete="username">
            </div>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label for="password_input" style="margin-bottom:0;">Password</label>
                    <a href="forgot-password.php" class="auth-link">Forgot Password?</a>
                </div>
                <input id="password_input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="button gold-btn auth-submit">Log In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php" class="auth-link-bold">Create Account</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>