<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

if (!isset($_SESSION['company_id'])) {
    header('Location: ../main/company-login.php');
    exit;
}

$company_id = $_SESSION['company_id'];
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
$stmt->bind_param("i", $company_id);

if ($stmt->execute()) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
} else {
    echo "Error deleting company: " . $conn->error;
}
$stmt->close();
$conn->close();
exit();
?>