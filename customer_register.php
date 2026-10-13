
<?php
session_start();
require_once 'config.php';

$message = "";
$message_type = "";

if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $message = "Please enter your full name.";
        $message_type = "error";

    } elseif (strlen($name) > 100) {
        $message = "Name must not exceed 100 characters.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($email) > 150) {
        $message = "Email must not exceed 150 characters.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";

    } else {

        try {
            $check_sql = "
                SELECT id
                FROM users
                WHERE email = ?
                LIMIT 1
            ";

            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message = "This email is already registered.";
                $message_type = "error";

            } else {
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert_sql = "
                    INSERT INTO users
                    (name, email, password, role)
                    VALUES (?, ?, ?, 'customer')
                ";

                $insert_stmt = $conn->prepare($insert_sql);

                $insert_stmt->bind_param(
                    "sss",
                    $name,
                    $email,
                    $hashed_password
                );

                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    $check_stmt->close();

                    header(
                        "Location: customer_login.php?registered=1"
                    );
                    exit();
                }

                $insert_stmt->close();
            }

            $check_stmt->close();

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $message = "This email is already registered.";
            } else {
                $message = "Registration failed. Please try again.";
            }

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

<title>NetMart - Customer Registration</title>

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
    width: 450px;
    max-width: 92%;
    margin: 60px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #ccc;
}

h2 {
    text-align: center;
    margin-top: 0;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 16px;
}

input {
    width: 100%;
    padding: 13px;
    margin-top: 7px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

input:focus {
    outline: none;
    border-color: #007bff;
}

button {
    width: 100%;
    padding: 14px;
    margin-top: 24px;
    border: none;
    border-radius: 6px;
    background: #007bff;
    color: white;
    font-size: 17px;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}

.message {
    margin-top: 20px;
    padding: 13px;
    text-align: center;
    border-radius: 6px;
    font-weight: bold;
}

.error {
    background: #f8d7da;
    color: #721c24;
}

.login-link,
.home-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
}

.login-link {
    color: #007bff;
}

.home-link {
    margin-top: 12px;
    color: #555;
}

.login-link:hover,
.home-link:hover {
    text-decoration: underline;
}

.password-note {
    color: #777;
    font-size: 13px;
    margin-top: 5px;
}
</style>
</head>

<body>

<div class="box">

    <h2>👤 Customer Registration</h2>

    <form method="POST" action="" autocomplete="on">

        <label for="name">Full Name</label>
        <input
            type="text"
            id="name"
            name="name"
            maxlength="100"
            placeholder="Enter your name"
            value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

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
            minlength="6"
            placeholder="Minimum 6 characters"
            required
        >

        <div class="password-note">
            Password must contain at least 6 characters.
        </div>

        <label for="confirm_password">Confirm Password</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="6"
            placeholder="Re-enter password"
            required
        >

        <button type="submit">Create Account</button>

    </form>

    <?php if ($message !== "") { ?>
        <div class="message <?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <a class="login-link" href="customer_login.php">
        Already have an account? Login
    </a>

    <a class="home-link" href="index.php">
        ← Back to Home
    </a>

</div>

</body>
</html>
