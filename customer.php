<?php
include 'config.php';

$result = $conn->query("SELECT * FROM products");

/* Find the book name column automatically */
$columns = [];

$columnResult = $conn->query("SHOW COLUMNS FROM products");

while ($row = $columnResult->fetch_assoc()) {
    $columns[] = $row['Field'];
}

$nameColumn = null;

foreach ($columns as $column) {

    $lower = strtolower($column);

    if (
        $lower == "name" ||
        $lower == "book_name" ||
        $lower == "bookname" ||
        $lower == "title" ||
        $lower == "book_title"
    ) {
        $nameColumn = $column;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>NetMart Customer Shop</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.header {
    background: #222;
    color: white;
    padding: 20px 40px;
}

.header h1 {
    margin: 0;
}

.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 20px;
}

.top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart {
    background: #28a745;
    color: white;
    padding: 11px 18px;
    text-decoration: none;
    border-radius: 6px;
}

.books {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.book {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 10px #ccc;
}

.book h3 {
    margin-top: 0;
}

.price {
    font-size: 20px;
    font-weight: bold;
    margin: 15px 0;
}

.add {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 6px;
}

.add:hover {
    background: #0056b3;
}

.empty {
    background: white;
    padding: 30px;
    border-radius: 10px;
}

</style>

</head>

<body>

<div class="header">

<h1>📚 NetMart</h1>

<p>Customer Book Shop</p>

</div>

<div class="container">

<div class="top">

<h2>📖 Available Books</h2>

<a class="cart" href="cart.php">
🛒 Cart
</a>

</div>

<div class="books">

<?php

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        if ($nameColumn) {

            $bookName = $row[$nameColumn];

        } else {

            $bookName = "Book";

        }

?>

<div class="book">

<h3>
📖 <?php echo htmlspecialchars($bookName); ?>
</h3>

<div class="price">

₹<?php echo htmlspecialchars($row['price']); ?>

</div>

<a
    class="add"
    href="add_to_cart.php?id=<?php echo $row['id']; ?>"
>
🛒 Add to Cart
</a>

</div>

<?php

    }

} else {

?>

<div class="empty">

<h3>No Books Available</h3>

<p>Please ask the seller to add books.</p>

</div>

<?php } ?>

</div>

</div>

</body>

</html>