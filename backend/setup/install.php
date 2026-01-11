<?php
echo '<!DOCTYPE html>
<html>
<head>
    <title>SpotBro Setup</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .box { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; }
        .success { background: #dfd; color: #3a3; padding: 15px; border-radius: 5px; }
        .error { background: #fee; color: #c33; padding: 15px; border-radius: 5px; }
        input { width: 100%; padding: 10px; margin: 5px 0 15px 0; }
        button { background: #4CAF50; color: white; padding: 12px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body style="background:#f0f0f0;">
    <div class="box">
        <h2>SpotBro Database Setup</h2>';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'spotbro_db';
    
    // Test connection
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        echo '<div class="error">Connection Failed: ' . $conn->connect_error . '</div>';
    } else {
        echo '<div class="success">✅ Connected to MySQL</div>';
        
        // Create database
        $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
        $conn->select_db($dbname);
        
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            echo '<div class="success">✅ Database created</div>';
            
            // Create config file
            $config = "<?php
define('DB_HOST', '$host');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_NAME', '$dbname');

function getDBConnection() {
    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (\$conn->connect_error) {
        die('Database error: ' . \$conn->connect_error);
    }
    return \$conn;
}
?>";
            
            file_put_contents('../config/database.php', $config);
            echo '<div class="success">✅ Config file created</div>';
            echo '<p><a href="../../frontend/signup.php">Go to Signup Page</a></p>';
        }
        $conn->close();
    }
}

echo '<form method="POST">
        <p><strong>MySQL Settings:</strong></p>
        <input type="text" name="host" value="localhost" placeholder="Host" required><br>
        <input type="text" name="user" value="root" placeholder="Username" required><br>
        <input type="password" name="pass" placeholder="Password (usually empty for XAMPP)"><br>
        <input type="text" name="dbname" value="spotbro_db" placeholder="Database Name" required><br>
        <button type="submit">Setup Database</button>
    </form>
    
    <p><strong>Default XAMPP:</strong><br>
    Host: localhost<br>
    Username: root<br>
    Password: (empty)<br>
    Database: spotbro_db</p>
</div>
</body>
</html>';
?>