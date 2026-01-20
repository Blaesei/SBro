<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SpotBro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
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
                <a href="index.php">← Back to home</a>
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
