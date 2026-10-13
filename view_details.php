<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid Book ID");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM products WHERE id = $id";

$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}

if ($result->num_rows == 0) {
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

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.header {
    background: linear-gradient(135deg, #1769ff, #5b00ff);
    color: white;
    padding: 25px 50px;
}

.header h1 {
    margin: 0;
}

.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
}

.product {
    background: white;
    padding: 30px;
    border-radius: 15px;

    display: flex;
    gap: 40px;
    flex-wrap: wrap;

    box-shadow: 0 4px 15px #ccc;
}

.image-box {
    width: 350px;
    height: 400px;

    background: #f0f2f5;
    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;
}

.image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    font-size: 100px;
}

.details {
    flex: 1;
}

.details h2 {
    font-size: 32px;
    margin-top: 0;
}

.author {
    color: #555;
    font-size: 18px;
    margin: 15px 0;
}

.price {
    color: #20a044;
    font-size: 30px;
    font-weight: bold;
    margin: 20px 0;
}

.stock {
    font-weight: bold;
    margin: 15px 0;
}

.description {
    line-height: 1.7;
    color: #444;
}

.btn {
    display: inline-block;
    padding: 13px 22px;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    margin-top: 20px;
    margin-right: 10px;
}

.cart {
    background: #007bff;
}

.back {
    background: #555;
}

.cart:hover {
    background: #0056b3;
}

.back:hover {
    background: #333;
}

</style>

</head>

<body>

<div class="header">

    <h1>📚 NetMart</h1>

    <p>Online Book Shopping Store</p>

</div>


<div class="container">

    <div class="product">

        <div class="image-box">

            <?php if (!empty($book['image'])) { ?>

                <img
                    src="<?php echo htmlspecialchars($book['image']); ?>"
                    alt="Book"
                >

            <?php } else { ?>

                <div class="no-image">
                    📖
                </div>

            <?php } ?>

        </div>


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


            <div class="price">

                ₹<?php echo number_format($book['price'], 2); ?>

            </div>


            <div class="stock">

                📦 Available Stock:
                <?php echo htmlspecialchars($book['stock']); ?>

            </div>


            <?php if (!empty($book['description'])) { ?>

                <div class="description">

                    <h3>📖 Description</h3>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars($book['description'])
                        );
                        ?>
                    </p>

                </div>

            <?php } ?>


            <?php if ($book['stock'] > 0) { ?>

                <a
                    class="btn cart"
                    href="add_to_cart.php?id=<?php echo $book['id']; ?>"
                >
                    🛒 Add to Cart
                </a>

            <?php } else { ?>

                <p style="color:red;font-weight:bold;">
                    ❌ Out of Stock
                </p>

            <?php } ?>


            <a
                class="btn back"
                href="index.php"
            >
                ← Back to Books
            </a>

        </div>

    </div>

</div>

</body>

</html>