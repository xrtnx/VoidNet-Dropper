<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Cancel tasks older than 5 minutes
$mysqli->query("UPDATE tasks SET status = 'cancelled' WHERE status = 'pending' AND created_at < NOW() - INTERVAL 5 MINUTE");

// Stats
$total_clients = $mysqli->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'];
$pending_tasks = $mysqli->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'")->fetch_assoc()['count'];
$completed_tasks = $mysqli->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'")->fetch_assoc()['count'];
$total_screenshots = $mysqli->query("SELECT COUNT(*) as count FROM screenshots")->fetch_assoc()['count'];

$recent_clients_result = $mysqli->query("SELECT machine_name, last_ip, last_seen FROM clients ORDER BY last_seen DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - VoidNet</title>
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
      padding: 0;
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
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
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
      margin-bottom: 5px;
    }
    
    .page-header p {
      color: #999;
      font-size: 14px;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
      padding: 25px;
      border-radius: 10px;
      text-align: center;
      border: 1px solid #3a3a3a;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      border-color: #4a4a4a;
    }
    
    .stat-number {
      font-size: 36px;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 8px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    
    .stat-label {
      color: #aaa;
      font-size: 14px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .panel {
      background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
      border-radius: 10px;
      border: 1px solid #3a3a3a;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      overflow: hidden;
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
      padding: 0;
    }
    
    .data-table {
      width: 100%;
      border-collapse: collapse;
      color: #e0e0e0;
    }
    
    .data-table th,
    .data-table td {
      padding: 15px 25px;
      text-align: left;
      border-bottom: 1px solid #3a3a3a;
      font-size: 14px;
    }
    
    .data-table th {
      background: #1a1a1a;
      color: #ccc;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 12px;
    }
    
    .data-table tr:hover {
      background: rgba(255, 255, 255, 0.02);
    }
    
    .data-table tr:last-child td {
      border-bottom: none;
    }
    
    .no-data {
      text-align: center;
      color: #666;
      font-style: italic;
      padding: 40px;
    }
    
    @media (max-width: 768px) {
      .sidebar {
        width: 220px;
      }
      
      .main-content {
        margin-left: 220px;
        padding: 20px;
      }
      
      .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
      }
      
      .stat-card {
        padding: 20px;
      }
      
      .stat-number {
        font-size: 28px;
      }
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
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="clients.php">Clients</a>
      <a href="drop.php">Drop File</a>
      <a href="screenshots.php">Screenshots</a>
      <a href="logs.php">Logs</a>
    </nav>
    
    <div class="sidebar-footer">
      <a href="../logout.php">Log Out</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p>Overview of system status and recent activity</p>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><?php echo $total_clients; ?></div>
        <div class="stat-label">Total Clients</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $pending_tasks; ?></div>
        <div class="stat-label">Pending Tasks</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $completed_tasks; ?></div>
        <div class="stat-label">Completed Tasks</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $total_screenshots; ?></div>
        <div class="stat-label">Screenshots</div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2 class="panel-title">Recently Active Clients</h2>
      </div>
      <div class="panel-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>Machine Name</th>
              <th>Last IP</th>
              <th>Last Seen</th>
            </tr>
          </thead>
          <tbody>
            <?php while($client = $recent_clients_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($client['machine_name']); ?></td>
                <td><?php echo htmlspecialchars($client['last_ip']); ?></td>
                <td><?php echo htmlspecialchars($client['last_seen']); ?></td>
              </tr>
            <?php endwhile; ?>
            <?php if ($recent_clients_result->num_rows === 0): ?>
              <tr>
                <td colspan="3" class="no-data">No clients have checked in yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>