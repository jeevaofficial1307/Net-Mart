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
   POST CHECK
========================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: seller_orders.php");
    exit();

}


$order_id = intval($_POST['order_id'] ?? 0);

$status = trim($_POST['status'] ?? '');


/* =========================
   VALIDATE ORDER ID
========================= */

if ($order_id <= 0) {

    die("Invalid Order ID.");

}


/* =========================
   ALLOWED STATUS
========================= */

$allowed_statuses = [

    'Pending',
    'Confirmed',
    'Shipped',
    'Delivered',
    'Cancelled'

];


if (!in_array($status, $allowed_statuses, true)) {

    die("Invalid order status.");

}


/* =========================
   CHECK SELLER OWNS ORDER
========================= */

$check_sql = "
    SELECT DISTINCT orders.id

    FROM orders

    JOIN order_items
        ON orders.id = order_items.order_id

    JOIN products
        ON order_items.product_id = products.id

    WHERE orders.id = ?

    AND products.seller_id = ?
";


$check_stmt = $conn->prepare($check_sql);


if (!$check_stmt) {

    die(
        "Check query failed: " .
        htmlspecialchars($conn->error)
    );

}


$check_stmt->bind_param(
    "ii",
    $order_id,
    $seller_id
);


$check_stmt->execute();


$check_result = $check_stmt->get_result();


if ($check_result->num_rows === 0) {

    die(
        "You are not authorized to update this order."
    );

}


$check_stmt->close();


/* =========================
   UPDATE STATUS
========================= */

$update_sql = "
    UPDATE orders

    SET status = ?

    WHERE id = ?
";


$update_stmt = $conn->prepare($update_sql);


if (!$update_stmt) {

    die(
        "Update query failed: " .
        htmlspecialchars($conn->error)
    );

}


$update_stmt->bind_param(
    "si",
    $status,
    $order_id
);


if (!$update_stmt->execute()) {

    die(
        "Order status update failed: " .
        htmlspecialchars($update_stmt->error)
    );

}


$update_stmt->close();


/* =========================
   GO BACK
========================= */

header("Location: seller_orders.php");
exit();

?>