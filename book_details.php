<?php
include 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Book ID");
}

$id = intval($_GET['id']);

$sql = "SELECT products.*, categories.name AS category_name
        FROM products
        LEFT JOIN categories
        ON products.category_id = categories.id
        WHERE products.id = $id
        LIMIT 1";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Book not found");
}

$book = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($book['title']); ?> - NetMart
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

/* HEADER */

.header {
    background: linear-gradient(135deg, #1769ff, #5b00ff);
    color: white;
    padding: 25px 40px;
}

.header h1 {
    margin: 0;
}

/* CONTAINER */

.container {
    max-width: 1000px;
    margin: 50px auto;
    padding: 20px;
}

/* PRODUCT */

.product {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px #ccc;

    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 40px;
}

/* IMAGE */

.book-image {
    width: 100%;
    height: 450px;

    background: #eef0f3;
    border-radius: 10px;

    display: flex;
    justify-content: center;
    align-items: center;

    overflow: hidden;
}

.book-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    font-size: 100px;
}

/* DETAILS */

.details h2 {
    margin-top: 0;
    font-size: 32px;
}

.author {
    color: #666;
    font-size: 18px;
    margin: 15px 0;
}

.category {
    color: #777;
    margin: 15px 0;
}

.price {
    color: #20a044;
    font-size: 30px;
    font-weight: bold;
    margin: 20px 0;
}

.stock {
    margin: 15px 0;
    font-weight: bold;
}

.description {
    line-height: 1.7;
    color: #444;
    margin-top: 25px;
}

/* BUTTONS */

.buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 13px 20px;
    border-radius: 7px;
    color: white;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.cart {
    background: #28a745;
}

.shop {
    background: #007bff;
}

.cart:hover {
    background: #218838;
}

.shop:hover {
    background: #0056b3;
}

/* MOBILE */

@media (max-width: 750px) {

    .product {
        grid-template-columns: 1fr;
    }

    .book-image {
        height: 350px;
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

    <div class="product">

        <!-- BOOK IMAGE -->

        <div class="book-image">

            <?php if (!empty($book['image'])) { ?>

                <img
                    src="<?php echo htmlspecialchars($book['image']); ?>"
                    alt="<?php echo htmlspecialchars($book['title']); ?>"
                >

            <?php } else { ?>

                <div class="no-image">
                    📖
                </div>

            <?php } ?>

        </div>


        <!-- BOOK DETAILS -->

        <div class="details">

            <h2>
                <?php echo htmlspecialchars($book['title']); ?>
            </h2>


            <?php if (!empty($book['author'])) { ?>

                <div class="author">

                    ✍️ Author:
                    <?php echo htmlspecialchars($book['author']); ?>

                </div>

            <?php } ?>


            <div class="category">

                📂 Category:

                <?php

                if (!empty($book['category_name'])) {

                    echo htmlspecialchars($book['category_name']);

                } else {

                    echo "General";

                }

                ?>

            </div>


            <div class="price">

                ₹<?php echo number_format($book['price'], 2); ?>

            </div>


            <div class="stock">

                📦 Stock:

                <?php echo intval($book['stock']); ?>

            </div>


            <?php if (!empty($book['description'])) { ?>

                <div class="description">

                    <h3>About this Book</h3>

                    <?php
                    echo nl2br(
                        htmlspecialchars($book['description'])
                    );
                    ?>

                </div>

            <?php } ?>


            <div class="buttons">

                <?php if (intval($book['stock']) > 0) { ?>

                    <a
                        class="btn cart"
                        href="add_to_cart.php?id=<?php echo $book['id']; ?>"
                    >
                        🛒 Add to Cart
                    </a>

                <?php } else { ?>

                    <button
                        class="btn"
                        style="background:#dc3545;"
                        disabled
                    >
                        Out of Stock
                    </button>

                <?php } ?>


                <a
                    class="btn shop"
                    href="index.php"
                >
                    ← Continue Shopping
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>