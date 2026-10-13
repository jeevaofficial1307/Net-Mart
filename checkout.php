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

$customer_id = intval($_SESSION['customer_id']);


/* =========================
   GET CART ITEMS
========================= */

$sql = "
    SELECT
        cart.id AS cart_id,
        cart.product_id,
        cart.quantity,
        products.title,
        products.price,
        products.stock
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
    ORDER BY cart.id DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . htmlspecialchars($conn->error));

}


$stmt->bind_param("i", $customer_id);

$stmt->execute();

$result = $stmt->get_result();


$total = 0;

$cart_items = [];

$stock_error = "";


/* =========================
   READ CART
========================= */

while ($row = $result->fetch_assoc()) {

    $quantity = (int)$row['quantity'];

    $stock = (int)$row['stock'];

    $price = (float)$row['price'];


    if ($quantity <= 0) {
        continue;
    }


    $subtotal = $price * $quantity;

    $total += $subtotal;

    $cart_items[] = $row;


    /* STOCK CHECK */

    if ($quantity > $stock && $stock_error === "") {

        $stock_error =
            "Only " .
            $stock .
            " stock available for " .
            $row['title'];

    }

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

<title>NetMart - Checkout</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}

.header {

    background: #222;

    color: white;

    padding: 22px 40px;

}

.header h1 {

    margin: 0;

    font-size: 30px;

}

.header p {

    margin-bottom: 0;

}

.container {

    max-width: 1000px;

    margin: 40px auto;

    padding: 20px;

}

.box {

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}

table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;

}

th {

    background: #333;

    color: white;

    padding: 13px;

}

td {

    padding: 13px;

    text-align: center;

    border-bottom: 1px solid #ddd;

}

.total {

    text-align: right;

    font-size: 24px;

    font-weight: bold;

    margin: 25px 0;

}

label {

    display: block;

    font-weight: bold;

    margin-top: 20px;

    margin-bottom: 8px;

}

textarea {

    width: 100%;

    padding: 13px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 15px;

    height: 110px;

    resize: vertical;

}

.payment-box {

    margin-top: 25px;

    padding: 20px;

    background: #f8f9fa;

    border: 1px solid #ddd;

    border-radius: 10px;

}

.payment-option {

    margin-top: 15px;

    padding: 18px;

    background: white;

    border: 2px solid #28a745;

    border-radius: 8px;

}

.payment-option label {

    display: flex;

    align-items: center;

    gap: 12px;

    margin: 0;

    cursor: pointer;

}

.payment-option input {

    width: 18px;

    height: 18px;

}

.place-btn {

    width: 100%;

    margin-top: 25px;

    padding: 15px;

    border: none;

    border-radius: 7px;

    background: #28a745;

    color: white;

    font-size: 18px;

    cursor: pointer;

}

.place-btn:hover {

    background: #218838;

}

.back-btn {

    display: inline-block;

    margin-top: 15px;

    padding: 11px 18px;

    background: #007bff;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}

.error {

    background: #f8d7da;

    color: #721c24;

    padding: 15px;

    border-radius: 7px;

    margin-bottom: 20px;

}

.empty {

    text-align: center;

    padding: 40px;

}

@media (max-width: 700px) {

    .container {

        margin: 20px auto;

        padding: 10px;

    }

    .box {

        padding: 18px;

    }

    table {

        font-size: 13px;

    }

    th,
    td {

        padding: 8px;

    }

    .total {

        font-size: 20px;

    }

}

</style>

</head>


<body>


<div class="header">

    <h1>📚 NetMart</h1>

    <p>Secure Checkout</p>

</div>


<div class="container">

<div class="box">


<?php if (empty($cart_items)) { ?>


    <div class="empty">

        <h2>🛒 Your Cart is Empty</h2>

        <p>
            Please add books before checkout.
        </p>

        <a
            class="back-btn"
            href="index.php"
        >
            📚 Shop Books
        </a>

    </div>


<?php } else { ?>


    <?php if ($stock_error !== "") { ?>

        <div class="error">

            ⚠️

            <?php
            echo htmlspecialchars($stock_error);
            ?>

        </div>

    <?php } ?>


    <h2>🛒 Checkout</h2>

    <h3>📦 Order Summary</h3>


    <table>

        <tr>

            <th>Book</th>

            <th>Price</th>

            <th>Quantity</th>

            <th>Subtotal</th>

        </tr>


        <?php foreach ($cart_items as $item) { ?>

        <tr>

            <td>

                <?php
                echo htmlspecialchars($item['title']);
                ?>

            </td>


            <td>

                ₹<?php
                echo number_format(
                    $item['price'],
                    2
                );
                ?>

            </td>


            <td>

                <?php
                echo (int)$item['quantity'];
                ?>

            </td>


            <td>

                ₹<?php

                echo number_format(
                    $item['price'] * $item['quantity'],
                    2
                );

                ?>

            </td>

        </tr>

        <?php } ?>


    </table>


    <div class="total">

        Total Amount:

        ₹<?php

        echo number_format(
            $total,
            2
        );

        ?>

    </div>


    <form
        method="POST"
        action="create_payment.php"
    >


        <label>
            📍 Delivery Address
        </label>


        <textarea
            name="address"
            placeholder="Enter your complete delivery address"
            required
        ></textarea>


        <div class="payment-box">

            <h3>💳 Payment Method</h3>


            <div class="payment-option">

                <label>

                    <input
                        type="radio"
                        name="payment_type"
                        value="cod"
                        checked
                        required
                    >

                    <span>

                        💵
                        <b>Cash on Delivery</b>

                        <br>

                        <small>
                            Pay when your order is delivered.
                        </small>

                    </span>

                </label>

            </div>

        </div>


        <?php if ($stock_error === "") { ?>

            <button
                type="submit"
                name="place_order"
                class="place-btn"
            >

                🛍️ Place Order

            </button>

        <?php } ?>


    </form>


    <a
        class="back-btn"
        href="cart.php"
    >

        ← Back to Cart

    </a>


<?php } ?>


</div>

</div>


</body>

</html>


<?php

$stmt->close();

?>