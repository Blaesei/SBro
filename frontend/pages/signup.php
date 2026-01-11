<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - SpotBro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Inline fallback styles with FIXED INPUT VISIBILITY */
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
            color: #111827;
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
            color: #374151;
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
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
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
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
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
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="logo-image">
                <img src="../assets/images/logo.png" alt="SpotBro" onerror="this.style.display='none'">
            </div>
            
            <!-- Title -->
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Start your fitness journey with AI coaching</p>
            
            <!-- Alert Container -->
            <div id="alertContainer"></div>
            
            <!-- Signup Form -->
            <form id="signupForm">
                <div class="form-group">
                    <label class="form-label" for="fullName">Full Name</label>
                    <input 
                        type="text" 
                        id="fullName" 
                        name="full_name" 
                        class="form-input" 
                        placeholder="John Doe"
                        required
                        autocomplete="name"
                    >
                    <span class="form-error" id="fullNameError"></span>
                </div>
                
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
                        autocomplete="new-password"
                    >
                    <small style="color: #6b7280; font-size: 0.875rem;">Must be at least 6 characters</small>
                    <span class="form-error" id="passwordError"></span>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirm_password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    >
                    <span class="form-error" id="confirmPasswordError"></span>
                </div>
                
                <button type="submit" class="btn" id="signupBtn">
                    <span id="signupBtnText">Create Account</span>
                    <span id="signupBtnSpinner" class="spinner" style="display: none;"></span>
                </button>
            </form>
            
            <!-- Footer -->
            <div class="auth-footer">
                Already have an account? 
                <a href="login.php">Sign in</a>
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="../index.php" style="color: #6b7280; font-size: 0.875rem; text-decoration: none;">
                    ← Back to home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Get correct API and redirect paths
        function getApiPath() {
            // From /sbro/frontend/pages/signup.php to /sbro/backend/api/auth/signup.php
            return '../../backend/api/auth/signup.php';
        }
        
        function getHomePath() {
            // From /sbro/frontend/pages/signup.php to /sbro/frontend/pages/home.php
            return 'home.php'; // Same directory
        }
        
        // Signup form handler
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            console.log('=== SIGNUP DEBUG START ===');
            
            // Clear previous errors
            ['fullName', 'email', 'password', 'confirmPassword'].forEach(field => {
                document.getElementById(field + 'Error').textContent = '';
            });
            document.getElementById('alertContainer').innerHTML = '';
            
            // Get form data
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            console.log('Form data:', { fullName, email, passwordLength: password.length });
            
            // Validation
            let hasError = false;
            
            if (!fullName) {
                document.getElementById('fullNameError').textContent = 'Full name is required';
                hasError = true;
            }
            
            if (!email) {
                document.getElementById('emailError').textContent = 'Email is required';
                hasError = true;
            } else if (!isValidEmail(email)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email';
                hasError = true;
            }
            
            if (!password) {
                document.getElementById('passwordError').textContent = 'Password is required';
                hasError = true;
            } else if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'Password must be at least 6 characters';
                hasError = true;
            }
            
            if (!confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Please confirm your password';
                hasError = true;
            } else if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                hasError = true;
            }
            
            if (hasError) {
                console.log('Validation failed');
                return;
            }
            
            // Show loading state
            const signupBtn = document.getElementById('signupBtn');
            const signupBtnText = document.getElementById('signupBtnText');
            const signupBtnSpinner = document.getElementById('signupBtnSpinner');
            
            signupBtn.disabled = true;
            signupBtnText.style.display = 'none';
            signupBtnSpinner.style.display = 'inline-block';
            
            const apiUrl = getApiPath();
            console.log('Using API URL:', apiUrl);
            
            try {
                const formData = new URLSearchParams({
                    full_name: fullName,
                    email: email,
                    password: password
                });
                
                console.log('Sending request to:', apiUrl);
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                console.log('Response status:', response.status);
                
                const responseText = await response.text();
                console.log('Response preview:', responseText.substring(0, 200));
                
                // Check if response is HTML (404 error)
                if (responseText.trim().startsWith('<!DOCTYPE') || responseText.trim().startsWith('<html')) {
                    throw new Error('API endpoint not found (404). Check backend file location.');
                }
                
                // Parse JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    throw new Error('Server returned invalid response');
                }
                
                console.log('Parsed data:', data);
                
                if (data.success) {
                    // Store user info
                    sessionStorage.setItem('user', JSON.stringify(data.user));
                    console.log('User stored in sessionStorage');
                    
                    // Show success message
                    showAlert('Account created successfully! Redirecting...', 'success');
                    
                    // FIXED: Redirect to correct path
                    const homePath = getHomePath();
                    console.log('Redirecting to:', homePath);
                    
                    setTimeout(() => {
                        window.location.href = homePath;
                    }, 1500);
                } else {
                    console.error('Signup failed:', data.error);
                    showAlert(data.error || 'Registration failed. Please try again.', 'error');
                    
                    // Reset button
                    signupBtn.disabled = false;
                    signupBtnText.style.display = 'inline';
                    signupBtnSpinner.style.display = 'none';
                }
            } catch (error) {
                console.error('FETCH ERROR:', error);
                showAlert('Error: ' + error.message, 'error');
                
                // Reset button
                signupBtn.disabled = false;
                signupBtnText.style.display = 'inline';
                signupBtnSpinner.style.display = 'none';
            }
            
            console.log('=== SIGNUP DEBUG END ===');
        });
        
        // Email validation helper
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
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