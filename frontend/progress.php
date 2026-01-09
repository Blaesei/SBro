<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotBro - Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/lucide.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center space-x-2">
                <img src="images/logo.png" alt="SpotBro Logo" class="h-12">
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
                <a href="login.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" x2="9" y1="12" y2="12"></line>
                    </svg>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Form Score Trend -->
                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Form Score Trend</h2>
                    <div class="h-64 flex items-end justify-between space-x-2" id="formScoreChart"></div>
                </div>

                <!-- Workout Frequency -->
                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Weekly Workouts</h2>
                    <div class="h-64 flex items-end justify-between space-x-2" id="workoutFrequencyChart"></div>
                </div>
            </div>

            <!-- Exercise Breakdown -->
            <div class="bg-white rounded-2xl p-8 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Exercise Breakdown</h2>
                <div class="space-y-4" id="exerciseBreakdown"></div>
            </div>
        </div>
    </div>

    <script src="app.js"></script>
    <script>
        // Load progress dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProgressDashboard();
        });
    </script>
</body>
</html>