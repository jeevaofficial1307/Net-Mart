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


/* =========================
   CHECK ORDER ID
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    die("Invalid Order ID");

}

$order_id = intval($_GET['id']);


/* =========================
   GET ORDER
========================= */

$sql = "SELECT
            id,
            user_id,
            total_amount,
            delivery_address,
            payment_method,
            payment_status,
            status,
            created_at
        FROM orders
        WHERE id = ?
        AND user_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Order query failed: " . $conn->error);

}

$stmt->bind_param(
    "ii",
    $order_id,
    $user_id
);

$stmt->execute();

$order_result = $stmt->get_result();


if ($order_result->num_rows == 0) {

    die("Order not found");

}

$order = $order_result->fetch_assoc();


/* =========================
   GET ORDER ITEMS
========================= */

$sql_items = "SELECT
                order_items.product_id,
                order_items.quantity,
                order_items.price,
                products.title
              FROM order_items
              JOIN products
              ON order_items.product_id = products.id
              WHERE order_items.order_id = ?";

$stmt_items = $conn->prepare($sql_items);

if (!$stmt_items) {

    die(
        "Order items query failed: "
        . $conn->error
    );

}

$stmt_items->bind_param(
    "i",
    $order_id
);

$stmt_items->execute();

$items_result = $stmt_items->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - Order Details</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}

.header {

    background: #222;

    color: white;

    padding: 20px 40px;

}

.header h1 {

    margin: 0;

}

.header p {

    margin-bottom: 0;

}

.container {

    max-width: 1000px;

    margin: 40px auto;

    padding: 20px;

}

.box {

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow: 0 3px 10px #ccc;

}

.order-info {

    background: #f8f9fa;

    padding: 20px;

    border-radius: 8px;

    margin-bottom: 25px;

}

.order-info p {

    margin: 12px 0;

    line-height: 1.5;

}

.info-label {

    font-weight: bold;

}

.status {

    display: inline-block;

    padding: 7px 12px;

    border-radius: 5px;

    font-weight: bold;

}

.pending {

    background: #fff3cd;

    color: #856404;

}

.confirmed {

    background: #d1ecf1;

    color: #0c5460;

}

.shipped {

    background: #cce5ff;

    color: #004085;

}

.delivered {

    background: #d4edda;

    color: #155724;

}

.cancelled {

    background: #f8d7da;

    color: #721c24;

}

.payment {

    display: inline-block;

    padding: 7px 12px;

    background: #e2e3e5;

    color: #383d41;

    border-radius: 5px;

    font-weight: bold;

}

.address {

    background: white;

    border: 1px solid #ddd;

    padding: 12px;

    margin-top: 8px;

    border-radius: 6px;

    white-space: pre-wrap;

}

table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;

}

th {

    background: #333;

    color: white;

    padding: 14px;

}

td {

    padding: 14px;

    text-align: center;

    border-bottom: 1px solid #ddd;

}

.total {

    text-align: right;

    font-size: 22px;

    font-weight: bold;

    margin-top: 25px;

}

.btn {

    display: inline-block;

    margin-top: 25px;

    padding: 11px 18px;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    background: #007bff;

}

.btn:hover {

    background: #0056b3;

}

.shop-btn {

    background: #28a745;

    margin-left: 8px;

}

@media(max-width:700px) {

    .container {

        margin: 20px auto;

        padding: 10px;

    }

    .box {

        padding: 18px;

    }

    table {

        font-size: 13px;

    }

    th,
    td {

        padding: 8px;

    }

}

</style>

</head>

<body>


<!-- =========================
     HEADER
========================= -->

<div class="header">

    <h1>📚 NetMart</h1>

    <p>Order Details</p>

</div>


<div class="container">

<div class="box">


<h2>

    📦 Order #<?php

    echo intval($order['id']);

    ?>

</h2>


<!-- =========================
     ORDER INFORMATION
========================= -->

<div class="order-info">


    <p>

        <span class="info-label">
            Order ID:
        </span>

        #

        <?php
        echo intval($order['id']);
        ?>

    </p>


    <p>

        <span class="info-label">
            Order Date:
        </span>

        <?php
        echo htmlspecialchars(
            $order['created_at']
        );
        ?>

    </p>


    <p>

        <span class="info-label">
            Order Status:
        </span>

        <?php

        $status_class =
            strtolower(
                trim($order['status'])
            );

        ?>

        <span
            class="status <?php
            echo htmlspecialchars(
                $status_class
            );
            ?>"
        >

            <?php
            echo htmlspecialchars(
                $order['status']
            );
            ?>

        </span>

    </p>


    <p>

        <span class="info-label">
            Payment Method:
        </span>

        <span class="payment">

            <?php
            echo htmlspecialchars(
                $order['payment_method']
            );
            ?>

        </span>

    </p>


    <p>

        <span class="info-label">
            Payment Status:
        </span>

        <?php

        $payment_status =
            $order['payment_status']
            ?? 'Pending';

        ?>

        <span class="payment">

            <?php
            echo htmlspecialchars(
                $payment_status
            );
            ?>

        </span>

    </p>


    <p>

        <span class="info-label">
            📍 Delivery Address:
        </span>


        <div class="address">

            <?php

            echo nl2br(
                htmlspecialchars(
                    $order['delivery_address']
                )
            );

            ?>

        </div>

    </p>


</div>


<!-- =========================
     ORDERED BOOKS
========================= -->

<h3>

    📚 Ordered Books

</h3>


<table>

<tr>

    <th>
        Book Name
    </th>

    <th>
        Price
    </th>

    <th>
        Quantity
    </th>

    <th>
        Subtotal
    </th>

</tr>


<?php

if ($items_result->num_rows > 0) {

    while (
        $item =
        $items_result->fetch_assoc()
    ) {

        $subtotal =
            floatval($item['price'])
            *
            intval($item['quantity']);

?>

<tr>

    <td>

        <?php

        echo htmlspecialchars(
            $item['title']
        );

        ?>

    </td>


    <td>

        ₹<?php

        echo number_format(
            $item['price'],
            2
        );

        ?>

    </td>


    <td>

        <?php

        echo intval(
            $item['quantity']
        );

        ?>

    </td>


    <td>

        ₹<?php

        echo number_format(
            $subtotal,
            2
        );

        ?>

    </td>

</tr>

<?php

    }

} else {

?>

<tr>

    <td colspan="4">

        No items found for this order.

    </td>

</tr>

<?php

}

?>

</table>


<!-- =========================
     TOTAL
========================= -->

<div class="total">

    Total Amount:

    ₹<?php

    echo number_format(
        $order['total_amount'],
        2
    );

    ?>

</div>


<!-- =========================
     BUTTONS
========================= -->

<a
    class="btn"
    href="my_orders.php"
>

    ← Back to My Orders

</a>


<a
    class="btn shop-btn"
    href="view_books.php"
>

    📚 Continue Shopping

</a>


</div>

</div>

</body>

</html>