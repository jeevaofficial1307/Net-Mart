<?php

session_start();

include 'config.php';

require_once __DIR__ . '/razorpay_config.php';

use Razorpay\Api\Api;


/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");

    exit();

}


$user_id = intval($_SESSION['user_id']);


/* =========================
   GET PAYMENT DATA
========================= */

$payment_id =
    $_POST['razorpay_payment_id'] ?? '';

$razorpay_order_id =
    $_POST['razorpay_order_id'] ?? '';

$signature =
    $_POST['razorpay_signature'] ?? '';


if (
    $payment_id == '' ||
    $razorpay_order_id == '' ||
    $signature == ''
) {

    die("Invalid payment response.");

}


/* =========================
   VERIFY PAYMENT
========================= */

try {

    $api = new Api(
        $razorpay_key_id,
        $razorpay_key_secret
    );


    $attributes = [

        'razorpay_order_id' =>
            $razorpay_order_id,

        'razorpay_payment_id' =>
            $payment_id,

        'razorpay_signature' =>
            $signature

    ];


    $api->utility
        ->verifyPaymentSignature(
            $attributes
        );


}
catch (Exception $e) {

    die(
        "Payment verification failed."
    );

}


/* =========================
   GET ADDRESS
========================= */

$address =
    $_SESSION['pending_address'] ?? '';


if ($address == '') {

    die("Delivery address not found.");

}


/* =========================
   GET CART AGAIN
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

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();


$total = 0;

$cart_items = [];


while ($row = $result->fetch_assoc()) {

    if ($row['quantity'] > $row['stock']) {

        die(
            "Stock unavailable for "
            . htmlspecialchars(
                $row['title']
            )
        );

    }


    $total +=
        $row['price']
        * $row['quantity'];


    $cart_items[] = $row;

}


if (empty($cart_items)) {

    die(
        "Cart is empty or order already processed."
    );

}


/* =========================
   CREATE ORDER
========================= */

$conn->begin_transaction();


try {


    /* ORDER */

    $order_sql = "INSERT INTO orders
    (
        user_id,
        total_amount,
        delivery_address,
        payment_method,
        payment_id,
        razorpay_order_id,
        razorpay_signature,
        payment_status,
        status
    )
    VALUES
    (
        ?, ?, ?, 'Razorpay',
        ?, ?, ?, 'Paid', 'Pending'
    )";


    $order_stmt =
        $conn->prepare($order_sql);


    $order_stmt->bind_param(
        "idsssss",
        $user_id,
        $total,
        $address,
        $payment_id,
        $razorpay_order_id,
        $signature
    );


    $order_stmt->execute();


    $order_id =
        $conn->insert_id;


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
    VALUES (?, ?, ?, ?)";


    $item_stmt =
        $conn->prepare($item_sql);


    /* =========================
       STOCK
    ========================= */

    $stock_sql = "UPDATE products
                  SET stock = stock - ?
                  WHERE id = ?
                  AND stock >= ?";


    $stock_stmt =
        $conn->prepare($stock_sql);


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


        /* ORDER ITEM */

        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $product_id,
            $quantity,
            $price
        );


        $item_stmt->execute();


        /* STOCK */

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
        $conn->prepare($clear_sql);


    $clear_stmt->bind_param(
        "i",
        $user_id
    );


    $clear_stmt->execute();


    /* =========================
       COMMIT
    ========================= */

    $conn->commit();


    /* Clear pending session */

    unset(
        $_SESSION['pending_address'],
        $_SESSION['pending_total'],
        $_SESSION['razorpay_order_id']
    );


    /* Redirect */

    header(
        "Location: order_success.php?id="
        . $order_id
    );

    exit();


}
catch (Exception $e) {

    $conn->rollback();

    die(
        "Order creation failed: "
        . htmlspecialchars(
            $e->getMessage()
        )
    );

}