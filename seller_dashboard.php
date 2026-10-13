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
   TOTAL BOOKS
========================= */

$books = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM products"
);

if ($result) {

    $row = $result->fetch_assoc();

    $books = intval($row['total']);

}


/* =========================
   TOTAL ORDERS
========================= */

$orders = 0;

$order_result = $conn->query(
    "SELECT COUNT(*) AS total FROM orders"
);

if ($order_result) {

    $row = $order_result->fetch_assoc();

    $orders = intval($row['total']);

}


/* =========================
   PENDING ORDERS
========================= */

$pending_orders = 0;

$pending_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE status = 'Pending'"
);

if ($pending_result) {

    $row = $pending_result->fetch_assoc();

    $pending_orders = intval($row['total']);

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

<title>NetMart Seller Dashboard</title>


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

    padding: 20px 30px;

}

.header h1 {

    margin: 0;

}

.header p {

    margin-bottom: 0;

}


/* =========================
   LAYOUT
========================= */

.container {

    display: flex;

    min-height: calc(100vh - 80px);

}


/* =========================
   SIDEBAR
========================= */

.sidebar {

    width: 240px;

    background: #333;

}


.sidebar a {

    display: block;

    color: white;

    text-decoration: none;

    padding: 16px 20px;

    border-bottom: 1px solid #444;

}


.sidebar a:hover {

    background: #555;

}


/* ORDERS LINK */

.orders-link {

    background: #007bff;

}


.orders-link:hover {

    background: #0056b3 !important;

}


/* LOGOUT */

.logout {

    margin-top: 30px;

    background: #d9534f;

}


.logout:hover {

    background: #c9302c !important;

}


/* =========================
   CONTENT
========================= */

.content {

    flex: 1;

    padding: 30px;

}


/* =========================
   CARDS
========================= */

.cards {

    display: flex;

    gap: 20px;

    flex-wrap: wrap;

}


.card {

    background: white;

    padding: 25px;

    width: 250px;

    border-radius: 10px;

    box-shadow: 0 3px 10px #ccc;

    text-align: center;

}


.card h2 {

    font-size: 35px;

    margin: 10px 0;

}


.card p {

    font-size: 17px;

}


/* =========================
   BUTTON
========================= */

.btn {

    display: inline-block;

    background: #007bff;

    color: white;

    padding: 12px 18px;

    text-decoration: none;

    border-radius: 6px;

    margin-top: 10px;

}


.btn:hover {

    background: #0056b3;

}


/* =========================
   ORDER BUTTON
========================= */

.order-btn {

    background: #28a745;

}


.order-btn:hover {

    background: #218838;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

    .container {

        flex-direction: column;

    }

    .sidebar {

        width: 100%;

    }

    .content {

        padding: 20px;

    }

    .card {

        width: 100%;

    }

}

</style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<div class="header">

    <h1>📚 NetMart Seller Dashboard</h1>

    <p>Welcome, Seller</p>

</div>


<div class="container">


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">


    <a href="seller_dashboard.php">

        📊 Dashboard

    </a>


    <a href="add_book.php">

        ➕ Add Book

    </a>


    <a href="view_books.php">

        📚 View Books

    </a>


    <!-- SELLER ORDERS -->

    <a
        href="seller_orders.php"
        class="orders-link"
    >

        📦 Seller Orders

    </a>


    <a href="cart.php">

        🛒 View Cart

    </a>


    <a href="index.php">

        🏠 Home

    </a>


    <!-- LOGOUT -->

    <a
        class="logout"
        href="logout.php"
    >

        🚪 Logout

    </a>


</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <h2>Seller Overview</h2>


    <div class="cards">


        <!-- BOOKS -->

        <div class="card">

            <h2>
                <?php echo $books; ?>
            </h2>

            <p>
                📚 Total Books
            </p>

        </div>


        <!-- ORDERS -->

        <div class="card">

            <h2>
                <?php echo $orders; ?>
            </h2>

            <p>
                📦 Total Orders
            </p>

            <a
                class="btn order-btn"
                href="seller_orders.php"
            >

                View Orders

            </a>

        </div>


        <!-- PENDING -->

        <div class="card">

            <h2>
                <?php echo $pending_orders; ?>
            </h2>

            <p>
                ⏳ Pending Orders
            </p>

            <a
                class="btn"
                href="seller_orders.php"
            >

                Manage Orders

            </a>

        </div>


    </div>


    <!-- =========================
         SELLER FUNCTIONS
    ========================= -->

    <h2 style="margin-top:40px;">

        Seller Functions

    </h2>


    <p>

        You can add books, view books and
        manage customer orders.

    </p>


    <a
        class="btn"
        href="add_book.php"
    >

        ➕ Add New Book

    </a>


    <a
        class="btn order-btn"
        href="seller_orders.php"
    >

        📦 Manage Customer Orders

    </a>


</div>


</div>


</body>

</html>