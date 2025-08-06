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

if (isset($_GET['delete_dept_id'])) {
    $dept_id = intval($_GET['delete_dept_id']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ? AND company_id = ?");
    $stmt->bind_param("ii", $dept_id, $company_id);
    $stmt->execute();
    $stmt->close();
} elseif (isset($_GET['delete_role_id'])) {
    $role_id = intval($_GET['delete_role_id']);
    $stmt = $conn->prepare("DELETE FROM roles WHERE id = ? AND company_id = ?");
    $stmt->bind_param("ii", $role_id, $company_id);
    $stmt->execute();
    $stmt->close();
}
$conn->close();
header('Location: company_info.php');
exit;
?>