<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$error = '';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_logged_in'])) {
    header('Location: panel/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch stored admin user and pass hash from settings table
    $user_result = $mysqli->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_user'");
    $pass_result = $mysqli->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_pass'");
    
    $stored_user = $user_result->fetch_assoc()['setting_value'];
    $stored_hash = $pass_result->fetch_assoc()['setting_value'];

    if ($username === $stored_user && password_verify($password, $stored_hash)) {
        $_SESSION['user_logged_in'] = true;
        header('Location: panel/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VoidNet - Admin Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      color: #e0e0e0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .login-container {
      background: #242424;
      border: 1px solid #3a3a3a;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      padding: 40px;
      width: 100%;
      max-width: 420px;
      backdrop-filter: blur(10px);
    }
    
    .logo-section {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .logo-section img {
      width: 64px;
      height: 64px;
      margin-bottom: 15px;
      filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.1));
    }
    
    .logo-section h1 {
      color: #ffffff;
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 8px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    
    .logo-section p {
      color: #999;
      font-size: 14px;
      font-weight: 300;
    }
    
    .error-message {
      background: linear-gradient(135deg, #ff4444 0%, #cc3333 100%);
      color: #fff;
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 6px;
      text-align: center;
      font-size: 14px;
      border: 1px solid #ff6666;
      box-shadow: 0 2px 8px rgba(255, 68, 68, 0.2);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #ccc;
      font-size: 14px;
      font-weight: 500;
    }
    
    .form-group input {
      width: 100%;
      padding: 14px 16px;
      border: 1px solid #404040;
      border-radius: 6px;
      background: #1a1a1a;
      color: #e0e0e0;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: #666;
      background: #202020;
      box-shadow: 0 0 0 3px rgba(102, 102, 102, 0.1);
    }
    
    .form-group input::placeholder {
      color: #666;
    }
    
    .login-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #404040 0%, #2d2d2d 100%);
      color: #ffffff;
      border: 1px solid #555;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .login-btn:hover {
      background: linear-gradient(135deg, #4a4a4a 0%, #373737 100%);
      border-color: #666;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .login-btn:active {
      transform: translateY(0);
    }
    
    .footer-text {
      text-align: center;
      margin-top: 25px;
      color: #666;
      font-size: 12px;
    }
    
    @media (max-width: 480px) {
      .login-container {
        margin: 20px;
        padding: 30px 25px;
      }
      
      .logo-section h1 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo-section">
      <img src="assets/image/logo.png" alt="VoidNet Logo">
      <h1>VoidNet</h1>
      <p>Administrative Access Panel</p>
    </div>
    
    <?php if ($error): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form action="index.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required />
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required />
      </div>
      
      <button type="submit" class="login-btn">Authenticate</button>
    </form>
    
    <div class="footer-text">
      Secure access required • VoidNet v1.0
    </div>
  </div>
</body>
</html>