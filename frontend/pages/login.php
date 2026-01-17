<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SpotBro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Override body styles for login page */
        body {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url('../assets/images/login_bg.jpg')
                        center center / cover no-repeat fixed;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Container */
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        /* Modern Glass Card */
        .login-card {
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 3rem 2.2rem;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.4);
            text-align: center;
            animation: fadeIn 0.8s ease;
        }

        /* Logo */
        .logo-image img {
            height: 80px;
            width: auto;
            display: block;
            margin: 0 auto 1.5rem;
        }

        /* Title */
        .login-title {
            margin-bottom: 2rem;
            color: #f1f1f1;
            font-size: 1.3rem;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Form */
        .login-form {
            text-align: left;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 1.4rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            color: #eaeaea;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            background: rgba(255,255,255,0.15);
            color: #fff;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            transition: 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255,255,255,0.25);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }

        /* Password inputs - ensure dots are visible */
        input[type="password"] {
            font-family: text-security-disc;
            -webkit-text-security: disc;
        }

        /* Error messages */
        .form-error {
            color: #ff6b6b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        /* Alert Container */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            color: #ffcccc;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            color: #ccffcc;
            border: 1px solid rgba(34, 197, 94, 0.4);
        }

        /* Button */
        .login-btn {
            width: 100%;
            padding: 1rem;
            margin-top: 0.7rem;
            border-radius: 10px;
            border: none;
            background: #F6AE2D;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-btn:hover:not(:disabled) {
            background: #d18800;
            transform: translateY(-2px);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Spinner */
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Signup Link */
        .login-signup {
            margin-top: 1.4rem;
            color: #dddddd;
            font-size: 0.95rem;
        }

        .login-signup a {
            color: #F6AE2D;
            font-weight: 600;
            text-decoration: none;
        }

        .login-signup a:hover {
            text-decoration: underline;
        }

        /* Back Link */
        .login-back {
            margin-top: 2rem;
        }

        .login-back a {
            color: #fff;
            font-size: 0.92rem;
            opacity: 0.8;
            text-decoration: none;
        }

        .login-back a:hover {
            opacity: 1;
        }

        /* Animations */
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(15px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 500px) {
            .login-card {
                padding: 2.5rem 1.7rem;
            }
            .login-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-image">
                <img src="../assets/images/logo.png" alt="SpotBro Logo" onerror="this.style.display='none'">
            </div>
            
            <!-- Title -->
            <h2 class="login-title">Welcome Back</h2>
            
            <!-- Alert Container -->
            <div id="alertContainer"></div>
            
            <!-- Login Form -->
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="you@spotbro.com"
                        required
                        autocomplete="email"
                    >
                    <span class="form-error" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <span class="form-error" id="passwordError"></span>
                </div>
                
                <button type="submit" class="btn login-btn" id="loginBtn">
                    <span id="loginBtnText">Sign In</span>
                    <span id="loginBtnSpinner" class="spinner" style="display: none;"></span>
                </button>
            </form>
            
            <!-- Signup Link -->
            <p class="login-signup">
                Don't have an account?
                <a href="signup.php">Sign up for free</a>
            </p>
            
            <!-- Back to Home -->
            <div class="login-back">
                <a href="../index.php">← Back to home</a>
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
                    
                    // Redirect to home
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