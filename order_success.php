<?php

session_start();
include 'config.php';


/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$user_id = intval($_SESSION['user_id']);

$order_id = intval($_GET['id'] ?? 0);


/* =========================
   ORDER CHECK
========================= */

if ($order_id <= 0) {

    die("Invalid order ID.");

}


$order_sql = "SELECT
                id,
                total_amount,
                delivery_address,
                payment_method,
                payment_status,
                status
              FROM orders
              WHERE id = ?
              AND user_id = ?";

$order_stmt = $conn->prepare($order_sql);

if (!$order_stmt) {

    die("Order query failed: " . $conn->error);

}

$order_stmt->bind_param(
    "ii",
    $order_id,
    $user_id
);

$order_stmt->execute();

$order_result = $order_stmt->get_result();


if ($order_result->num_rows === 0) {

    die("Order not found.");

}


$order = $order_result->fetch_assoc();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - Order Success</title>


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

    max-width: 650px;

    margin: 80px auto;

    background: white;

    padding: 40px;

    text-align: center;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}


h1 {

    color: #28a745;

    margin-top: 0;

}


.order-id {

    font-size: 24px;

    font-weight: bold;

    margin: 25px 0;

}


.info {

    background: #f8f9fa;

    padding: 20px;

    border-radius: 8px;

    text-align: left;

    margin: 25px 0;

}


.info p {

    margin: 10px 0;

}


.status {

    color: #856404;

    font-weight: bold;

}


.payment {

    color: #856404;

    font-weight: bold;

}


.btn {

    display: inline-block;

    margin: 10px 5px;

    padding: 12px 20px;

    background: #007bff;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}


.btn:hover {

    background: #0056b3;

}


.orders {

    background: #6f42c1;

}


.orders:hover {

    background: #59339d;

}


</style>

</head>


<body>


<div class="box">


    <h1>
        ✅ Order Placed Successfully!
    </h1>


    <p>
        Thank you for shopping with NetMart.
    </p>


    <div class="order-id">

        Order ID:
        #<?php echo $order_id; ?>

    </div>


    <div class="info">


        <p>

            <b>Payment Method:</b>

            <?php
            echo htmlspecialchars(
                $order['payment_method']
            );
            ?>

        </p>


        <p>

            <b>Payment Status:</b>

            <span class="payment">

                <?php
                echo htmlspecialchars(
                    $order['payment_status']
                );
                ?>

            </span>

        </p>


        <p>

            <b>Order Status:</b>

            <span class="status">

                <?php
                echo htmlspecialchars(
                    $order['status']
                );
                ?>

            </span>

        </p>


        <p>

            <b>Total Amount:</b>

            ₹<?php

            echo number_format(
                $order['total_amount'],
                2
            );

            ?>

        </p>


        <p>

            <b>Delivery Address:</b><br>

            <?php

            echo nl2br(
                htmlspecialchars(
                    $order['delivery_address']
                )
            );

            ?>

        </p>


    </div>


    <p>

        Your order has been received and is
        <b>Pending</b>.

    </p>


    <a
        class="btn"
        href="view_books.php"
    >

        📚 Continue Shopping

    </a>


    <a
        class="btn orders"
        href="my_orders.php"
    >

        📦 My Orders

    </a>


</div>


</body>

</html>