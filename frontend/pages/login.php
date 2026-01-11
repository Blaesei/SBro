<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SpotBro</title>
    <!-- FIXED: Correct path from /sbro/frontend/pages/ -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Fallback inline styles with FIXED INPUT VISIBILITY */
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem;
        }
        .auth-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .logo-image {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-image img {
            height: 60px;
        }
        .auth-title {
            font-size: 1.875rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .auth-subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            box-sizing: border-box;
            /* CRITICAL: Make text visible */
            color: #111827 !important;
            background-color: #ffffff !important;
        }
        /* CRITICAL: Fix password masking */
        input[type="password"] {
            font-family: text-security-disc;
            -webkit-text-security: disc;
        }
        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-input::placeholder {
            color: #9ca3af;
            opacity: 1;
        }
        .form-error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .btn {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
            margin-left: 0.5rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #6b7280;
        }
        .auth-footer a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo - FIXED PATH -->
            <div class="logo-image">
                <img src="../assets/images/logo.png" alt="SpotBro" onerror="this.style.display='none'">
            </div>
            
            <!-- Title -->
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to continue your fitness journey</p>
            
            <!-- Alert Container -->
            <div id="alertContainer"></div>
            
            <!-- Login Form -->
            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="you@example.com"
                        required
                        autocomplete="email"
                    >
                    <span class="form-error" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <span class="form-error" id="passwordError"></span>
                </div>
                
                <button type="submit" class="btn" id="loginBtn">
                    <span id="loginBtnText">Sign In</span>
                    <span id="loginBtnSpinner" class="spinner" style="display: none;"></span>
                </button>
            </form>
            
            <!-- Footer -->
            <div class="auth-footer">
                Don't have an account? 
                <a href="signup.php">Sign up for free</a>
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="../index.php" style="color: #6b7280; font-size: 0.875rem; text-decoration: none;">
                    ← Back to home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Get correct paths
        function getApiPath() {
            // From /sbro/frontend/pages/login.php to /sbro/backend/api/auth/login.php
            return '../../backend/api/auth/login.php';
        }
        
        function getHomePath() {
            // From /sbro/frontend/pages/login.php to /sbro/frontend/pages/home.php
            return 'home.php'; // Same directory
        }
        
        // Login form handler
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            console.log('Login form submitted');
            
            // Clear previous errors
            document.getElementById('emailError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('alertContainer').innerHTML = '';
            
            // Get form data
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            // Basic validation
            if (!email) {
                document.getElementById('emailError').textContent = 'Email is required';
                return;
            }
            
            if (!password) {
                document.getElementById('passwordError').textContent = 'Password is required';
                return;
            }
            
            // Show loading state
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const loginBtnSpinner = document.getElementById('loginBtnSpinner');
            
            loginBtn.disabled = true;
            loginBtnText.style.display = 'none';
            loginBtnSpinner.style.display = 'inline-block';
            
            const apiUrl = getApiPath();
            console.log('API URL:', apiUrl);
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        email: email,
                        password: password
                    })
                });
                
                console.log('Response status:', response.status);
                
                const responseText = await response.text();
                console.log('Response preview:', responseText.substring(0, 200));
                
                // Check for HTML response (404)
                if (responseText.trim().startsWith('<!DOCTYPE') || responseText.trim().startsWith('<html')) {
                    throw new Error('API endpoint not found');
                }
                
                const data = JSON.parse(responseText);
                console.log('Response data:', data);
                
                if (data.success) {
                    // Store user info
                    sessionStorage.setItem('user', JSON.stringify(data.user));
                    
                    // Show success message
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // FIXED: Redirect to correct path
                    const homePath = getHomePath();
                    console.log('Redirecting to:', homePath);
                    
                    setTimeout(() => {
                        window.location.href = homePath;
                    }, 1000);
                } else {
                    showAlert(data.error || 'Login failed. Please try again.', 'error');
                    
                    // Reset button
                    loginBtn.disabled = false;
                    loginBtnText.style.display = 'inline';
                    loginBtnSpinner.style.display = 'none';
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Network error. Please check your connection and try again.', 'error');
                
                // Reset button
                loginBtn.disabled = false;
                loginBtnText.style.display = 'inline';
                loginBtnSpinner.style.display = 'none';
            }
        });
        
        // Alert helper function
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;
        }
    </script>
</body>
</html>