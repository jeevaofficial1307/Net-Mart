<?php

session_start();



unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);



if (
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'customer'
) {

    unset($_SESSION['role']);

}



if (empty($_SESSION)) {

    session_destroy();

}

/*
 * Home page.
 */

header("Location: index.php");
exit();

?>