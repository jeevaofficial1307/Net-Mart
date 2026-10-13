<?php

include 'config.php';

$email = "devil1307@gmail.com";
$new_password = "123456";

$hashed_password = password_hash(
    $new_password,
    PASSWORD_DEFAULT
);

$sql = "UPDATE customers
        SET password = ?
        WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "ss",
    $hashed_password,
    $email
);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        echo "Password reset successfully!<br>";
        echo "Email: " . htmlspecialchars($email) . "<br>";
        echo "New Password: 123456";

    } else {

        echo "Customer email not found.";

    }

} else {

    echo "Password update failed: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>