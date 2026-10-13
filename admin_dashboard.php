<?php
session_start();
require_once "config.php";

/* Admin login check */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* Total Books */
$books = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM products");

if ($result) {
    $books = $result->fetch_assoc()['total'];
}

/* Total Customers */
$customers = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='customer'");

if ($result) {
    $customers = $result->fetch_assoc()['total'];
}

/* Total Sellers */
$sellers = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='seller'");

if ($result) {
    $sellers = $result->fetch_assoc()['total'];
}

/* Total Orders */
$orders = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM orders");

if ($result) {
    $orders = $result->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>NetMart Admin Dashboard</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

.header {
    background: #222;
    color: white;
    padding: 18px 30px;
}

.header h1 {
    margin: 0;
}

.container {
    display: flex;
    min-height: calc(100vh - 80px);
}

.sidebar {
    width: 230px;
    background: #333;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 16px 20px;
}

.sidebar a:hover {
    background: #555;
}

.content {
    flex: 1;
    padding: 30px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 8px #ccc;
}

.card h2 {
    font-size: 32px;
    margin: 5px;
}

.card p {
    color: #666;
}

.menu-title {
    color: #aaa;
    padding: 15px 20px 5px;
    font-size: 13px;
}

.logout {
    margin-top: 30px;
    background: #d9534f;
}

.logout:hover {
    background: #c9302c !important;
}

</style>

</head>

<body>

<div class="header">

<h1>📚 NetMart Admin Dashboard</h1>

<p>
Welcome, Admin
</p>

</div>

<div class="container">

<!-- SIDEBAR -->

<div class="sidebar">

<div class="menu-title">
ADMIN MENU
</div>

<a href="admin_dashboard.php">
📊 Dashboard
</a>

<a href="add_book.php">
➕ Add Book
</a>

<a href="view_books.php">
📚 View Books
</a>

<a href="cart.php">
🛒 Cart
</a>

<a href="index.php">
🏠 NetMart Home
</a>

<!-- LOGOUT -->
<a class="logout" href="logout.php">
🚪 Logout
</a>

</div>


<!-- CONTENT -->

<div class="content">

<h2>Admin Overview</h2>

<div class="cards">

<div class="card">

<h2>
<?php echo $books; ?>
</h2>

<p>Total Books</p>

</div>


<div class="card">

<h2>
<?php echo $sellers; ?>
</h2>

<p>Total Sellers</p>

</div>


<div class="card">

<h2>
<?php echo $customers; ?>
</h2>

<p>Total Customers</p>

</div>


<div class="card">

<h2>
<?php echo $orders; ?>
</h2>

<p>Total Orders</p>

</div>

</div>


<h2>Admin Functions</h2>

<p>
Use the menu to manage books and view the NetMart shopping system.
</p>


</div>

</div>

</body>

</html>