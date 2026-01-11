<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotBro - AI-Powered Fitness Coach</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Inline styles for guaranteed styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
        }
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            padding: 2rem;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 800px;
            margin: 0 auto;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero-btn {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
            border-radius: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .hero-btn-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .hero-btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .hero-btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            backdrop-filter: blur(10px);
        }
        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-4px);
        }
        .features {
            padding: 4rem 2rem;
            background: white;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .features-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #111827;
        }
        .features-subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 1.125rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 1rem;
            background: #f9fafb;
            transition: all 0.3s;
        }
        .feature-card:hover {
            background: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transform: translateY(-8px);
        }
        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #111827;
        }
        .feature-description {
            color: #6b7280;
            line-height: 1.6;
        }
        .cta-section {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            text-align: center;
            color: white;
        }
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .cta-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        footer {
            padding: 2rem;
            background: #111827;
            color: white;
            text-align: center;
        }
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.25rem;
            }
            .hero-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div style="margin-bottom: 2rem;">
                <img src="assets/images/logo.png" alt="SpotBro Logo" style="height: 80px; margin: 0 auto;" onerror="this.style.display='none'">
            </div>
            
            <h1 class="hero-title">Perfect Your Form with AI</h1>
            <p class="hero-subtitle">
                Real-time exercise analysis powered by machine learning. 
                Get instant feedback and improve your fitness journey.
            </p>
            
            <div class="hero-buttons">
                <a href="signup.php" class="hero-btn hero-btn-primary">
                    Get Started Free
                </a>
                <a href="login.php" class="hero-btn hero-btn-secondary">
                    Sign In
                </a>
            </div>
            
            <div style="margin-top: 3rem; opacity: 0.9;">
                <p style="font-size: 0.875rem; margin-bottom: 0.5rem;">Trusted by fitness enthusiasts</p>
                <div style="display: flex; gap: 1rem; justify-content: center; align-items: center;">
                    <div style="display: flex;">
                        <span style="font-size: 1.5rem;">⭐⭐⭐⭐⭐</span>
                    </div>
                    <span style="font-weight: 600;">4.9/5 Rating</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="features-title">Why Choose SpotBro?</h2>
            <p class="features-subtitle">
                Advanced AI technology meets fitness expertise to help you train smarter and safer.
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5"></path>
                            <path d="M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Real-Time Analysis</h3>
                    <p class="feature-description">
                        Get instant feedback on your form as you exercise. 
                        Our AI analyzes every movement in real-time.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <h3 class="feature-title">Form Correction</h3>
                    <p class="feature-description">
                        Identify and fix common mistakes with detailed guidance. 
                        Prevent injuries and maximize results.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">Progress Tracking</h3>
                    <p class="feature-description">
                        Monitor your improvement over time with detailed analytics 
                        and comprehensive workout history.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Transform Your Workouts?</h2>
            <p class="cta-subtitle">
                Join thousands of users improving their fitness with AI-powered coaching.
            </p>
            <a href="signup.php" class="hero-btn hero-btn-primary">
                Start Training Now →
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p style="opacity: 0.8; margin-bottom: 0.5rem;">
                &copy; 2025 SpotBro. All rights reserved.
            </p>
            <p style="opacity: 0.6; font-size: 0.875rem;">
                Built with ❤️ using AI and Machine Learning
            </p>
        </div>
    </footer>
</body>
</html>