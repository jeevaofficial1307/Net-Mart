<?php

session_start();
include 'config.php';


/* LOGIN CHECK */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


$user_id = intval($_SESSION['user_id']);


/* GET WISHLIST */

$sql = "
    SELECT
        products.id,
        products.title,
        products.author,
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

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

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

.header {

    background: linear-gradient(
        135deg,
        #1769ff,
        #5b00ff
    );

    color: white;

    padding: 30px 20px;

    text-align: center;

}

.container {

    max-width: 1200px;

    margin: 40px auto;

    padding: 20px;

}

.books {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(240px, 1fr)
        );

    gap: 25px;

}

.book {

    background: white;

    padding: 18px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}

.book-image {

    height: 220px;

    background: #eee;

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

.author {

    color: #666;

    margin: 8px 0;

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

.btn {

    display: block;

    text-align: center;

    padding: 11px;

    border-radius: 7px;

    text-decoration: none;

    margin-top: 10px;

    color: white;

}

.view {

    background: #007bff;

}

.remove {

    background: #dc3545;

}

.back {

    display: inline-block;

    margin-top: 30px;

    padding: 12px 18px;

    background: #333;

    color: white;

    text-decoration: none;

    border-radius: 7px;

}

.empty {

    background: white;

    padding: 50px;

    text-align: center;

    border-radius: 10px;

}

</style>

</head>

<body>


<div class="header">

    <h1>❤️ My Wishlist</h1>

    <p>Your favourite books</p>

</div>


<div class="container">


<?php if ($result->num_rows > 0) { ?>


<div class="books">


<?php while ($book = $result->fetch_assoc()) { ?>


<div class="book">


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


<h3>

<?php

echo htmlspecialchars(
    $book['title']
);

?>

</h3>


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


<div class="category">

📂

<?php

echo htmlspecialchars(
    $book['category_name'] ?? 'General'
);

?>

</div>


<div class="price">

₹<?php

echo number_format(
    (float)$book['price'],
    2
);

?>

</div>


<a
    class="btn view"
    href="product_details.php?id=<?php
        echo (int)$book['id'];
    ?>"
>

🛒 View Details

</a>


<a
    class="btn remove"
    href="remove_from_wishlist.php?product_id=<?php
        echo (int)$book['id'];
    ?>"
    onclick="return confirm(
        'Remove this book from wishlist?'
    );"
>

❌ Remove Wishlist

</a>


</div>


<?php } ?>


</div>


<?php } else { ?>


<div class="empty">

    <h2>
        ❤️ Wishlist is Empty
    </h2>

    <p>
        You have not added any books yet.
    </p>

</div>


<?php } ?>


<a
    href="index.php"
    class="back"
>

← Continue Shopping

</a>


</div>

</body>

</html>