// SpotBro App - Main JavaScript File

// Sample Data
const exercises = [
    { id: 1, name: 'Squat', difficulty: 'Beginner', muscles: 'Legs, Glutes, Core', image: '🏋️', reps: 12 },
    { id: 2, name: 'Push-up', difficulty: 'Beginner', muscles: 'Chest, Triceps, Shoulders', image: '💪', reps: 15 },
    { id: 3, name: 'Plank', difficulty: 'Intermediate', muscles: 'Core, Shoulders', image: '🧘', reps: 30 },
    { id: 4, name: 'Lunge', difficulty: 'Beginner', muscles: 'Legs, Glutes', image: '🦵', reps: 10 },
];

const recentWorkouts = [
    { date: '2025-11-20', exercise: 'Squat', score: 87, reps: 12 },
    { date: '2025-11-19', exercise: 'Push-up', score: 92, reps: 15 },
    { date: '2025-11-18', exercise: 'Plank', score: 78, reps: 30 },
];

let selectedExercise = null;

// Authentication
function login() {
    // Hide login page
    document.getElementById('loginPage').classList.add('hidden');
    
    // Show navigation
    document.getElementById('navigation').classList.remove('hidden');
    
    // Show dashboard
    showPage('dashboard');
}

function logout() {
    // Hide all pages that exist
    const pages = ['dashboardPage', 'libraryPage', 'detailPage', 'cameraSetupPage', 'workoutActivePage', 'summaryPage', 'progressPage'];
    pages.forEach(page => {
        const element = document.getElementById(page);
        if (element) {
            element.classList.add('hidden');
        }
    });

    // Hide navigation if it exists
    const navigation = document.getElementById('navigation');
    if (navigation) {
        navigation.classList.add('hidden');
    }

    // Show login page if it exists
    const loginPage = document.getElementById('loginPage');
    if (loginPage) {
        loginPage.classList.remove('hidden');
    }
}

// Page Navigation
function showPage(pageName) {
    // Hide all pages that exist in the current document
    const pages = ['dashboardPage', 'libraryPage', 'detailPage', 'cameraSetupPage', 'workoutActivePage', 'summaryPage', 'progressPage'];
    pages.forEach(page => {
        const element = document.getElementById(page);
        if (element) {
            element.classList.add('hidden');
        }
    });

    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });

    // Show selected page
    const pageMap = {
        'dashboard': 'dashboardPage',
        'library': 'libraryPage',
        'detail': 'detailPage',
        'cameraSetup': 'cameraSetupPage',
        'workoutActive': 'workoutActivePage',
        'summary': 'summaryPage',
        'progress': 'progressPage'
    };

    const targetPage = document.getElementById(pageMap[pageName]);
    if (targetPage) {
        targetPage.classList.remove('hidden');
    }

    // Set active nav item
    if (pageName === 'dashboard') {
        const navDashboard = document.getElementById('navDashboard');
        if (navDashboard) navDashboard.classList.add('active');
    } else if (pageName === 'library') {
        const navLibrary = document.getElementById('navLibrary');
        if (navLibrary) navLibrary.classList.add('active');
    } else if (pageName === 'progress') {
        const navProgress = document.getElementById('navProgress');
        if (navProgress) navProgress.classList.add('active');
    }

    // Load page content
    if (pageName === 'dashboard') {
        loadDashboard();
    } else if (pageName === 'library') {
        loadExerciseLibrary();
    } else if (pageName === 'detail') {
        loadExerciseDetail();
    } else if (pageName === 'summary') {
        loadWorkoutSummary();
    } else if (pageName === 'progress') {
        loadProgressDashboard();
    }
}

// Dashboard
function loadDashboard() {
    const container = document.getElementById('recentWorkouts');
    container.innerHTML = '';
    
    recentWorkouts.forEach(workout => {
        const workoutCard = `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="icon text-blue-600" viewBox="0 0 24 24">
                            <path d="m6.5 6.5 11 11"></path>
                            <path d="m21 21-1-1"></path>
                            <path d="m3 21 9-9"></path>
                            <circle cx="10.5" cy="10.5" r="7.5"></circle>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">${workout.exercise}</p>
                        <p class="text-sm text-gray-500">${workout.date}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-600">${workout.score}%</p>
                    <p class="text-sm text-gray-500">${workout.reps} reps</p>
                </div>
            </div>
        `;
        container.innerHTML += workoutCard;
    });
}

// Exercise Library
function loadExerciseLibrary() {
    const container = document.getElementById('exerciseGrid');
    container.innerHTML = '';
    
    exercises.forEach(exercise => {
        const difficultyColor = exercise.difficulty === 'Beginner' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
        
        const exerciseCard = `
            <button onclick="selectExercise(${exercise.id})" class="card bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl text-left">
                <div class="text-6xl mb-4 text-center">${exercise.image}</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">${exercise.name}</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Difficulty:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${difficultyColor}">
                            ${exercise.difficulty}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">${exercise.muscles}</p>
                </div>
                <div class="mt-4 flex items-center text-blue-600 font-semibold">
                    <span>Start Exercise</span>
                    <svg class="icon ml-1" viewBox="0 0 24 24">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </div>
            </button>
        `;
        container.innerHTML += exerciseCard;
    });
}

