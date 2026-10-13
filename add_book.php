<?php

session_start();
include 'config.php';

$message = "";


/* =========================
   SELLER LOGIN CHECK
========================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {

    header("Location: login.php");
    exit();

}


/* =========================
   SELLER ID
========================= */

if (isset($_SESSION['seller_id'])) {

    $seller_id = intval($_SESSION['seller_id']);

} elseif (isset($_SESSION['user_id'])) {

    $seller_id = intval($_SESSION['user_id']);

} else {

    die("Seller ID not found. Please login again.");

}


/* =========================
   ADD BOOK
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title'] ?? '');

    $author = trim($_POST['author'] ?? '');

    $description = trim($_POST['description'] ?? '');

    $price = floatval($_POST['price'] ?? 0);

    $stock = intval($_POST['stock'] ?? 0);

    $category_id = intval($_POST['category_id'] ?? 0);

    $image_path = "";


    /* =========================
       VALIDATION
    ========================= */

    if ($title === "") {

        $message = "Please enter book title.";

    } elseif ($price <= 0) {

        $message = "Please enter a valid price.";

    } elseif ($stock < 0) {

        $message = "Stock cannot be negative.";

    } elseif ($category_id <= 0) {

        $message = "Please select a category.";

    } else {


        /* =========================
           CHECK CATEGORY
        ========================= */

        $category_check = $conn->prepare(
            "SELECT id FROM categories WHERE id = ?"
        );

        if (!$category_check) {

            $message = "Category query failed: " . $conn->error;

        } else {

            $category_check->bind_param(
                "i",
                $category_id
            );

            $category_check->execute();

            $category_result =
                $category_check->get_result();

            if ($category_result->num_rows === 0) {

                $message = "Selected category does not exist.";

            }

            $category_check->close();

        }


        /* =========================
           IMAGE UPLOAD
        ========================= */

        if ($message === "") {

            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if (
                    $_FILES['image']['error'] !==
                    UPLOAD_ERR_OK
                ) {

                    $message = "Image upload failed.";

                } else {

                    $file_name =
                        $_FILES['image']['name'];

                    $tmp_name =
                        $_FILES['image']['tmp_name'];

                    $file_size =
                        $_FILES['image']['size'];

                    $extension = strtolower(
                        pathinfo(
                            $file_name,
                            PATHINFO_EXTENSION
                        )
                    );


                    /* Allowed image types */

                    $allowed = [
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'webp'
                    ];


                    if (
                        !in_array(
                            $extension,
                            $allowed,
                            true
                        )
                    ) {

                        $message =
                            "Only JPG, JPEG, PNG, GIF and WEBP images are allowed.";

                    } elseif (
                        $file_size > 5 * 1024 * 1024
                    ) {

                        $message =
                            "Image size must be less than 5 MB.";

                    } else {


                        /* =========================
                           CHECK REAL IMAGE
                        ========================= */

                        $image_info =
                            getimagesize($tmp_name);

                        if ($image_info === false) {

                            $message =
                                "Please upload a valid image.";

                        } else {


                            /* =========================
                               CREATE UPLOAD FOLDER
                            ========================= */

                            $upload_dir = "uploads/";

                            if (!is_dir($upload_dir)) {

                                mkdir(
                                    $upload_dir,
                                    0777,
                                    true
                                );

                            }


                            /* =========================
                               UNIQUE FILE NAME
                            ========================= */

                            $new_name =
                                "book_" .
                                time() .
                                "_" .
                                uniqid() .
                                "." .
                                $extension;


                            $destination =
                                $upload_dir .
                                $new_name;


                            if (
                                move_uploaded_file(
                                    $tmp_name,
                                    $destination
                                )
                            ) {

                                $image_path =
                                    $destination;

                            } else {

                                $message =
                                    "Image upload failed.";

                            }

                        }

                    }

                }

            }


            /* =========================
               INSERT BOOK
            ========================= */

            if ($message === "") {

                $sql = "INSERT INTO products
                        (
                            seller_id,
                            category_id,
                            title,
                            author,
                            description,
                            price,
                            stock,
                            image
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


                $stmt = $conn->prepare($sql);


                if (!$stmt) {

                    $message =
                        "Database error: " .
                        $conn->error;

                } else {

                    $stmt->bind_param(
                        "iisssdis",
                        $seller_id,
                        $category_id,
                        $title,
                        $author,
                        $description,
                        $price,
                        $stock,
                        $image_path
                    );


                    if ($stmt->execute()) {

                        $message =
                            "Book added successfully!";

                    } else {

                        $message =
                            "Error: " .
                            $stmt->error;

                    }


                    $stmt->close();

                }

            }

        }

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

<title>NetMart - Add Book</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;

}


