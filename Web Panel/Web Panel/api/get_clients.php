<?php
require_once __DIR__ . '/../includes/auth.php'; // Use auth to protect the endpoint
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$clients_array = [];
$result = $mysqli->query("SELECT id, client_hwid, machine_name, last_ip, last_seen FROM clients ORDER BY last_seen DESC");

while($client = $result->fetch_assoc()) {
    $last_seen_timestamp = strtotime($client['last_seen']);
    // Add a calculated 'status' field to the data
    $client['is_disconnected'] = (time() - $last_seen_timestamp) > 300;
    $clients_array[] = $client;
}

echo json_encode($clients_array);
$mysqli->close();
?>