<?php

session_start();
include 'config.php';

/* =========================
   SELLER LOGIN CHECK
========================= */

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'seller'
) {
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
   GET BOOK ID
========================= */

$book_id = intval($_GET['id'] ?? 0);

if ($book_id <= 0) {

    die("Invalid book ID.");

}


/* =========================
   CHECK BOOK BELONGS TO SELLER
========================= */

$sql = "SELECT id, image
        FROM products
        WHERE id = ?
        AND seller_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . htmlspecialchars($conn->error));

}

$stmt->bind_param(
    "ii",
    $book_id,
    $seller_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    die("Book not found or you are not authorized to delete this book.");

}

$book = $result->fetch_assoc();

$stmt->close();


/* =========================
   START TRANSACTION
========================= */

$conn->begin_transaction();

try {

    /* =========================
       DELETE FROM CART
    ========================= */

    $stmt = $conn->prepare(
        "DELETE FROM cart WHERE product_id = ?"
    );

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "i",
        $book_id
    );

    $stmt->execute();

    $stmt->close();


    /* =========================
       CHECK ORDER ITEMS
    ========================= */

    /*
       If this book was already ordered,
       products cannot be deleted directly
       because order_items references it.

       So first remove those order_items.
    */

    $stmt = $conn->prepare(
        "DELETE FROM order_items WHERE product_id = ?"
    );

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "i",
        $book_id
    );

    $stmt->execute();

    $stmt->close();


    /* =========================
       DELETE PRODUCT
    ========================= */

    $stmt = $conn->prepare(
        "DELETE FROM products
         WHERE id = ?
         AND seller_id = ?"
    );

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "ii",
        $book_id,
        $seller_id
    );

    if (!$stmt->execute()) {

        throw new Exception($stmt->error);

    }

    $stmt->close();


    /* =========================
       DELETE IMAGE FILE
    ========================= */

    if (!empty($book['image'])) {

        $image_path = $book['image'];

        /*
           Convert database path into
           physical server path.
        */

        $full_path = __DIR__ . DIRECTORY_SEPARATOR . $image_path;

        if (
            file_exists($full_path) &&
            is_file($full_path)
        ) {

            unlink($full_path);

        }

    }


    /* =========================
       COMMIT
    ========================= */

    $conn->commit();


    /* =========================
       BACK TO BOOK LIST
    ========================= */

    header("Location: view_books.php?deleted=1");
    exit();


} catch (Exception $e) {

    /* =========================
       ROLLBACK
    ========================= */

    $conn->rollback();

    die(
        "Book delete failed: " .
        htmlspecialchars($e->getMessage())
    );

}

?>