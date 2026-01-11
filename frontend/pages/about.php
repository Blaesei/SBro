<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SpotBro</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <img src="../assets/images/logo.png" alt="SpotBro" class="navbar-logo">
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="index.php" class="btn btn-primary">Home</a>
                <a href="login.php" class="btn btn-primary">Sign In</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container" style="max-width: 800px;">
            <div class="page-header text-center">
                <h1 class="page-title">About SpotBro</h1>
                <p class="page-subtitle">Your AI-Powered Personal Trainer</p>
            </div>

            <div class="card">
                <h2>Our Mission</h2>
                <p>
                    SpotBro was created to make professional fitness coaching accessible to everyone. 
                    We believe that proper exercise form is crucial for both safety and results.
                </p>
            </div>

            <div class="card">
                <h2>How It Works</h2>
                <ol style="color: var(--gray-600); line-height: 1.8;">
                    <li><strong>Pose Detection:</strong> AI detects 33 key points on your body in real-time</li>
                    <li><strong>Form Analysis:</strong> ML models analyze biomechanical angles</li>
                    <li><strong>Instant Feedback:</strong> Receive immediate corrections</li>
                    <li><strong>Progress Tracking:</strong> All workouts saved and analyzed</li>
                </ol>
            </div>

            <div class="text-center" style="margin-top: 2rem;">
                <a href="signup.php" class="btn btn-primary btn-lg">
                    Create Your Free Account
                </a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>