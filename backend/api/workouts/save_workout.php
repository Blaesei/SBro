<?php
/**
 * backend/api/workouts/save_workout.php
 * Save completed workout session
 */
header('Content-Type: application/json');
require_once '../../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    sendJSON(['success' => false, 'error' => 'Not logged in'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['success' => false, 'error' => 'Invalid request method'], 405);
}

validateRequired(['exercise_name', 'form_score', 'reps_completed', 'duration_seconds']);

$user_id = $_SESSION['user_id'];
$exercise_name = trim($_POST['exercise_name']);
$form_score = floatval($_POST['form_score']);
$reps_completed = intval($_POST['reps_completed']);
$duration_seconds = intval($_POST['duration_seconds']);
$rep_details = isset($_POST['rep_details']) ? $_POST['rep_details'] : '[]'; // JSON array

$conn = getDBConnection();

// Insert workout session
$result = executeQuery(
    $conn,
    "INSERT INTO exercise_sessions (user_id, exercise_name, form_score, reps_completed, duration_seconds) 
     VALUES (?, ?, ?, ?, ?)",
    'isdii',
    [$user_id, $exercise_name, $form_score, $reps_completed, $duration_seconds]
);

if (!$result) {
    $conn->close();
    sendJSON(['success' => false, 'error' => 'Failed to save workout'], 500);
}

$session_id = $conn->insert_id;

// Save individual rep details if provided
if ($rep_details !== '[]') {
    $reps = json_decode($rep_details, true);
    if (is_array($reps)) {
        foreach ($reps as $rep) {
            $rep_number = $rep['rep_number'] ?? 0;
            $rep_score = $rep['form_score'] ?? 0;
            $feedback = json_encode($rep['feedback'] ?? []);
            
            executeQuery(
                $conn,
                "INSERT INTO rep_details (session_id, rep_number, form_score, feedback_text) 
                 VALUES (?, ?, ?, ?)",
                'iids',
                [$session_id, $rep_number, $rep_score, $feedback]
            );
        }
    }
}

// Update user stats
$conn->query("
    UPDATE user_stats SET
        total_workouts = total_workouts + 1,
        total_reps = total_reps + $reps_completed,
        last_workout_date = CURDATE()
    WHERE user_id = $user_id
");

// Recalculate average form score
$avg_result = $conn->query("
    SELECT AVG(form_score) as avg_score 
    FROM exercise_sessions 
    WHERE user_id = $user_id
");

if ($avg_result && $row = $avg_result->fetch_assoc()) {
    $avg_score = round($row['avg_score'], 2);
    $conn->query("UPDATE user_stats SET avg_form_score = $avg_score WHERE user_id = $user_id");
}

$conn->close();

sendJSON([
    'success' => true,
    'message' => 'Workout saved successfully',
    'session_id' => $session_id
]);
?>