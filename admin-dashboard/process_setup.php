<?php
session_start();

if (!isset($_SESSION['company_id'])) {
    header("Location: ../main/company-login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_department'])) {
        $dept_name = $_POST['dept_name'] ?? '';
        $stmt = $conn->prepare("INSERT INTO departments (dept_name, company_id) VALUES (?, ?)");
        $stmt->bind_param("si", $dept_name, $company_id);
        $stmt->execute();
        $stmt->close();

    } elseif (isset($_POST['add_role'])) {
        $role_name = $_POST['role_name'] ?? '';
        $expected_intime = $_POST['expected_intime'] ?? '';
        $stmt = $conn->prepare("INSERT INTO roles (name, expected_intime, company_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $role_name, $expected_intime, $company_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: setup.php");
exit();
?>