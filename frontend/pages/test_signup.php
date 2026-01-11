<?php
/**
 * DIAGNOSTIC TOOL - Place in frontend/ folder
 * Run this to diagnose signup issues
 * URL: http://localhost/spotbro/frontend/test_signup.php
 */

echo "<h1>SpotBro Diagnostic Tool</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#f0f0f0;}h1{color:#667eea;}pre{background:white;padding:15px;border-radius:8px;overflow:auto;}</style>";

echo "<h2>1. Testing Database Connection</h2>";
try {
    require_once '../backend/config/database.php';
    $conn = getDBConnection();
    echo "<pre style='background:#d4edda;color:#155724;'>✅ Database connected successfully!</pre>";
    
    // Test tables
    $tables = ['users', 'exercise_sessions', 'rep_details', 'user_stats'];
    echo "<h3>Checking tables:</h3>";
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "<pre>✅ Table '$table' exists with $count records</pre>";
        } else {
            echo "<pre style='background:#f8d7da;color:#721c24;'>❌ Table '$table' not found!</pre>";
        }
    }
    $conn->close();
} catch (Exception $e) {
    echo "<pre style='background:#f8d7da;color:#721c24;'>❌ Database Error: " . $e->getMessage() . "</pre>";
}

echo "<h2>2. Testing Signup API</h2>";
echo "<p>Attempting to call signup API...</p>";

// Test data
$testData = [
    'full_name' => 'Test User ' . time(),
    'email' => 'test' . time() . '@example.com',
    'password' => 'password123'
];

echo "<pre>Test data:\n" . print_r($testData, true) . "</pre>";

// Try to call signup API
$apiUrl = '../backend/api/auth/signup.php';
echo "<p>API URL: <code>$apiUrl</code></p>";

// Check if file exists
if (file_exists($apiUrl)) {
    echo "<pre>✅ API file exists</pre>";
} else {
    echo "<pre style='background:#f8d7da;color:#721c24;'>❌ API file NOT FOUND at: " . realpath($apiUrl) . "</pre>";
}

// Simulate POST request
$_POST = $testData;
$_SERVER['REQUEST_METHOD'] = 'POST';

ob_start();
try {
    include $apiUrl;
    $response = ob_get_clean();
    echo "<h3>API Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    $json = json_decode($response, true);
    if ($json && isset($json['success'])) {
        if ($json['success']) {
            echo "<pre style='background:#d4edda;color:#155724;'>✅ Signup API works! User created successfully.</pre>";
        } else {
            echo "<pre style='background:#fff3cd;color:#856404;'>⚠️ API returned: " . $json['error'] . "</pre>";
        }
    } else {
        echo "<pre style='background:#f8d7da;color:#721c24;'>❌ API returned invalid JSON</pre>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<pre style='background:#f8d7da;color:#721c24;'>❌ API Error: " . $e->getMessage() . "</pre>";
}

echo "<h2>3. File Paths Check</h2>";
echo "<pre>";
echo "Current directory: " . __DIR__ . "\n";
echo "Backend path: " . realpath('../backend') . "\n";
echo "Database config: " . (file_exists('../backend/config/database.php') ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "Signup API: " . (file_exists('../backend/api/auth/signup.php') ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "Login API: " . (file_exists('../backend/api/auth/login.php') ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "</pre>";

echo "<h2>4. PHP Info</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";

echo "<hr><p><a href='signup.php'>← Back to Signup</a></p>";
?>