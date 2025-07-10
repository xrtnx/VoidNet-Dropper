<?php
// Endpoint for clients to fetch pending tasks
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Only POST method is accepted.']));
}

$hwid = $_POST['hwid'] ?? '';

if (empty($hwid)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'HWID is required.']));
}

// Find client ID from HWID
$stmt = $mysqli->prepare("SELECT id FROM clients WHERE client_hwid = ?");
$stmt->bind_param('s', $hwid);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    http_response_code(404); // Not Found
    die(json_encode(['status' => 'error', 'message' => 'Client not registered.']));
}

$client_id = $client['id'];

// Check for a pending task for this client
$stmt = $mysqli->prepare("SELECT id, file_url, drop_location FROM tasks WHERE client_id = ? AND status = 'pending' ORDER BY created_at ASC LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if ($task) {
    // Task found, send it to the client and update its status to 'sent'
    $task_id = $task['id'];
    $update_stmt = $mysqli->prepare("UPDATE tasks SET status = 'sent' WHERE id = ?");
    $update_stmt->bind_param('i', $task_id);
    $update_stmt->execute();
    
    echo json_encode(['status' => 'task_found', 'task' => $task]);
} else {
    // No task found
    echo json_encode(['status' => 'no_task']);
}

$stmt->close();
$mysqli->close();
?>