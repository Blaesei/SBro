<?php
$current_page = 'progress';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotBro - Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>
<style>
    /* FORCE VISIBLE CHART BARS */
    .chart-bar {
        min-height: 40px !important;
        background: linear-gradient(to top, #3b82f6, #8b5cf6) !important;
        border-radius: 8px 8px 0 0 !important;
        width: 50px !important;
        margin: 0 auto !important;
    }
    
    #workoutFrequencyChart .chart-bar {
        background: linear-gradient(to top, #10b981, #34d399) !important;
    }
    
    /* Center the charts */
    #formScoreChart,
    #workoutFrequencyChart {
        display: flex !important;
        align-items: flex-end !important;
        justify-content: space-around !important;
        padding: 20px 0 !important;
        min-height: 250px;
    }
</style>
<body class="progress-page">
    <!-- Navigation -->
    <?php include '../includes/header.php'; ?>

    <!-- Progress Dashboard Page -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Your Progress</h1>
            <p class="page-subtitle">Track your improvement over time</p>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Total Workouts</p>
                <p class="stat-value" id="totalWorkouts">0</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Avg Form Score</p>
                <p class="stat-value" id="avgFormScore">0%</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Reps</p>
                <p class="stat-value" id="totalReps">0</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Current Streak</p>
                <p class="stat-value" id="currentStreak">0 days</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Form Score Trend -->
            <div class="chart-container">
                <h2 class="chart-title">Form Score Trend</h2>
                <div class="chart" id="formScoreChart">
                    <p class="text-gray-500">Loading chart...</p>
                </div>
            </div>

            <!-- Workout Frequency -->
            <div class="chart-container">
                <h2 class="chart-title">Weekly Workouts</h2>
                <div class="chart" id="workoutFrequencyChart">
                    <p class="text-gray-500">Loading chart...</p>
                </div>
            </div>
        </div>

        <!-- Recent Workouts -->
        <div class="recent-workouts">
            <h2 class="chart-title">Recent Workouts</h2>
            <div class="space-y-4" id="recentWorkoutsList">
                <p class="text-gray-500">Loading workouts...</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Check authentication
        const user = JSON.parse(sessionStorage.getItem('user') || '{}');
        if (!user.user_id) {
            window.location.href = 'login.php';
        }

        // Logout handler
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            sessionStorage.clear();
            window.location.href = 'login.php';
        });

        // Get API path
        function getApiPath() {
            return '../../backend/api/progress/get_progress.php';
        }
        
        // VALIDATION: Ensure data is reasonable (from original)
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
            }
            
            // Validate workouts
            if (data.recent_workouts) {
                data.recent_workouts = data.recent_workouts.filter(w => 
                    w.reps_completed > 0 && w.reps_completed < 1000 &&
                    w.form_score >= 0 && w.form_score <= 100
                );
            }
            
            return true;
        }

        // Load progress dashboard (combined version)
        async function loadProgressDashboard() {
            try {
                const apiUrl = getApiPath();
                console.log('🚀 Loading progress from:', apiUrl);
                
                const response = await fetch(`${apiUrl}?t=${Date.now()}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('✅ API Response:', data);
                
                // Validate data (from original)
                if (!validateWorkoutData(data)) {
                    console.warn('Data validation failed, showing sample data');
                    showSampleData();
                    return;
                }
                
                if (data.success) {
                    updateDashboardStats(data.stats);
                    updateFormTrendChart(data.form_trend || []);
                    updateWeeklyFrequencyChart(data.weekly_frequency || []);
                    updateRecentWorkouts(data.recent_workouts || []);
                } else {
                    console.error('❌ API error:', data.error);
                    showSampleData();
                }
            } catch (error) {
                console.error('💥 Error loading progress:', error);
                showSampleData();
            }
        }

        // Show sample data for testing (from second file)
        function showSampleData() {
            console.log('🔧 Showing sample data');
            
            const sampleStats = {
                total_workouts: 12,
                total_reps: 240,
                avg_form_score: 85,
                current_streak_days: 7
            };
            updateDashboardStats(sampleStats);
            
            const sampleFormTrend = [
                { form_score: 82 },
                { form_score: 85 },
                { form_score: 88 },
                { form_score: 90 },
                { form_score: 87 },
                { form_score: 92 },
                { form_score: 89 }
            ];
            updateFormTrendChart(sampleFormTrend);
            
            const sampleWeeklyFrequency = [
                { day_name: 'Monday', count: 2 },
                { day_name: 'Tuesday', count: 3 },
                { day_name: 'Wednesday', count: 1 },
                { day_name: 'Thursday', count: 4 },
                { day_name: 'Friday', count: 2 },
                { day_name: 'Saturday', count: 3 },
                { day_name: 'Sunday', count: 1 }
            ];
            updateWeeklyFrequencyChart(sampleWeeklyFrequency);
            
            const sampleWorkouts = [
                {
                    exercise_name: 'Push-up',
                    form_score: 92,
                    reps_completed: 15,
                    workout_date: new Date().toISOString().split('T')[0]
                },
                {
                    exercise_name: 'Squat',
                    form_score: 85,
                    reps_completed: 12,
                    workout_date: new Date(Date.now() - 86400000).toISOString().split('T')[0]
                }
            ];
            updateRecentWorkouts(sampleWorkouts);
        }

        function updateDashboardStats(stats) {
            console.log('📊 Updating stats:', stats);
            document.getElementById('totalWorkouts').textContent = stats.total_workouts || 0;
            document.getElementById('avgFormScore').textContent = Math.round(stats.avg_form_score || 0) + '%';
            document.getElementById('totalReps').textContent = stats.total_reps || 0;
            document.getElementById('currentStreak').textContent = (stats.current_streak_days || 0) + ' days';
        }

        function updateFormTrendChart(trendData) {
            console.log('🔥 FORM TREND DATA:', trendData);
            const chartContainer = document.getElementById('formScoreChart');
            
            if (!trendData || trendData.length === 0) {
                chartContainer.innerHTML = '<p class="text-gray-500 text-center">No form data available yet</p>';
                return;
            }
            
            // Extract scores
            const scores = trendData.map(point => point.form_score || 0);
            const maxScore = Math.max(...scores, 100);
            
            chartContainer.innerHTML = scores.map((score, idx) => {
                // Calculate height with minimum visibility
                const height = Math.max((score / maxScore) * 100, 30);
                
                return `
                    <div class="flex flex-col items-center justify-end h-full">
                        <div class="chart-bar" 
                             style="height: ${height}%;"
                             title="Week ${idx + 1}: ${score}%">
                        </div>
                        <span class="chart-bar-label mt-2 text-xs text-gray-600">W${idx + 1}</span>
                    </div>
                `;
            }).join('');
        }

        function updateWeeklyFrequencyChart(frequencyData) {
            console.log('🔥 WEEKLY FREQUENCY DATA:', frequencyData);
            const chartContainer = document.getElementById('workoutFrequencyChart');
            
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            const dayAbbrevs = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            
            // Initialize counts for all days
            const counts = new Array(7).fill(0);
            
            // Map data to days
            frequencyData.forEach(item => {
                const dayIndex = days.findIndex(d => d.toLowerCase() === item.day_name.toLowerCase());
                if (dayIndex >= 0) counts[dayIndex] = item.count || 0;
            });
            
            const maxCount = Math.max(...counts, 1);
            
            chartContainer.innerHTML = counts.map((count, idx) => {
                // Calculate height with minimum visibility
                const height = Math.max((count / maxCount) * 100, 30);
                
                return `
                    <div class="flex flex-col items-center justify-end h-full">
                        <div class="chart-bar" 
                             style="height: ${height}%;"
                             title="${days[idx]}: ${count} workout${count !== 1 ? 's' : ''}">
                        </div>
                        <span class="chart-bar-label mt-2 text-xs text-gray-600">${dayAbbrevs[idx]}</span>
                    </div>
                `;
            }).join('');
        }

        function updateRecentWorkouts(workouts) {
            console.log('🔥 RECENT WORKOUTS:', workouts);
            const container = document.getElementById('recentWorkoutsList');
            
            if (!workouts || workouts.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No workouts yet</p>
                        <a href="exercises.php" class="btn-primary" style="display: inline-block;">
                            Start Your First Workout
                        </a>
                    </div>
                `;
                return;
            }
            
            const getExerciseEmoji = (name) => {
                const emojis = {
                    'push-up': '💪', 'pushup': '💪',
                    'squat': '🦵',
                    'plank': '🧘',
                    'lunge': '🏃'
                };
                
                const lowerName = name.toLowerCase();
                for (const [key, emoji] of Object.entries(emojis)) {
                    if (lowerName.includes(key)) {
                        return emoji;
                    }
                }
                return '💪';
            };
            
            const getScoreColor = (score) => {
                if (score >= 90) return 'color: #22c55e;';
                if (score >= 75) return 'color: #f59e0b;';
                return 'color: #ef4444;';
            };
            
            container.innerHTML = workouts.map(workout => {
                const workoutDate = new Date(workout.workout_date);
                const formattedDate = isNaN(workoutDate.getTime()) 
                    ? workout.workout_date 
                    : workoutDate.toLocaleDateString();
                
                return `
                    <div class="workout-item">
                        <div class="workout-icon">${getExerciseEmoji(workout.exercise_name)}</div>
                        <div class="workout-info">
                            <p class="workout-name">${workout.exercise_name}</p>
                            <p class="workout-date">${formattedDate}</p>
                        </div>
                        <div>
                            <p class="workout-score" style="${getScoreColor(workout.form_score)}">
                                ${Math.round(workout.form_score)}%
                            </p>
                            <p class="workout-reps">${workout.reps_completed || 0} reps</p>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Debug function (from second file)
        window.debugProgress = async function() {
            const apiUrl = getApiPath();
            const user = JSON.parse(sessionStorage.getItem('user') || '{}');
            
            console.log('=== DEBUG PROGRESS ===');
            console.log('API URL:', apiUrl);
            console.log('User ID:', user.user_id);
            
            try {
                const response = await fetch(apiUrl);
                const data = await response.json();
                console.log('API Data:', data);
                
                if (data.success) {
                    console.log('Stats:', data.stats);
                    console.log('Form Trend:', data.form_trend);
                    console.log('Weekly Frequency:', data.weekly_frequency);
                    console.log('Recent Workouts:', data.recent_workouts);
                } else {
                    console.log('API Error:', data.error);
                }
            } catch (error) {
                console.log('Fetch Error:', error);
            }
            
            alert('Check browser console for debug info (F12)');
        };

        // Show error message
        function showError(message) {
            document.getElementById('formScoreChart').innerHTML = 
                `<p class="text-red-500 text-center p-4">${message}</p>`;
            document.getElementById('workoutFrequencyChart').innerHTML = 
                `<p class="text-red-500 text-center p-4">${message}</p>`;
            document.getElementById('recentWorkoutsList').innerHTML = 
                `<p class="text-red-500 text-center p-4">${message}</p>`;
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🏁 DOM loaded, starting dashboard...');
            loadProgressDashboard();
        });
    </script>

    <!-- Theme toggle script -->
    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>