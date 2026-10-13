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

$product_id = intval($_GET['product_id'] ?? 0);


/* =========================
   PRODUCT ID CHECK
========================= */

if ($product_id <= 0) {

    header("Location: index.php");
    exit();

}


/* =========================
   CHECK PRODUCT EXISTS
========================= */

$stmt = $conn->prepare(
    "SELECT id FROM products WHERE id = ? LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit();

}

$stmt->close();


/* =========================
   CHECK WISHLIST
========================= */

$stmt = $conn->prepare(
    "SELECT id
     FROM wishlist
     WHERE user_id = ?
     AND product_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param(
    "ii",
    $user_id,
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();

$already = ($result->num_rows > 0);

$stmt->close();


/* =========================
   REMOVE
========================= */

if ($already) {

    $stmt = $conn->prepare(
        "DELETE FROM wishlist
         WHERE user_id = ?
         AND product_id = ?"
    );

    if (!$stmt) {
        die("Database error: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param(
        "ii",
        $user_id,
        $product_id
    );

    $stmt->execute();

    $stmt->close();

}


/* =========================
   ADD
========================= */

else {

    $stmt = $conn->prepare(
        "INSERT INTO wishlist
        (user_id, product_id)
        VALUES (?, ?)"
    );

    if (!$stmt) {
        die("Database error: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param(
        "ii",
        $user_id,
        $product_id
    );

    $stmt->execute();

    $stmt->close();

}


/* =========================
   BACK
========================= */

$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';

if (
    strpos($back, 'http://') === 0 ||
    strpos($back, 'https://') === 0
) {
    $back = 'index.php';
}

header("Location: " . $back);
exit();

?>