<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";
$result = $row = $sql = "";
$counter = 0;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle Filters
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'attendance_date';
$order = $_GET['order'] ?? 'asc';
$search = $_GET['search'] ?? '';
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
  $search = $conn->real_escape_string($search); // prevent SQL injection
  $where[] = "employee_id LIKE '%$search%'";
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM attendance $whereClause ORDER BY $sort $order";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Attendance Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      background-color: #f5faff;
    }

    .sidebar {
      height: 100vh;
      background-color: #ffffff;
      padding-top: 30px;
      border-right: 1px solid #e0e0e0;
    }

    .sidebar .nav-link {
      color: #333;
      padding: 12px 20px;
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
      background-color: #2a74f9;
      color: #fff;
    }

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
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../images/Logo.png" alt="Attentify Logo">
        <span class="fw-bold">Attentify</span>
      </a>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <nav class="col-md-2 d-md-block sidebar">
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link" href="./reports.php"><i class="fa-solid nav-icon fa-chart-simple"></i>Attendance
              Report</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="./index.php"><i class="fa-solid fa-clock nav-icon"></i></i>Attendance Log</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./dashboard.php"><i class="fa-solid nav-icon fa-tablet"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa-solid nav-icon fa-user"></i>Company Info</a>
          </li>
        </ul>
      </nav>

      <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h3 class="mb-4">Attendance Report</h3>

        <!-- Controls -->
        <form method="GET" class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <input type="text" class="form-control search-box" placeholder="Search..." name="search"
            value="<?= htmlspecialchars($search) ?>">
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
          <a href="export_excel.php?from=<?= $from ?>&to=<?= $to ?>&status=<?= $status ?>&sort=<?= $sort ?>&order=<?= $order ?>&search=<?= $search ?>" class="btn btn-success"><i class="fa-solid fa-download nav-icon"></i>Export</a>

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
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>