.box {

    width: 500px;

    max-width: 92%;

    margin: 40px auto;

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 12px #ccc;

}


h2 {

    text-align: center;

    margin-top: 0;

    margin-bottom: 25px;

}


label {

    display: block;

    font-weight: bold;

    margin-top: 15px;

}


input,
textarea,
select {

    width: 100%;

    padding: 11px;

    margin-top: 7px;

    box-sizing: border-box;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 15px;

}


textarea {

    min-height: 100px;

    resize: vertical;

}


input:focus,
textarea:focus,
select:focus {

    outline: none;

    border-color: #007bff;

}


button {

    width: 100%;

    padding: 13px;

    margin-top: 25px;

    background: #007bff;

    color: white;

    border: none;

    border-radius: 6px;

    cursor: pointer;

    font-size: 16px;

    font-weight: bold;

}


button:hover {

    background: #0056b3;

}


.message {

    margin-top: 20px;

    padding: 12px;

    text-align: center;

    font-weight: bold;

    background: #e8f5e9;

    color: #155724;

    border-radius: 6px;

}


.back {

    display: block;

    margin-top: 20px;

    text-align: center;

    text-decoration: none;

    color: #007bff;

}


.image-note {

    font-size: 13px;

    color: #777;

    margin-top: 6px;

}


.required {

    color: red;

}


</style>

</head>


<body>


<div class="box">


<h2>
    📚 Add New Book
</h2>


<form
    method="POST"
    enctype="multipart/form-data"
>


<!-- BOOK TITLE -->

<label>

    Book Title
    <span class="required">*</span>

</label>

<input
    type="text"
    name="title"
    placeholder="Example: C Programming"
    required
>


<!-- AUTHOR -->

<label>

    Author

</label>

<input
    type="text"
    name="author"
    placeholder="Example: Dennis Ritchie"
>


<!-- DESCRIPTION -->

<label>

    Description

</label>

<textarea
    name="description"
    placeholder="Enter book description"
></textarea>


<!-- PRICE -->

<label>

    Price
    <span class="required">*</span>

</label>

<input
    type="number"
    name="price"
    placeholder="Example: 450"
    min="1"
    step="0.01"
    required
>


<!-- STOCK -->

<label>

    Stock
    <span class="required">*</span>

</label>

<input
    type="number"
    name="stock"
    placeholder="Example: 10"
    min="0"
    value="1"
    required
>


<!-- CATEGORY -->

<label>

    Category
    <span class="required">*</span>

</label>


<select
    name="category_id"
    required
>


    <option value="">

        -- Select Category --

    </option>


    <?php

    $catResult = $conn->query(
        "SELECT id, name
         FROM categories
         ORDER BY name ASC"
    );


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
        >

            <?php

            echo htmlspecialchars(
                $cat['name']
            );

            ?>

        </option>


    <?php

        }

    } else {

    ?>

        <option value="">

            No categories available

        </option>

    <?php

    }

    ?>

</select>


<!-- IMAGE -->

<label>

    Book Image

</label>


<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.gif,.webp,image/*"
>


<div class="image-note">

    JPG, JPEG, PNG, GIF or WEBP —
    Maximum 5 MB

</div>


<!-- SUBMIT -->

<button type="submit">

    ➕ Add Book

</button>


</form>


<!-- MESSAGE -->

<?php if ($message !== "") { ?>

<div class="message">

    <?php

    echo htmlspecialchars($message);

    ?>

</div>

<?php } ?>


<!-- BACK -->

<a
    class="back"
    href="seller_dashboard.php"
>

    ← Back to Seller Dashboard

</a>


</div>


</body>

</html>