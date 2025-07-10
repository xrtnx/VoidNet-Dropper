<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
date_default_timezone_set('UTC');

$client_id = $_POST['id'] ?? 0;
if ($client_id > 0) {
    // Set the ping request time and clear any old response
    $stmt = $mysqli->prepare("UPDATE clients SET ping_request = NOW(), ping_response = NULL WHERE id = ?");
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    echo json_encode(['status' => 'ping_sent']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
}
$mysqli->close();
?>