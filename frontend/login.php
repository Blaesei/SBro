<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SpotBro – Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="login-body">

  <div class="login-container">
    <div class="login-card">

    <a href="index.php" class="logo-image login-logo">
        <img src="images/logo.png" alt="SpotBro Logo">
    </a>
      <h2 class="login-title">Welcome Back</h2>

      <form class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input type="email" required placeholder="you@spotbro.com" />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" required placeholder="••••••••" />
        </div>

        <button type="submit" onclick="window.location.href='home.php'" class="btn login-btn">Log In & Start Training</button>
      </form>

      <p class="login-signup">
        Don't have an account yet?
        <a href="signup.php">Sign Up</a>
      </p>
    </div>
  </div>

</body>
</html>