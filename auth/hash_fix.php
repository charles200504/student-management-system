<?php
require_once '../config/database.php';

$email = 'admin@example.com';
$plainPassword = 'admin123';
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// Check if user exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user) {
    // Update password
    $update = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
    $update->execute(['password' => $hashedPassword, 'email' => $email]);
    echo "<h2>Success! Password for <b>$email</b> has been reset to <b>$plainPassword</b>.</h2>";
    echo "<p><a href='login.php'>Click here to log in now →</a></p>";
} else {
    // Insert new user
    $insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $insert->execute([
        'name' => 'Admin User',
        'email' => $email,
        'password' => $hashedPassword
    ]);
    echo "<h2>Success! Admin account created for <b>$email</b> with password <b>$plainPassword</b>.</h2>";
    echo "<p><a href='login.php'>Click here to log in now →</a></p>";
}
