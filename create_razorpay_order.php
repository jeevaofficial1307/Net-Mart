<?php

session_start();

include 'config.php';
require_once 'razorpay_config.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}

$user_id = intval($_SESSION['user_id']);

/* Get cart */
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
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$total = 0;
$items = [];

while ($row = $result->fetch_assoc()) {

    if ($row['quantity'] > $row['stock']) {

        echo json_encode([
            "success" => false,
            "message" => "Insufficient stock for " . $row['title']
        ]);

        exit();
    }

    $total += $row['price'] * $row['quantity'];

    $items[] = $row;
}

if (empty($items)) {

    echo json_encode([
        "success" => false,
        "message" => "Your cart is empty."
    ]);

    exit();
}

/*
 * Razorpay amount is in paise.
 * ₹100 = 10000 paise
 */

$amount = (int) round($total * 100);

try {

    $razorpayOrder = $api->order->create([
        'receipt' => 'NETMART_' . time() . '_' . $user_id,
        'amount' => $amount,
        'currency' => 'INR'
    ]);

    $razorpayOrderId = $razorpayOrder['id'];

    /*
     * Create temporary order in database.
     */

    $payment_method = "Razorpay";

    $status = "Pending";

    $payment_status = "Pending";

    $address = "";

    $order_sql = "INSERT INTO orders
        (
            user_id,
            total_amount,
            delivery_address,
            payment_method,
            status,
            razorpay_order_id,
            payment_status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $order_stmt = $conn->prepare($order_sql);

    $order_stmt->bind_param(
        "idsssss",
        $user_id,
        $total,
        $address,
        $payment_method,
        $status,
        $razorpayOrderId,
        $payment_status
    );

    $order_stmt->execute();

    $order_id = $conn->insert_id;

    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "razorpay_order_id" => $razorpayOrderId,
        "amount" => $amount,
        "currency" => "INR",
        "key_id" => $keyId
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Unable to create payment order."
    ]);
}