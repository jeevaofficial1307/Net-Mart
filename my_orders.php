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
   GET USER ORDERS
========================= */

$sql = "SELECT
            id,
            total_amount,
            payment_method,
            payment_status,
            status,
            created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY id DESC";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Order query failed: " . $conn->error);

}


$stmt->bind_param(
    "i",
    $user_id
);

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

<title>NetMart - My Orders</title>


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

    font-size: 30px;

}


.header p {

    margin-bottom: 0;

}


.container {

    max-width: 1100px;

    margin: 40px auto;

    padding: 20px;

}


.box {

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow: 0 3px 10px #ccc;

}


h2 {

    margin-top: 0;

}


.table-container {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;

    min-width: 850px;

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


/* =========================
   STATUS
========================= */

.status {

    padding: 7px 12px;

    border-radius: 5px;

    font-weight: bold;

    display: inline-block;

}


/* Pending */

.pending {

    background: #fff3cd;

    color: #856404;

}


/* Confirmed */

.confirmed {

    background: #d1ecf1;

    color: #0c5460;

}


/* Shipped */

.shipped {

    background: #cce5ff;

    color: #004085;

}


/* Delivered */

.delivered {

    background: #d4edda;

    color: #155724;

}


/* Cancelled */

.cancelled {

    background: #f8d7da;

    color: #721c24;

}


/* =========================
   PAYMENT STATUS
========================= */

.payment-status {

    padding: 6px 10px;

    border-radius: 5px;

    font-weight: bold;

    display: inline-block;

}


.payment-pending {

    background: #fff3cd;

    color: #856404;

}


.payment-paid {

    background: #d4edda;

    color: #155724;

}


/* =========================
   VIEW BUTTON
========================= */

.view-btn {

    display: inline-block;

    padding: 8px 14px;

    background: #007bff;

    color: white;

    text-decoration: none;

    border-radius: 5px;

}


.view-btn:hover {

    background: #0056b3;

}


/* =========================
   SHOP BUTTON
========================= */

.shop-btn {

    display: inline-block;

    margin-top: 25px;

    padding: 11px 18px;

    background: #28a745;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}


.shop-btn:hover {

    background: #218838;

}


/* =========================
   EMPTY
========================= */

.empty {

    text-align: center;

    padding: 40px;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

    .header {

        padding: 18px 20px;

    }

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

    <h1>
        📚 NetMart
    </h1>

    <p>
        My Orders
    </p>

</div>


<div class="container">

<div class="box">


<h2>
    📦 My Orders
</h2>


<?php if ($result->num_rows > 0) { ?>


<div class="table-container">


<table>


<tr>

    <th>
        Order ID
    </th>

    <th>
        Total Amount
    </th>

    <th>
        Payment Method
    </th>

    <th>
        Payment Status
    </th>

    <th>
        Order Status
    </th>

    <th>
        Order Date
    </th>

    <th>
        Action
    </th>

</tr>


<?php while ($row = $result->fetch_assoc()) { ?>


<tr>


<!-- ORDER ID -->

<td>

    #<?php

    echo intval(
        $row['id']
    );

    ?>

</td>


<!-- TOTAL -->

<td>

    ₹<?php

    echo number_format(
        $row['total_amount'],
        2
    );

    ?>

</td>


<!-- PAYMENT METHOD -->

<td>

    <?php

    echo htmlspecialchars(
        $row['payment_method']
    );

    ?>

</td>


<!-- PAYMENT STATUS -->

<td>

<?php

$payment_status =
    strtolower(
        trim(
            $row['payment_status']
        )
    );

$payment_class =
    $payment_status === 'paid'
    ? 'payment-paid'
    : 'payment-pending';

?>

<span
    class="payment-status <?php echo $payment_class; ?>"
>

    <?php

    echo htmlspecialchars(
        $row['payment_status']
    );

    ?>

</span>

</td>


<!-- ORDER STATUS -->

<td>

<?php

$status =
    strtolower(
        trim(
            $row['status']
        )
    );

?>

<span
    class="status <?php echo htmlspecialchars($status); ?>"
>

    <?php

    echo htmlspecialchars(
        $row['status']
    );

    ?>

</span>

</td>


<!-- DATE -->

<td>

    <?php

    echo htmlspecialchars(
        $row['created_at']
    );

    ?>

</td>


<!-- VIEW -->

<td>

    <a
        class="view-btn"
        href="order_details.php?id=<?php echo intval($row['id']); ?>"
    >

        View Details

    </a>

</td>


</tr>


<?php } ?>


</table>


</div>


<a
    class="shop-btn"
    href="view_books.php"
>

    📚 Continue Shopping

</a>


<?php } else { ?>


<!-- =========================
     NO ORDERS
========================= -->

<div class="empty">

    <h3>
        📦 No Orders Found
    </h3>

    <p>
        You have not placed any orders yet.
    </p>


    <a
        class="shop-btn"
        href="view_books.php"
    >

        📚 Shop Books

    </a>

</div>


<?php } ?>


</div>

</div>


</body>

</html>