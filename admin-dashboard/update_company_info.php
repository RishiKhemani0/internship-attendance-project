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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $company_name = $_POST['company_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_num = $_POST['phone_num'] ?? '';

    $stmt = $conn->prepare("UPDATE companies SET name = ?, email = ?, phone_num = ? WHERE id = ?");
    $stmt->bind_param("sssi", $company_name, $email, $phone_num, $company_id);

    if ($stmt->execute()) {
        $_SESSION['company_name'] = $company_name;
        header("Location: company_info.php?status=success");
    } else {
        header("Location: company_info.php?status=error");
    }
    $stmt->close();
}

$conn->close();
exit();
?>