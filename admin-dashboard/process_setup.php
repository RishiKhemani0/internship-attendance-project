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

        // Check for duplicate department
        $check_dept_sql = "SELECT department_id FROM departments WHERE dept_name = ? AND company_id = ?";
        $check_dept_stmt = $conn->prepare($check_dept_sql);
        $check_dept_stmt->bind_param("si", $dept_name, $company_id);
        $check_dept_stmt->execute();
        $check_dept_stmt->store_result();

        if ($check_dept_stmt->num_rows > 0) {
            $_SESSION['error_message'] = "Department with this name already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO departments (dept_name, company_id) VALUES (?, ?)");
            $stmt->bind_param("si", $dept_name, $company_id);
            if (!$stmt->execute()) {
                $_SESSION['error_message'] = "Error adding department: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_dept_stmt->close();
        
    } elseif (isset($_POST['add_role'])) {
        $role_name = $_POST['role_name'] ?? '';
        $expected_intime = $_POST['expected_intime'] ?? '';

        // Check for duplicate role
        $check_role_sql = "SELECT id FROM roles WHERE name = ? AND company_id = ?";
        $check_role_stmt = $conn->prepare($check_role_sql);
        $check_role_stmt->bind_param("si", $role_name, $company_id);
        $check_role_stmt->execute();
        $check_role_stmt->store_result();

        if ($check_role_stmt->num_rows > 0) {
            $_SESSION['error_message'] = "Role with this name already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO roles (name, expected_intime, company_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $role_name, $expected_intime, $company_id);
            if (!$stmt->execute()) {
                $_SESSION['error_message'] = "Error adding role: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_role_stmt->close();
    }
}

$conn->close();
header("Location: setup.php");
exit();
?>