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
   POST CHECK
========================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: checkout.php");
    exit();

}


/* =========================
   GET FORM DATA
========================= */

$address = trim($_POST['address'] ?? '');

$payment_type = trim($_POST['payment_type'] ?? '');


/* =========================
   VALIDATION
========================= */

if ($address === '') {

    die("Delivery address is required.");

}


/*
 * Only Cash on Delivery is allowed.
 */

if ($payment_type !== 'cod') {

    die("Invalid payment method.");

}


/* =========================
   GET CART
========================= */

$sql = "SELECT
            cart.product_id,
            cart.quantity,
            products.title,
            products.price,
            products.stock
        FROM cart
        JOIN products
        ON cart.product_id = products.id
        WHERE cart.user_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Cart query failed: " .
        htmlspecialchars($conn->error)
    );

}

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


$cart_items = [];

$total = 0;


/* =========================
   READ CART
========================= */

while ($row = $result->fetch_assoc()) {

    $product_id = intval(
        $row['product_id']
    );

    $quantity = intval(
        $row['quantity']
    );

    $price = floatval(
        $row['price']
    );

    $stock = intval(
        $row['stock']
    );


    /* Invalid quantity */

    if ($quantity <= 0) {

        continue;

    }


    /* Stock check */

    if ($quantity > $stock) {

        die(
            "Only " .
            $stock .
            " stock available for " .
            htmlspecialchars(
                $row['title']
            )
        );

    }


    /* Calculate total */

    $total +=
        $price * $quantity;


    $cart_items[] = [

        'product_id' => $product_id,

        'quantity' => $quantity,

        'price' => $price

    ];

}


/* =========================
   EMPTY CART CHECK
========================= */

if (empty($cart_items)) {

    die("Your cart is empty.");

}


/* =========================
   TRANSACTION
========================= */

$conn->begin_transaction();


try {


    /* =========================
       CREATE ORDER
    ========================= */

    $order_sql = "INSERT INTO orders
    (
        user_id,
        total_amount,
        delivery_address,
        payment_method,
        payment_status,
        status
    )
    VALUES
    (
        ?,
        ?,
        ?,
        'Cash on Delivery',
        'Pending',
        'Pending'
    )";


    $order_stmt =
        $conn->prepare(
            $order_sql
        );


    if (!$order_stmt) {

        throw new Exception(
            "Order prepare failed: " .
            $conn->error
        );

    }


    /*
     * i = integer
     * d = decimal
     * s = string
     */

    $order_stmt->bind_param(
        "ids",
        $user_id,
        $total,
        $address
    );


    $order_stmt->execute();


    $order_id =
        $conn->insert_id;


    if ($order_id <= 0) {

        throw new Exception(
            "Order creation failed."
        );

    }


    /* =========================
       ORDER ITEMS
    ========================= */

    $item_sql = "INSERT INTO order_items
    (
        order_id,
        product_id,
        quantity,
        price
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )";


    $item_stmt =
        $conn->prepare(
            $item_sql
        );


    if (!$item_stmt) {

        throw new Exception(
            "Order item prepare failed: " .
            $conn->error
        );

    }


    /* =========================
       STOCK UPDATE
    ========================= */

    $stock_sql = "UPDATE products
                  SET stock = stock - ?
                  WHERE id = ?
                  AND stock >= ?";


    $stock_stmt =
        $conn->prepare(
            $stock_sql
        );


    if (!$stock_stmt) {

        throw new Exception(
            "Stock update prepare failed: " .
            $conn->error
        );

    }


    /* =========================
       INSERT ITEMS + STOCK
    ========================= */

    foreach ($cart_items as $item) {

        $product_id =
            intval(
                $item['product_id']
            );

        $quantity =
            intval(
                $item['quantity']
            );

        $price =
            floatval(
                $item['price']
            );


        /* INSERT ORDER ITEM */

        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $product_id,
            $quantity,
            $price
        );


        $item_stmt->execute();


        if ($item_stmt->affected_rows !== 1) {

            throw new Exception(
                "Order item creation failed."
            );

        }


        /* UPDATE STOCK */

        $stock_stmt->bind_param(
            "iii",
            $quantity,
            $product_id,
            $quantity
        );


        $stock_stmt->execute();


        if (
            $stock_stmt->affected_rows !== 1
        ) {

            throw new Exception(
                "Stock update failed."
            );

        }

    }


    /* =========================
       CLEAR CART
    ========================= */

    $clear_sql =
        "DELETE FROM cart
         WHERE user_id = ?";


    $clear_stmt =
        $conn->prepare(
            $clear_sql
        );


    if (!$clear_stmt) {

        throw new Exception(
            "Cart clear failed: " .
            $conn->error
        );

    }


    $clear_stmt->bind_param(
        "i",
        $user_id
    );


    $clear_stmt->execute();


    /* =========================
       COMMIT
    ========================= */

    $conn->commit();


    /* =========================
       REDIRECT
    ========================= */

    header(
        "Location: order_success.php?id=" .
        $order_id
    );

    exit();


}
catch (Exception $e) {


    /* Rollback */

    $conn->rollback();


    die(
        "COD Order failed: " .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>