<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "attendance-db";

if (!isset($_SESSION['company_id'])) {
  header('Location: ../../main/company-login.php');
  exit;
}
$company_id = $_SESSION['company_id'];

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$roles = [];
$role_sql = "SELECT id, name FROM roles WHERE company_id = $company_id";
$result = $conn->query($role_sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
  }
}

$departments = [];
$dept_sql = "SELECT department_id, dept_name FROM departments WHERE company_id = $company_id";
$result = $conn->query($dept_sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
  }
}

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

  // Check dates on the server side
  $birthDateObj = new DateTime($birth_date);
  $hireDateObj = new DateTime($hire_date);
  $today = new DateTime();
  $age = $birthDateObj->diff($today)->y;

  if ($age < 18) {
    echo "<script>alert('Employee must be at least 18 years old.'); window.history.back();</script>";
    exit;
  }

  if ($hireDateObj > $today) {
    echo "<script>alert('Hire date cannot be in the future.'); window.history.back();</script>";
    exit;
  }

  if ($hireDateObj < $birthDateObj->modify('+18 years')) {
    echo "<script>alert('Hire date must be at least 18 years after birth date.'); window.history.back();</script>";
    exit;
  }

  // Begin a transaction to ensure atomicity
  $conn->begin_transaction();

  try {
    // Get and increment the employee ID counter
    $stmt_counter = $conn->prepare("SELECT employee_id_counter FROM companies WHERE id = ? FOR UPDATE");
    $stmt_counter->bind_param("i", $company_id);
    $stmt_counter->execute();
    $result_counter = $stmt_counter->get_result();
    $row_counter = $result_counter->fetch_assoc();
    $new_employee_id = $row_counter['employee_id_counter'] + 1;
    $stmt_counter->close();

    // Insert new employee with the new ID
    $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, middle_name, last_name, email, phone_num, birth_date, hire_date, salary, role, department_id, gender, company_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssiiisii", $new_employee_id, $first_name, $middle_name, $last_name, $email, $phone_num, $birth_date, $hire_date, $salary, $role_id, $department_id, $gender, $company_id);
    if (!$stmt->execute()) {
      throw new Exception("Error registering employee: " . $stmt->error);
    }
    $stmt->close();

    // Update the counter
    $stmt_update_counter = $conn->prepare("UPDATE companies SET employee_id_counter = ? WHERE id = ?");
    $stmt_update_counter->bind_param("ii", $new_employee_id, $company_id);
    if (!$stmt_update_counter->execute()) {
      throw new Exception("Error updating employee ID counter: " . $stmt_update_counter->error);
    }
    $stmt_update_counter->close();

    $conn->commit();
    echo "<script>alert('Employee registered successfully with ID: {$new_employee_id}');</script>";

  } catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8">
  <title>Register Employee</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script>
    tailwind.config = {
      darkMode: 'class',
    };
  </script>
  <style>
    /*
      This is a custom style to change the color of the date input selector icon
      The color is changed based on the parent's `dark` class
    */
    .dark input[type="date"]::-webkit-calendar-picker-indicator {
      filter: invert(1);
    }
  </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
  <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
    <div class="max-w-full flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <img src="../../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
      </div>
      <div class="flex items-center gap-4">
        <button onclick="toggleTheme()" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
          </svg>
        </button>
        <div
          class="w-9 h-9 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-gray-700 dark:text-white">
          <i class="fa-solid fa-user text-sm"></i>
        </div>
      </div>
    </div>
  </nav>
  <div class="flex">
    <aside
      class="w-64 bg-white dark:bg-gray-800 p-6 shadow-md md:block min-h-screen border-r border-gray-200 dark:border-gray-700">
      <ul class="space-y-3">
        <li>
          <a href="../reports.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-chart-simple text-lg"></i>
            <span>Attendance Report</span>
          </a>
        </li>
        <li>
          <a href="../dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-tablet text-lg"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="../company_info.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-user-gear text-lg"></i>
            <span>Company Info</span>
          </a>
        <li>
          <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-100 dark:bg-indigo-900 font-semibold text-indigo-800 dark:text-indigo-200">
            <i class="fa-solid fa-user-plus text-lg"></i>
            <span>Add Employee</span>
          </a>
        </li>
        </li>
      </ul>
    </aside>

    <main class="container mx-auto my-8 px-4">
      <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-center mb-2">Register Employee</h2>
        <p class="text-center text-gray-600 dark:text-gray-300 mb-6">Fill in the details below</p>
        <form action="register.php" method="post" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
          onsubmit="return validateDates()">
          <div>
            <label class="block mb-1">First Name</label>
            <input type="text" name="first_name"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
          </div>
          <div>
            <label class="block mb-1">Middle Name</label>
            <input type="text" name="middle_name"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
          </div>
          <div>
            <label class="block mb-1">Last Name</label>
            <input type="text" name="last_name"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
          </div>

          <div>
            <label class="block mb-1">Email</label>
            <input type="email" name="email"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 invalid:border-pink-500 invalid:text-pink-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-pink-500 focus:invalid:outline-pink-500"
              required>
          </div>
          <div>
            <label class="block mb-1">Phone Number</label>
            <input type="tel" name="phone_num"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 invalid:border-pink-500 invalid:text-pink-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-pink-500 focus:invalid:outline-pink-500"
              required>
          </div>

          <div>
            <label class="block mb-1">Birth Date</label>
            <input type="date" name="birth_date" id="birth_date"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
          </div>
          <div>
            <label class="block mb-1">Hire Date</label>
            <input type="date" name="hire_date" id="hire_date"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
          </div>

          <div>
            <label class="block mb-1">Salary</label>
            <input type="number" name="salary"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 invalid:border-pink-500 invalid:text-pink-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-pink-500 focus:invalid:outline-pink-500"
              required>
          </div>

          <div>
            <label class="block mb-1">Role</label>
            <select name="role"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
              <option value="">Select Role</option>
              <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block mb-1">Department</label>
            <select name="department"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
              <option value="">Select Department</option>
              <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['dept_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block mb-1">Gender</label>
            <select name="gender"
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
              required>
              <option value="">Select Gender</option>
              <option>Male</option>
              <option>Female</option>
              <option>Other</option>
            </select>
          </div>
          <div class="col-span-full text-center">
            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded">Register</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    function validateDates() {
      const birthDateInput = document.getElementById('birth_date').value;
      const hireDateInput = document.getElementById('hire_date').value;

      const birthDate = new Date(birthDateInput);
      const hireDate = new Date(hireDateInput);
      const today = new Date();

      const age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }

      if (age < 18) {
        alert("Employee must be at least 18 years old.");
        return false;
      }

      if (hireDate > today) {
        alert("Hire date cannot be in the future.");
        return false;
      }

      const minHireDate = new Date(birthDate);
      minHireDate.setFullYear(minHireDate.getFullYear() + 18);
      if (hireDate < minHireDate) {
        alert("Hire date must be at least 18 years after birth date.");
        return false;
      }

      return true;
    }

    function toggleTheme() {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
    if (localStorage.getItem('theme') === 'dark' ||
      (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
</body>

</html>