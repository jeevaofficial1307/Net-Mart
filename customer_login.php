
<?php
session_start();
require_once 'config.php';

$message = "";
$message_type = "";

if (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $message = "Registration successful! Please login.";
    $message_type = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email.";
        $message_type = "error";

    } elseif ($password === "") {
        $message = "Please enter your password.";
        $message_type = "error";

    } else {
        try {
            $sql = "
                SELECT id, name, email, password
                FROM users
                WHERE email = ? AND role = 'customer'
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $customer = $result->fetch_assoc();

                if (password_verify($password, $customer['password'])) {
                    session_regenerate_id(true);

                    $_SESSION['customer_id'] = (int)$customer['id'];
                    $_SESSION['customer_name'] = $customer['name'];
                    $_SESSION['customer_email'] = $customer['email'];
                    $_SESSION['role'] = 'customer';

                    $stmt->close();

                    header("Location: index.php");
                    exit();

                } else {
                    $message = "Incorrect email or password.";
                    $message_type = "error";
                }

            } else {
                $message = "Incorrect email or password.";
                $message_type = "error";
            }

            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            error_log("Customer login database error: " . $e->getMessage());
            $message = "Login failed due to a database error. Please try again.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NetMart - Customer Login</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.box {
    width: 420px;
    max-width: 92%;
    margin: 80px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #ccc;
}

h2 {
    text-align: center;
    margin-top: 0;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 15px;
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 7px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

button {
    width: 100%;
    padding: 13px;
    margin-top: 22px;
    border: none;
    border-radius: 6px;
    background: #007bff;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}

.message {
    margin-top: 20px;
    padding: 12px;
    text-align: center;
    border-radius: 6px;
    font-weight: bold;
}

.success {
    background: #d4edda;
    color: #155724;
}

.error {
    background: #f8d7da;
    color: #721c24;
}

.register-link,
.home-link {
    display: block;
    text-align: center;
    margin-top: 18px;
    text-decoration: none;
}

.register-link {
    color: #007bff;
}

.home-link {
    color: #555;
}
</style>
</head>

<body>

<div class="box">

    <h2>🔐 Customer Login</h2>

    <form method="POST" action="">

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
            placeholder="Enter your email"
            value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="submit">Login</button>

    </form>

    <?php if ($message !== "") { ?>
        <div class="message <?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <a class="register-link" href="customer_register.php">
        New customer? Create account
    </a>

    <a class="home-link" href="index.php">
        ← Back to Home
    </a>

</div>

</body>
</html>
