<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotBro - Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FIXED: Correct path to CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center space-x-2">
                <!-- FIXED: Correct path to logo -->
                <img src="../assets/images/logo.png" alt="SpotBro Logo" class="h-12" onerror="this.style.display='none'">
            </div>
            <div class="flex items-center space-x-6">
                <a href="home.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="font-medium">Home</span>
                </a>
                <a href="exercises.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="m6.5 6.5 11 11"></path>
                        <path d="m21 21-1-1"></path>
                        <path d="m3 21 9-9"></path>
                        <circle cx="10.5" cy="10.5" r="7.5"></circle>
                    </svg>
                    <span class="font-medium">Exercises</span>
                </a>
                <a href="progress.php" class="nav-item active flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                        <polyline points="16 7 22 7 22 13"></polyline>
                    </svg>
                    <span class="font-medium">Progress</span>
                </a>
                <a href="#" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50" id="logoutBtn">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" x2="9" y1="12" y2="12"></line>
                    </svg>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Progress Dashboard Page -->
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto p-6">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Your Progress</h1>
                <p class="text-gray-600">Track your improvement over time</p>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <p class="text-gray-600 text-sm mb-2">Total Workouts</p>
                    <p class="text-3xl font-bold text-blue-600" id="totalWorkouts">0</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <p class="text-gray-600 text-sm mb-2">Avg Form Score</p>
                    <p class="text-3xl font-bold text-purple-600" id="avgFormScore">0%</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <p class="text-gray-600 text-sm mb-2">Total Reps</p>
                    <p class="text-3xl font-bold text-green-600" id="totalReps">0</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <p class="text-gray-600 text-sm mb-2">Current Streak</p>
                    <p class="text-3xl font-bold text-orange-600" id="currentStreak">0 days</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Form Score Trend -->
                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Form Score Trend</h2>
                    <div class="h-64 flex items-end justify-between space-x-2" id="formScoreChart">
                        <p class="text-gray-500">Loading chart...</p>
                    </div>
                </div>

                <!-- Workout Frequency -->
                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Weekly Workouts</h2>
                    <div class="h-64 flex items-end justify-between space-x-2" id="workoutFrequencyChart">
                        <p class="text-gray-500">Loading chart...</p>
                    </div>
                </div>
            </div>

            <!-- Recent Workouts -->
            <div class="bg-white rounded-2xl p-8 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Workouts</h2>
                <div class="space-y-4" id="recentWorkoutsList">
                    <p class="text-gray-500">Loading workouts...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="text-center">
                <p class="text-gray-400 mb-2">
                    © 2025 SpotBro. All rights reserved.
                </p>
                <p class="text-gray-500 text-sm">
                    Built with ❤️ using AI and Machine Learning
                </p>
                <p class="text-gray-600 text-xs mt-2">
                    Created by Your Team | Powered by MediaPipe & TensorFlow
                </p>
            </div>
        </div>
    </footer>

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

        // Load progress dashboard
        async function loadProgressDashboard() {
            try {
                const apiUrl = getApiPath();
                console.log('Loading progress from:', apiUrl);
                
                const response = await fetch(apiUrl);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                
                console.log('Progress API Response:', data);
                
                // Validate data
                if (!validateWorkoutData(data)) {
                    throw new Error('Data validation failed');
                }
                
                if (data.success) {
                    updateDashboardStats(data.stats);
                    updateFormTrendChart(data.form_trend || []);
                    updateWeeklyFrequencyChart(data.weekly_frequency || []);
                    updateRecentWorkouts(data.recent_workouts || []);
                } else {
                    console.error('Failed to load progress:', data.error);
                    showError('Failed to load progress data');
                }
            } catch (error) {
                console.error('Error loading progress:', error);
                showError('Error loading progress data: ' + error.message);
            }
        }

        function updateDashboardStats(stats) {
            document.getElementById('totalWorkouts').textContent = stats.total_workouts || 0;
            document.getElementById('avgFormScore').textContent = Math.round(stats.avg_form_score || 0) + '%';
            document.getElementById('totalReps').textContent = stats.total_reps || 0;
            document.getElementById('currentStreak').textContent = (stats.current_streak_days || 0) + ' days';
        }

        function updateFormTrendChart(trendData) {
            const chartContainer = document.getElementById('formScoreChart');
            
            if (!trendData || trendData.length === 0) {
                chartContainer.innerHTML = '<p class="text-gray-500 text-center">No data available yet</p>';
                return;
            }
            
            const maxScore = Math.max(...trendData.map(d => d.form_score || 0), 100);
            
            chartContainer.innerHTML = trendData.map((point, idx) => {
                const score = point.form_score || 0;
                const height = (score / maxScore) * 100;
                return `
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gradient-to-t from-blue-500 to-purple-600 rounded-t-lg transition hover:opacity-80" 
                             style="height: ${height}%;"
                             title="Score: ${score}%">
                        </div>
                        <span class="text-xs text-gray-500 mt-2">W${idx + 1}</span>
                    </div>
                `;
            }).join('');
        }

        function updateWeeklyFrequencyChart(frequencyData) {
            const chartContainer = document.getElementById('workoutFrequencyChart');
            
            const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const counts = new Array(7).fill(0);
            
            // Map data to days
            frequencyData.forEach(item => {
                const dayIndex = days.findIndex(d => item.day_name.startsWith(d));
                if (dayIndex >= 0) counts[dayIndex] = item.count;
            });
            
            const maxCount = Math.max(...counts, 1);
            
            chartContainer.innerHTML = counts.map((count, idx) => {
                const height = (count / maxCount) * 100;
                return `
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gradient-to-t from-green-500 to-green-400 rounded-t-lg transition hover:opacity-80" 
                             style="height: ${height}%;"
                             title="${count} workouts">
                        </div>
                        <span class="text-xs text-gray-500 mt-2">${days[idx]}</span>
                    </div>
                `;
            }).join('');
        }

        function updateRecentWorkouts(workouts) {
            const container = document.getElementById('recentWorkoutsList');
            
            if (!workouts || workouts.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No workouts yet</p>
                        <a href="exercises.php" class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-lg">
                            Start Your First Workout
                        </a>
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
                if (score >= 90) return 'text-green-600';
                if (score >= 75) return 'text-yellow-600';
                return 'text-red-600';
            };
            
            container.innerHTML = workouts.map(workout => `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div class="flex items-center space-x-4">
                        <div class="text-4xl">${getExerciseEmoji(workout.exercise_name)}</div>
                        <div>
                            <p class="font-semibold text-gray-900">${workout.exercise_name}</p>
                            <p class="text-sm text-gray-500">${new Date(workout.workout_date).toLocaleDateString()}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold ${getScoreColor(workout.form_score)}">
                            ${Math.round(workout.form_score)}%
                        </p>
                        <p class="text-sm text-gray-500">${workout.reps_completed} reps</p>
                    </div>
                </div>
            `).join('');
        }

        function showError(message) {
            document.getElementById('formScoreChart').innerHTML = 
                `<p class="text-red-500 text-center">${message}</p>`;
            document.getElementById('workoutFrequencyChart').innerHTML = 
                `<p class="text-red-500 text-center">${message}</p>`;
            document.getElementById('recentWorkoutsList').innerHTML = 
                `<p class="text-red-500 text-center">${message}</p>`;
        }

        // Load data on page load
        loadProgressDashboard();
    </script>
</body>
</html>