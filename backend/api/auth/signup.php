<?php
/**
 * User Signup API - FIXED VERSION with Clean JSON Output
 */

// CRITICAL: Start output buffering FIRST
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
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validation
if (empty($full_name)) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Full name is required'], 400);
}

if (empty($email)) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Email is required'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Invalid email format'], 400);
}

if (empty($password)) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Password is required'], 400);
}

if (strlen($password) < 6) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Password must be at least 6 characters'], 400);
}

try {
    // Get database connection
    $conn = getDBConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    
    if (!$stmt) {
        throw new Exception("Database query preparation failed");
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        ob_end_clean();
        sendJSON(['success' => false, 'error' => 'Email already registered'], 409);
    }
    $stmt->close();
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare insert statement");
    }
    
    $stmt->bind_param("sss", $full_name, $email, $password_hash);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create account");
    }
    
    $user_id = $stmt->insert_id;
    $stmt->close();
    
    // Initialize user stats
    $stmt2 = $conn->prepare("INSERT INTO user_stats (user_id, total_workouts, total_reps, avg_form_score, current_streak_days) VALUES (?, 0, 0, 0, 0)");
    
    if ($stmt2) {
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $stmt2->close();
    }
    
    $conn->close();
    
    // Start session
    session_start();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    
    // Clear output buffer before sending JSON
    ob_end_clean();
    
    // Send success response
    sendJSON([
        'success' => true,
        'message' => 'Account created successfully',
        'user' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $email
        ]
    ], 201);
    
} catch (Exception $e) {
    // Log error to file
    error_log("Signup error: " . $e->getMessage());
    
    // Close connection if open
    if (isset($conn) && $conn) {
        $conn->close();
    }
    
    // Clear any buffered output
    ob_end_clean();
    
    // Send clean JSON error
    sendJSON(['success' => false, 'error' => 'Server error. Please try again.'], 500);
}
?>