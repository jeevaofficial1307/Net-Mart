<?php

include "config.php";

echo "<h2>✅ Database Connected Successfully!</h2>";

$result = $conn->query("SHOW TABLES");

if ($result) {

    echo "<h3>Database Tables:</h3>";

    while ($row = $result->fetch_array()) {

        echo "✅ " . htmlspecialchars($row[0]) . "<br>";

    }

} else {

    echo "❌ Cannot read tables: " . htmlspecialchars($conn->error);

}

?>