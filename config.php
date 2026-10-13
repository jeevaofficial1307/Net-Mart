```php
<?php

mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'netmart';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    error_log('MySQL connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database connection failed.');
}

$conn->set_charset('utf8mb4');

?>
```
