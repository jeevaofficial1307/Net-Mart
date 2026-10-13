
<?php

mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$database = getenv('MYSQLDATABASE');
$port = (int) (getenv('MYSQLPORT') ?: 3306);

if (!$host || !$user || !$database || !$password) {
    error_log('Railway MySQL environment variables are missing.');
    http_response_code(500);
    exit('Database configuration is missing.');
}

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
