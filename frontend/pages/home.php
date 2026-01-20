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

    <style>
        /* Additional inline styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f9fafb;
            line-height: 1.6;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        .card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #111827;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.125rem;
        }
        .workout-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        .workout-item:hover {
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }
        .workout-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .spinner {
            border: 3px solid #e5e7eb;
            border-top-color: #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
            margin: 2rem auto;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Include Navigation Header -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto p-6">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome back, <span id="userName">User</span>! 👋</h1>
            <p class="text-gray-600">Ready to improve your form today?</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-value" id="totalWorkouts">0</div>
                <div class="stat-label">Workouts This Week</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #a855f7, #9333ea);">
                <div class="stat-value" id="avgFormScore">0%</div>
                <div class="stat-label">Average Form Score</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                <div class="stat-value" id="currentStreak">0</div>
                <div class="stat-label">Day Streak</div>
            </div>
        </div>
        
        <!-- Quick Start -->
        <div class="card">
            <h2 class="card-title">Quick Start</h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                Choose an exercise and start training with real-time AI feedback
            </p>
            <a href="exercises.php" class="btn btn-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                </svg>
                Start New Workout
            </a>
        </div>
        
        <!-- Recent Workouts -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="card-title" style="margin-bottom: 0;">Recent Workouts</h2>
                <a href="progress.php" style="color: #667eea; font-weight: 600; font-size: 0.875rem; text-decoration: none;">
                    View All →
                </a>
            </div>
            
            <div id="recentWorkouts">
                <!-- Loading state -->
                <div style="text-align: center; padding: 2rem; color: #6b7280;">
                    <div class="spinner"></div>
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
                        '<p style="text-align: center; color: #6b7280; padding: 2rem;">Failed to load workouts</p>';
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
                document.getElementById('recentWorkouts').innerHTML = 
                    '<p style="text-align: center; color: #6b7280; padding: 2rem;">Error loading data: ' + error.message + '</p>';
            }
        }
        
        function displayRecentWorkouts(workouts) {
            const container = document.getElementById('recentWorkouts');
            
            if (workouts.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #6b7280;">
                        <p style="margin-bottom: 1rem;">No workouts yet</p>
                        <a href="exercises.php" class="btn">Start Your First Workout</a>
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
                <div class="workout-item">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="workout-icon">${getExerciseEmoji(workout.exercise_name)}</div>
                        <div>
                            <p style="font-weight: 600; color: #111827;">${workout.exercise_name}</p>
                            <p style="font-size: 0.875rem; color: #6b7280;">${new Date(workout.workout_date).toLocaleDateString()}</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 1.5rem; font-weight: 700; color: ${getScoreColor(workout.form_score)};">
                            ${Math.round(workout.form_score)}%
                        </p>
                        <p style="font-size: 0.875rem; color: #6b7280;">
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