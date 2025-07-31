<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

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

$sql = "SELECT e.employee_id, e.first_name, e.middle_name, e.last_name, e.email, e.phone_num, r.name AS role
        FROM employees e
        JOIN roles r ON e.role = r.id
        WHERE e.employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode($row);
} else {
  echo json_encode([]);
}
?>
