<?php

session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CUSTOMER WISHLIST
|--------------------------------------------------------------------------
| Login is not required.
| Wishlist is stored in session.
| Duplicate books are not added.
*/

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}


/* =========================================================
   ADD TO WISHLIST
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['wishlist_action']) &&
    $_POST['wishlist_action'] === 'add'
) {

    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id > 0) {

        /*
         * check whether the book exists
         */
        $check_stmt = $conn->prepare(
            "SELECT id FROM products WHERE id = ? LIMIT 1"
        );

        if ($check_stmt) {

            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {

                /*
                 * Duplicate protection
                 */
                if (!in_array($product_id, $_SESSION['wishlist'])) {

                    $_SESSION['wishlist'][] = $product_id;
                }
            }

            $check_stmt->close();
        }
    }

    /*
     * Search / category values preserve
     */
    $redirect_url = 'index.php';

    $params = [];

    if (!empty($_POST['return_search'])) {
        $params['search'] = $_POST['return_search'];
    }

    if (!empty($_POST['return_category'])) {
        $params['category'] = intval($_POST['return_category']);
    }

    if (!empty($params)) {
        $redirect_url .= '?' . http_build_query($params);
    }

    header("Location: " . $redirect_url);
    exit();
}


/* =========================================================
   REMOVE FROM WISHLIST
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['wishlist_action']) &&
    $_POST['wishlist_action'] === 'remove'
) {

    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id > 0) {

        $_SESSION['wishlist'] = array_values(
            array_filter(
                $_SESSION['wishlist'],
                function ($id) use ($product_id) {
                    return intval($id) !== $product_id;
                }
            )
        );
    }

    /*
     * Search / category preserve
     */
    $redirect_url = 'index.php';

    $params = [];

    if (!empty($_POST['return_search'])) {
        $params['search'] = $_POST['return_search'];
    }

    if (!empty($_POST['return_category'])) {
        $params['category'] = intval($_POST['return_category']);
    }

    if (!empty($params)) {
        $redirect_url .= '?' . http_build_query($params);
    }

    header("Location: " . $redirect_url);
    exit();
}


/* =========================================================
   CLEAN OLD / DELETED PRODUCT IDS FROM WISHLIST
========================================================= */

if (!empty($_SESSION['wishlist'])) {

    $clean_wishlist = [];

    foreach ($_SESSION['wishlist'] as $wishlist_id) {

        $wishlist_id = intval($wishlist_id);

        if ($wishlist_id <= 0) {
            continue;
        }

        $stmt = $conn->prepare(
            "SELECT id FROM products WHERE id = ? LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("i", $wishlist_id);
            $stmt->execute();

            $check = $stmt->get_result();

            if ($check->num_rows > 0) {

                if (!in_array($wishlist_id, $clean_wishlist)) {
                    $clean_wishlist[] = $wishlist_id;
                }
            }

            $stmt->close();
        }
    }

    $_SESSION['wishlist'] = $clean_wishlist;
}


/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


/* =========================================================
   CATEGORY
========================================================= */

$category = isset($_GET['category'])
    ? intval($_GET['category'])
    : 0;


/* =========================================================
   CATEGORIES
========================================================= */

$catResult = $conn->query(
    "SELECT id, category_name
     FROM categories
     ORDER BY id DESC"
);


/* =========================================================
   PRODUCTS
========================================================= */

$sql = "
    SELECT
        products.*,
        categories.category_name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    WHERE 1=1
";


/* SEARCH FILTER */

if ($search !== '') {

    $safeSearch = $conn->real_escape_string($search);

    $sql .= "
        AND (
            products.title LIKE '%$safeSearch%'
            OR products.author LIKE '%$safeSearch%'
        )
    ";
}


/* CATEGORY FILTER */

if ($category > 0) {

    $sql .= "
        AND products.category_id = $category
    ";
}


/* SORT */

$sql .= "
    ORDER BY products.id DESC
";


$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>NetMart - Online Book Store</title>


<style>

.menu-button {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1001;
    background: #1769ff;
    color: white;
    border: none;
    padding: 12px 16px;
    font-size: 22px;
    cursor: pointer;
    border-radius: 6px;
}
.side-menu {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    height: 100%;
    background: white;
    padding: 75px 15px 20px;
    box-shadow: 2px 0 10px #aaa;
    z-index: 1000;
}
.side-menu a {
    display: block;
    padding: 15px;
    margin-bottom: 8px;
    color: #222;
    text-decoration: none;
    border-radius: 6px;
}
.side-menu a:hover {
    background: #e8efff;
}

/* =========================================================
   GENERAL
========================================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}


/* =========================================================
   HEADER
========================================================= */

.header {

    background:
        linear-gradient(
            135deg,
            #1769ff,
            #5b00ff
        );

    color: white;

    padding: 35px 20px;

    text-align: center;

}

.header h1 {

    font-size: 42px;

    margin: 0 0 10px;

}

.header p {

    font-size: 18px;

}


/* =========================================================
   TOP NAV
========================================================= */

