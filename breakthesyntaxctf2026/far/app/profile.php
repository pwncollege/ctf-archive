<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/svg+xml'];
    $max_size = 2 * 1024 * 1024;

    if ($file['error'] === UPLOAD_ERR_OK) {
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $destination = 'uploads/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$filename, $user_id]);
                $message = "Avatar updated successfully.";
            } else {
                $message = "File upload failed.";
            }
        } else {
            $message = "Invalid file. Max 2MB, JPG/PNG/SVG only.";
        }
    } else {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $message = "No file selected.";
        } else if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $message = "File exceeds maximum size of 2MB.";
        } else {
            $message = "File upload error.";
        }
    }
}

$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container wide">
        <nav class="nav">
            <a href="index.php">Dashboard</a>
            <a href="profile.php" style="color:var(--accent-color); font-weight:600;">Settings</a>
            <a href="logout.php" style="margin-left:auto;">Logout</a>
        </nav>

        <h2>Account Settings</h2>

        <div style="display:flex; gap: 2rem; align-items:flex-start;">
            
            <div>
                <?php if ($user['profile_pic']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-img">
                <?php else: ?>
                    <div class="profile-placeholder">No Avatar</div>
                <?php endif; ?>
            </div>

            <div style="flex-grow:1;">
                <form method="post" enctype="multipart/form-data">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*" required>
                    <div style="margin-top:0.5rem;">
                        <button type="submit">Save Changes</button>
                    </div>
                </form>
                <?php if($message): ?>
                    <p style="color:var(--text-secondary); margin-top:1rem; font-size:0.9rem;">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>