<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SpotBro – Sign Up</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="login-body">

  <div class="login-container">
    <div class="login-card">

    <div class="logo-image login-logo">
        <img src="images/logo.png" alt="SpotBro Logo">
    </div>
      <h2 class="login-title">Create Account</h2>

      <form class="login-form">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" required placeholder="John Doe" />
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" required placeholder="you@spotbro.com" />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" required placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" required placeholder="••••••••" />
        </div>

        <button type="submit" onclick="window.location.href='login.php'" class="btn login-btn">Create Account</button>
      </form>

      <p class="login-signup">
        Already have an account?
        <a href="login.php">Log In</a>
      </p>
    </div>
  </div>

</body>
</html>