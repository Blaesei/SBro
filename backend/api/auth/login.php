<?php
/**
 * User Login API - FIXED VERSION with Clean JSON Output
 */

// CRITICAL: Start output buffering FIRST to catch any errors
ob_start();

// Disable display errors (prevent HTML error messages)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database config
require_once '../../config/database.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Invalid request method'], 405);
}

// Get and validate input
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validation
if (empty($email)) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Email is required'], 400);
}

if (empty($password)) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Password is required'], 400);
}

try {
    // Get database connection
    $conn = getDBConnection();

    // Get user by email
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash FROM users WHERE email = ?");
    
    if (!$stmt) {
        throw new Exception("Database query preparation failed");
    }
    
    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        throw new Exception("Database query execution failed");
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        sendJSON(['success' => false, 'error' => 'Invalid email or password'], 401);
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        ob_end_clean();
        sendJSON(['success' => false, 'error' => 'Invalid email or password'], 401);
    }
    
    // Start session
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    
    // Clear output buffer before sending JSON
    ob_end_clean();
    
    // Send success response
    sendJSON([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'user_id' => $user['user_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email']
        ]
    ]);
    
} catch (Exception $e) {
    // Log error to file (not to output)
    error_log("Login error: " . $e->getMessage());
    
    // Clear any buffered output
    ob_end_clean();
    
    // Send clean JSON error
    sendJSON(['success' => false, 'error' => 'Server error. Please try again.'], 500);
}
?>