<?php
// Endpoint to handle file uploads (screenshots)
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Only POST method is accepted.']));
}

$hwid = $_POST['hwid'] ?? '';
if (empty($hwid) || !isset($_FILES['screenshot'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'HWID and screenshot file are required.']));
}

// Find client ID from HWID
$stmt = $mysqli->prepare("SELECT id FROM clients WHERE client_hwid = ?");
$stmt->bind_param('s', $hwid);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    http_response_code(404);
    die(json_encode(['status' => 'error', 'message' => 'Client not registered.']));
}
$client_id = $client['id'];

// --- Handle the file upload ---
$uploadDir = __DIR__ . '/../screenshots/';
$file = $_FILES['screenshot'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'File upload error.']));
}

// Generate a unique filename: clientID_timestamp.extension
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = $client_id . '_' . time() . '.' . $extension;
$destination = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $destination)) {
    // File moved successfully, now add it to the database
    $db_path = 'screenshots/' . $filename; // Store relative path
    $stmt = $mysqli->prepare("INSERT INTO screenshots (client_id, file_path, captured_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('is', $client_id, $db_path);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Screenshot uploaded.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file.']);
}

$stmt->close();
$mysqli->close();
?>