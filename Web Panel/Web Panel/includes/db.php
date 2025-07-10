<?php
// Force a consistent timezone for the entire application
date_default_timezone_set('UTC');

// Main Database Connection File

// Include the configuration file created by the installer
require_once __DIR__ . '/../config/config.php';

// Create a new database connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    // In a real-world application, you would log this error instead of outputting it.
    // For our purpose, we will stop the script and report the error.
    http_response_code(500); // Internal Server Error
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

// Set the charset to utf8mb4 for full Unicode support
$mysqli->set_charset('utf8mb4');
?>