<?php
// Connect to database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "attendance-db";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;


// Fetch employee info
$sql = "SELECT e.*, r.name AS role_name, d.dept_name FROM employees e
        JOIN roles r ON e.role = r.id
        JOIN departments d ON e.department_id = d.department_id
        WHERE e.employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$employee = $result->fetch_assoc();

// Handle delete request
if (isset($_GET['delete_id'])) {
  echo
    $delete_id = intval($_GET['delete_id']);
  $delete_sql = "DELETE FROM employees WHERE employee_id = $delete_id";
  if ($conn->query($delete_sql)) {
    echo "<script>alert('Employee deleted successfully'); window.location.href='dashboard.php';</script>";
    exit;
  } else {
    echo "<script>alert('Error deleting employee');</script>";
  }
}

if ($employee_id <= 0) {
  die("Invalid employee ID.");
}

if ($result->num_rows == 0) {
  die("Employee not found.");
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'attendance_date';
$order = $_GET['order'] ?? 'asc';
$expected_time = "13:00:00";

$where = [];
if (!empty($from) && !empty($to)) {
  $where[] = "attendance_date BETWEEN '$from' AND '$to'";
}
if (!empty($status)) {
  if ($status == "On-Time") {
    $where[] = "TIME(in_time) <= '" . $expected_time . "'";
  } elseif ($status == "Late") {
    $where[] = "TIME(in_time) > '" . $expected_time . "'";
  }
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM attendance WHERE employee_id = " . $employee_id . "$whereClause ORDER BY $sort $order";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Employee Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .table-status {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 6px;
      vertical-align: middle;
    }

    .on-time {
      background-color: green;
    }

    .late {
      background-color: orange;
    }

    .search-box {
      max-width: 200px;
    }

    .legend span {
      margin-right: 20px;
    }

    .nav-icon {
      margin-right: 1rem;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../images/Logo.png" alt="Attentify Logo" class="d-inline-block align-text-top">
        <span class="fw-bold">Attentify</span>
      </a>
    </div>
  </nav>

  <div class="container my-5">
    <h2 class="text-center mb-4">Employee Details</h2>
    <table class="table table-bordered table-striped-columns">
      <tr>
        <th>Employee ID</th>
        <td><?= $employee['employee_id'] ?></td>
      </tr>
      <tr>
        <th>Name</th>
        <td><?= $employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name'] ?></td>
      </tr>
      <tr>
        <th>Email</th>
        <td><?= $employee['email'] ?></td>
      </tr>
      <tr>
        <th>Phone Number</th>
        <td><?= $employee['phone_num'] ?></td>
      </tr>
      <tr>
        <th>Gender</th>
        <td><?= $employee['gender'] ?></td>
      </tr>
      <tr>
        <th>Birth Date</th>
        <td><?= $employee['birth_date'] ?></td>
      </tr>
      <tr>
        <th>Hire Date</th>
        <td><?= $employee['hire_date'] ?></td>
      </tr>
      <tr>
        <th>Department</th>
        <td><?= $employee['dept_name'] ?></td>
      </tr>
      <tr>
        <th>Role</th>
        <td><?= $employee['role_name'] ?></td>
      </tr>
      <tr>
        <th>Salary</th>
        <td>₹<?= number_format($employee['salary'], 2) ?></td>
      </tr>
    </table>

    <div class="d-flex justify-content-center gap-3">
      <a href="edit.php?id= <?= $employee_id ?>" class="btn btn-warning">Edit</a>
      <a href="table.php?delete_id=<?= $employee_id ?>" class="btn btn-danger"
        onclick="return confirm('Are you sure you want to delete this employee?');">
        Delete
      </a>
    </div>
  </div>

  <main class="col-md-12 ms-sm-auto px-md-4 py-4">
    <h3 class="mb-4">Attendance Report</h3>

    <!-- Controls -->
    <form method="GET" class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="form-label mb-0">From:</label>
        <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from) ?>">
        <label class="form-label mb-0">To:</label>
        <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to) ?>">
      </div>
      <select class="form-select" name="status">
        <option value="">All</option>
        <option value="On-Time" <?= $status == 'On-Time' ? 'selected' : '' ?>>On-Time</option>
        <option value="Late" <?= $status == 'Late' ? 'selected' : '' ?>>Late</option>
      </select>
      <button class="btn btn-primary">Apply Filters</button>
      <a href="export_excel.php?from=<?= $from ?>&to=<?= $to ?>&status=<?= $status ?>&sort=<?= $sort ?>&order=<?= $order ?>&search=<?= $search ?>"
        class="btn btn-success"><i class="fa-solid fa-download nav-icon"></i>Export</a>

    </form>

    <!-- Attendance Table -->
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="attendance-table">
        <thead class="table-light">
          <tr>
            <th><a
                href="?sort=employee_id&order=<?= $sort === 'employee_id' && $order === 'asc' ? 'desc' : 'asc' ?>&from=<?= $from ?>&to=<?= $to ?>&status=<?= $status ?>">Employee
                ID</a></th>
            <th><a
                href="?sort=attendance_date&order=<?= $sort === 'attendance_date' && $order === 'asc' ? 'desc' : 'asc' ?>&from=<?= $from ?>&to=<?= $to ?>&status=<?= $status ?>">Date</a>
            </th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Working Hours</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($row = $result->fetch_assoc()) {
            $in = new DateTime($row['in_time']);
            $out = new DateTime($row['out_time']);
            $time = new DateTime(date("H:i:s"));
            $interval = $row['out_time'] == "00:00:00" ? $in->diff($time) : $in->diff($out);

            $statusClass = $row['status'];
            $out_time = $row['out_time'] == "00:00:00" ? "-" : $row['out_time'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["employee_id"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["attendance_date"]) . "</td>";
            echo "<td><span class='table-status $statusClass'></span>" . htmlspecialchars($row["in_time"]) . "</td>";
            echo "<td>" . $out_time . "</td>";
            echo "<td>" . $interval->format('%h hr %i min') . "</td>";
            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Legend -->
    <div class="legend mt-2">
      <span><span class="table-status on-time"></span> On Time</span>
      <span><span class="table-status late"></span> Late</span>
    </div>

  </main>
</body>

</html>