<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Set New Password';
$basePath = '../';
$activePage = 'login';
$token = trim((string)($_GET['token'] ?? ''));
$error = '';

if ($token === '') {
    redirect('login.php');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = :token AND reset_expires_at > NOW() LIMIT 1');
$stmt->execute(['token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    $error = 'Invalid or expired password reset link.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = trim((string)($_POST['password'] ?? ''));
    $password_confirm = trim((string)($_POST['password_confirm'] ?? ''));

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE users SET password = :password, reset_token = NULL, reset_expires_at = NULL WHERE id = :id');
        $update->execute([
            'password' => $hashed,
            'id' => $user['id']
        ]);

        redirect('login.php?reset=1');
    }
}

require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Set New Password</h2>
            <p>Create a secure new password for your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form method="post" action="reset-password.php?token=<?= e($token) ?>" class="auth-form">
                <label>
                    <span>New Password</span>
                    <input type="password" name="password" placeholder="••••••••" required>
                </label>

                <label>
                    <span>Confirm New Password</span>
                    <input type="password" name="password_confirm" placeholder="••••••••" required>
                </label>

                <button type="submit" class="button gold-btn auth-submit">Update Password</button>
            </form>
        <?php else: ?>
            <div class="auth-footer">
                <a href="forgot-password.php" class="auth-link-bold">Request a new reset link</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>