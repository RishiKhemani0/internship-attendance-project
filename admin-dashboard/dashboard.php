<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch filter options
$rolesResult = $conn->query("SELECT id, name FROM roles");
$departmentsResult = $conn->query("SELECT department_id, dept_name FROM departments");

// Filter logic
$filters = [];
$params = [];

if (!empty($_GET['role'])) {
  $filters[] = 'e.role = ?';
  $params[] = $_GET['role'];
}
if (!empty($_GET['department'])) {
  $filters[] = 'e.department_id = ?';
  $params[] = $_GET['department'];
}
if (!empty($_GET['gender'])) {
  $filters[] = 'e.gender = ?';
  $params[] = $_GET['gender'];
}
if (!empty($_GET['search_emp_id'])) {
  $filters[] = 'e.employee_id = ?';
  $params[] = $_GET['search_emp_id'];
}

$sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name, r.name AS role, e.salary
        FROM employees e
        JOIN roles r ON e.role = r.id";

if (!empty($filters)) {
  $sql .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($sql);

if (!empty($filters)) {
  $types = str_repeat("s", count($params));
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
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
            <a class="nav-link" href="./reports.php"><i class="fa-solid nav-icon fa-chart-simple"></i>Attendance
              Report</a>
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

        <form class="row g-3 mb-4" method="GET">
          <div class="col-md-3">
            <label for="role" class="form-label">Filter by Role</label>
            <select name="role" id="role" class="form-select">
              <option value="">All Roles</option>
              <?php while ($role = $rolesResult->fetch_assoc()) {
                $selected = isset($_GET['role']) && $_GET['role'] == $role['id'] ? 'selected' : '';
                echo "<option value='{$role['id']}' $selected>{$role['name']}</option>";
              } ?>
            </select>
          </div>

          <div class="col-md-3">
            <label for="department" class="form-label">Filter by Department</label>
            <select name="department" id="department" class="form-select">
              <option value="">All Departments</option>
              <?php while ($dept = $departmentsResult->fetch_assoc()) {
                $selected = isset($_GET['department']) && $_GET['department'] == $dept['department_id'] ? 'selected' : '';
                echo "<option value='{$dept['department_id']}' $selected>{$dept['dept_name']}</option>";
              } ?>
            </select>
          </div>

          <div class="col-md-3">
            <label for="gender" class="form-label">Filter by Gender</label>
            <select name="gender" id="gender" class="form-select">
              <option value="">All Genders</option>
              <option value="Male" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : '' ?>>Male
              </option>
              <option value="Female" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : '' ?>>
                Female</option>
              <option value="Other" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Other') ? 'selected' : '' ?>>Other
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label for="search_emp_id" class="form-label">Search by Employee ID</label>
            <input type="text" name="search_emp_id" id="search_emp_id" class="form-control"
              value="<?= isset($_GET['search_emp_id']) ? htmlspecialchars($_GET['search_emp_id']) : '' ?>">
          </div>

          <div class="col-md-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="dashboard.php" class="btn btn-secondary ms-2">Reset</a>
          </div>
        </form>


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