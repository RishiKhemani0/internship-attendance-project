<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'attendance_date';
$order = $_GET['order'] ?? 'asc';
$expected_time = "13:00:00";

$where = [];
if (!empty($from) && !empty($to)) {
  $where[] = "attendance_date BETWEEN '$from' AND '$to'";
}
if (!empty($status)) {
  if ($status == "On-Time") {
    $where[] = "TIME(in_time) <= '" .$expected_time ."'";
  } elseif ($status == "Late") {
    $where[] = "TIME(in_time) > '" .$expected_time ."'";
  }
}

if (!empty($search)) {
  $search = $conn->real_escape_string($search);
  $where[] = "employee_id LIKE '%$search%'";
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM attendance $whereClause ORDER BY $sort $order";
$result = $conn->query($sql);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output Excel table
echo "<table border='1'>";
echo "<tr><th>Employee ID</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Working Hours</th></tr>";

while ($row = $result->fetch_assoc()) {
  $in = new DateTime($row['in_time']);
  $out = new DateTime($row['out_time']);
  $interval = $in->diff($out);
  echo "<tr>";
  echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
  echo "<td>" . $row['attendance_date'] . "</td>";
  echo "<td>" . htmlspecialchars($row['in_time']) . "</td>";
  echo "<td>" . htmlspecialchars($row['out_time']) . "</td>";
  echo "<td>" . $interval->format('%h hr %i min') . "</td>";
  echo "</tr>";
}

echo "</table>";
$conn->close();
