
<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

$role = strtolower($_GET['role'] ?? $_POST['role'] ?? 'seller');

if (!in_array($role, ['admin', 'seller'], true)) {
    $role = 'seller';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter valid details. Password must contain at least 8 characters.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'Email already exists. Please use another email.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role, phone, status)
                 VALUES (?, ?, ?, ?, ?, 'active')"
            );
            $stmt->bind_param("sssss", $name, $email, $hash, $role, $phone);

            if ($stmt->execute()) {
                $success = ucfirst($role) . ' account created successfully!';
            } else {
                $error = 'Account creation failed: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account - NetMart</title>
<style>
*{box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#f2f5fa;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;padding:20px}
.register-box{background:white;padding:30px;border-radius:12px;width:100%;max-width:380px;box-shadow:0 4px 15px #ccc}
h2{text-align:center;color:#1769ff}
input,button{width:100%;padding:12px;margin-top:12px;border-radius:6px}
input{border:1px solid #ccc}
button{background:#1769ff;color:white;border:0;cursor:pointer}
.error{color:red}
.success{color:green}
a{color:#551a8b}
</style>
</head>
<body>
<div class="register-box">
<h2><?php echo ucfirst($role); ?> Create Account</h2>

<?php if ($error !== ''): ?>
<p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($success !== ''): ?>
<p class="success"><?php echo htmlspecialchars($success); ?></p>
<p><a href="admin_seller_login.php?role=<?php echo urlencode($role); ?>">Go to Login</a></p>
<?php else: ?>
<form method="POST">
<input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email Address" required>
<input type="tel" name="phone" placeholder="Phone Number">
<input type="password" name="password" placeholder="Password (minimum 8 characters)" minlength="8" required>
<button type="submit">Create Account</button>
</form>
<?php endif; ?>

<p><a href="admin_seller_login.php?role=<?php echo urlencode($role); ?>">Back to Login</a></p>
<p><a href="index.php">Back to Website</a></p>
</div>
</body>
</html>
