<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - SpotBro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-image login-logo">
                <img src="../assets/images/logo.png" alt="SpotBro Logo" onerror="this.style.display='none'">
            </div>
            
            <!-- Title -->
            <h2 class="login-title">Create Account</h2>
            
            <!-- Alert Container -->
            <div id="alertContainer"></div>
            
            <!-- Signup Form -->
            <form id="signupForm" class="login-form">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input 
                        type="text" 
                        id="fullName" 
                        name="full_name" 
                        placeholder="John Doe"
                        required
                        autocomplete="name"
                    >
                    <span class="form-error" id="fullNameError"></span>
                </div>
                
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
                        autocomplete="new-password"
                    >
                    <small class="form-helper">Must be at least 6 characters</small>
                    <span class="form-error" id="passwordError"></span>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirm_password" 
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    >
                    <span class="form-error" id="confirmPasswordError"></span>
                </div>
                
                <button type="submit" class="btn login-btn" id="signupBtn">
                    <span id="signupBtnText">Create Account</span>
                    <span id="signupBtnSpinner" class="spinner" style="display: none;"></span>
                </button>
            </form>
            
            <!-- Login Link -->
            <p class="login-signup">
                Already have an account?
                <a href="login.php">Log In</a>
            </p>
            
            <!-- Back to Home -->
            <div class="login-back">
                <a href="index.php">← Back to home</a>
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
                    
                    // Redirect to home
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
