<?php
/**
 * backend/api/progress/get_progress.php
 * FIXED VERSION - Matches your actual database structure
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
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn = getDBConnection();
    
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
    // GET RECENT WORKOUTS (last 10)
    // ==========================================
    $workouts_query = "
        SELECT 
            session_id,
            exercise_name,
            form_score,
            reps_completed,
            duration_seconds,
            created_at,
            DATE_FORMAT(created_at, '%Y-%m-%d') as workout_date
        FROM exercise_sessions 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($workouts_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $workouts_result = $stmt->get_result();
    
    $recent_workouts = [];
    while ($row = $workouts_result->fetch_assoc()) {
        // Clean up data
        $row['form_score'] = floatval($row['form_score']);
        $row['reps_completed'] = intval($row['reps_completed']);
        $row['duration_seconds'] = intval($row['duration_seconds']);
        $recent_workouts[] = $row;
    }
    $stmt->close();
    
    // ==========================================
    // GET FORM TREND DATA (last 7 workouts)
    // ==========================================
    $trend_query = "
        SELECT 
            form_score,
            DATE_FORMAT(created_at, '%Y-%m-%d') as workout_date,
            created_at
        FROM exercise_sessions 
        WHERE user_id = ?
        AND form_score > 0
        ORDER BY created_at DESC
        LIMIT 7
    ";
    
    $stmt = $conn->prepare($trend_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $trend_result = $stmt->get_result();
    
    $form_trend = [];
    while ($row = $trend_result->fetch_assoc()) {
        $form_trend[] = [
            'form_score' => floatval($row['form_score']),
            'workout_date' => $row['workout_date']
        ];
    }
    $stmt->close();
    
    // Reverse to show oldest first
    $form_trend = array_reverse($form_trend);
    
    // If less than 7 items, pad with zeros
    while (count($form_trend) < 7) {
        $form_trend[] = ['form_score' => 0, 'workout_date' => ''];
    }
    
    // ==========================================
    // GET WEEKLY FREQUENCY (last 7 days)
    // ==========================================
    $weekly_query = "
        SELECT 
            DAYNAME(created_at) as day_name,
            COUNT(*) as count
        FROM exercise_sessions 
        WHERE user_id = ?
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DAYNAME(created_at)
        ORDER BY 
            CASE DAYNAME(created_at)
                WHEN 'Sunday' THEN 7
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                WHEN 'Saturday' THEN 6
            END
    ";
    
    $stmt = $conn->prepare($weekly_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $weekly_result = $stmt->get_result();
    
    $weekly_frequency = [];
    $all_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $temp_frequency = [];
    
    while ($row = $weekly_result->fetch_assoc()) {
        $temp_frequency[$row['day_name']] = intval($row['count']);
    }
    
    // Ensure all days are present
    foreach ($all_days as $day) {
        $weekly_frequency[] = [
            'day_name' => $day,
            'count' => isset($temp_frequency[$day]) ? $temp_frequency[$day] : 0
        ];
    }

    $stmt->close();
    $conn->close();
    
    // ==========================================
    // DATA VALIDATION & CLEANUP
    // ==========================================
    
    // Ensure all required fields exist in stats
    $required_stats = ['total_workouts', 'total_reps', 'avg_form_score', 'current_streak_days'];
    foreach ($required_stats as $field) {
        if (!isset($stats[$field])) {
            $stats[$field] = 0;
        }
        // Cast to appropriate type
        if (in_array($field, ['total_workouts', 'total_reps', 'current_streak_days'])) {
            $stats[$field] = intval($stats[$field]);
        } elseif ($field === 'avg_form_score') {
            $stats[$field] = floatval($stats[$field]);
        }
    }
    
    // ==========================================
    // RETURN SUCCESS RESPONSE
    // ==========================================
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'recent_workouts' => $recent_workouts,
        'form_trend' => $form_trend,
        'weekly_frequency' => $weekly_frequency
    ]);
    
} catch (Exception $e) {
    // Clean up output buffer
    ob_end_clean();
    
    // Log the error (for debugging)
    error_log("Get progress error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load progress data',
        'debug' => $e->getMessage() // Remove in production
    ]);
}

