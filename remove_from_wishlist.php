<?php

session_start();
include 'config.php';


/* LOGIN CHECK */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


$user_id = intval($_SESSION['user_id']);

$product_id = intval(
    $_GET['product_id'] ?? 0
);


if ($product_id <= 0) {

    header("Location: wishlist.php");
    exit();

}


/* REMOVE ONLY CURRENT USER'S WISHLIST */

$stmt = $conn->prepare(
    "DELETE FROM wishlist
     WHERE user_id = ?
     AND product_id = ?"
);

$stmt->bind_param(
    "ii",
    $user_id,
    $product_id
);

$stmt->execute();

$stmt->close();


header("Location: wishlist.php");
exit();

?>