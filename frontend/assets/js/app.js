// SpotBro Frontend Integration
// Connects to both PHP backend and ML Flask API

// API Configuration
const API_CONFIG = {
    PHP_BASE: '/sbro/backend/api',  // PHP backend for database
    ML_BASE: 'http://localhost:5000/api'  // Flask ML backend
};

// Current workout state
let currentWorkout = {
    exercise: null,
    startTime: null,
    repCount: 0,
    formScores: [],
    repDetails: [],
    isActive: false,
    videoStream: null,
    canvas: null,
    analysisInterval: null
};

// ==========================================
// AUTHENTICATION FUNCTIONS
// ==========================================

async function handleLogin(event) {
    event.preventDefault();
    
    const email = document.querySelector('input[type="email"]').value;
    const password = document.querySelector('input[type="password"]').value;
    
    try {
        const response = await fetch(`${API_CONFIG.PHP_BASE}/auth/login.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Store user info in sessionStorage
            sessionStorage.setItem('user', JSON.stringify(data.user));
            // Redirect to home
            window.location.href = 'home.php';
        } else {
            alert(data.error || 'Login failed');
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('Network error. Please try again.');
    }
}

async function handleSignup(event) {
    event.preventDefault();
    
    const fullName = document.querySelector('input[type="text"]').value;
    const email = document.querySelector('input[type="email"]').value;
    const password = document.querySelectorAll('input[type="password"]')[0].value;
    const confirmPassword = document.querySelectorAll('input[type="password"]')[1].value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    try {
        const response = await fetch(`${API_CONFIG.PHP_BASE}/auth/signup.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ full_name: fullName, email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Registration successful! Redirecting to login...');
            window.location.href = 'login.php';
        } else {
            alert(data.error || 'Registration failed');
        }
    } catch (error) {
        console.error('Signup error:', error);
        alert('Network error. Please try again.');
    }
}

// ==========================================
// WORKOUT CAMERA FUNCTIONS
// ==========================================

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        });
        
        const videoElement = document.getElementById('workoutVideo');
        if (videoElement) {
            videoElement.srcObject = stream;
            currentWorkout.videoStream = stream;
            
            // Create canvas for frame capture
            currentWorkout.canvas = document.createElement('canvas');
            currentWorkout.canvas.width = 640;
            currentWorkout.canvas.height = 480;
            
            return true;
        }
        return false;
    } catch (error) {
        console.error('Camera error:', error);
        alert('Could not access camera. Please check permissions.');
        return false;
    }
}

function stopCamera() {
    if (currentWorkout.videoStream) {
        currentWorkout.videoStream.getTracks().forEach(track => track.stop());
        currentWorkout.videoStream = null;
    }
    if (currentWorkout.analysisInterval) {
        clearInterval(currentWorkout.analysisInterval);
        currentWorkout.analysisInterval = null;
    }
}