.top-nav {

    max-width: 1200px;

    margin: 20px auto 0;

    padding: 0 20px;

    display: flex;

    justify-content: flex-end;

}

.wishlist-link {

    display: inline-block;

    background: #e91e63;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 7px;

    font-weight: bold;

}

.wishlist-link:hover {

    background: #c2185b;

}


/* =========================================================
   SEARCH
========================================================= */

.search-box {

    max-width: 700px;

    margin: 25px auto 0;

    display: flex;

}

.search-box input {

    flex: 1;

    padding: 15px;

    border: none;

    border-radius: 8px 0 0 8px;

    font-size: 16px;

}

.search-box button {

    padding: 15px 25px;

    border: none;

    background: #20a044;

    color: white;

    font-size: 16px;

    border-radius: 0 8px 8px 0;

    cursor: pointer;

}

.search-box button:hover {

    background: #168637;

}


/* =========================================================
   CONTAINER
========================================================= */

.container {

    max-width: 1200px;

    margin: 30px auto;

    padding: 20px;

}


/* =========================================================
   CATEGORY
========================================================= */

.category-box {

    background: white;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 30px;

    box-shadow: 0 3px 10px #ddd;

}

.category-box form {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}

.category-box select {

    padding: 12px;

    min-width: 220px;

    border: 1px solid #ccc;

    border-radius: 6px;

}

.category-box button {

    padding: 12px 20px;

    background: #007bff;

    color: white;

    border: none;

    border-radius: 6px;

    cursor: pointer;

}

.category-box button:hover {

    background: #0056b3;

}


/* =========================================================
   TITLE
========================================================= */

.title {

    text-align: center;

    margin-bottom: 25px;

}


/* =========================================================
   BOOK GRID
========================================================= */

.books {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(240px, 1fr)
        );

    gap: 25px;

}


/* =========================================================
   BOOK CARD
========================================================= */

.book {

    background: white;

    border-radius: 12px;

    padding: 18px;

    box-shadow: 0 3px 12px #ccc;

    transition: 0.2s;

}

.book:hover {

    transform: translateY(-4px);

}


/* =========================================================
   IMAGE
========================================================= */

.book-image {

    height: 220px;

    background: #eef0f3;

    border-radius: 8px;

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

    font-size: 70px;

}


/* =========================================================
   BOOK TEXT
========================================================= */

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


/* =========================================================
   BUTTON AREA
========================================================= */

.button-area {

    display: flex;

    flex-direction: column;

    gap: 8px;

    margin-top: 15px;

}


/* =========================================================
   VIEW DETAILS
========================================================= */

.details-btn {

    display: block;

    text-align: center;

    background: #007bff;

    color: white;

    padding: 12px;

    border-radius: 7px;

    text-decoration: none;

}

.details-btn:hover {

    background: #0056b3;

}


/* =========================================================
   WISHLIST BUTTON
========================================================= */

.wishlist-btn {

    width: 100%;

    padding: 12px;

    border: none;

    border-radius: 7px;

    cursor: pointer;

    font-size: 15px;

    font-weight: bold;

}


/* ADD */

.add-wishlist {

    background: #e91e63;

    color: white;

}

.add-wishlist:hover {

    background: #c2185b;

}


/* REMOVE */

.remove-wishlist {

    background: #555;

    color: white;

}

.remove-wishlist:hover {

    background: #333;

}


/* ALREADY WISHLISTED */

.already-wishlist {

    background: #eee;

    color: #e91e63;

    cursor: default;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    background: white;

    padding: 40px;

    text-align: center;

    border-radius: 10px;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .header h1 {

        font-size: 30px;

    }

    .container {

        padding: 10px;

    }

    .top-nav {

        padding: 0 10px;

    }

    .search-box {

        width: 100%;

    }

}

</style>

</head>


<body>

<button class="menu-button" onclick="toggleMenu()">☰</button>

<div class="side-menu" id="sideMenu">
    <a href="admin_seller_login.php?role=admin">Admin</a>
<a href="admin_seller_login.php?role=seller">Seller</a>
    <a href="index.php">Customer</a>
    <a href="logout.php">Logout</a>
</div>

<script>
function toggleMenu() {
    const menu = document.getElementById("sideMenu");
    menu.style.display =
        menu.style.display === "block" ? "none" : "block";
}
</script>


<!-- =======================================================
     HEADER
======================================================= -->

<div class="header">

    <h1>
        📚 Welcome to NetMart
    </h1>

    <p>
        Your Online Book Shopping Store
    </p>


    <!-- SEARCH -->

    <form
        class="search-box"
        method="GET"
    >

        <input
            type="text"
            name="search"
            placeholder="Search books or authors..."
            value="<?php
                echo htmlspecialchars($search);
            ?>"
        >

        <button type="submit">

            🔍 Search

        </button>

    </form>

</div>


<!-- =======================================================
     WISHLIST LINK
======================================================= -->

<div class="top-nav">

    <a
        class="wishlist-link"
        href="wishlist.php"
    >

        ❤️ My Wishlist

        (<?php
            echo count($_SESSION['wishlist']);
        ?>)

    </a>

