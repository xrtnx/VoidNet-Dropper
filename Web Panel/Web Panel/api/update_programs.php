<?php
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Only POST method is accepted.']));
}

$hwid = $_POST['hwid'] ?? '';
$programs_json = $_POST['programs'] ?? '';

if (empty($hwid) || empty($programs_json)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'HWID and programs list are required.']));
}

$stmt = $mysqli->prepare("UPDATE clients SET programs = ? WHERE client_hwid = ?");
$stmt->bind_param('ss', $programs_json, $hwid);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Program list updated.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update program list.']);
}

$stmt->close();
$mysqli->close();
?>