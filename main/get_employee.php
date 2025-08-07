<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

// Check if company ID is set in the session
if (!isset($_SESSION['company_id'])) {
    echo json_encode(["error" => "Company not authenticated."]);
    exit();
}

$company_id = $_SESSION['company_id'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo json_encode(["error" => "Database connection failed"]);
  exit();
}

$employee_id = $_GET['employee_id'] ?? '';
if (!$employee_id) {
  echo json_encode(["error" => "Employee ID not provided"]);
  exit();
}

// SQL to fetch employee details and the in_time of the most recent open shift
$sql = "SELECT 
          e.employee_id, 
          e.first_name, 
          e.middle_name, 
          e.last_name, 
          e.email, 
          e.phone_num, 
          r.name AS role,
          a.in_time
        FROM employees e
        JOIN roles r ON e.role = r.id
        LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.out_time IS NULL AND a.company_id = ?
        WHERE e.employee_id = ? AND e.company_id = ?
        ORDER BY a.in_time DESC
        LIMIT 1"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $company_id, $employee_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode($row);
} else {
  echo json_encode([]);
}

$conn->close();
?>