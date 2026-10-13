<?php

session_start();
include 'config.php';


/* =========================
   SELLER LOGIN CHECK
========================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {

    header("Location: login.php");
    exit();

}


/* =========================
   GET SELLER ID
========================= */

if (isset($_SESSION['seller_id'])) {

    $seller_id = intval($_SESSION['seller_id']);

} elseif (isset($_SESSION['user_id'])) {

    $seller_id = intval($_SESSION['user_id']);

} else {

    die("Seller ID not found. Please login again.");

}


/* =========================
   GET SELLER BOOKS
========================= */

$sql = "
    SELECT
        id,
        title,
        author,
        description,
        price,
        stock,
        image,
        created_at
    FROM products
    WHERE seller_id = ?
    ORDER BY id DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Query failed: " .
        htmlspecialchars($conn->error)
    );

}

$stmt->bind_param("i", $seller_id);

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

<title>NetMart - My Books</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}


/* =========================
   HEADER
========================= */

.header {

    background: #222;

    color: white;

    padding: 22px 40px;

}

.header h1 {

    margin: 0;

}

.header p {

    margin-bottom: 0;

}


/* =========================
   CONTAINER
========================= */

.container {

    max-width: 1200px;

    margin: 40px auto;

    padding: 20px;

}


.box {

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}


/* =========================
   TABLE
========================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;

    min-width: 800px;

}


th {

    background: #333;

    color: white;

    padding: 14px;

    text-align: center;

}


td {

    padding: 14px;

    text-align: center;

    border-bottom: 1px solid #ddd;

}


tr:hover {

    background: #f8f8f8;

}


/* =========================
   IMAGE
========================= */

.book-image {

    width: 70px;

    height: 90px;

    object-fit: cover;

    border-radius: 6px;

    border: 1px solid #ddd;

}


.no-image {

    width: 70px;

    height: 90px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eee;

    border-radius: 6px;

    font-size: 12px;

    color: #777;

}


/* =========================
   STOCK
========================= */

.stock {

    font-weight: bold;

}


.in-stock {

    color: green;

}


.out-stock {

    color: red;

}


/* =========================
   DELETE BUTTON
========================= */

.delete-btn {

    display: inline-block;

    background: #dc3545;

    color: white;

    padding: 9px 14px;

    text-decoration: none;

    border-radius: 6px;

    font-weight: bold;

}


.delete-btn:hover {

    background: #c82333;

}


/* =========================
   BACK BUTTON
========================= */

.back-btn {

    display: inline-block;

    margin-top: 25px;

    padding: 11px 18px;

    background: #28a745;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}


.back-btn:hover {

    background: #218838;

}


/* =========================
   EMPTY
========================= */

.empty {

    text-align: center;

    padding: 60px 20px;

}


.empty h3 {

    font-size: 24px;

}


/* =========================
   MOBILE
========================= */

@media(max-width:700px) {

    .container {

        margin: 20px auto;

        padding: 10px;

    }

    .box {

        padding: 18px;

    }

}

</style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<div class="header">

    <h1>📚 NetMart</h1>

    <p>Seller - My Books</p>

</div>


<div class="container">

<div class="box">


<h2>
    📚 My Books
</h2>


<?php if ($result->num_rows > 0) { ?>


<div class="table-wrapper">

<table>


<thead>

<tr>

    <th>Image</th>

    <th>Book</th>

    <th>Author</th>

    <th>Price</th>

    <th>Stock</th>

    <th>Action</th>

</tr>

</thead>


<tbody>


<?php while ($book = $result->fetch_assoc()) { ?>


<tr>


<!-- IMAGE -->

<td>

<?php

if (!empty($book['image'])) {

?>

<img
    class="book-image"
    src="<?php echo htmlspecialchars($book['image']); ?>"
    alt="Book"
>

<?php

} else {

?>

<div class="no-image">
    No Image
</div>

<?php

}

?>

</td>


<!-- TITLE -->

<td>

<strong>

<?php

echo htmlspecialchars(
    $book['title']
);

?>

</strong>

</td>


<!-- AUTHOR -->

<td>

<?php

echo htmlspecialchars(
    $book['author'] ?? 'Unknown'
);

?>

</td>


<!-- PRICE -->

<td>

₹<?php

echo number_format(
    (float)$book['price'],
    2
);

?>

</td>


<!-- STOCK -->

<td>

<?php

$stock = intval(
    $book['stock']
);

if ($stock > 0) {

?>

<span class="stock in-stock">

    <?php echo $stock; ?>

</span>

<?php

} else {

?>

<span class="stock out-stock">

    Out of Stock

</span>

<?php

}

?>

</td>


<!-- DELETE -->

<td>

<a
    class="delete-btn"
    href="delete_book.php?id=<?php echo intval($book['id']); ?>"
    onclick="return confirm('Are you sure you want to delete this book?');"
>
    🗑️ Delete
</a>

</td>


</tr>


<?php } ?>


</tbody>

</table>

</div>


<?php } else { ?>


<div class="empty">

    <h3>
        📚 No Books Found
    </h3>

    <p>
        You have not added any books yet.
    </p>

</div>


<?php } ?>


<a
    href="seller_dashboard.php"
    class="back-btn"
>
    ← Seller Dashboard
</a>


</div>

</div>


</body>

</html>