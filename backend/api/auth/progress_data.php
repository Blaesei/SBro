<?php
// backend/api/progress_data.php - FINAL SIMPLE VERSION
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the type of data requested
$data_type = $_GET['type'] ?? 'all';

if ($data_type == 'form_trend') {
    // Return form trend data
    echo json_encode([
        'weeks' => ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10', 'W11', 'W12'],
        'scores' => [65, 70, 68, 75, 78, 82, 80, 85, 87, 89, 92, 90]
    ]);
} 
else if ($data_type == 'weekly_frequency') {
    // Return weekly frequency data
    echo json_encode([
        'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'counts' => [3, 4, 2, 5, 4, 6, 5]
    ]);
}
else if ($data_type == 'exercise_breakdown') {
    // Return exercise breakdown data
    echo json_encode([
        'exercises' => ['Squat', 'Push-up', 'Plank', 'Lunge', 'Sit-up'],
        'counts' => [15, 12, 8, 10, 6]
    ]);
}
else {
    // Return all data
    echo json_encode([
        'form_trend' => [
            'weeks' => ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10', 'W11', 'W12'],
            'scores' => [65, 70, 68, 75, 78, 82, 80, 85, 87, 89, 92, 90]
        ],
        'weekly_frequency' => [
            'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'counts' => [3, 4, 2, 5, 4, 6, 5]
        ],
        'exercise_breakdown' => [
            'exercises' => ['Squat', 'Push-up', 'Plank', 'Lunge', 'Sit-up'],
            'counts' => [15, 12, 8, 10, 6]
        ],
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>