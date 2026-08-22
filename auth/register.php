<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$pageTitle = 'Create Account';
$basePath = '../';
$activePage = 'register';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));
    $password_confirm = trim((string)($_POST['password_confirm'] ?? ''));

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already registered
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered. Please log in.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
            $insert->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            redirect('login.php?registered=1');
        }
    }
}

require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Create an Account</h2>
            <p>Register an administrator profile for portal management</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" class="auth-form">
            <label>
                <span>Full Name</span>
                <input type="text" name="name" placeholder="John Doe" required>
            </label>

            <label>
                <span>Email Address</span>
                <input type="email" name="email" placeholder="john@example.com" required>
            </label>

            <label>
                <span>Password (min. 6 characters)</span>
                <input type="password" name="password" placeholder="••••••••" required>
            </label>

            <label>
                <span>Confirm Password</span>
                <input type="password" name="password_confirm" placeholder="••••••••" required>
            </label>

            <button type="submit" class="button gold-btn auth-submit">Sign Up</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php" class="auth-link-bold">Log In</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>