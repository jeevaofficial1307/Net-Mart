<?php

include 'config.php';

if (isset($_GET['id'])) {

    $cart_id = (int) $_GET['id'];

    $sql = "DELETE FROM cart WHERE id = $cart_id";

    if ($conn->query($sql) === TRUE) {

        header("Location: cart.php");
        exit();

    } else {

        echo "Error removing item: " . $conn->error;
    }

} else {

    echo "No cart item selected.";
}

?>