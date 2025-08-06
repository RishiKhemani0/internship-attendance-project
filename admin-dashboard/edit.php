<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "attendance-db";

if (!isset($_SESSION['company_id'])) {
    header('Location: ../main/company-login.php');
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

$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$employee = null;

if ($employee_id > 0) {
  $sql = "SELECT * FROM employees WHERE employee_id = ? AND company_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $employee_id, $company_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows > 0) {
    $employee = $res->fetch_assoc();
  } else {
    die("Employee not found or does not belong to your company.");
  }
} else {
  die("Invalid employee ID.");
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
  $password = $_POST["password"];

  $stmt = $conn->prepare("UPDATE employees SET first_name=?, middle_name=?, last_name=?, email=?, phone_num=?, birth_date=?, hire_date=?, salary=?, role=?, department_id=?, gender=?, password=? WHERE employee_id=? AND company_id=?");
  $stmt->bind_param("sssssssdiissi", 
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
  $employee_id,
  $company_id
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
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Edit Employee</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans transition-all duration-300">
  <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
    <div class="max-w-full flex justify-between items-center">
    
      <div class="flex items-center space-x-3">
        <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
      </div>

      <div class="flex items-center gap-4">
        <button onclick="toggleTheme()" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
          </svg>
        </button>

        <div class="w-9 h-9 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-gray-700 dark:text-white">
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
          <a href="./reports.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-chart-simple text-lg"></i>
            <span>Attendance Report</span>
          </a>
        </li>
        <li>
          <a href="./dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-tablet text-lg"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="./company_info.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-user-gear text-lg"></i>
            <span>Company Info</span>
          </a>
        </li>
      </ul>
    </aside>

    <main class="flex-1 max-w-5xl mx-auto p-6 mt-10">
      <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-center">Edit Employee</h2>
        <form action="" method="post" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">First Name</label>
              <input type="text" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Middle Name</label>
              <input type="text" name="middle_name" value="<?= htmlspecialchars($employee['middle_name']) ?>"
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Last Name</label>
              <input type="text" name="last_name" value="<?= htmlspecialchars($employee['last_name']) ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Phone Number</label>
              <input type="tel" name="phone_num" value="<?= htmlspecialchars($employee['phone_num']) ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Birth Date</label>
              <input type="date" name="birth_date" value="<?= $employee['birth_date'] ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Hire Date</label>
              <input type="date" name="hire_date" value="<?= $employee['hire_date'] ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Salary</label>
              <input type="number" name="salary" value="<?= $employee['salary'] ?>" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Role</label>
              <select name="role" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>" <?= ($employee['role'] == $role['id']) ? "selected" : "" ?>>
                  <?= htmlspecialchars($role['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Department</label>
              <select name="department" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['department_id'] ?>" <?= ($employee['department_id'] == $dept['department_id']) ? "selected" : "" ?>>
                  <?= htmlspecialchars($dept['dept_name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium mb-1">Gender</label>
            <select name="gender" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring focus:ring-blue-500">
              <option value="">Select Gender</option>
              <option <?= ($employee['gender'] == "Male") ? "selected" : "" ?>>Male</option>
              <option <?= ($employee['gender'] == "Female") ? "selected" : "" ?>>Female</option>
              <option <?= ($employee['gender'] == "Other") ? "selected" : "" ?>>Other</option>
            </select>
          </div>

          <div class="text-center pt-6">
            <button type="submit"
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Update</button>
            <a href="dashboard.php"
              class="ml-3 px-6 py-2 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-white rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</a>
          </div>
        </form>
      </div>
    </main>
  </div>


  <script>
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