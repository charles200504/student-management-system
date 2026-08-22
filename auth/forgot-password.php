<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Forgot Password';
$basePath = '../';
$activePage = 'login';
$message = '';
$resetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please provide a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour validity

            $update = $pdo->prepare('UPDATE users SET reset_token = :token, reset_expires_at = :expires WHERE id = :id');
            $update->execute([
                'token' => $token,
                'expires' => $expires,
                'id' => $user['id']
            ]);

            // Local development link generation
            $resetUrl = "reset-password.php?token=" . $token;
        } else {
            $message = 'If that email exists in our system, a password recovery link has been generated.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Reset Password</h2>
            <p>Enter your email to receive a password recovery link</p>
        </div>

        <?php if ($message): ?>
            <div class="alert error"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($resetUrl): ?>
            <div class="alert success" style="line-height: 1.5;">
                <strong>Recovery Token Generated!</strong><br>
                Click below to complete password change:<br>
                <a href="<?= e($resetUrl) ?>" style="color: #065f46; font-weight: bold; text-decoration: underline;">
                    Click Here to Set New Password →
                </a>
            </div>
        <?php endif; ?>

        <form method="post" action="forgot-password.php" class="auth-form">
            <label>
                <span>Email Address</span>
                <input type="email" name="email" placeholder="admin@example.com" required>
            </label>

            <button type="submit" class="button gold-btn auth-submit">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            <p><a href="login.php" class="auth-link">⬅ Back to Login</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>