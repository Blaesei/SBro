<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - SpotBro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">

</head>
<body>
    <!-- HEADER -->
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container" style="max-width: 800px;">
            <div class="page-header text-center">
                <h1 class="page-title">Frequently Asked Questions</h1>
            </div>

            <div class="card">
                <h3>What is SpotBro?</h3>
                <p style="color: var(--gray-600);">
                    SpotBro is an AI-powered fitness assistant that provides real-time form analysis 
                    for your exercises using your device's camera.
                </p>
            </div>

            <div class="card">
                <h3>How does the AI analyze my form?</h3>
                <p style="color: var(--gray-600);">
                    We use MediaPipe to detect 33 key points on your body, then analyze biomechanical 
                    angles with machine learning models trained on thousands of exercise videos.
                </p>
            </div>

            <div class="card">
                <h3>What exercises does SpotBro support?</h3>
                <p style="color: var(--gray-600);">
                    Currently push-ups with full AI analysis. Support for squats, planks, and lunges 
                    coming soon.
                </p>
            </div>

            <div class="card">
                <h3>Is my workout data private?</h3>
                <p style="color: var(--gray-600);">
                    Yes! All pose detection happens locally in your browser. Your camera feed never 
                    leaves your device. Only workout statistics are saved.
                </p>
            </div>

            <div class="text-center" style="margin-top: 2rem;">
                <a href="signup.php" class="btn btn-primary btn-lg">
                    Start Training Now
                </a>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include '../includes/footer.php'; ?>

    <!-- SCRIPTS -->
    <script>
        // Logout handler
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            sessionStorage.clear();
            window.location.href = 'login.php';
        });
    </script>

    <!-- theme toggle script -->
    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>
