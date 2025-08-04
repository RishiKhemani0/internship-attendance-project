<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

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

// SQL to fetch employee details + today's in_time from attendance
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
        LEFT JOIN attendance a ON e.employee_id = a.employee_id AND DATE(a.attendance_date) = CURDATE()
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

$conn->close();
?>
