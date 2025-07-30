<?php
// Connect to database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "attendance-db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch roles
$roles = [];
$role_sql = "SELECT id, name FROM roles";
$result = $conn->query($role_sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
  }
}

// Fetch departments
$departments = [];
$dept_sql = "SELECT department_id, dept_name FROM departments";
$result = $conn->query($dept_sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
  }
}

// Get employee data
$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$employee = null;

if ($employee_id > 0) {
  $sql = "SELECT * FROM employees WHERE employee_id = $employee_id";
  $res = $conn->query($sql);
  if ($res && $res->num_rows > 0) {
    $employee = $res->fetch_assoc();
  } else {
    die("Employee not found.");
  }
} else {
  die("Invalid employee ID.");
}

// Update logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $first_name = $_POST["first_name"];
  $middle_name = $_POST["middle_name"];
  $last_name = $_POST["last_name"];
  $email = $_POST["email"];
  $phone_num = $_POST["phone_num"];
  $birth_date = $_POST["birth_date"];
  $hire_date = $_POST["hire_date"];
  $salary = $_POST["salary"];
  $role_id = $_POST["role"];
  $department_id = $_POST["department"];
  $gender = $_POST["gender"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("UPDATE employees SET first_name=?, middle_name=?, last_name=?, email=?, phone_num=?, birth_date=?, hire_date=?, salary=?, role=?, department_id=?, gender=?, password=? WHERE employee_id=?");
$stmt->bind_param("sssssssiiissi", 
  $first_name,
  $middle_name,
  $last_name,
  $email,
  $phone_num,
  $birth_date,
  $hire_date,
  $salary,
  $role_id,
  $department_id,
  $gender,
  $password,
  $employee_id
);
  if ($stmt->execute()) {
    echo "<script>alert('Employee updated successfully'); window.location.href='dashboard.php';</script>";
    exit;
  } else {
    echo "Error: " . $stmt->error;
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Employee</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

  <!-- Edit Form -->
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-md-12">
        <div class="card shadow p-4">
          <h2 class="mb-3 text-center">Edit Employee</h2>
          <form action="" method="post">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Middle Name</label>
                <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($employee['middle_name']) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" name="phone_num" value="<?= htmlspecialchars($employee['phone_num']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Birth Date</label>
                <input type="date" class="form-control" name="birth_date" value="<?= $employee['birth_date'] ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Hire Date</label>
                <input type="date" class="form-control" name="hire_date" value="<?= $employee['hire_date'] ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Salary</label>
                <input type="number" class="form-control" name="salary" value="<?= $employee['salary'] ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Role</label>
                <select class="form-select" name="role" required>
                  <option value="">Select Role</option>
                  <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= ($employee['role'] == $role['id']) ? "selected" : "" ?>>
                      <?= htmlspecialchars($role['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Department</label>
                <select class="form-select" name="department" required>
                  <option value="">Select Department</option>
                  <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>" <?= ($employee['department_id'] == $dept['department_id']) ? "selected" : "" ?>>
                      <?= htmlspecialchars($dept['dept_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Gender</label>
                <select class="form-select" name="gender" required>
                  <option value="">Select Gender</option>
                  <option <?= ($employee['gender'] == "Male") ? "selected" : "" ?>>Male</option>
                  <option <?= ($employee['gender'] == "Female") ? "selected" : "" ?>>Female</option>
                  <option <?= ($employee['gender'] == "Other") ? "selected" : "" ?>>Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" value="<?= htmlspecialchars($employee['password']) ?>" id="password" required>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="showPass">
                  <label class="form-check-label" for="showPass">Show Password</label>
                </div>
              </div>

              <div class="col-12 text-center mt-3">
                <button type="submit" class="btn btn-success px-5">Update</button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const checkShowBox = document.querySelector("#showPass");
    const password = document.querySelector("#password");
    checkShowBox.addEventListener('click', () => {
      password.type = checkShowBox.checked ? "text" : "password";
    });
  </script>
</body>
</html>
