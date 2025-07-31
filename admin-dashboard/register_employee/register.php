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

// Insert logic
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

  $stmt = $conn->prepare("INSERT INTO employees (first_name, middle_name, last_name, email, phone_num, birth_date, hire_date, salary, role, department_id, gender, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssssdiiss", $first_name, $middle_name, $last_name, $email, $phone_num, $birth_date, $hire_date, $salary, $role_id, $department_id, $gender, $password);

  if ($stmt->execute()) {
    echo "<script>alert('Employee registered successfully');</script>";
  } else {
    echo "Error: " . $stmt->error;
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8">
  <title>Register Employee</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    
  </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
  <!-- Navbar -->
<nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
  <div class="max-w-full flex justify-between items-center">
    
    <!-- Left: Logo + Title -->
    <div class="flex items-center space-x-3">
      <img src="../../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
      <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
    </div>

    <!-- Right: Controls -->
    <div class="flex items-center gap-4">
      <!-- Dark Mode Toggle -->
      <button onclick="document.documentElement.classList.toggle('dark')" title="Toggle Dark Mode"
        class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
        </svg>
      </button>

      <!-- Profile Icon (placeholder) -->
      <div class="w-9 h-9 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-gray-700 dark:text-white">
        <i class="fa-solid fa-user text-sm"></i>
      </div>
    </div>

  </div>
</nav>
  <!-- Registration Form -->
  <div class="container mx-auto my-8 px-4">
    <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h2 class="text-2xl font-bold text-center mb-2">Register Employee</h2>
      <p class="text-center text-gray-600 dark:text-gray-300 mb-6">Fill in the details below</p>
      <form action="register.php" method="post" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1">First Name</label>
          <input type="text" name="first_name" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>
        <div>
          <label class="block mb-1">Middle Name</label>
          <input type="text" name="middle_name" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
        </div>
        <div>
          <label class="block mb-1">Last Name</label>
          <input type="text" name="last_name" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>

        <div>
          <label class="block mb-1">Email</label>
          <input type="email" name="email" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>
        <div>
          <label class="block mb-1">Phone Number</label>
          <input type="tel" name="phone_num" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>

        <div>
          <label class="block mb-1">Birth Date</label>
          <input type="date" name="birth_date" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>
        <div>
          <label class="block mb-1">Hire Date</label>
          <input type="date" name="hire_date" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>

        <div>
          <label class="block mb-1">Salary</label>
          <input type="number" name="salary" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>

        <div>
          <label class="block mb-1">Role</label>
          <select name="role" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
            <option value="">Select Role</option>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block mb-1">Department</label>
          <select name="department" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
            <option value="">Select Department</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['dept_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block mb-1">Gender</label>
          <select name="gender" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
          </select>
        </div>

        <div>
          <label class="block mb-1">Password</label>
          <input type="password" name="password" id="password" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" required>
        </div>

        <div class="col-span-full">
          <label class="inline-flex items-center">
            <input type="checkbox" id="showPass" class="form-checkbox text-blue-600">
            <span class="ml-2">Show Password</span>
          </label>
        </div>

        <div class="col-span-full text-center">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded">Register</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Script to toggle password -->
  <script>
    const checkShowBox = document.querySelector("#showPass");
    const password = document.querySelector("#password");
    checkShowBox.addEventListener('click', () => {
      password.type = checkShowBox.checked ? "text" : "password";
    });
  </script>
</body>

</html>
