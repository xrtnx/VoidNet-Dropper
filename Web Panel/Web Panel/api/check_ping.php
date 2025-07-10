<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
date_default_timezone_set('UTC');

$client_id = $_POST['id'] ?? 0;
if ($client_id > 0) {
    $stmt = $mysqli->prepare("SELECT ping_request, ping_response FROM clients WHERE id = ?");
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();

    // Check if a response exists and is later than the request
    if ($client && $client['ping_response'] && strtotime($client['ping_response']) > strtotime($client['ping_request'])) {
        echo json_encode(['status' => 'online']);
    } else {
        echo json_encode(['status' => 'offline']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
}
$mysqli->close();
?>