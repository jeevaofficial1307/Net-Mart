<?php

session_start();
include 'config.php';


/* =========================
   CUSTOMER LOGIN CHECK
========================= */

if (!isset($_SESSION['customer_id'])) {

    header("Location: customer_login.php");
    exit();

}

$user_id = intval($_SESSION['customer_id']);


/* =========================
   PRODUCT ID
========================= */

if (isset($_POST['product_id'])) {

    $product_id = intval($_POST['product_id']);

} elseif (isset($_GET['id'])) {

    $product_id = intval($_GET['id']);

} else {

    header("Location: index.php");
    exit();

}


/* =========================
   QUANTITY
========================= */

$quantity = isset($_POST['quantity'])
    ? intval($_POST['quantity'])
    : 1;

if ($quantity < 1) {
    $quantity = 1;
}


/* =========================
   GET PRODUCT
========================= */

$stmt = $conn->prepare(
    "SELECT id, title, price, stock
     FROM products
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit();

}

$product = $result->fetch_assoc();

$stmt->close();


/* =========================
   STOCK CHECK
========================= */

$stock = intval($product['stock']);

if ($stock <= 0) {

    echo "<script>
        alert('This book is out of stock.');
        window.location.href='product_details.php?id={$product_id}';
    </script>";

    exit();

}

if ($quantity > $stock) {
    $quantity = $stock;
}


/* =========================
   CHECK CART
========================= */

$stmt = $conn->prepare(
    "SELECT id, quantity
     FROM cart
     WHERE user_id = ?
     AND product_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param(
    "ii",
    $user_id,
    $product_id
);

$stmt->execute();

$cartResult = $stmt->get_result();


/* =========================
   EXISTING CART ITEM
========================= */

if ($cartResult->num_rows > 0) {

    $cart = $cartResult->fetch_assoc();

    $newQuantity =
        intval($cart['quantity']) + $quantity;

    if ($newQuantity > $stock) {
        $newQuantity = $stock;
    }

    $cart_id = intval($cart['id']);

    $stmt->close();

    $stmt = $conn->prepare(
        "UPDATE cart
         SET quantity = ?
         WHERE id = ?
         AND user_id = ?"
    );

    if (!$stmt) {
        die("Database error: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param(
        "iii",
        $newQuantity,
        $cart_id,
        $user_id
    );

    $stmt->execute();

    $stmt->close();

}


/* =========================
   NEW CART ITEM
========================= */

else {

    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO cart
        (user_id, product_id, quantity)
        VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        die("Database error: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param(
        "iii",
        $user_id,
        $product_id,
        $quantity
    );

    $stmt->execute();

    $stmt->close();

}


/* =========================
   GO TO CART
========================= */

header("Location: cart.php");
exit();

?>