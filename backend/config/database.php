<?php
/**
 * SpotBro Database Configuration
 * FIXED VERSION - Clean JSON output
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for XAMPP default
define('DB_NAME', 'spotbro_db');

/**
 * Get database connection
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        // Log error
        error_log("Database connection failed: " . $conn->connect_error);
        
        // Return JSON error (don't die with HTML)
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed'
        ]);
        exit();
    }
    
    // Set charset to UTF-8
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

/**
 * Send JSON response and exit
 * CRITICAL: This clears output buffer before sending
 */
function sendJSON($data, $statusCode = 200) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    // Send JSON
    echo json_encode($data);
    exit();
}

/**
 * Validate required POST fields
 */
function validateRequired($fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendJSON([
            'success' => false,
            'error' => 'Missing required fields: ' . implode(', ', $missing)
        ], 400);
    }
}

/**
 * Execute prepared statement with error handling
 */
function executeQuery($conn, $query, $types = '', $params = []) {
    try {
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Query preparation failed: " . $conn->error);
            return false;
        }
        
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Query execution failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        // For SELECT queries, return result
        if (stripos($query, 'SELECT') === 0) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }
        
        // For INSERT/UPDATE/DELETE, return statement
        return $stmt;
        
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
        return false;
    }
}
?>