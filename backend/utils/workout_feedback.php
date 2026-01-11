<?php
/**
 * Workout Feedback Generator
 * Generates qualitative feedback based on workout performance
 */

function generateWorkoutFeedback($reps, $form_score, $duration_seconds, $exercise_name = 'Push-up') {
    $feedback = [
        'summary' => '',
        'positives' => [],
        'improvements' => [],
        'overall_rating' => ''
    ];
    
    // Calculate rep rate (reps per minute)
    $rep_rate = $duration_seconds > 0 ? ($reps / ($duration_seconds / 60)) : 0;
    
    // Determine overall rating
    if ($form_score >= 90) {
        $feedback['overall_rating'] = 'Excellent';
    } elseif ($form_score >= 80) {
        $feedback['overall_rating'] = 'Great';
    } elseif ($form_score >= 70) {
        $feedback['overall_rating'] = 'Good';
    } elseif ($form_score >= 60) {
        $feedback['overall_rating'] = 'Fair';
    } else {
        $feedback['overall_rating'] = 'Needs Improvement';
    }
    
    // POSITIVES
    if ($reps >= 10) {
        $feedback['positives'][] = "Great endurance completing {$reps} reps";
    } elseif ($reps >= 5) {
        $feedback['positives'][] = "Solid set with {$reps} reps";
    }
    
    if ($form_score >= 85) {
        $feedback['positives'][] = "Excellent form throughout the set";
    } elseif ($form_score >= 75) {
        $feedback['positives'][] = "Maintained good form on most reps";
    }
    
    if ($rep_rate >= 12 && $rep_rate <= 20) {
        $feedback['positives'][] = "Perfect rep tempo - controlled and steady";
    }
    
    // IMPROVEMENTS
    if ($form_score < 75) {
        $feedback['improvements'][] = "Focus on maintaining proper form throughout each rep";
    }
    
    if ($form_score >= 60 && $form_score < 85) {
        $feedback['improvements'][] = "Try slowing down slightly to perfect your technique";
    }
    
    if ($reps < 5) {
        $feedback['improvements'][] = "Build up strength to complete more reps with good form";
    }
    
    if ($rep_rate > 25) {
        $feedback['improvements'][] = "Slow down your reps to ensure full range of motion";
    }
    
    // Generate summary
    $summary_parts = [];
    $summary_parts[] = "{$feedback['overall_rating']} workout! You completed {$reps} {$exercise_name}s with an average form score of " . round($form_score) . "%.";
    
    if (!empty($feedback['positives'])) {
        $summary_parts[] = $feedback['positives'][0] . ".";
    }
    
    if (!empty($feedback['improvements'])) {
        $summary_parts[] = $feedback['improvements'][0] . ".";
    }
    
    if ($form_score >= 80) {
        $summary_parts[] = "Keep up the excellent work!";
    } else {
        $summary_parts[] = "Keep practicing and your form will improve!";
    }
    
    $feedback['summary'] = implode(' ', $summary_parts);
    
    return $feedback;
}
?>