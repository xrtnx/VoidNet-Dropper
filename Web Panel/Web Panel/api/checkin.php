<?php
// Endpoint for clients to check in and respond to pings
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
date_default_timezone_set('UTC');

$ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$hwid = $_POST['hwid'] ?? '';
$machine_name = $_POST['machine_name'] ?? '';

if (empty($hwid)) { /* ... (error handling) ... */ }

// First, find the client
$stmt = $mysqli->prepare("SELECT id, ping_request FROM clients WHERE client_hwid = ?");
$stmt->bind_param('s', $hwid);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if ($client) {
    // Client exists. Update last_seen and IP.
    $update_stmt = $mysqli->prepare("UPDATE clients SET last_seen = NOW(), last_ip = ? WHERE id = ?");
    $update_stmt->bind_param('si', $ip_address, $client['id']);
    $update_stmt->execute();

    // NEW: If there was a ping request, log the response time
    if ($client['ping_request'] !== null) {
        $update_ping_stmt = $mysqli->prepare("UPDATE clients SET ping_response = NOW() WHERE id = ?");
        $update_ping_stmt->bind_param('i', $client['id']);
        $update_ping_stmt->execute();
    }
    $message = 'Client updated.';
} else {
    // New client, insert it
    $insert_stmt = $mysqli->prepare("INSERT INTO clients (client_hwid, machine_name, last_ip, first_seen, last_seen) VALUES (?, ?, ?, NOW(), NOW())");
    $insert_stmt->bind_param('sss', $hwid, $machine_name, $ip_address);
    $insert_stmt->execute();
    $message = 'Client registered.';
}

echo json_encode(['status' => 'success', 'message' => $message]);
$mysqli->close();
?>