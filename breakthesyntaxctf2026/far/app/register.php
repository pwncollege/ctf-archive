<?php
require 'db.php';
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, profile_pic) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, 'default.svg']);
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $message = "Username already taken.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        <?php if($message): ?><div class="error"><?php echo $message; ?></div><?php endif; ?>
        
        <form method="post">
            <div>
                <label>Username</label>
                <input type="text" name="username" placeholder="username" required>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        
        <div class="sub-text">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>