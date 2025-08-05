<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

function getRoleColorClass($roleName)
{
  // Create a hash from the role name
  $hash = crc32($roleName);
  $hue = $hash % 360; // Restrict hue to 0-359

  // Generate a Tailwind-compatible HSL class (e.g., text-[hsl(var(--hue))]
  return "bg-[hsl({$hue},70%,90%)] text-[hsl({$hue},40%,30%)] dark:bg-[hsl({$hue},60%,30%)] dark:text-white";
}


$rolesResult = $conn->query("SELECT id, name FROM roles");
$departmentsResult = $conn->query("SELECT department_id, dept_name FROM departments");

$salaryRangeQuery = "SELECT MIN(salary) as min_salary, MAX(salary) as max_salary FROM employees";
$rangeResult = $conn->query($salaryRangeQuery);
$rangeData = $rangeResult->fetch_assoc();
$minSalary = $rangeData['min_salary'];
$maxSalary = $rangeData['max_salary'];

function getSalaryColor($salary, $min, $max) {
  if ($max == $min) {
    $percent = 0.5; // Prevent divide-by-zero
  } else {
    $percent = ($salary - $min) / ($max - $min);
  }

  // Interpolate hue from red (0) to green (120)
  $hue = intval(120 * $percent); // 0 = red, 60 = yellow, 120 = green

  return "bg-[hsl({$hue},90%,90%)] text-[hsl({$hue},60%,25%)] dark:bg-[hsl({$hue},40%,20%)] dark:text-white";
}


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

  <!-- Navbar -->
  <!-- Navbar -->
  <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
    <div class="max-w-full flex justify-between items-center">

      <!-- Left: Logo + Title -->
      <div class="flex items-center space-x-3">
        <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
      </div>

      <!-- Middle: Search Form -->
      <form method="GET" class="hidden md:block w-1/3">
        <input type="text" name="search_emp_id"
          value="<?= isset($_GET['search_emp_id']) ? htmlspecialchars($_GET['search_emp_id']) : '' ?>"
          placeholder="Search by Employee ID..."
          class="w-full px-4 py-2 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-white text-sm border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </form>


      <!-- Right: Controls -->
      <div class="flex items-center gap-4">
        <!-- Dark Mode Toggle -->
        <button onclick="document.documentElement.classList.toggle('dark')" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
          </svg>
        </button>

        <!-- Profile Icon (placeholder) -->
        <div
          class="w-9 h-9 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-gray-700 dark:text-white">
          <i class="fa-solid fa-user text-sm"></i>
        </div>
      </div>

    </div>
  </nav>


  <div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 p-6 shadow-md hidden md:block min-h-screen">
      <ul class="space-y-3">
        <li><a href="./reports.php" class="block px-4 py-2 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900"><i
              class="fa-solid nav-icon fa-chart-simple"></i> Attendance Report</a></li>
        <li><a href="./dashboard.php" class="block px-4 py-2 rounded bg-indigo-100 dark:bg-indigo-900 font-semibold"><i
              class="fa-solid nav-icon fa-tablet"></i> Dashboard</a></li>
        <li><a href="#" class="block px-4 py-2 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900"><i
              class="fa-solid fa-user"></i> Company Info</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6">
      <h1 class="text-2xl font-semibold mb-6">Employee Overview</h1>

      <!-- Filters -->
      <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
          <label for="role" class="block mb-1 font-medium">Role</label>
          <select name="role" id="role" class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
            <option value="">All Roles</option>
            <?php while ($role = $rolesResult->fetch_assoc()) {
              $selected = isset($_GET['role']) && $_GET['role'] == $role['id'] ? 'selected' : '';
              echo "<option value='{$role['id']}' $selected>{$role['name']}</option>";
            } ?>
          </select>
        </div>
        <div>
          <label for="department" class="block mb-1 font-medium">Department</label>
          <select name="department" id="department"
            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
            <option value="">All Departments</option>
            <?php while ($dept = $departmentsResult->fetch_assoc()) {
              $selected = isset($_GET['department']) && $_GET['department'] == $dept['department_id'] ? 'selected' : '';
              echo "<option value='{$dept['department_id']}' $selected>{$dept['dept_name']}</option>";
            } ?>
          </select>
        </div>
        <div>
          <label for="gender" class="block mb-1 font-medium">Gender</label>
          <select name="gender" id="gender"
            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600">
            <option value="">All Genders</option>
            <option value="Male" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : '' ?>>Male
            </option>
            <option value="Female" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : '' ?>>Female
            </option>
            <option value="Other" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Other') ? 'selected' : '' ?>>Other
            </option>
          </select>
        </div>
        <div>
          <label for="search_emp_id" class="block mb-1 font-medium">Employee ID</label>
          <input type="text" name="search_emp_id" id="search_emp_id"
            class="w-full px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600"
            value="<?= isset($_GET['search_emp_id']) ? htmlspecialchars($_GET['search_emp_id']) : '' ?>">
        </div>
        <div class="md:col-span-4 flex justify-end gap-3 mt-2">
          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply
            Filters</button>
          <a href="dashboard.php"
            class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700">Reset</a>
        </div>
      </form>

      <!-- Employee Table -->
      <div class="overflow-auto rounded shadow-md">
        <table class="min-w-full table-auto border-collapse">
          <thead class="bg-gray-200 dark:bg-gray-700 text-left">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Employee ID</th>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Role</th>
              <th class="px-4 py-3">Salary</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800">
            <?php
            $i = 1;
            while ($row = $result->fetch_assoc()) {
              $role = htmlspecialchars($row["role"]);
              $empId = htmlspecialchars($row["employee_id"]);
              $name = htmlspecialchars($row["full_name"]);
              $salary = number_format($row["salary"]);
              $salaryClass = $row["salary"] > 50000 ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

              echo "<tr onclick=\"window.location.href='table.php?employee_id={$empId}'\" class='hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer transition'>";
              echo "<td class='px-4 py-3'>{$i}</td>";
              echo "<td class='px-4 py-3'>{$empId}</td>";
              echo "<td class='px-4 py-3'>{$name}</td>";
              $roleClass = getRoleColorClass($role);
              echo "<td class='px-4 py-3'><span class='px-2 py-1 text-sm font-medium rounded {$roleClass}'>{$role}</span></td>";
              $salaryColor = getSalaryColor($row["salary"], $minSalary, $maxSalary);
              echo "<td><span class='px-2 py-1 rounded font-medium {$salaryColor}'>{$salary}</span></td>";
              echo "</tr>";
              $i++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script>
    // On page load, set dark mode based on saved preference
    if (localStorage.getItem('theme') === 'dark' ||
      (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>

</body>

</html>