<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

// Create connection and check for errors
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Function to get a consistent color for roles based on a hash
function getRoleColorClass($roleName)
{
  $colors = [
    'bg-blue-200 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
    'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200',
    'bg-yellow-200 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
    'bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200',
    'bg-purple-200 text-purple-800 dark:bg-purple-800 dark:text-purple-200',
    'bg-indigo-200 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-200',
    'bg-pink-200 text-pink-800 dark:bg-pink-800 dark:text-pink-200',
    'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  ];
  $hash = crc32($roleName);
  $index = abs($hash) % count($colors);
  return $colors[$index];
}

// Fetch all roles for the filter dropdown
$rolesResult = $conn->query("SELECT id, name FROM roles");
if ($rolesResult === false) {
    die("Error fetching roles: " . $conn->error);
}

// Fetch all departments for the filter dropdown
$departmentsResult = $conn->query("SELECT department_id, dept_name FROM departments");
if ($departmentsResult === false) {
    die("Error fetching departments: " . $conn->error);
}

// Fetch min and max salary for dynamic salary coloring
$salaryRangeQuery = "SELECT MIN(salary) as min_salary, MAX(salary) as max_salary FROM employees";
$rangeResult = $conn->query($salaryRangeQuery);
if ($rangeResult === false) {
    die("Error fetching salary range: " . $conn->error);
}
$rangeData = $rangeResult->fetch_assoc();
$minSalary = $rangeData['min_salary'];
$maxSalary = $rangeData['max_salary'];

function getSalaryColor($salary, $min, $max)
{
  if ($max - $min == 0) {
    return 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
  }
  $percent = ($salary - $min) / ($max - $min);
  $hue = intval(120 * $percent);
  return "bg-[hsl({$hue},90%,90%)] text-[hsl({$hue},60%,25%)] dark:bg-[hsl({$hue},40%,20%)] dark:text-white";
}

$filters = [];
$params = [];
$paramTypes = '';

if (!empty($_GET['role'])) {
  $filters[] = 'e.role = ?';
  $params[] = $_GET['role'];
  $paramTypes .= 'i';
}
if (!empty($_GET['department'])) {
  $filters[] = 'e.department_id = ?';
  $params[] = $_GET['department'];
  $paramTypes .= 'i';
}
if (!empty($_GET['gender'])) {
  $filters[] = 'e.gender = ?';
  $params[] = $_GET['gender'];
  $paramTypes .= 's';
}
if (!empty($_GET['search_emp_id'])) {
  $filters[] = 'e.employee_id = ?';
  $params[] = $_GET['search_emp_id'];
  $paramTypes .= 'i';
}

$sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.middle_name, ' ', e.last_name) AS full_name, r.name AS role, e.salary
        FROM employees e
        JOIN roles r ON e.role = r.id";

if (!empty($filters)) {
  $sql .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($sql);

if ($stmt === false) {
  die("Error preparing statement: " . $conn->error);
}

if (!empty($filters)) {
  $stmt->bind_param($paramTypes, ...$params);
}

if (!$stmt->execute()) {
  die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
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

      <form method="GET" class="w-1/2 md:w-1/3">
        <div class="relative">
          <input type="text" name="search_emp_id"
            value="<?= isset($_GET['search_emp_id']) ? htmlspecialchars($_GET['search_emp_id']) : '' ?>"
            placeholder="Search by Employee ID..."
            class="w-full pl-10 pr-4 py-2 rounded-full bg-gray-100 dark:bg-gray-700 dark:text-white text-sm border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </form>

      <div class="flex items-center gap-4">
        <button onclick="document.documentElement.classList.toggle('dark')" title="Toggle Dark Mode"
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
            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-100 dark:bg-indigo-900 font-semibold text-indigo-800 dark:text-indigo-200">
            <i class="fa-solid fa-tablet text-lg"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="#"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-user-gear text-lg"></i>
            <span>Company Info</span>
          </a>
        </li>
      </ul>
    </aside>

    <main class="flex-1 p-6 lg:p-10">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Employee Overview</h1>

      <form method="GET" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
          <div>
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
            <select name="role" id="role"
              class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
              <option value="">All Roles</option>
              <?php
              if ($rolesResult->num_rows > 0) {
                  $rolesResult->data_seek(0);
                  while ($role = $rolesResult->fetch_assoc()) {
                      $selected = isset($_GET['role']) && $_GET['role'] == $role['id'] ? 'selected' : '';
                      echo "<option value='{$role['id']}' $selected>{$role['name']}</option>";
                  }
              }
              ?>
            </select>
          </div>
          <div>
            <label for="department"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
            <select name="department" id="department"
              class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
              <option value="">All Departments</option>
              <?php
              if ($departmentsResult->num_rows > 0) {
                  $departmentsResult->data_seek(0);
                  while ($dept = $departmentsResult->fetch_assoc()) {
                      $selected = isset($_GET['department']) && $_GET['department'] == $dept['department_id'] ? 'selected' : '';
                      echo "<option value='{$dept['department_id']}' $selected>{$dept['dept_name']}</option>";
                  }
              }
              ?>
            </select>
          </div>
          <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
            <select name="gender" id="gender"
              class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
              <option value="">All Genders</option>
              <option value="Male" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : '' ?>>Male
              </option>
              <option value="Female" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : '' ?>>Female
              </option>
              <option value="Other" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Other') ? 'selected' : '' ?>>Other
              </option>
            </select>
          </div>
          <div class="flex gap-3">
            <button type="submit"
              class="w-full bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">Apply
              Filters</button>
            <a href="dashboard.php"
              class="w-full bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white px-4 py-2 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 text-center transition">Reset</a>
          </div>
        </div>
      </form>

      <div class="overflow-auto rounded-2xl shadow-lg">
        <table class="min-w-full bg-white dark:bg-gray-800 border-collapse">
          <thead class="bg-gray-200 dark:bg-gray-700 text-left">
            <tr>
              <th class="px-6 py-3">#</th>
              <th class="px-6 py-3">Employee ID</th>
              <th class="px-6 py-3">Name</th>
              <th class="px-6 py-3">Role</th>
              <th class="px-6 py-3">Salary</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php
            $i = 1;
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $role = htmlspecialchars($row["role"]);
                $empId = htmlspecialchars($row["employee_id"]);
                $name = htmlspecialchars($row["full_name"]);
                $salary = number_format($row["salary"]);
                $roleClass = getRoleColorClass($role);
                $salaryColor = getSalaryColor($row["salary"], $minSalary, $maxSalary);

                echo "<tr onclick=\"window.location.href='table.php?employee_id={$empId}'\" class='hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200'>";
                echo "<td class='px-6 py-4'>{$i}</td>";
                echo "<td class='px-6 py-4'>{$empId}</td>";
                echo "<td class='px-6 py-4'>{$name}</td>";
                echo "<td class='px-6 py-4'><span class='px-3 py-1 text-sm font-medium rounded-full {$roleClass}'>{$role}</span></td>";
                echo "<td class='px-6 py-4'><span class='px-3 py-1 text-sm font-medium rounded-full {$salaryColor}'>{$salary}</span></td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='5' class='text-center px-6 py-4 text-gray-500 dark:text-gray-400'>No employees found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script>
    // On page load, set dark mode based on saved preference
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