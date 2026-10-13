<?php

session_start();
include 'config.php';

$message = "";


/* =========================
   IF ALREADY LOGGED IN
========================= */

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'admin') {

        header("Location: admin_dashboard.php");
        exit();

    } elseif ($_SESSION['role'] == 'seller') {

        header("Location: seller_dashboard.php");
        exit();

    } elseif ($_SESSION['role'] == 'customer') {

        header("Location: index.php");
        exit();

    }
}


/* =========================
   LOGIN
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email == "" || $password == "") {

        $message = "Please enter email and password.";

    } else {

        /* =================================================
           STEP 1: CHECK ADMIN / SELLER FROM users TABLE
        ================================================= */

        $sql = "
            SELECT id, email, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $message = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows == 1) {

                $user = $result->fetch_assoc();

                /*
                 * Your users table currently uses
                 * normal/plain password according to
                 * your previous login code.
                 */

                if ($password === $user['password']) {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    /* ADMIN */

                    if ($user['role'] == 'admin') {

                        header("Location: admin_dashboard.php");
                        exit();

                    }

                    /* SELLER */

                    elseif ($user['role'] == 'seller') {

                        header("Location: seller_dashboard.php");
                        exit();

                    }

                    else {

                        $message = "Unknown user role.";

                    }

                } else {

                    $message = "Invalid Email or Password.";

                }

            } else {

                /* ==========================================
                   STEP 2: CHECK CUSTOMER TABLE
                ========================================== */

                $customer_sql = "
                    SELECT id, name, email, password
                    FROM customers
                    WHERE email = ?
                    LIMIT 1
                ";

                $customer_stmt = $conn->prepare($customer_sql);

                if (!$customer_stmt) {

                    $message = "Database error: " . $conn->error;

                } else {

                    $customer_stmt->bind_param("s", $email);
                    $customer_stmt->execute();

                    $customer_result =
                        $customer_stmt->get_result();

                    if ($customer_result->num_rows == 1) {

                        $customer =
                            $customer_result->fetch_assoc();

                        /*
                         * Customer password is stored using
                         * password_hash(), so use password_verify()
                         */

                        if (
                            password_verify(
                                $password,
                                $customer['password']
                            )
                        ) {

                            session_regenerate_id(true);

                            $_SESSION['customer_id'] =
                                $customer['id'];

                            $_SESSION['customer_name'] =
                                $customer['name'];

                            $_SESSION['customer_email'] =
                                $customer['email'];

                            $_SESSION['role'] = 'customer';

                            /* CUSTOMER */

                            header("Location: index.php");
                            exit();

                        } else {

                            $message =
                                "Invalid Email or Password.";

                        }

                    } else {

                        $message =
                            "Invalid Email or Password.";

                    }

                    $customer_stmt->close();
                }
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

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - Login</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}

.login-container {

    width: 420px;

    max-width: 92%;

    margin: 80px auto;

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 4px 15px rgba(0,0,0,0.15);

}

h2 {

    text-align: center;

    margin-top: 0;

    margin-bottom: 25px;

}

.subtitle {

    text-align: center;

    color: #666;

    margin-bottom: 25px;

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

    background: #007bff;

    color: white;

    border: none;

    border-radius: 6px;

    cursor: pointer;

    font-size: 16px;

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

    background: #f8d7da;

    color: #721c24;

}

.register {

    text-align: center;

    margin-top: 20px;

}

.register a {

    color: #007bff;

    text-decoration: none;

}

.back {

    text-align: center;

    margin-top: 15px;

}

.back a {

    color: #555;

    text-decoration: none;

}

</style>

</head>

<body>


<div class="login-container">

    <h2>📚 NetMart Login</h2>

    <div class="subtitle">

        Admin • Seller • Customer

    </div>


    <form method="POST">

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
        >


        <button type="submit">

            🔐 Login

        </button>

    </form>


    <?php if ($message != "") { ?>

        <div class="message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>


    <div class="register">

        New customer?

        <a href="customer_register.php">

            Create Account

        </a>

    </div>


    <div class="back">

        <a href="index.php">

            ← Back to NetMart

        </a>

    </div>

</div>


</body>

</html>