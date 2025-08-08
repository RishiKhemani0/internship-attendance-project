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

date_default_timezone_set('Asia/Kolkata');

$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

if (isset($_GET['delete_id'])) {
  $delete_id = intval($_GET['delete_id']);
  $delete_sql = "DELETE FROM employees WHERE employee_id = ? AND company_id = ?";
  $stmt = $conn->prepare($delete_sql);
  $stmt->bind_param("ii", $delete_id, $company_id);
  if ($stmt->execute()) {
    echo "<script>alert('Employee deleted successfully'); window.location.href='dashboard.php';</script>";
    exit;
  } else {
    echo "<script>alert('Error deleting employee');</script>";
  }
}

if ($employee_id <= 0) {
  die("Invalid employee ID.");
}

$sql = "SELECT e.*, r.name AS role_name, d.dept_name FROM employees e
        JOIN roles r ON e.role = r.id
        JOIN departments d ON e.department_id = d.department_id
        WHERE e.employee_id = ? AND e.company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $employee_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

$employee = $result->fetch_assoc();

if ($result->num_rows == 0) {
  die("Employee not found or does not belong to your company.");
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'in_time';
$order = $_GET['order'] ?? 'desc';

$where = [];
$params = [$employee_id, $company_id];
$paramTypes = 'ii';

if (!empty($from) && !empty($to)) {
    $where[] = "DATE(in_time) BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $paramTypes .= 'ss';
}
if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
    $paramTypes .= 's';
}

$whereClause = count($where) > 0 ? ' AND ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM attendance WHERE employee_id = ? AND company_id = ? $whereClause ORDER BY $sort $order";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$attendanceResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script>
    tailwind.config = {
      darkMode: 'class',
    };
  </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans transition-all duration-300">
  <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md px-6 py-4">
    <div class="max-w-full flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Employee Details</span>
      </div>
      <div class="flex items-center gap-4">
        <button onclick="toggleTheme()" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
          </svg>
        </button>
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
          <a href="./company_info.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-user-gear text-lg"></i>
            <span>Company Info</span>
          </a>
        </li>
      </ul>
    </aside>

    <main class="flex-1 max-w-5xl mx-auto py-10 px-4">
      <h2 class="text-3xl font-bold mb-6 text-center">Employee Details</h2>
      <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm sm:text-base">
          <?php
          foreach ([
            'Employee ID' => $employee['employee_id'],
            'Name' => $employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name'],
            'Email' => $employee['email'],
            'Phone Number' => $employee['phone_num'],
            'Gender' => $employee['gender'],
            'Birth Date' => $employee['birth_date'],
            'Hire Date' => $employee['hire_date'],
            'Department' => $employee['dept_name'],
            'Role' => $employee['role_name'],
            'Salary' => '₹' . number_format($employee['salary'], 2)
          ] as $label => $value) {
            echo "<div class='flex flex-col'><span class='font-semibold text-gray-500 dark:text-gray-400'>$label:</span><span class='font-medium'>$value</span></div>";
          }
          ?>
        </div>
        <div class="flex justify-center gap-4 mt-8">
          <a href="edit.php?id=<?= $employee_id ?>"
            class="px-6 py-2 bg-yellow-500 text-white rounded-full hover:bg-yellow-600 transition">Edit</a>
          <a href="table.php?delete_id=<?= $employee_id ?>" onclick="return confirm('Are you sure you want to delete this employee?');"
            class="px-6 py-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition">Delete</a>
        </div>
      </div>

      <h3 class="text-2xl font-bold mb-6">Attendance Report</h3>

      <form method="GET" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg mb-8">
        <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label for="from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From:</label>
            <input type="date" name="from" id="from" value="<?= htmlspecialchars($from) ?>"
              class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
          </div>
          <div>
            <label for="to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To:</label>
            <input type="date" name="to" id="to" value="<?= htmlspecialchars($to) ?>"
              class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
          </div>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select name="status" id="status"
              class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
              <option value="">All</option>
              <option value="on-time" <?= $status == 'on-time' ? 'selected' : '' ?>>On-Time</option>
              <option value="late" <?= $status == 'late' ? 'selected' : '' ?>>Late</option>
            </select>
          </div>
          <div class="flex gap-2">
            <a href="export_excel.php" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">Export</a>
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">Apply</button>
            <a href="table.php?employee_id=<?= $employee_id ?>" class="w-full bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white px-4 py-2 rounded-xl text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">Reset</a>
          </div>
        </div>
      </form>

      <div class="overflow-auto rounded-2xl shadow-lg">
        <table class="min-w-full bg-white dark:bg-gray-800 border-collapse">
          <thead class="bg-gray-200 dark:bg-gray-700 text-left">
            <tr>
              <th class="px-6 py-3">Date</th>
              <th class="px-6 py-3">Time In</th>
              <th class="px-6 py-3">Time Out</th>
              <th class="px-6 py-3">Working Hours</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php
            if ($attendanceResult && $attendanceResult->num_rows > 0) {
                while ($row = $attendanceResult->fetch_assoc()) {
                  $in = new DateTime($row['in_time']);
                  $out = $row['out_time'] ? new DateTime($row['out_time']) : new DateTime();
                  $interval = $in->diff($out);
                  $statusClass = $row['status'] === 'on-time' ? 'bg-green-500' : 'bg-orange-500';
                  $out_time_display = $row['out_time'] ? (new DateTime($row['out_time']))->format('Y-m-d H:i:s') : "Not punched out";
                  $working_hours = $row['out_time'] ? $interval->format('%h hr %i min') : 'Ongoing';

                  echo "<tr class='hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200'>";
                  echo "<td class='px-6 py-4'>" . htmlspecialchars((new DateTime($row["shift_start_date"]))->format('Y-m-d')) . "</td>";
                  echo "<td class='px-6 py-4'><span class='inline-block w-3 h-3 rounded-full mr-2 $statusClass'></span>" . htmlspecialchars($in->format('Y-m-d H:i:s')) . "</td>";
                  echo "<td class='px-6 py-4'>" . $out_time_display . "</td>";
                  echo "<td class='px-6 py-4'>" . $working_hours . "</td>";
                  echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center px-6 py-4 text-gray-500 dark:text-gray-400'>No attendance records found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
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