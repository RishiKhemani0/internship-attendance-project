<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

// Check if the user is logged in
if (!isset($_SESSION['company_id'])) {
    die("Unauthorized access.");
}

// Get company ID from the session
$company_id = $_SESSION['company_id'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'in_time';
$order = $_GET['order'] ?? 'asc';

$where = ["company_id = ?"];
$paramTypes = 'i';
$params = [$company_id];

if (!empty($from) && !empty($to)) {
  $where[] = "DATE(in_time) BETWEEN ? AND ?";
  $params[] = $from;
  $params[] = $to;
  $paramTypes .= 'ss';
}
if (!empty($status)) {
  $where[] = "status = ?";
  $params[] = $status;
  $paramTypes .= 's';
}

if (!empty($search)) {
  $where[] = "employee_id = ?";
  $params[] = $search;
  $paramTypes .= 'i';
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT employee_id, in_time, out_time FROM attendance $whereClause ORDER BY $sort $order";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}

if (!$stmt->execute()) {
    die("Error executing statement: " . $conn->error);
}

$result = $stmt->get_result();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr><th>Employee ID</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Working Hours</th></tr>";

while ($row = $result->fetch_assoc()) {
  $in = new DateTime($row['in_time']);
  $out = $row['out_time'] ? new DateTime($row['out_time']) : new DateTime();
  $interval = $in->diff($out);
  $out_time_display = $row['out_time'] ? $out->format('Y-m-d H:i:s') : "Not punched out";
  $working_hours = $row['out_time'] ? $interval->format('%h hr %i min') : 'Ongoing';

  echo "<tr>";
  echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
  echo "<td>" . $in->format('Y-m-d') . "</td>";
  echo "<td>" . htmlspecialchars($in->format('H:i:s')) . "</td>";
  echo "<td>" . $out_time_display . "</td>";
  echo "<td>" . $working_hours . "</td>";
  echo "</tr>";
}

echo "</table>";
$conn->close();
?>