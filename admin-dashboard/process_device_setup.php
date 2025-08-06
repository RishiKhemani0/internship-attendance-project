<?php
session_start();

if (!isset($_SESSION['company_id'])) {
    header("Location: ../main/company-login.php");
    exit();
}

$device_type = $_GET['type'] ?? '';

if ($device_type === 'admin' || $device_type === 'punchin') {
    // Set a session variable to remember the device type
    $_SESSION['device_type'] = $device_type;

    // Redirect based on the selected type
    if ($device_type === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: ../main/index.php");
    }
} else {
    // If an invalid type is provided, redirect back to setup
    header("Location: device_setup.php");
}
exit();
?>