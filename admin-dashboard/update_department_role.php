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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_department'])) {
        $dept_id = intval($_POST['item_id']);
        $new_name = $_POST['new_dept_name'];
        $stmt = $conn->prepare("UPDATE departments SET dept_name = ? WHERE department_id = ? AND company_id = ?");
        $stmt->bind_param("sii", $new_name, $dept_id, $company_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update_role'])) {
        $role_id = intval($_POST['item_id']);
        $new_name = $_POST['new_role_name'];
        $new_intime = $_POST['new_expected_intime'];
        $stmt = $conn->prepare("UPDATE roles SET name = ?, expected_intime = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ssii", $new_name, $new_intime, $role_id, $company_id);
        $stmt->execute();
        $stmt->close();
    }
}
$conn->close();
header('Location: company_info.php');
exit;
?>