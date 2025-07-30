<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch employee info
    $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name, r.name AS role, e.salary
            FROM employees e
            JOIN roles r ON e.role = r.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee Overview</title>
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
            <a class="nav-link" href="./reports.php"><i class="fa-solid nav-icon fa-chart-simple"></i>Attendance Report</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./index.php"><i class="fa-solid fa-clock nav-icon"></i>Attendance Log</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="./dashboard.php"><i class="fa-solid nav-icon fa-tablet"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa-solid nav-icon fa-user"></i>Company Info</a>
          </li>
        </ul>
      </nav>

      <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h3 class="mb-4">Employee Overview</h3>

        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="employee-table">
            <thead class="table-light">
              <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Salary</th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = $result->fetch_assoc()) {
                echo "<tr onclick=\"window.location.href='table.php?employee_id=" . $row["employee_id"] . "'\" style='cursor:pointer'>";
                echo "<td>" . htmlspecialchars($row["employee_id"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                echo "<td>₹" . number_format($row["salary"]) . "</td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>

      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
