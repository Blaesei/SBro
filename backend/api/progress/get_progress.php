<?php
/**
 * backend/api/progress/get_progress.php
 * UNIFIED & OPTIMIZED - Single source of truth for progress data
 * No redundant queries, no dummy data
 */

// Start output buffering
ob_start();

// Disable display errors
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../../config/database.php';

session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Not logged in'], 401);
}

$user_id = $_SESSION['user_id'];

try {
    $conn = getDBConnection();
    
    // ==========================================
    // SINGLE OPTIMIZED QUERY
    // ==========================================
    // Fetch all workout data in one query (last 50 workouts)
    // This is more efficient than 4 separate queries
    
    $query = "
        SELECT 
            session_id,
            exercise_name,
            form_score,
            reps_completed,
            duration_seconds,
            created_at,
            DATE_FORMAT(created_at, '%Y-%m-%d') as workout_date,
            DATE_FORMAT(created_at, '%H:%i') as workout_time,
            DATE_FORMAT(created_at, '%W') as day_name,
            DATE_FORMAT(created_at, '%w') as day_num,
            DATEDIFF(NOW(), created_at) as days_ago
        FROM exercise_sessions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare workout query");
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $all_workouts = [];
    while ($row = $result->fetch_assoc()) {
        $all_workouts[] = $row;
    }
    $stmt->close();
    
    // ==========================================
    // GET USER STATS (from user_stats table)
    // ==========================================
    $stats_query = "SELECT * FROM user_stats WHERE user_id = ?";
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats_result = $stmt->get_result();
    
    if ($stats_result->num_rows > 0) {
        $stats = $stats_result->fetch_assoc();
    } else {
        // Initialize if not exists
        $stats = [
            'total_workouts' => 0,
            'total_reps' => 0,
            'avg_form_score' => 0,
            'current_streak_days' => 0
        ];
    }
    $stmt->close();
    
    // ==========================================
    // DERIVE ALL METRICS FROM SINGLE DATASET
    // ==========================================
    
    // 1. Recent Workouts (last 10)
    $recent_workouts = array_slice($all_workouts, 0, 10);
    
    // 2. Weekly Frequency (last 7 days)
    $weekly_workouts = array_filter($all_workouts, function($w) {
        return $w['days_ago'] <= 7;
    });
    
    // Calculate frequency by day
    $weekly_frequency = [];
    $day_counts = array_fill(0, 7, 0); // [Mon, Tue, Wed, ...]
    
    foreach ($weekly_workouts as $workout) {
        $day_index = intval($workout['day_num']);
        if ($day_index == 0) $day_index = 7; // Sunday = 7
        $day_counts[$day_index - 1]++;
    }
    
    $day_names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    for ($i = 0; $i < 7; $i++) {
        if ($day_counts[$i] > 0) {
            $weekly_frequency[] = [
                'day_name' => $day_names[$i],
                'count' => $day_counts[$i]
            ];
        }
    }
    
    // 3. Form Trend (last 12 workouts)
    $form_trend = array_slice($all_workouts, 0, 12);
    
    // Reverse to show oldest first (for charts)
    $form_trend = array_reverse($form_trend);
    
    // ==========================================
    // DATA VALIDATION
    // ==========================================
    // Ensure data integrity
    
    // Validate stats
    if ($stats['total_workouts'] < 0) $stats['total_workouts'] = 0;
    if ($stats['total_reps'] < 0) $stats['total_reps'] = 0;
    if ($stats['avg_form_score'] < 0) $stats['avg_form_score'] = 0;
    if ($stats['avg_form_score'] > 100) $stats['avg_form_score'] = 100;
    if ($stats['current_streak_days'] < 0) $stats['current_streak_days'] = 0;
    
    // Validate workout data
    foreach ($recent_workouts as &$workout) {
        // Ensure reps are reasonable
        if ($workout['reps_completed'] < 0) $workout['reps_completed'] = 0;
        if ($workout['reps_completed'] > 1000) $workout['reps_completed'] = 1000;
        
        // Ensure form score is in range
        if ($workout['form_score'] < 0) $workout['form_score'] = 0;
        if ($workout['form_score'] > 100) $workout['form_score'] = 100;
        
        // Round form score to 1 decimal
        $workout['form_score'] = round($workout['form_score'], 1);
    }
    
    $conn->close();
    
    // ==========================================
    // CLEAR BUFFER & SEND RESPONSE
    // ==========================================
    ob_end_clean();
    
    sendJSON([
        'success' => true,
        'stats' => [
            'total_workouts' => intval($stats['total_workouts']),
            'total_reps' => intval($stats['total_reps']),
            'avg_form_score' => floatval($stats['avg_form_score']),
            'current_streak_days' => intval($stats['current_streak_days'])
        ],
        'recent_workouts' => $recent_workouts,
        'weekly_frequency' => $weekly_frequency,
        'form_trend' => $form_trend,
        'data_timestamp' => date('Y-m-d H:i:s'),
        'total_workouts_fetched' => count($all_workouts)
    ]);
    
} catch (Exception $e) {
    error_log("Progress API error: " . $e->getMessage());
    
    if (isset($conn)) {
        $conn->close();
    }
    
    ob_end_clean();
    sendJSON(['success' => false, 'error' => 'Server error'], 500);
}
?>