function selectExercise(exerciseId) {
    selectedExercise = exercises.find(ex => ex.id === exerciseId);
    showPage('detail');
}

// Exercise Detail
function loadExerciseDetail() {
    if (!selectedExercise) return;
    
    const container = document.getElementById('exerciseDetail');
    container.innerHTML = `
        <div class="text-center mb-8">
            <div class="text-8xl mb-4">${selectedExercise.image}</div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">${selectedExercise.name}</h1>
            <p class="text-gray-600">Target: ${selectedExercise.muscles}</p>
        </div>

        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">How to Perform</h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start space-x-2">
                        <span class="text-blue-600 font-bold">1.</span>
                        <span>Stand with feet shoulder-width apart</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="text-blue-600 font-bold">2.</span>
                        <span>Keep your back straight and core engaged</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="text-blue-600 font-bold">3.</span>
                        <span>Lower your body until thighs are parallel to ground</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="text-blue-600 font-bold">4.</span>
                        <span>Push through heels to return to starting position</span>
                    </li>
                </ul>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <h4 class="font-bold text-yellow-900 mb-2">Common Mistakes</h4>
                <ul class="space-y-1 text-yellow-800 text-sm">
                    <li>• Knees caving inward</li>
                    <li>• Lifting heels off the ground</li>
                    <li>• Leaning too far forward</li>
                </ul>
            </div>
        </div>

        <button onclick="showPage('cameraSetup')" class="btn w-full mt-8 gradient-button text-white py-4 rounded-xl font-semibold text-lg flex items-center justify-center space-x-2">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"></path>
                <circle cx="12" cy="13" r="3"></circle>
            </svg>
            <span>Start Exercise</span>
        </button>
    `;
    
    // Update current exercise name in workout active page
    if (selectedExercise) {
        document.getElementById('currentExerciseName').textContent = `Exercise: ${selectedExercise.name}`;
    }
}

// Workout Summary
function loadWorkoutSummary() {
    const container = document.getElementById('repBreakdown');
    const scores = [95, 89, 92, 87, 84, 90, 88, 85, 91, 86, 83, 88];
    
    container.innerHTML = '';
    scores.forEach((score, idx) => {
        const colorClass = score >= 90 ? 'bg-green-500' : score >= 80 ? 'bg-yellow-500' : 'bg-red-500';
        
        const repRow = `
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Rep ${idx + 1}</span>
                <div class="flex items-center space-x-2">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="${colorClass} h-2 rounded-full" style="width: ${score}%"></div>
                    </div>
                    <span class="font-semibold text-gray-900 w-12">${score}%</span>
                </div>
            </div>
        `;
        container.innerHTML += repRow;
    });
}

// Progress Dashboard
function loadProgressDashboard() {
    // Load Form Score Trend Chart
    const formScoreData = [65, 70, 68, 75, 78, 82, 80, 85, 87, 89, 92, 90];
    const formScoreContainer = document.getElementById('formScoreChart');
    formScoreContainer.innerHTML = '';
    
    formScoreData.forEach((score, idx) => {
        const bar = `
            <div class="flex-1 flex flex-col items-center">
                <div class="chart-bar w-full rounded-t-lg" 
                     style="height: ${(score / 100) * 100}%; background: linear-gradient(to top, #667eea, #764ba2);">
                </div>
                <span class="text-xs text-gray-500 mt-2">W${idx + 1}</span>
            </div>
        `;
        formScoreContainer.innerHTML += bar;
    });
    
    // Load Workout Frequency Chart
    const frequencyData = [3, 4, 2, 5, 4, 6, 5];
    const frequencyContainer = document.getElementById('workoutFrequencyChart');
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    frequencyContainer.innerHTML = '';
    
    frequencyData.forEach((count, idx) => {
        const bar = `
            <div class="flex-1 flex flex-col items-center">
                <div class="chart-bar w-full rounded-t-lg" 
                     style="height: ${(count / 6) * 100}%; background: linear-gradient(to top, #22c55e, #4ade80);">
                </div>
                <span class="text-xs text-gray-500 mt-2">${days[idx]}</span>
            </div>
        `;
        frequencyContainer.innerHTML += bar;
    });
    
    // Load Exercise Breakdown
    const breakdownContainer = document.getElementById('exerciseBreakdown');
    breakdownContainer.innerHTML = '';
    
    exercises.forEach(exercise => {
        const sessions = Math.floor(Math.random() * 20 + 10);
        const breakdownCard = `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <div class="flex items-center space-x-4">
                    <div class="text-3xl">${exercise.image}</div>
                    <div>
                        <p class="font-semibold text-gray-900">${exercise.name}</p>
                        <p class="text-sm text-gray-500">Last 7 days</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900">${sessions}</p>
                    <p class="text-sm text-gray-500">sessions</p>
                </div>
            </div>
        `;
        breakdownContainer.innerHTML += breakdownCard;
    });
}

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    // App starts at login page by default
    console.log('SpotBro App Initialized');
});