<?php
require_once 'config/database.php';

echo "Testing database connection...\n";

try {
    $conn = getDBConnection();
    echo "✅ Database connected successfully!\n";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    echo "✅ Tables found: " . $result->num_rows . "\n";
    
    while ($row = $result->fetch_array()) {
        echo "  - " . $row[0] . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>