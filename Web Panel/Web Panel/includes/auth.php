<?php
session_start();

// Check if the user is not logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_logged_in'])) {
    // The `../` is to go up one directory from /panel/ to the root where index.php is.
    header('Location: ../index.php');
    exit;
}
?>