async function captureAndAnalyzeFrame() {
    if (!currentWorkout.isActive) return;
    
    const videoElement = document.getElementById('workoutVideo');
    const canvas = currentWorkout.canvas;
    
    if (!videoElement || !canvas) return;
    
    // Draw current video frame to canvas
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
    
    // Convert to base64
    const base64Image = canvas.toDataURL('image/jpeg', 0.8);
    
    try {
        // Send to ML backend
        const response = await fetch(`${API_CONFIG.ML_BASE}/analyze`, {
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
        } else {
            console.error('Analysis error:', result.error);
        }
    } catch (error) {
        console.error('ML API error:', error);
    }
}

function updateWorkoutUI(result) {
    // Update rep count
    const repCountEl = document.getElementById('repCount');
    if (repCountEl) {
        repCountEl.textContent = result.rep_count;
        currentWorkout.repCount = result.rep_count;
    }
    
    // Update feedback
    const feedbackEl = document.getElementById('feedback');
    if (feedbackEl) {
        feedbackEl.textContent = result.feedback;
    }
    
    // Update form score
    const formScoreEl = document.getElementById('formScore');
    if (formScoreEl) {
        formScoreEl.textContent = `${result.form_score}%`;
        currentWorkout.formScores.push(result.form_score);
    }
    
    // Update issues list
    const issuesListEl = document.getElementById('issuesList');
    if (issuesListEl && result.issues) {
        issuesListEl.innerHTML = '';
        result.issues.slice(0, 4).forEach(issue => {
            const li = document.createElement('li');
            li.textContent = issue;
            li.className = issue.includes('✓') ? 'text-green-600' : 'text-yellow-600';
            issuesListEl.appendChild(li);
        });
    }
    
    // Update suggestions
    const suggestionsListEl = document.getElementById('suggestionsList');
    if (suggestionsListEl && result.suggestions) {
        suggestionsListEl.innerHTML = '';
        result.suggestions.slice(0, 3).forEach(suggestion => {
            if (suggestion) {
                const li = document.createElement('li');
                li.textContent = suggestion;
                suggestionsListEl.appendChild(li);
            }
        });
    }
    
    // Store rep details when rep completes
    if (result.rep_count > currentWorkout.repDetails.length) {
        currentWorkout.repDetails.push({
            rep_number: result.rep_count,
            form_score: result.form_score,
            feedback: result.issues
        });
    }
}

async function startWorkout() {
    currentWorkout.isActive = true;
    currentWorkout.startTime = Date.now();
    currentWorkout.repCount = 0;
    currentWorkout.formScores = [];
    currentWorkout.repDetails = [];
    
    // Start camera
    const cameraStarted = await startCamera();
    if (!cameraStarted) {
        currentWorkout.isActive = false;
        return;
    }
    
    // Reset ML backend counter
    try {
        await fetch(`${API_CONFIG.ML_BASE}/reset`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ exercise: currentWorkout.exercise })
        });
    } catch (error) {
        console.error('Reset error:', error);
    }
    
    // Start analysis loop (every 500ms)
    currentWorkout.analysisInterval = setInterval(captureAndAnalyzeFrame, 500);
    
    console.log('Workout started:', currentWorkout.exercise);
}

