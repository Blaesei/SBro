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
        
        #workoutVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror video */
        }
        
        /* IMPROVED: Larger, more visible feedback */
        .feedback-good { 
            color: #22c55e; 
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        .feedback-warning { 
            color: #f59e0b; 
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        .feedback-error { 
            color: #ef4444; 
            font-size: 1.5rem !important;
            font-weight: 700 !important;
        }
        
        /* IMPROVED: Position indicator much larger */
        .position-indicator {
            position: absolute;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-weight: bold;
            font-size: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .position-valid { 
            background: rgba(34, 197, 94, 0.9); 
            border: 3px solid #22c55e;
        }
        .position-invalid { 
            background: rgba(239, 68, 68, 0.9); 
            border: 3px solid #ef4444;
        }
        
        /* IMPROVED: Feedback panel items much larger */
        #feedbackContainer p,
        #suggestionsContainer p {
            font-size: 1.25rem !important;
            line-height: 1.8 !important;
            padding: 0.5rem 0 !important;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center space-x-2">
                <img id="logo" src="../assets/images/logo.png" alt="SpotBro Logo" class="h-12" onerror="this.style.display='none'">
            </div>
            <div class="flex items-center space-x-6">
                <a href="home.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="font-medium">Home</span>
                </a>
                                <a href="about.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                    <span class="font-medium">About Us</span>
                </a>
                <a href="faq.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M9 12h6"></path>
                        <path d="M9 16h6"></path>
                        <path d="M9 8h6"></path>
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                        <path d="M8 16H3v5"></path>
                    </svg>
                    <span class="font-medium">FAQ</span>
                </a>

                <a href="exercises.php" class="nav-item active flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="m6.5 6.5 11 11"></path>
                        <path d="m21 21-1-1"></path>
                        <path d="m3 21 9-9"></path>
                        <circle cx="10.5" cy="10.5" r="7.5"></circle>
                    </svg>
                    <span class="font-medium">Exercises</span>
                </a>
                <a href="progress.php" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                    <svg class="icon" viewBox="0 0 24 24">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                        <polyline points="16 7 22 7 22 13"></polyline>
                    </svg>
                    <span class="font-medium">Progress</span>
                </a>

            <button id="themeToggle" class="nav-item flex items-center space-x-2 px-4 py-2 rounded-lg">
                <svg class="icon" id="sunIcon" viewBox="0 0 24 24">
                     <circle cx="12" cy="12" r="5"></circle>
                     <line x1="12" y1="1" x2="12" y2="3"></line>
                     <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                 </svg>
                 <svg class="icon hidden" id="moonIcon" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>

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

    <!-- Exercise Library Page -->
    <div id="libraryPage" class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto p-6">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Exercise Library</h1>
                <p class="text-gray-600">Choose an exercise to get started with AI-powered form analysis</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="exerciseGrid"></div>
        </div>
    </div>

    <!-- Exercise Detail Page -->
    <div id="detailPage" class="hidden min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto p-6">
            <button onclick="showPage('library')" class="mb-6 text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <svg class="icon" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
                <span>Back to Library</span>
            </button>
            <div class="bg-white rounded-2xl p-8 shadow-sm mb-6" id="exerciseDetail"></div>
        </div>
    </div>

    <!-- Workout Active Page (ML Integration) -->
    <div id="workoutPage" class="hidden min-h-screen bg-gray-900">
        <div class="max-w-7xl mx-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Camera Feed -->
                <div class="lg:col-span-2">
                    <div class="bg-black rounded-2xl overflow-hidden relative" style="height: 600px;">
                        <video id="workoutVideo" autoplay playsinline></video>
                        
                        <!-- Position Indicator -->
                        <div id="positionIndicator" class="position-indicator position-invalid">
                            Setting up...
                        </div>
                        
                        <!-- Rep Counter -->
                        <div class="absolute top-4 right-4 bg-black bg-opacity-70 text-white px-6 py-4 rounded-lg">
                            <p class="text-sm text-gray-400">Reps</p>
                            <p class="text-5xl font-bold" id="repCount">0</p>
                        </div>
                        
                        <!-- Form Score -->
                        <div class="absolute bottom-4 left-4 bg-black bg-opacity-70 text-white px-6 py-4 rounded-lg">
                            <p class="text-sm text-gray-400">Form Score</p>
                            <p class="text-3xl font-bold" id="formScore">0%</p>
                        </div>
                    </div>
                </div>

                <!-- Feedback Panel -->
                <div class="space-y-4">
                    <!-- Exercise Info -->
                    <div class="bg-white rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2" id="currentExerciseName">Push-up</h3>
                        <p class="text-sm text-gray-600 mb-4">AI is analyzing your form in real-time</p>
                        
                        <!-- ML Backend Status -->
                        <div class="mb-4 p-3 bg-gray-100 rounded-lg">
                            <p class="text-xs text-gray-600">ML Backend Status:</p>
                            <p class="text-sm font-semibold" id="mlStatus">Connecting...</p>
                        </div>
                    </div>

                    <!-- Real-Time Feedback -->
                    <div class="bg-white rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Real-Time Feedback</h3>
                        <div id="feedbackContainer" class="space-y-3">
                            <p class="text-gray-500 text-sm">Start exercising to see feedback...</p>
                        </div>
                    </div>

                    <!-- Suggestions -->
                    <div class="bg-white rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">To Improve</h3>
                        <div id="suggestionsContainer" class="space-y-2">
                            <p class="text-gray-500 text-sm">Suggestions will appear here...</p>
                        </div>
                    </div>

                    <!-- Control Buttons -->
                    <div class="bg-white rounded-2xl p-6 space-y-3">
                        <button onclick="stopWorkout()" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-semibold transition">
                            Stop & Save Workout
                        </button>
                        <button onclick="resetReps()" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-xl font-semibold transition">
                            Reset Rep Counter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workout Summary Page -->
    <div id="summaryPage" class="hidden min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto p-6">
            <div class="bg-white rounded-2xl p-8 shadow-sm text-center">
                <div class="text-6xl mb-4">🎉</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Workout Complete!</h1>
                <p class="text-gray-600 mb-8">Great job on your session</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-50 rounded-xl p-6">
                        <p class="text-blue-600 font-semibold mb-2">Reps Completed</p>
                        <p class="text-4xl font-bold text-blue-700" id="summaryReps">0</p>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-6">
                        <p class="text-purple-600 font-semibold mb-2">Avg Form Score</p>
                        <p class="text-4xl font-bold text-purple-700" id="summaryFormScore">0%</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6">
                        <p class="text-green-600 font-semibold mb-2">Duration</p>
                        <p class="text-4xl font-bold text-green-700" id="summaryDuration">0:00</p>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <a href="progress.php" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                        View Progress
                    </a>
                    <button onclick="showPage('library')" class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-xl font-semibold hover:shadow-lg transition">
                        New Workout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include '../includes/footer.php'; ?>

    <!-- SCRIPTS -->
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
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition cursor-pointer" onclick="showExerciseDetail('${ex.id}')">
                    <div class="text-6xl mb-4 text-center">${ex.icon}</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">${ex.name}</h3>
                    <p class="text-sm text-gray-600 mb-4">${ex.description}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full difficulty-badge">${ex.difficulty}</span>
                        ${ex.hasML ? '<span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full">🤖 AI Ready</span>' : ''}
                    </div>
                </div>
            `).join('');
        }

        // Show exercise detail
        function showExerciseDetail(exerciseId) {
            const exercise = exercises.find(ex => ex.id === exerciseId);
            if (!exercise) return;

            document.getElementById('exerciseDetail').innerHTML = `
                <div class="text-6xl mb-4 text-center">${exercise.icon}</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4 text-center">${exercise.name}</h1>
                <p class="text-gray-600 text-center mb-6">${exercise.description}</p>
                
                ${exercise.hasML ? '<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-center"><p class="text-green-800 font-semibold">✨ AI-Powered Form Analysis Available</p></div>' : ''}
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="font-bold text-gray-900 mb-3">Target Muscles</h3>
                        <div class="flex flex-wrap gap-2">
                            ${exercise.muscles.map(m => `<span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">${m}</span>`).join('')}
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-3">Difficulty</h3>
                        <span class="bg-blue-100 text-blue-700 px-4 py-2 rounded-full">${exercise.difficulty}</span>
                    </div>
                </div>

                <button onclick="startExercise('${exercise.id}')" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 rounded-xl font-semibold text-lg hover:shadow-lg transition">
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
                } else {
                    statusEl.innerHTML = '<span class="text-red-600">✗ Backend Error</span>';
                }
            } catch (error) {
                console.error('ML Backend not responding:', error);
                statusEl.innerHTML = '<span class="text-red-600">✗ Not Connected</span><br><span class="text-xs">Start Python backend: python app.py</span>';
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
                    return `<p class="${className} text-sm font-semibold">${issue}</p>`;
                }).join('');
            }

            // Update suggestions
            const suggestionsContainer = document.getElementById('suggestionsContainer');
            if (result.suggestions && result.suggestions.length > 0) {
                suggestionsContainer.innerHTML = result.suggestions.slice(0, 3).map(suggestion => 
                    `<p class="text-sm text-gray-700">→ ${suggestion}</p>`
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

    <!-- theme toggle script -->
    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>
