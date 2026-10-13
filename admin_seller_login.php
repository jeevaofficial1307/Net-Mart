
<?php
session_start();
require_once 'config.php';

$error = '';
$role = strtolower($_GET['role'] ?? $_POST['role'] ?? '');

if (!in_array($role, ['admin', 'seller'], true)) {
    $role = 'admin';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ? AND role = ?
         LIMIT 1"
    );

    if ($stmt) {
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            $stmt->close();

            if ($role === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: seller_dashboard.php');
            }
            exit();
        }

        $stmt->close();
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NetMart Login</title>
<style>
body{font-family:Arial;background:#f2f5fa;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}
.login-box{background:white;padding:30px;border-radius:12px;width:320px;box-shadow:0 4px 15px #ccc}
h2{text-align:center;color:#1769ff}
input,button,.create-account{width:100%;padding:12px;margin-top:12px;box-sizing:border-box}
button{background:#1769ff;color:white;border:0;border-radius:6px;cursor:pointer}
.create-account{display:block;background:#20a044;color:white;text-align:center;text-decoration:none;border-radius:6px}
.create-account:hover{background:#168637}
.error{color:red;text-align:center}
</style>
</head>
<body>
<div class="login-box">
<h2><?php echo ucfirst(htmlspecialchars($role)); ?> Login</h2>

<?php if ($error !== ''): ?>
<p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>

<p style="text-align:center">Don't have an account?</p>
<a class="create-account" href="admin_seller_register.php?role=<?php echo urlencode($role); ?>">Create New Account</a>

<p><a href="index.php">Back to Website</a></p>
</div>
</body>
</html>
