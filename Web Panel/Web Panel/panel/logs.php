<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$query = "SELECT t.status, t.file_url, t.drop_location, t.created_at, c.machine_name 
          FROM tasks t 
          JOIN clients c ON t.client_id = c.id 
          ORDER BY t.created_at DESC";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Task Logs - VoidNet</title>
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

    table {
      width: 100%;
      border-collapse: collapse;
      color: #e0e0e0;
    }

    th, td {
      padding: 15px 20px;
      text-align: left;
      border-bottom: 1px solid #3a3a3a;
      font-size: 14px;
    }

    th {
      background: #1a1a1a;
      color: #ccc;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 12px;
    }

    tr:hover {
      background: rgba(255, 255, 255, 0.02);
    }

    .status {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: bold;
      text-transform: capitalize;
      font-size: 13px;
    }

    .status-pending {
      background-color: #ffa500;
      color: #000;
    }

    .status-completed {
      background-color: #2d8f2d;
      color: #fff;
    }

    .status-cancelled {
      background-color: #a04444;
      color: #fff;
    }

    .no-data {
      text-align: center;
      color: #777;
      padding: 30px;
      font-style: italic;
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
      <a href="drop.php">Drop File</a>
      <a href="screenshots.php">Screenshots</a>
      <a href="logs.php" class="active">Logs</a>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php">Log Out</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>Task Logs</h1>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2 class="panel-title">Task History</h2>
      </div>
      <div class="panel-body">
        <table>
          <thead>
            <tr>
              <th>Status</th>
              <th>Client</th>
              <th>File</th>
              <th>Drop Path</th>
              <th>Date Created</th>
            </tr>
          </thead>
          <tbody>
            <?php while($task = $result->fetch_assoc()): ?>
            <tr>
              <td><span class="status status-<?php echo strtolower($task['status']); ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
              <td><?php echo htmlspecialchars($task['machine_name']); ?></td>
              <td><?php echo basename(htmlspecialchars($task['file_url'])); ?></td>
              <td><?php echo htmlspecialchars($task['drop_location']); ?></td>
              <td><?php echo htmlspecialchars($task['created_at']); ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
            <tr>
              <td colspan="5" class="no-data">No tasks have been created yet.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
