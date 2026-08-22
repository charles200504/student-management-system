<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Admin Profile Settings';
$basePath = '../';
$activePage = 'profile';

$userId = (int) $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user details
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    redirect('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $current_password = (string)($_POST['current_password'] ?? '');
    $new_password = (string)($_POST['new_password'] ?? '');
    $avatarName = $currentUser['avatar'];

    if ($name === '' || $email === '') {
        $error = 'Name and Email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        // Check if email is already used by another user
        $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $emailCheck->execute(['email' => $email, 'id' => $userId]);
        if ($emailCheck->fetch()) {
            $error = 'This email address is already in use by another account.';
        }
    }

    // Handle Avatar Upload
    if (!$error && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            $error = 'Invalid image type. Only JPG, PNG, and WEBP files are allowed.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $error = 'Avatar image must not exceed 2MB in size.';
        } else {
            $newAvatarName = 'admin_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
            $uploadDir = '../assets/uploads/';
            $destPath = $uploadDir . $newAvatarName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Delete old avatar if exists
                if (!empty($currentUser['avatar']) && file_exists($uploadDir . $currentUser['avatar'])) {
                    unlink($uploadDir . $currentUser['avatar']);
                }
                $avatarName = $newAvatarName;
            } else {
                $error = 'Failed to upload avatar image.';
            }
        }
    }

    // Process Password Change if requested
    $updatePassword = false;
    $hashedNewPassword = '';
    if (!$error && $new_password !== '') {
        if ($current_password === '') {
            $error = 'Please enter your current password to set a new password.';
        } elseif (!password_verify($current_password, $currentUser['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $updatePassword = true;
            $hashedNewPassword = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    // Execute Database Update
    if (!$error) {
        if ($updatePassword) {
            $updateStmt = $pdo->prepare('
                UPDATE users SET name = :name, email = :email, avatar = :avatar, password = :password WHERE id = :id
            ');
            $updateStmt->execute([
                'name' => $name,
                'email' => $email,
                'avatar' => $avatarName,
                'password' => $hashedNewPassword,
                'id' => $userId
            ]);
        } else {
            $updateStmt = $pdo->prepare('
                UPDATE users SET name = :name, email = :email, avatar = :avatar WHERE id = :id
            ');
            $updateStmt->execute([
                'name' => $name,
                'email' => $email,
                'avatar' => $avatarName,
                'id' => $userId
            ]);
        }

        // Refresh Session Details
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_avatar'] = $avatarName;

        $success = 'Your profile has been updated successfully!';
        
        // Refresh local user data
        $currentUser['name'] = $name;
        $currentUser['email'] = $email;
        $currentUser['avatar'] = $avatarName;
    }
}

require_once '../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">User Management</p>
        <h2>Admin Profile Settings</h2>
    </div>
    <a class="button muted" href="../index.php">Back to Dashboard</a>
</section>

<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success"><?= e($success) ?></div>
<?php endif; ?>

<div class="panel form-card">
    <form method="post" action="profile.php" enctype="multipart/form-data">
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
            <?php if (!empty($currentUser['avatar'])): ?>
                <img src="../assets/uploads/<?= e($currentUser['avatar']) ?>" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-gold);">
            <?php else: ?>
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--bg-surface-elevated); border: 2px solid var(--border); display: grid; place-items: center; font-size: 26px; font-weight: 800; color: var(--primary-gold);">
                    <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>

            <label style="flex: 1;">
                <span>Change Profile Picture (JPG, PNG, WEBP)</span>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
            </label>
        </div>

        <div class="grid-2">
            <label>
                <span>Full Name *</span>
                <input type="text" name="name" value="<?= e($currentUser['name']) ?>" required>
            </label>

            <label>
                <span>Email Address *</span>
                <input type="email" name="email" value="<?= e($currentUser['email']) ?>" required>
            </label>
        </div>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
            <h3 style="font-size: 16px; margin-bottom: 14px; color: #ffffff;">Change Password (Optional)</h3>
            <div class="grid-2">
                <label>
                    <span>Current Password</span>
                    <input type="password" name="current_password" placeholder="••••••••">
                </label>

                <label>
                    <span>New Password (Leave blank to keep current)</span>
                    <input type="password" name="new_password" placeholder="••••••••">
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="button gold-btn">Save Profile Changes</button>
            <a class="button muted" href="../index.php">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>