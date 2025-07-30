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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Employee Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../../images/Logo.png" alt="Attentify Logo" class="d-inline-block align-text-top">
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
</body>

</html>