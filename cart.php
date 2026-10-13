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
   CART ITEMS
========================= */

$sql = "
    SELECT
        cart.id AS cart_id,
        cart.quantity,

        products.id AS product_id,
        products.title,
        products.price,
        products.stock,
        products.image

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

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$total = 0;

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - My Cart</title>


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

    padding: 25px 40px;

}

.header h1 {

    margin: 0;

}

.container {

    max-width: 1100px;

    margin: 40px auto;

    padding: 20px;

}

.cart-box {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}

table {

    width: 100%;

    border-collapse: collapse;

}

th {

    background: #333;

    color: white;

    padding: 15px;

}

td {

    padding: 15px;

    text-align: center;

    border-bottom: 1px solid #ddd;

}

.book-img {

    width: 70px;

    height: 80px;

    object-fit: cover;

    border-radius: 6px;

}

.quantity {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 8px;

}

.quantity a {

    width: 32px;

    height: 32px;

    display: flex;

    justify-content: center;

    align-items: center;

    color: white;

    text-decoration: none;

    border-radius: 5px;

    font-size: 20px;

}

.minus {

    background: #dc3545;

}

.plus {

    background: #28a745;

}

.qty-number {

    min-width: 30px;

    font-weight: bold;

}

.remove {

    background: #dc3545;

    color: white;

    padding: 9px 14px;

    border-radius: 6px;

    text-decoration: none;

}

.total {

    text-align: right;

    font-size: 24px;

    font-weight: bold;

    margin-top: 25px;

}

.buttons {

    margin-top: 25px;

    display: flex;

    justify-content: space-between;

    gap: 10px;

}

.btn {

    padding: 12px 20px;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}

.shop {

    background: #007bff;

}

.checkout {

    background: #28a745;

}

.empty {

    text-align: center;

    padding: 40px;

}

@media(max-width:700px) {

    table {

        font-size: 12px;

    }

    th,
    td {

        padding: 7px;

    }

    .book-img {

        width: 45px;

        height: 55px;

    }

    .buttons {

        flex-direction: column;

    }

}

</style>

</head>


<body>


<div class="header">

    <h1>📚 NetMart</h1>

    <p>🛒 My Shopping Cart</p>

</div>


<div class="container">

<div class="cart-box">


<h2>🛒 My Cart</h2>


<?php if ($result && $result->num_rows > 0) { ?>


<table>

<tr>

    <th>Image</th>

    <th>Book</th>

    <th>Price</th>

    <th>Quantity</th>

    <th>Subtotal</th>

    <th>Action</th>

</tr>


<?php while ($row = $result->fetch_assoc()) {


    $price = (float)$row['price'];

    $quantity = (int)$row['quantity'];

    $subtotal = $price * $quantity;

    $total += $subtotal;

?>


<tr>


<td>

<?php if (!empty($row['image'])) { ?>

<img
    class="book-img"
    src="<?php echo htmlspecialchars($row['image']); ?>"
    alt="Book"
>

<?php } else { ?>

<span style="font-size:40px;">📖</span>

<?php } ?>

</td>


<td>

<?php
echo htmlspecialchars($row['title']);
?>

</td>


<td>

₹<?php
echo number_format($price, 2);
?>

</td>


<td>

<div class="quantity">


<a
    class="minus"
    href="update_cart.php?id=<?php
    echo (int)$row['cart_id'];
    ?>&action=minus"
>
−
</a>


<span class="qty-number">

<?php
echo $quantity;
?>

</span>


<a
    class="plus"
    href="update_cart.php?id=<?php
    echo (int)$row['cart_id'];
    ?>&action=plus"
>
+
</a>


</div>

</td>


<td>

₹<?php
echo number_format($subtotal, 2);
?>

</td>


<td>

<a
    class="remove"
    href="remove_from_cart.php?id=<?php
    echo (int)$row['cart_id'];
    ?>"
    onclick="return confirm('Remove this book from cart?');"
>

Remove

</a>

</td>


</tr>


<?php } ?>


</table>


<div class="total">

Total Amount:
₹<?php echo number_format($total, 2); ?>

</div>


<div class="buttons">


<a
    class="btn shop"
    href="index.php"
>

← Continue Shopping

</a>


<a
    class="btn checkout"
    href="checkout.php"
>

Proceed to Checkout →

</a>


</div>


<?php } else { ?>


<div class="empty">

<h3>🛒 Your Cart is Empty</h3>

<p>Add some books to your cart.</p>


<a
    class="btn shop"
    href="index.php"
>

📚 Shop Books

</a>

</div>


<?php } ?>


</div>

</div>


</body>

</html>

<?php

$stmt->close();

?>