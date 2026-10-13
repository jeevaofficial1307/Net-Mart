<?php

session_start();
include 'config.php';


/* =========================
   PRODUCT ID
========================= */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {

    header("Location: index.php");
    exit();

}


/* =========================
   GET PRODUCT
========================= */

$stmt = $conn->prepare(
    "SELECT
        products.*,
        categories.name AS category_name
     FROM products
     LEFT JOIN categories
        ON products.category_id = categories.id
     WHERE products.id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $id);

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
   WISHLIST STATUS
========================= */

$is_wishlisted = false;

if (isset($_SESSION['customer_id'])) {

    $user_id = intval($_SESSION['customer_id']);

    $stmt = $conn->prepare(
        "SELECT id
         FROM wishlist
         WHERE user_id = ?
         AND product_id = ?
         LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $user_id,
            $id
        );

        $stmt->execute();

        $wishResult = $stmt->get_result();

        if ($wishResult->num_rows > 0) {

            $is_wishlisted = true;

        }

        $stmt->close();

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

<title>
<?php echo htmlspecialchars($product['title']); ?>
- NetMart
</title>


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

    background: linear-gradient(
        135deg,
        #1769ff,
        #5b00ff
    );

    color: white;

    padding: 25px;

}

.header h1 {

    margin: 0;

}

.container {

    width: 92%;

    max-width: 1000px;

    margin: 40px auto;

}

.card {

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 15px #ccc;

}

.product-layout {

    display: grid;

    grid-template-columns: 300px 1fr;

    gap: 35px;

}

.product-image {

    width: 100%;

    height: 400px;

    background: #eef0f3;

    border-radius: 10px;

    display: flex;

    justify-content: center;

    align-items: center;

    overflow: hidden;

}

.product-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}

.no-image {

    font-size: 100px;

}

h1 {

    margin-top: 0;

}

.author {

    color: #666;

    font-size: 17px;

}

.category {

    color: #777;

    margin-top: 10px;

}

.price {

    font-size: 30px;

    color: #20a044;

    font-weight: bold;

    margin: 20px 0;

}

.stock {

    margin: 15px 0;

    font-weight: bold;

}

.buttons {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 25px;

}

.btn {

    display: inline-block;

    padding: 13px 20px;

    color: white;

    text-decoration: none;

    border-radius: 7px;

}

.cart {

    background: #007bff;

}

.cart:hover {

    background: #0056b3;

}

.wishlist {

    background: #e91e63;

}

.wishlist:hover {

    background: #c2185b;

}

.wishlisted {

    background: #28a745;

}

.back {

    background: #555;

}

.out {

    color: red;

    font-weight: bold;

}

@media(max-width:700px) {

    .product-layout {

        grid-template-columns: 1fr;

    }

    .product-image {

        height: 320px;

    }

}

</style>

</head>


<body>


<div class="header">

<h1>📚 NetMart</h1>

<p>Book Details</p>

</div>


<div class="container">


<div class="card">


<div class="product-layout">


<!-- IMAGE -->

<div class="product-image">


<?php if (!empty($product['image'])) { ?>

<img
    src="<?php
    echo htmlspecialchars($product['image']);
    ?>"
    alt="<?php
    echo htmlspecialchars($product['title']);
    ?>"
>

<?php } else { ?>

<div class="no-image">
📖
</div>

<?php } ?>


</div>


<!-- DETAILS -->

<div>


<h1>

📖

<?php
echo htmlspecialchars(
    $product['title']
);
?>

</h1>


<?php if (!empty($product['author'])) { ?>

<p class="author">

✍️ Author:

<?php
echo htmlspecialchars(
    $product['author']
);
?>

</p>

<?php } ?>


<p class="category">

📂 Category:

<?php

echo !empty($product['category_name'])
    ? htmlspecialchars($product['category_name'])
    : "General";

?>

</p>


<p>

<strong>Description</strong>

</p>


<p>

<?php

echo nl2br(
    htmlspecialchars(
        $product['description'] ?? ''
    )
);

?>

</p>


<div class="price">

₹<?php

echo number_format(
    (float)$product['price'],
    2
);

?>

</div>


<div class="stock">

<?php if ((int)$product['stock'] > 0) { ?>

<span style="color:green;">

✅ In Stock:
<?php echo (int)$product['stock']; ?>

</span>

<?php } else { ?>

<span class="out">

❌ Out of Stock

</span>

<?php } ?>

</div>


<div class="buttons">


<!-- ADD CART -->

<?php if ((int)$product['stock'] > 0) { ?>

<a
    class="btn cart"
    href="add_to_cart.php?id=<?php
    echo (int)$product['id'];
    ?>"
>

🛒 Add to Cart

</a>

<?php } ?>


<!-- WISHLIST -->

<?php if (isset($_SESSION['customer_id'])) { ?>


<a
    class="btn <?php
    echo $is_wishlisted
        ? 'wishlisted'
        : 'wishlist';
    ?>"
    href="add_wishlist.php?product_id=<?php
    echo (int)$product['id'];
    ?>"
>

<?php

echo $is_wishlisted
    ? '❤️ Already Wishlisted'
    : '🤍 Add Wishlist';

?>

</a>


<?php } else { ?>


<a
    class="btn wishlist"
    href="customer_login.php"
>

❤️ Login to Wishlist

</a>


<?php } ?>


<a
    class="btn back"
    href="index.php"
>

← Back to Shop

</a>


</div>


</div>


</div>


</div>


</div>


</body>

</html>