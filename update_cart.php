<?php

session_start();

include 'config.php';


if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


$user_id = intval($_SESSION['user_id']);


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: cart.php");
    exit();

}


$cart_id = intval($_GET['id']);

$action = isset($_GET['action'])
    ? $_GET['action']
    : '';


/* GET CART ITEM */

$sql = "SELECT cart.quantity, cart.product_id, products.stock
        FROM cart
        JOIN products
        ON cart.product_id = products.id
        WHERE cart.id = $cart_id
        AND cart.user_id = $user_id
        LIMIT 1";


$result = $conn->query($sql);


if (!$result || $result->num_rows == 0) {

    header("Location: cart.php");
    exit();

}


$row = $result->fetch_assoc();


$quantity = intval($row['quantity']);

$stock = intval($row['stock']);


/* PLUS */

if ($action == 'plus') {

    if ($quantity < $stock) {

        $quantity++;

    }

}


/* MINUS */

elseif ($action == 'minus') {

    $quantity--;

}


/* DELETE WHEN ZERO */

if ($quantity <= 0) {

    $delete = "DELETE FROM cart
               WHERE id = $cart_id
               AND user_id = $user_id";

    $conn->query($delete);

}


/* UPDATE QUANTITY */

else {

    $update = "UPDATE cart
               SET quantity = $quantity
               WHERE id = $cart_id
               AND user_id = $user_id";

    $conn->query($update);

}


header("Location: cart.php");

exit();

?>