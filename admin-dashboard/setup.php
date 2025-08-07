<?php
session_start();

if (!isset($_SESSION['company_id'])) {
    header('Location: ../main/company-login.php');
    exit();
}

$company_id = $_SESSION['company_id'];

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'attendance-db';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}

$departments = [];
$dept_sql = 'SELECT department_id, dept_name FROM departments WHERE company_id = ?';
$stmt_dept = $conn->prepare($dept_sql);
$stmt_dept->bind_param('i', $company_id);
$stmt_dept->execute();
$result_dept = $stmt_dept->get_result();
while ($row = $result_dept->fetch_assoc()) {
    $departments[] = $row;
}
$stmt_dept->close();

$roles = [];
$role_sql = 'SELECT id, name FROM roles WHERE company_id = ?';
$stmt_role = $conn->prepare($role_sql);
$stmt_role->bind_param('i', $company_id);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
while ($row = $result_role->fetch_assoc()) {
    $roles[] = $row;
}
$stmt_role->close();

$conn->close();

$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

function getBadgeColor($text) {
    $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-yellow-200 text-yellow-800', 'bg-red-200 text-red-800'];
    $hash = crc32($text);
    $index = abs($hash) % count($colors);
    return $colors[$index];
}
?>
<!DOCTYPE html>
<html lang='en' class='dark'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Company Setup</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class='bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white font-sans'>
  <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
    <div class="max-w-full flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
      </div>

      <div class="flex items-center gap-4">
        <button onclick="toggleTheme()" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-white transition">
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

    <div class='max-w-4xl mx-auto p-8'>
        <h1 class='text-3xl font-bold text-center mb-10'>Welcome to Attentify, <?= htmlspecialchars($_SESSION['company_name']) ?>!</h1>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <div class='bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8'>
            <h2 class='text-2xl font-semibold mb-4'>Create Departments</h2>
            <form action='process_setup.php' method='post' class='flex gap-4 items-center'>
                <input type='text' name='dept_name' placeholder='Department Name' required
                    class='flex-1 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                <button type='submit' name='add_department'
                    class='bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition'>Add Department</button>
            </form>
            <div class='mt-4 flex flex-wrap gap-2'>
                <?php foreach ($departments as $dept): ?>
                    <span class='px-3 py-1 rounded-full text-sm font-medium <?= getBadgeColor($dept['dept_name']) ?>'>
                        <?= htmlspecialchars($dept['dept_name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class='bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8'>
            <h2 class='text-2xl font-semibold mb-4'>Create Roles</h2>
            <form action='process_setup.php' method='post' class='flex gap-4 items-center'>
                <input type='text' name='role_name' placeholder='Role Name' required
                    class='flex-1 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                <input type='time' name='expected_intime' placeholder='Expected In Time (HH:MM:SS)' required
                    class='flex-1 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                <button type='submit' name='add_role'
                    class='bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition'>Add Role</button>
            </form>
            <div class='mt-4 flex flex-wrap gap-2'>
                <?php foreach ($roles as $role): ?>
                    <span class='px-3 py-1 rounded-full text-sm font-medium <?= getBadgeColor($role['name']) ?>'>
                        <?= htmlspecialchars($role['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class='text-center mt-10'>
            <a href='device_setup.php'
                class='inline-block px-8 py-3 bg-green-600 text-white text-lg font-semibold rounded-full hover:bg-green-700 transition'>
                Next: Set up your device
            </a>
        </div>
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