```php
<?php
$server = "sql306.infinityfree.com";
$user = "if0_43142986";
$password = "YOUR_VPANEL_PASSWORD";
$dbname = "if0_43142986_Netmart";

$conn = new mysqli($server, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
```