async function stopWorkout() {
    currentWorkout.isActive = false;
    stopCamera();
    
    // Calculate workout stats
    const duration = Math.floor((Date.now() - currentWorkout.startTime) / 1000);
    const avgFormScore = currentWorkout.formScores.length > 0
        ? currentWorkout.formScores.reduce((a, b) => a + b, 0) / currentWorkout.formScores.length
        : 0;
    
    // Save to database
    try {
        const response = await fetch(`${API_CONFIG.PHP_BASE}/workouts/save_workout.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                exercise_name: currentWorkout.exercise,
                form_score: avgFormScore.toFixed(2),
                reps_completed: currentWorkout.repCount,
                duration_seconds: duration,
                rep_details: JSON.stringify(currentWorkout.repDetails)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Workout saved:', data.session_id);
            // Show summary page
            showWorkoutSummary(avgFormScore, duration);
        } else {
            console.error('Save error:', data.error);
            alert('Failed to save workout');
        }
    } catch (error) {
        console.error('Save workout error:', error);
        alert('Network error. Workout not saved.');
    }
}

function showWorkoutSummary(avgFormScore, duration) {
    // Update summary page with workout data
    const summaryPage = document.getElementById('summaryPage');
    if (!summaryPage) return;
    
    // Update stats
    document.querySelector('#summaryPage .text-4xl.font-bold.text-blue-700').textContent = 
        currentWorkout.repCount;
    document.querySelector('#summaryPage .text-4xl.font-bold.text-purple-700').textContent = 
        `${Math.round(avgFormScore)}%`;
    document.querySelector('#summaryPage .text-4xl.font-bold.text-green-700').textContent = 
        `${Math.floor(duration / 60)}:${(duration % 60).toString().padStart(2, '0')}`;
    
    // Update rep breakdown
    const repBreakdown = document.getElementById('repBreakdown');
    if (repBreakdown) {
        repBreakdown.innerHTML = '';
        currentWorkout.repDetails.forEach((rep, idx) => {
            const colorClass = rep.form_score >= 90 ? 'bg-green-500' : 
                              rep.form_score >= 80 ? 'bg-yellow-500' : 'bg-red-500';
            
            const repRow = `
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Rep ${idx + 1}</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="${colorClass} h-2 rounded-full" style="width: ${rep.form_score}%"></div>
                        </div>
                        <span class="font-semibold text-gray-900 w-12">${Math.round(rep.form_score)}%</span>
                    </div>
                </div>
            `;
            repBreakdown.innerHTML += repRow;
        });
    }
    
    // Show summary page
    showPage('summary');
}

// ==========================================
// PROGRESS DASHBOARD
// ==========================================

async function loadProgressDashboard() {
    try {
        const response = await fetch(`${API_CONFIG.PHP_BASE}/progress/get_progress.php`);
        const data = await response.json();
        
        if (data.success) {
            updateDashboardStats(data.stats);
            updateFormTrendChart(data.form_trend);
            updateWeeklyFrequencyChart(data.weekly_frequency);
            updateRecentWorkouts(data.recent_workouts);
        } else {
            console.error('Progress load error:', data.error);
        }
    } catch (error) {
        console.error('Load progress error:', error);
    }
}

function updateDashboardStats(stats) {
    // Update stat cards on home page
    const totalWorkoutsEl = document.querySelector('.stat-card:nth-child(1) .text-3xl');
    if (totalWorkoutsEl) totalWorkoutsEl.textContent = stats.total_workouts || 0;
    
    const avgScoreEl = document.querySelector('.stat-card:nth-child(2) .text-3xl');
    if (avgScoreEl) avgScoreEl.textContent = `${Math.round(stats.avg_form_score || 0)}%`;
    
    const streakEl = document.querySelector('.stat-card:nth-child(3) .text-3xl');
    if (streakEl) streakEl.textContent = stats.current_streak_days || 0;
}

function updateFormTrendChart(trendData) {
    const chartContainer = document.getElementById('formScoreChart');
    if (!chartContainer || !trendData) return;
    
    chartContainer.innerHTML = '';
    
    trendData.forEach((point, idx) => {
        const score = point.form_score || 0;
        const bar = `
            <div class="flex-1 flex flex-col items-center">
                <div class="chart-bar w-full rounded-t-lg" 
                     style="height: ${score}%; background: linear-gradient(to top, #667eea, #764ba2);">
                </div>
                <span class="text-xs text-gray-500 mt-2">W${idx + 1}</span>
            </div>
        `;
        chartContainer.innerHTML += bar;
    });
}

function updateWeeklyFrequencyChart(frequencyData) {
    const chartContainer = document.getElementById('workoutFrequencyChart');
    if (!chartContainer || !frequencyData) return;
    
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const counts = new Array(7).fill(0);
    
    // Map data to days
    frequencyData.forEach(item => {
        const dayIndex = days.indexOf(item.day_name.substring(0, 3));
        if (dayIndex >= 0) counts[dayIndex] = item.count;
    });
    
    chartContainer.innerHTML = '';
    
    counts.forEach((count, idx) => {
        const maxCount = Math.max(...counts, 1);
        const bar = `
            <div class="flex-1 flex flex-col items-center">
                <div class="chart-bar w-full rounded-t-lg" 
                     style="height: ${(count / maxCount) * 100}%; background: linear-gradient(to top, #22c55e, #4ade80);">
                </div>
                <span class="text-xs text-gray-500 mt-2">${days[idx]}</span>
            </div>
        `;
        chartContainer.innerHTML += bar;
    });
}

function updateRecentWorkouts(workouts) {
    const container = document.getElementById('recentWorkouts');
    if (!container || !workouts) return;
    
    container.innerHTML = '';
    
    workouts.forEach(workout => {
        const card = `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="icon text-blue-600" viewBox="0 0 24 24">
                            <path d="m6.5 6.5 11 11"></path>
                            <circle cx="10.5" cy="10.5" r="7.5"></circle>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">${workout.exercise_name}</p>
                        <p class="text-sm text-gray-500">${workout.workout_date}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-600">${Math.round(workout.form_score)}%</p>
                    <p class="text-sm text-gray-500">${workout.reps_completed} reps</p>
                </div>
            </div>
        `;
        container.innerHTML += card;
    });
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    // Attach login handler
    const loginForm = document.querySelector('#loginPage form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Attach signup handler
    const signupForm = document.querySelector('#signupPage form');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
    
    // Load progress data if on progress page
    if (document.getElementById('progressPage') && 
        !document.getElementById('progressPage').classList.contains('hidden')) {
        loadProgressDashboard();
    }
    
    // Load dashboard data if on home page
    if (document.getElementById('dashboardPage') && 
        !document.getElementById('dashboardPage').classList.contains('hidden')) {
        loadProgressDashboard();
    }
    
    console.log('SpotBro Frontend Initialized');
});