</div>


<div class="container">


<!-- =======================================================
     CATEGORY FILTER
======================================================= -->

<div class="category-box">

    <h3>
        📂 Browse by Category
    </h3>


    <form method="GET">


        <input
            type="hidden"
            name="search"
            value="<?php
                echo htmlspecialchars($search);
            ?>"
        >


        <select name="category">

            <option value="0">

                All Categories

            </option>


            <?php

            if (
                $catResult &&
                $catResult->num_rows > 0
            ) {

                while (
                    $cat = $catResult->fetch_assoc()
                ) {

            ?>

                <option
                    value="<?php
                        echo intval($cat['id']);
                    ?>"
                    <?php

                    if (
                        $category ==
                        intval($cat['id'])
                    ) {

                        echo "selected";

                    }

                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $cat['category_name']
                    );
                    ?>

                </option>

            <?php

                }

            }

            ?>

        </select>


        <button type="submit">

            Filter

        </button>


    </form>

</div>


<!-- =======================================================
     TITLE
======================================================= -->

<div class="title">

    <h2>
        📚 All Books
    </h2>

    <p>
        Explore our available books
    </p>

</div>


<!-- =======================================================
     BOOKS
======================================================= -->

<div class="books">


<?php

if (
    $result &&
    $result->num_rows > 0
) {


    while (
        $book = $result->fetch_assoc()
    ) {


        $book_id = intval(
            $book['id']
        );


        /*
         * Check wishlist
         */

        $is_wishlisted =
            in_array(
                $book_id,
                $_SESSION['wishlist']
            );

?>



<div class="book">


    <!-- =================================================
         IMAGE
    ================================================= -->

    <div class="book-image">

        <?php

        if (
            !empty($book['image'])
        ) {

        ?>

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

        <?php

        } else {

        ?>

            <div class="no-image">

                📖

            </div>

        <?php

        }

        ?>

    </div>


    <!-- =================================================
         TITLE
    ================================================= -->

    <h3>

        <?php

        echo htmlspecialchars(
            $book['title']
        );

        ?>

    </h3>


    <!-- =================================================
         AUTHOR
    ================================================= -->

    <?php

    if (
        !empty($book['author'])
    ) {

    ?>

        <div class="author">

            ✍️

            <?php

            echo htmlspecialchars(
                $book['author']
            );

            ?>

        </div>

    <?php

    }

    ?>


    <!-- =================================================
         CATEGORY
    ================================================= -->

    <div class="category">

        📂

        <?php

        if (
            !empty(
                $book['category_name']
            )
        ) {

            echo htmlspecialchars(
                $book['category_name']
            );

        } else {

            echo "General";

        }

        ?>

    </div>


    <!-- =================================================
         PRICE
    ================================================= -->

    <div class="price">

        ₹<?php

        echo number_format(
            (float)$book['price'],
            2
        );

        ?>

    </div>


    <!-- =================================================
         BUTTONS
    ================================================= -->

    <div class="button-area">


        <!-- VIEW DETAILS -->

        <a
            class="details-btn"
            href="product_details.php?id=<?php
                echo $book_id;
            ?>"
        >

            🛒 View Details →

        </a>


        <?php

        if ($is_wishlisted) {

        ?>

            <!-- ALREADY WISHLISTED -->

            <div
                class="wishlist-btn already-wishlist"
            >

                ❤️ Already Wishlisted

            </div>


            <!-- REMOVE -->

            <form
                method="POST"
                style="margin:0;"
            >

                <input
                    type="hidden"
                    name="wishlist_action"
                    value="remove"
                >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?php
                        echo $book_id;
                    ?>"
                >

                <input
                    type="hidden"
                    name="return_search"
                    value="<?php
                        echo htmlspecialchars(
                            $search
                        );
                    ?>"
                >

                <input
                    type="hidden"
                    name="return_category"
                    value="<?php
                        echo $category;
                    ?>"
                >

                <button
                    type="submit"
                    class="wishlist-btn remove-wishlist"
                >

                    ❌ Remove Wishlist

                </button>

            </form>


        <?php

        } else {

        ?>


            <!-- ADD WISHLIST -->

            <form
                method="POST"
                style="margin:0;"
            >

                <input
                    type="hidden"
                    name="wishlist_action"
                    value="add"
                >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?php
                        echo $book_id;
                    ?>"
                >

                <input
                    type="hidden"
                    name="return_search"
                    value="<?php
                        echo htmlspecialchars(
                            $search
                        );
                    ?>"
                >

                <input
                    type="hidden"
                    name="return_category"
                    value="<?php
                        echo $category;
                    ?>"
                >

                <button
                    type="submit"
                    class="wishlist-btn add-wishlist"
                >

                    ❤️ Add Wishlist

                </button>

            </form>


        <?php

        }

        ?>


    </div>


</div>


<?php

    }

} else {

?>


    <div class="empty">

        <h3>
            😔 No Books Found
        </h3>

        <p>
            Try another book name or category.
        </p>

    </div>


<?php

}

?>


</div>


</div>


</body>

</html>