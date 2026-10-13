<?php

session_start();
include 'config.php';


/* =========================
   SELLER LOGIN CHECK
========================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {

    header("Location: login.php");
    exit();

}


/* =========================
   GET SELLER ID
========================= */

if (isset($_SESSION['seller_id'])) {

    $seller_id = intval($_SESSION['seller_id']);

} elseif (isset($_SESSION['user_id'])) {

    $seller_id = intval($_SESSION['user_id']);

} else {

    die("Seller ID not found. Please login again.");

}


/* =========================
   GET SELLER ORDERS
========================= */

$sql = "SELECT DISTINCT
            orders.id,
            orders.user_id,
            orders.total_amount,
            orders.delivery_address,
            orders.payment_method,
            orders.payment_status,
            orders.status,
            orders.created_at

        FROM orders

        JOIN order_items
            ON orders.id = order_items.order_id

        JOIN products
            ON order_items.product_id = products.id

        WHERE products.seller_id = ?

        ORDER BY orders.id DESC";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Order query failed: " .
        htmlspecialchars($conn->error)
    );

}

$stmt->bind_param("i", $seller_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - Seller Orders</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}


/* =========================
   HEADER
========================= */

.header {

    background: #222;

    color: white;

    padding: 22px 40px;

}

.header h1 {

    margin: 0;

    font-size: 30px;

}

.header p {

    margin-bottom: 0;

}


/* =========================
   CONTAINER
========================= */

.container {

    max-width: 1200px;

    margin: 40px auto;

    padding: 20px;

}


.box {

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}


/* =========================
   TABLE
========================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;

    min-width: 1000px;

}


th {

    background: #333;

    color: white;

    padding: 13px;

    text-align: center;

}


td {

    padding: 13px;

    text-align: center;

    border-bottom: 1px solid #ddd;

}


/* =========================
   STATUS
========================= */

.status {

    display: inline-block;

    padding: 7px 12px;

    border-radius: 6px;

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


/* =========================
   UPDATE FORM
========================= */

.status-form {

    display: flex;

    gap: 8px;

    justify-content: center;

    align-items: center;

}


.status-form select {

    padding: 8px;

    border: 1px solid #ccc;

    border-radius: 5px;

}


.update-btn {

    padding: 8px 12px;

    border: none;

    border-radius: 5px;

    background: #007bff;

    color: white;

    cursor: pointer;

}


.update-btn:hover {

    background: #0056b3;

}


/* =========================
   ADDRESS
========================= */

.address {

    max-width: 220px;

    text-align: left;

    word-wrap: break-word;

}


/* =========================
   EMPTY
========================= */

.empty {

    text-align: center;

    padding: 50px 20px;

}


/* =========================
   BACK BUTTON
========================= */

.back-btn {

    display: inline-block;

    margin-top: 20px;

    padding: 11px 18px;

    background: #28a745;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}


.back-btn:hover {

    background: #218838;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

    .container {

        margin: 20px auto;

        padding: 10px;

    }

    .box {

        padding: 18px;

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

    <p>Seller Order Management</p>

</div>


<div class="container">

<div class="box">


<h2>
    📦 Customer Orders
</h2>


<?php if ($result->num_rows > 0) { ?>


<div class="table-wrapper">

<table>

<tr>

    <th>Order ID</th>

    <th>Customer</th>

    <th>Total</th>

    <th>Payment</th>

    <th>Payment Status</th>

    <th>Delivery Address</th>

    <th>Order Status</th>

    <th>Update</th>

</tr>


<?php while ($order = $result->fetch_assoc()) { ?>


<?php

$status = strtolower(
    trim($order['status'] ?? 'pending')
);

?>


<tr>


    <!-- ORDER ID -->

    <td>

        <b>
            #<?php
            echo intval($order['id']);
            ?>
        </b>

    </td>


    <!-- CUSTOMER -->

    <td>

        User #

        <?php
        echo intval(
            $order['user_id']
        );
        ?>

    </td>


    <!-- TOTAL -->

    <td>

        ₹<?php

        echo number_format(
            (float)$order['total_amount'],
            2
        );

        ?>

    </td>


    <!-- PAYMENT METHOD -->

    <td>

        <?php

        echo htmlspecialchars(
            $order['payment_method'] ?? 'Cash on Delivery'
        );

        ?>

    </td>


    <!-- PAYMENT STATUS -->

    <td>

        <?php

        echo htmlspecialchars(
            $order['payment_status'] ?? 'Pending'
        );

        ?>

    </td>


    <!-- DELIVERY ADDRESS -->

    <td class="address">

        <?php

        echo nl2br(
            htmlspecialchars(
                $order['delivery_address'] ?? ''
            )
        );

        ?>

    </td>


    <!-- ORDER STATUS -->

    <td>

        <span
            class="status <?php
                echo htmlspecialchars($status);
            ?>"
        >

            <?php

            echo htmlspecialchars(
                $order['status'] ?? 'Pending'
            );

            ?>

        </span>

    </td>


    <!-- UPDATE STATUS -->

    <td>

        <form
            method="POST"
            action="update_order_status.php"
            class="status-form"
        >


            <input
                type="hidden"
                name="order_id"
                value="<?php
                    echo intval($order['id']);
                ?>"
            >


            <select
                name="status"
                required
            >

                <option
                    value="Pending"
                    <?php
                    if (($order['status'] ?? '') === 'Pending') {
                        echo 'selected';
                    }
                    ?>
                >
                    Pending
                </option>


                <option
                    value="Confirmed"
                    <?php
                    if (($order['status'] ?? '') === 'Confirmed') {
                        echo 'selected';
                    }
                    ?>
                >
                    Confirmed
                </option>


                <option
                    value="Shipped"
                    <?php
                    if (($order['status'] ?? '') === 'Shipped') {
                        echo 'selected';
                    }
                    ?>
                >
                    Shipped
                </option>


                <option
                    value="Delivered"
                    <?php
                    if (($order['status'] ?? '') === 'Delivered') {
                        echo 'selected';
                    }
                    ?>
                >
                    Delivered
                </option>


                <option
                    value="Cancelled"
                    <?php
                    if (($order['status'] ?? '') === 'Cancelled') {
                        echo 'selected';
                    }
                    ?>
                >
                    Cancelled
                </option>

            </select>


            <button
                type="submit"
                class="update-btn"
            >

                Update

            </button>


        </form>

    </td>


</tr>


<?php } ?>


</table>

</div>


<?php } else { ?>


<div class="empty">

    <h3>
        📦 No Orders Found
    </h3>

    <p>
        No customer orders are available for your products.
    </p>

</div>


<?php } ?>


<a
    href="seller_dashboard.php"
    class="back-btn"
>

    ← Seller Dashboard

</a>


</div>

</div>


</body>

</html>