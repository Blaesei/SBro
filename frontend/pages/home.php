<?php
/**
 * SpotBro Home/Dashboard Page
 * Refactored to use header.php component
 */
$current_page = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SpotBro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>
<body class="dashboard-page">

    <!-- Include Navigation Header -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto p-6">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome back, <span id="userName">User</span>!</h1>
            <p class="text-gray-600">Ready to improve your form today?</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-value" id="totalWorkouts">0</div>
                <div class="dashboard-stat-label">Workouts This Week</div>
            </div>
            
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-value" id="avgFormScore">0%</div>
                <div class="dashboard-stat-label">Average Form Score</div>
            </div>
            
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-value" id="currentStreak">0</div>
                <div class="dashboard-stat-label">Day Streak</div>
            </div>
        </div>
        
        <!-- Quick Start -->
        <div class="dashboard-card">
            <h2 class="dashboard-card-title">Quick Start</h2>
            <p class="text-gray-600 mb-6">Choose an exercise and start training with real-time AI feedback</p>
            <a href="exercises.php" class="dashboard-btn dashboard-btn-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                </svg>
                Start New Workout
            </a>
        </div>
        
        <!-- Recent Workouts -->
        <div class="dashboard-card">
            <div class="flex justify-between items-center mb-6">
                <h2 class="dashboard-card-title mb-0">Recent Workouts</h2>
                <a href="progress.php" class="dashboard-view-all">
                    View All →
                </a>
            </div>
            
            <div id="recentWorkouts">
                <!-- Loading state -->
                <div class="text-center py-8 text-gray-600">
                    <div class="dashboard-spinner"></div>
                    <p>Loading workouts...</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>
    
    <!-- Page-specific Scripts -->
    <script>
        // Check if user is logged in
        const user = JSON.parse(sessionStorage.getItem('user') || '{}');
        if (!user.user_id) {
            window.location.href = 'login.php';
        }
        
        // Display user name
        document.getElementById('userName').textContent = user.full_name || 'User';
        
        // Get correct API path
        function getApiPath() {
            return '../../backend/api/progress/get_progress.php';
        }
        
        // VALIDATION: Ensure data is reasonable
        function validateWorkoutData(data) {
            if (!data || !data.success) {
                console.error('API returned error:', data);
                return false;
            }
            
            // Validate stats
            if (data.stats) {
                if (data.stats.total_workouts < 0 || data.stats.total_workouts > 100000) {
                    console.error('Invalid total_workouts:', data.stats.total_workouts);
                    data.stats.total_workouts = 0;
                }
                
                if (data.stats.avg_form_score < 0 || data.stats.avg_form_score > 100) {
                    console.error('Invalid avg_form_score:', data.stats.avg_form_score);
                    data.stats.avg_form_score = 0;
                }
                
                if (data.stats.total_reps < 0 || data.stats.total_reps > 1000000) {
                    console.error('Invalid total_reps:', data.stats.total_reps);
                    data.stats.total_reps = 0;
                }
            }
            
            // Validate recent workouts
            if (data.recent_workouts && Array.isArray(data.recent_workouts)) {
                data.recent_workouts = data.recent_workouts.filter(workout => {
                    if (!workout.reps_completed || workout.reps_completed < 0 || workout.reps_completed > 1000) {
                        console.warn('Filtered invalid workout:', workout);
                        return false;
                    }
                    if (!workout.form_score || workout.form_score < 0 || workout.form_score > 100) {
                        console.warn('Filtered invalid workout:', workout);
                        return false;
                    }
                    return true;
                });
            }
            
            return true;
        }
        
        // Load dashboard data
        async function loadDashboardData() {
            try {
                const apiUrl = getApiPath();
                console.log('Loading data from:', apiUrl);
                
                const response = await fetch(apiUrl);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                console.log('API Response:', data);
                
                // Validate data
                if (!validateWorkoutData(data)) {
                    throw new Error('Data validation failed');
                }
                
                if (data.success) {
                    // Update stats
                    document.getElementById('totalWorkouts').textContent = data.stats.total_workouts || 0;
                    document.getElementById('avgFormScore').textContent = 
                        Math.round(data.stats.avg_form_score || 0) + '%';
                    document.getElementById('currentStreak').textContent = data.stats.current_streak_days || 0;
                    
                    // Display recent workouts
                    displayRecentWorkouts(data.recent_workouts || []);
                } else {
                    console.error('Failed to load data:', data.error);
                    document.getElementById('recentWorkouts').innerHTML = 
                        '<p class="text-center text-gray-600 py-8">Failed to load workouts</p>';
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
                document.getElementById('recentWorkouts').innerHTML = 
                    '<p class="text-center text-gray-600 py-8">Error loading data: ' + error.message + '</p>';
            }
        }
        
        function displayRecentWorkouts(workouts) {
            const container = document.getElementById('recentWorkouts');
            
            if (workouts.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-600">
                        <p class="mb-4">No workouts yet</p>
                        <a href="exercises.php" class="dashboard-btn">Start Your First Workout</a>
                    </div>
                `;
                return;
            }
            
            const getExerciseEmoji = (name) => {
                const emojis = {
                    'push-up': '💪', 'pushup': '💪',
                    'squat': '🦵', 'plank': '🧘', 'lunge': '🏃'
                };
                return emojis[name.toLowerCase()] || '💪';
            };
            
            const getScoreColor = (score) => {
                if (score >= 90) return '#22c55e';
                if (score >= 75) return '#f59e0b';
                return '#ef4444';
            };
            
            container.innerHTML = workouts.map(workout => `
                <div class="dashboard-workout-item">
                    <div class="flex items-center gap-4">
                        <div class="dashboard-workout-icon">${getExerciseEmoji(workout.exercise_name)}</div>
                        <div>
                            <p class="font-semibold text-gray-900">${workout.exercise_name}</p>
                            <p class="text-sm text-gray-600">${new Date(workout.workout_date).toLocaleDateString()}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold" style="color: ${getScoreColor(workout.form_score)};">
                            ${Math.round(workout.form_score)}%
                        </p>
                        <p class="text-sm text-gray-600">
                            ${workout.reps_completed} reps
                        </p>
                    </div>
                </div>
            `).join('');
        }
        
        // Load data on page load
        loadDashboardData();
    </script>

    <!-- Theme Toggle Script -->
    <script src="../assets/js/theme-toggle.js"></script>

</body>
</html>