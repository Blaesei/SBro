<?php
// backend/models/ProgressModel.php
require_once '../config/database.php';

class ProgressModel {
    
    public static function getDashboardStats($user_id) {
        $conn = getDBConnection();
        
        $stats = [
            'total_workouts' => 0,
            'avg_form_score' => 0,
            'total_reps' => 0,
            'weekly_count' => 0
        ];
        
        try {
            // Total workouts
            $stmt1 = $conn->prepare("SELECT COUNT(*) as count FROM exercise_sessions WHERE user_id = ?");
            $stmt1->bind_param("i", $user_id);
            $stmt1->execute();
            $result1 = $stmt1->get_result();
            $row1 = $result1->fetch_assoc();
            $stats['total_workouts'] = $row1['count'] ?? 0;
            $stmt1->close();
            
            // Average form score
            $stmt2 = $conn->prepare("SELECT AVG(form_score) as avg_score FROM exercise_sessions WHERE user_id = ?");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();
            $stats['avg_form_score'] = $row2['avg_score'] ? round($row2['avg_score'], 1) : 0;
            $stmt2->close();
            
            // Weekly workouts (last 7 days)
            $stmt3 = $conn->prepare("SELECT COUNT(*) as count FROM exercise_sessions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $row3 = $result3->fetch_assoc();
            $stats['weekly_count'] = $row3['count'] ?? 0;
            $stmt3->close();
            
        } catch (Exception $e) {
            // Return default stats if error
        }
        
        $conn->close();
        return $stats;
    }
    
    public static function getRecentWorkouts($user_id, $limit = 5) {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                exercise_name,
                form_score,
                reps_completed,
                DATE_FORMAT(created_at, '%Y-%m-%d') as workout_date
            FROM exercise_sessions 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $workouts = [];
        while ($row = $result->fetch_assoc()) {
            $workouts[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        // If no data, return dummy workouts
        if (empty($workouts)) {
            $workouts = [
                ['exercise_name' => 'Squat', 'form_score' => 87, 'reps_completed' => 12, 'workout_date' => date('Y-m-d')],
                ['exercise_name' => 'Push-up', 'form_score' => 92, 'reps_completed' => 15, 'workout_date' => date('Y-m-d', strtotime('-1 day'))],
                ['exercise_name' => 'Plank', 'form_score' => 78, 'reps_completed' => 30, 'workout_date' => date('Y-m-d', strtotime('-2 days'))]
            ];
        }
        
        return $workouts;
    }
    
    public static function saveWorkout($user_id, $exercise_name, $form_score, $reps, $duration) {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO exercise_sessions (user_id, exercise_name, form_score, reps_completed, duration_seconds) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isiii", $user_id, $exercise_name, $form_score, $reps, $duration);
        
        $success = $stmt->execute();
        $workout_id = $stmt->insert_id;
        
        $stmt->close();
        $conn->close();
        
        return $success ? $workout_id : false;
    }
}
?>