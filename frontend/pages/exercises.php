<?php
$current_page = 'exercises';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotBro - Exercises</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <style>
        /* Additional styles for improved visibility */
        #workoutVideo {
            transform: scaleX(-1); /* Mirror video */
        }
        
        /* IMPROVED: Larger, more visible feedback */
        .feedback-good { 
            color: #22c55e !important; 
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        .feedback-warning { 
            color: #f59e0b !important; 
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        .feedback-error { 
            color: #ef4444 !important; 
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        
        /* IMPROVED: Position indicator much larger */
        .position-indicator {
            position: absolute !important;
            top: 1rem !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: rgba(0, 0, 0, 0.85) !important;
            color: white !important;
            padding: 1rem 2rem !important;
            border-radius: 1rem !important;
            font-weight: bold !important;
            font-size: 1.5rem !important;
            text-align: center !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3) !important;
            z-index: 10 !important;
        }
        .position-valid { 
            background: rgba(34, 197, 94, 0.9) !important; 
            border: 3px solid #22c55e !important;
        }
        .position-invalid { 
            background: rgba(239, 68, 68, 0.9) !important; 
            border: 3px solid #ef4444 !important;
        }
        
        /* IMPROVED: Feedback panel items much larger */
        #feedbackContainer p,
        #suggestionsContainer p {
            font-size: 1.25rem !important;
            line-height: 1.8 !important;
            padding: 0.5rem 0 !important;
        }

        /* Ensure workout page is properly hidden */
        .hidden {
            display: none !important;
        }

        /* Better rep counter visibility */
        .rep-count {
            font-size: 3rem !important;
            font-weight: 800 !important;
        }

        /* Better form score visibility */
        .form-score-value {
            font-size: 2rem !important;
            font-weight: 700 !important;
        }
    </style>
</head>
<body class="exercises-page">
    <!-- Navigation -->
    <?php include '../includes/header.php'; ?>

    <!-- Exercise Library Page -->
    <div id="libraryPage" class="library-page">
        <div class="main-content">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Exercise Library</h1>
            <p class="text-gray-600">Choose an exercise to get started with AI-powered form analysis</p>
            
            <div class="exercise-grid" id="exerciseGrid"></div>
        </div>
    </div>

    <!-- Exercise Detail Page -->
    <div id="detailPage" class="detail-page hidden">
        <div class="main-content">
            <a href="javascript:void(0);" onclick="showPage('library')" class="back-button">
                <svg class="icon" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
                <span>Back to Library</span>
            </a>
            
            <div class="exercise-detail-card" id="exerciseDetail"></div>
        </div>
    </div>

    <!-- Workout Active Page (ML Integration) -->
    <div id="workoutPage" class="workout-page hidden">
        <div class="workout-container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Camera Feed -->
                <div class="lg:col-span-2">
                    <div class="camera-container">
                        <video id="workoutVideo" class="camera-feed" autoplay playsinline></video>
                        
                        <!-- Position Indicator -->
                        <div id="positionIndicator" class="position-indicator position-invalid">
                            Setting up...
                        </div>
                        
                        <!-- Rep Counter -->
                        <div class="rep-counter">
                            <p class="rep-counter-label">Reps</p>
                            <p class="rep-count" id="repCount">0</p>
                        </div>
                        
                        <!-- Form Score -->
                        <div class="form-score">
                            <p class="form-score-label">Form Score</p>
                            <p class="form-score-value" id="formScore">0%</p>
                        </div>
                    </div>
                </div>

                <!-- Feedback Panel -->
                <div class="space-y-4">
                    <!-- Exercise Info -->
                    <div class="feedback-panel">
                        <h3 id="currentExerciseName">Push-up</h3>
                        <p class="text-sm text-gray-600 mb-4">AI is analyzing your form in real-time</p>
                        
                        <!-- ML Backend Status -->
                        <div class="ml-status">
                            <p class="ml-status-label">ML Backend Status:</p>
                            <p class="ml-status-value" id="mlStatus">Connecting...</p>
                        </div>
                    </div>

                    <!-- Real-Time Feedback -->
                    <div class="feedback-panel">
                        <h3>Real-Time Feedback</h3>
                        <div id="feedbackContainer" class="space-y-3">
                            <p class="text-gray-500 text-sm">Start exercising to see feedback...</p>
                        </div>
                    </div>

                    <!-- Suggestions -->
                    <div class="feedback-panel">
                        <h3>To Improve</h3>
                        <div id="suggestionsContainer" class="space-y-2">
                            <p class="text-gray-500 text-sm">Suggestions will appear here...</p>
                        </div>
                    </div>

                    <!-- Control Buttons -->
                    <div class="control-buttons">
                        <button onclick="stopWorkout()" class="control-button stop">
                            Stop & Save Workout
                        </button>
                        <button onclick="resetReps()" class="control-button reset">
                            Reset Rep Counter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workout Summary Page -->
    <div id="summaryPage" class="summary-page hidden">
        <div class="summary-container">
            <div class="summary-card">
                <div class="summary-emoji">🎉</div>
                <h1 class="summary-title">Workout Complete!</h1>
                <p class="summary-subtitle">Great job on your session</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <p class="stat-label">Reps Completed</p>
                        <p class="stat-value" id="summaryReps">0</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Avg Form Score</p>
                        <p class="stat-value" id="summaryFormScore">0%</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Duration</p>
                        <p class="stat-value" id="summaryDuration">0:00</p>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="progress.php" class="action-button secondary">
                        View Progress
                    </a>
                    <button onclick="showPage('library')" class="action-button primary">
                        New Workout
                    </button>
                </div>
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

        // ML Backend Configuration
        const ML_API_URL = 'http://localhost:5000/api';
        
        // Workout State
        let currentWorkout = {
            exercise: null,
            startTime: null,
            repCount: 0,
            formScores: [],
            isActive: false,
            videoStream: null,
            analysisInterval: null,
            canvas: null
        };

        // Exercise data
        const exercises = [
            {
                id: 'pushup',
                name: 'Push-up',
                icon: '💪',
                difficulty: 'Beginner',
                muscles: ['Chest', 'Triceps', 'Shoulders'],
                description: 'Classic upper body exercise with AI form analysis.',
                hasML: true
            },
            {
                id: 'squat',
                name: 'Squat',
                icon: '🦵',
                difficulty: 'Beginner',
                muscles: ['Quadriceps', 'Glutes', 'Hamstrings'],
                description: 'Fundamental lower body exercise.',
                hasML: false
            },
            {
                id: 'plank',
                name: 'Plank',
                icon: '🧘',
                difficulty: 'Beginner',
                muscles: ['Core', 'Shoulders', 'Back'],
                description: 'Isometric core exercise.',
                hasML: false
            },
            {
                id: 'lunge',
                name: 'Lunge',
                icon: '🏃',
                difficulty: 'Intermediate',
                muscles: ['Quadriceps', 'Glutes', 'Hamstrings'],
                description: 'Unilateral leg exercise.',
                hasML: false
            }
        ];

        // Render exercise cards
        function renderExercises() {
            const grid = document.getElementById('exerciseGrid');
            grid.innerHTML = exercises.map(ex => `
                <div class="exercise-card" onclick="showExerciseDetail('${ex.id}')">
                    <div class="exercise-icon">${ex.icon}</div>
                    <h3 class="exercise-name">${ex.name}</h3>
                    <p class="exercise-description">${ex.description}</p>
                    <div class="exercise-meta">
                        <span class="difficulty-badge">${ex.difficulty}</span>
                        ${ex.hasML ? '<span class="ai-badge">🤖 AI Ready</span>' : ''}
                    </div>
                </div>
            `).join('');
        }

        // Show exercise detail
        function showExerciseDetail(exerciseId) {
            const exercise = exercises.find(ex => ex.id === exerciseId);
            if (!exercise) return;

            document.getElementById('exerciseDetail').innerHTML = `
                <div class="detail-icon">${exercise.icon}</div>
                <h1 class="detail-name">${exercise.name}</h1>
                <p class="detail-description">${exercise.description}</p>
                
                ${exercise.hasML ? 
                    '<div class="ai-available-badge">✨ AI-Powered Form Analysis Available</div>' : 
                    ''
                }
                
                <div class="mb-8">
                    <h3 class="font-bold text-gray-900 mb-3">Target Muscles</h3>
                    <div class="muscle-tags">
                        ${exercise.muscles.map(m => `<span class="muscle-tag">${m}</span>`).join('')}
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-gray-900 mb-3">Difficulty</h3>
                    <span class="difficulty-badge" style="font-size: 1rem;">${exercise.difficulty}</span>
                </div>

                <button onclick="startExercise('${exercise.id}')" class="start-button">
                    ${exercise.hasML ? '🤖 Start with AI Analysis' : 'Start Exercise'}
                </button>
            `;

            showPage('detail');
        }

        // Start exercise
        async function startExercise(exerciseId) {
            const exercise = exercises.find(ex => ex.id === exerciseId);
            if (!exercise) return;

            if (!exercise.hasML) {
                alert('AI analysis coming soon for this exercise!');
                return;
            }

            currentWorkout.exercise = exerciseId;
            currentWorkout.startTime = Date.now();
            currentWorkout.isActive = true;
            
            showPage('workout');
            
            // Check ML backend
            await checkMLBackend();
            
            // Start camera
            await startCamera();
            
            // Start ML analysis
            startMLAnalysis();
        }

        // Check ML backend status
        async function checkMLBackend() {
            const statusEl = document.getElementById('mlStatus');
            try {
                const response = await fetch(`${ML_API_URL}/health`);
                const data = await response.json();
                
                if (data.status === 'ok') {
                    statusEl.innerHTML = '<span class="text-green-600">✓ Connected & Ready</span>';
                    statusEl.className = 'ml-status-value';
                } else {
                    statusEl.innerHTML = '<span class="text-red-600">✗ Backend Error</span>';
                    statusEl.className = 'ml-status-value error';
                }
            } catch (error) {
                console.error('ML Backend not responding:', error);
                statusEl.innerHTML = '<span class="text-red-600">✗ Not Connected</span><br><span class="text-xs">Start Python backend: python app.py</span>';
                statusEl.className = 'ml-status-value error';
            }
        }

        // Start camera
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 640, height: 480, facingMode: 'user' }
                });
                
                const videoElement = document.getElementById('workoutVideo');
                videoElement.srcObject = stream;
                currentWorkout.videoStream = stream;
                
                // Create canvas for frame capture
                currentWorkout.canvas = document.createElement('canvas');
                currentWorkout.canvas.width = 640;
                currentWorkout.canvas.height = 480;
                
                console.log('Camera started successfully');
            } catch (error) {
                console.error('Camera error:', error);
                alert('Could not access camera. Please check permissions.');
            }
        }

        // Start ML analysis
        function startMLAnalysis() {
            // Reset ML backend counter
            fetch(`${ML_API_URL}/reset`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ exercise: currentWorkout.exercise })
            }).catch(err => console.error('Reset error:', err));

            // FIXED: Faster analysis rate (5 FPS instead of 2 FPS)
            currentWorkout.analysisInterval = setInterval(analyzeFrame, 200);
        }

        // Analyze single frame
        async function analyzeFrame() {
            if (!currentWorkout.isActive) return;

            const video = document.getElementById('workoutVideo');
            const canvas = currentWorkout.canvas;
            
            if (!video || !canvas || video.readyState !== 4) return;

            // Capture frame
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Convert to base64
            const base64Image = canvas.toDataURL('image/jpeg', 0.8);

            try {
                // Send to ML backend
                const response = await fetch(`${ML_API_URL}/analyze`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        image: base64Image,
                        exercise: currentWorkout.exercise
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    updateWorkoutUI(result);
                }
            } catch (error) {
                console.error('Analysis error:', error);
            }
        }

        // Update UI with ML results
        function updateWorkoutUI(result) {
            // Update rep count
            document.getElementById('repCount').textContent = result.rep_count || 0;
            currentWorkout.repCount = result.rep_count || 0;

            // Update form score
            const formScore = result.form_score || 0;
            document.getElementById('formScore').textContent = Math.round(formScore) + '%';
            if (formScore > 0) {
                currentWorkout.formScores.push(formScore);
            }

            // Update position indicator
            const posIndicator = document.getElementById('positionIndicator');
            if (result.position_valid) {
                posIndicator.className = 'position-indicator position-valid';
                posIndicator.textContent = '✓ In Position - Counting Reps';
            } else {
                posIndicator.className = 'position-indicator position-invalid';
                posIndicator.textContent = result.position_msg || '✗ Get into position';
            }

            // Update feedback
            const feedbackContainer = document.getElementById('feedbackContainer');
            if (result.issues && result.issues.length > 0) {
                feedbackContainer.innerHTML = result.issues.slice(0, 4).map(issue => {
                    const className = issue.includes('✓') ? 'feedback-good' : 
                                    issue.includes('⚠') ? 'feedback-warning' : 'feedback-error';
                    return `<p class="${className}">${issue}</p>`;
                }).join('');
            }

            // Update suggestions
            const suggestionsContainer = document.getElementById('suggestionsContainer');
            if (result.suggestions && result.suggestions.length > 0) {
                suggestionsContainer.innerHTML = result.suggestions.slice(0, 3).map(suggestion => 
                    `<p class="suggestion-item">${suggestion}</p>`
                ).join('');
            }
        }

        // Reset reps
        async function resetReps() {
            try {
                await fetch(`${ML_API_URL}/reset`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ exercise: currentWorkout.exercise })
                });
                
                currentWorkout.repCount = 0;
                currentWorkout.formScores = [];
                document.getElementById('repCount').textContent = '0';
                document.getElementById('formScore').textContent = '0%';
                
                console.log('Rep counter reset');
            } catch (error) {
                console.error('Reset error:', error);
            }
        }

        // Stop workout
        async function stopWorkout() {
            currentWorkout.isActive = false;
            
            // Stop camera
            if (currentWorkout.videoStream) {
                currentWorkout.videoStream.getTracks().forEach(track => track.stop());
            }
            
            // Stop analysis
            if (currentWorkout.analysisInterval) {
                clearInterval(currentWorkout.analysisInterval);
            }

            // Calculate stats
            const duration = Math.floor((Date.now() - currentWorkout.startTime) / 1000);
            const avgFormScore = currentWorkout.formScores.length > 0 
                ? currentWorkout.formScores.reduce((a, b) => a + b, 0) / currentWorkout.formScores.length 
                : 0;

            // Save to database
            await saveWorkout(avgFormScore, duration);

            // Show summary
            showSummary(avgFormScore, duration);
        }

        // Save workout to database
        async function saveWorkout(avgFormScore, duration) {
            try {
                const response = await fetch('../../backend/api/workouts/save_workout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        exercise_name: 'Push-up',
                        form_score: avgFormScore.toFixed(2),
                        reps_completed: currentWorkout.repCount,
                        duration_seconds: duration
                    })
                });

                const data = await response.json();
                if (data.success) {
                    console.log('Workout saved:', data.session_id);
                } else {
                    console.error('Save error:', data.error);
                }
            } catch (error) {
                console.error('Save workout error:', error);
            }
        }

        // Show summary
        function showSummary(avgFormScore, duration) {
            document.getElementById('summaryReps').textContent = currentWorkout.repCount;
            document.getElementById('summaryFormScore').textContent = Math.round(avgFormScore) + '%';
            document.getElementById('summaryDuration').textContent = 
                `${Math.floor(duration / 60)}:${(duration % 60).toString().padStart(2, '0')}`;
            
            showPage('summary');
        }

        // Page navigation
        function showPage(page) {
            ['libraryPage', 'detailPage', 'workoutPage', 'summaryPage'].forEach(p => {
                document.getElementById(p).classList.add('hidden');
            });
            
            document.getElementById(page + 'Page').classList.remove('hidden');
        }

        // Initialize
        renderExercises();
        showPage('library');
    </script>

    <!-- Theme toggle script -->
    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>