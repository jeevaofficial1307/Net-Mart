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

$customer_id = intval(
    $_SESSION['customer_id']
);


/* =========================
   GET CUSTOMER WISHLIST
========================= */

$sql = "
    SELECT
        wishlist.id AS wishlist_id,
        products.id AS product_id,
        products.title,
        products.author,
        products.description,
        products.price,
        products.stock,
        products.image,
        categories.name AS category_name

    FROM wishlist

    INNER JOIN products
        ON wishlist.product_id = products.id

    LEFT JOIN categories
        ON products.category_id = categories.id

    WHERE wishlist.user_id = ?

    ORDER BY wishlist.id DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Wishlist query failed: " .
        htmlspecialchars($conn->error)
    );

}

$stmt->bind_param(
    "i",
    $customer_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - My Wishlist</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}


/* HEADER */

.header {

    background: linear-gradient(
        135deg,
        #1769ff,
        #5b00ff
    );

    color: white;

    padding: 25px;

    text-align: center;

}

.header h1 {

    margin: 0;

}

.header p {

    margin-bottom: 0;

}


/* CONTAINER */

.container {

    max-width: 1200px;

    margin: 35px auto;

    padding: 20px;

}


/* TOP BAR */

.top-bar {

    background: white;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    flex-wrap: wrap;

}

.top-bar h2 {

    margin: 0;

}

.nav-btn {

    display: inline-block;

    padding: 10px 15px;

    background: #007bff;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}

.logout-btn {

    background: #dc3545;

}


/* BOOKS */

.books {

    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(240px, 1fr));

    gap: 25px;

}


/* BOOK */

.book {

    background: white;

    padding: 18px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}

.book-image {

    width: 100%;

    height: 240px;

    background: #eef0f3;

    border-radius: 8px;

    overflow: hidden;

    display: flex;

    align-items: center;

    justify-content: center;

}

.book-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}

.no-image {

    font-size: 70px;

}

.book h3 {

    margin-bottom: 8px;

}

.author {

    color: #666;

    margin-bottom: 10px;

}

.category {

    color: #777;

    font-size: 14px;

}

.price {

    color: #20a044;

    font-size: 22px;

    font-weight: bold;

    margin: 12px 0;

}

.stock {

    font-weight: bold;

    margin-bottom: 12px;

}

.in-stock {

    color: green;

}

.out-stock {

    color: red;

}


/* BUTTONS */

.buttons {

    display: flex;

    gap: 8px;

    flex-direction: column;

}

.details-btn {

    display: block;

    text-align: center;

    padding: 11px;

    background: #007bff;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}

.remove-btn {

    display: block;

    text-align: center;

    padding: 11px;

    background: #dc3545;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}

.details-btn:hover {

    background: #0056b3;

}

.remove-btn:hover {

    background: #c82333;

}


/* EMPTY */

.empty {

    background: white;

    padding: 60px 20px;

    border-radius: 12px;

    text-align: center;

}

.empty h2 {

    margin-bottom: 10px;

}

.shop-btn {

    display: inline-block;

    margin-top: 15px;

    padding: 12px 20px;

    background: #28a745;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}

</style>

</head>

<body>


<div class="header">

    <h1>📚 NetMart</h1>

    <p>
        ❤️ My Wishlist
    </p>

</div>


<div class="container">


<div class="top-bar">

    <h2>
        ❤️
        <?php
        echo htmlspecialchars(
            $_SESSION['customer_name'] ?? 'Customer'
        );
        ?>'s Wishlist
    </h2>


    <div>

        <a
            class="nav-btn"
            href="index.php"
        >
            🏠 Shop
        </a>

        <a
            class="nav-btn logout-btn"
            href="customer_logout.php"
        >
            🚪 Logout
        </a>

    </div>

</div>


<?php if ($result->num_rows > 0) { ?>


<div class="books">


<?php while ($book = $result->fetch_assoc()) { ?>


<div class="book">


<!-- IMAGE -->

<div class="book-image">

<?php if (!empty($book['image'])) { ?>

<img
    src="<?php
        echo htmlspecialchars(
            $book['image']
        );
    ?>"
    alt="<?php
        echo htmlspecialchars(
            $book['title']
        );
    ?>"
>

<?php } else { ?>

<div class="no-image">
    📖
</div>

<?php } ?>

</div>


<!-- TITLE -->

<h3>

<?php

echo htmlspecialchars(
    $book['title']
);

?>

</h3>


<!-- AUTHOR -->

<?php if (!empty($book['author'])) { ?>

<div class="author">

    ✍️

    <?php

    echo htmlspecialchars(
        $book['author']
    );

    ?>

</div>

<?php } ?>


<!-- CATEGORY -->

<div class="category">

    📂

    <?php

    echo htmlspecialchars(
        $book['category_name']
        ?: 'General'
    );

    ?>

</div>


<!-- PRICE -->

<div class="price">

₹<?php

echo number_format(
    (float)$book['price'],
    2
);

?>

</div>


<!-- STOCK -->

<div class="stock">

<?php

if ((int)$book['stock'] > 0) {

    echo '<span class="in-stock">✓ In Stock</span>';

} else {

    echo '<span class="out-stock">❌ Out of Stock</span>';

}

?>

</div>


<!-- BUTTONS -->

<div class="buttons">


<a
    class="details-btn"
    href="product_details.php?id=<?php
        echo (int)$book['product_id'];
    ?>"
>
    🛒 View Details
</a>


<a
    class="remove-btn"
    href="add_wishlist.php?product_id=<?php
        echo (int)$book['product_id'];
    ?>"
    onclick="return confirm('Remove this book from wishlist?');"
>
    ❌ Remove Wishlist
</a>


</div>


</div>


<?php } ?>


</div>


<?php } else { ?>


<div class="empty">

    <h2>
        ❤️ Your Wishlist is Empty
    </h2>

    <p>
        You haven't added any books to your wishlist yet.
    </p>

    <a
        class="shop-btn"
        href="index.php"
    >
        📚 Browse Books
    </a>

</div>


<?php } ?>


</div>

</body>

</html>