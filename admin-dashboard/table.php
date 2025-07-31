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

date_default_timezone_set('Asia/Kolkata');

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

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'attendance_date';
$order = $_GET['order'] ?? 'asc';
$expected_time = "13:00:00";

$where = [];
if (!empty($from) && !empty($to)) {
  $where[] = "attendance_date BETWEEN '$from' AND '$to'";
}
if (!empty($status)) {
  if ($status == "On-Time") {
    $where[] = "TIME(in_time) <= '" . $expected_time . "'";
  } elseif ($status == "Late") {
    $where[] = "TIME(in_time) > '" . $expected_time . "'";
  }
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM attendance WHERE employee_id = " . $employee_id . "$whereClause ORDER BY $sort $order";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function toggleTheme() {
      const html = document.documentElement;
      html.dataset.theme = html.dataset.theme === 'dark' ? 'light' : 'dark';
    }
  </script>
</head>

<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-white">
  <!-- Navbar -->
  <nav class="flex items-center justify-between px-6 py-4 bg-blue-600 text-white dark:bg-gray-800">
    <div class="flex items-center gap-4">
      <img src="../images/transparent-logo.png" alt="Logo" class="h-8">
      <span class="text-xl font-semibold">Attentify</span>
    </div>
    <button onclick="toggleTheme()"
      class="bg-blue-800 px-3 py-1 rounded hover:bg-blue-700 dark:bg-gray-700 dark:hover:bg-gray-600">
      Toggle Dark Mode
    </button>
  </nav>

  <div class="max-w-5xl mx-auto py-10">
    <h2 class="text-3xl font-bold mb-6 text-center">Employee Details</h2>
    <div class="overflow-x-auto">
      <table class="table-auto w-full border border-gray-300 dark:border-gray-600">
        <tbody>
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
            echo "<tr class='border-t dark:border-gray-700'><th class='px-4 py-2 text-left bg-gray-100 dark:bg-gray-800'>$label</th><td class='px-4 py-2'>$value</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="flex justify-center gap-4 mt-6">
      <a href="edit.php?id=<?= $employee_id ?>"
        class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</a>
      <a href="table.php?delete_id=<?= $employee_id ?>" onclick="return confirm('Are you sure?');"
        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</a>
    </div>
  </div>

  <main class="max-w-6xl mx-auto px-6 py-10">
    <h3 class="text-2xl font-bold mb-6">Attendance Report</h3>

    <!-- Filter Controls -->
    <form method="GET" class="flex flex-wrap items-center gap-4 mb-6">
      <div class="flex items-center gap-2">
        <label>From:</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"
          class="border rounded px-2 py-1 dark:bg-gray-800 dark:border-gray-600">
        <label>To:</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"
          class="border rounded px-2 py-1 dark:bg-gray-800 dark:border-gray-600">
      </div>
      <select name="status" class="border rounded px-2 py-1 dark:bg-gray-800 dark:border-gray-600">
        <option value="">All</option>
        <option value="On-Time" <?= $status == 'On-Time' ? 'selected' : '' ?>>On-Time</option>
        <option value="Late" <?= $status == 'Late' ? 'selected' : '' ?>>Late</option>
      </select>
      <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apply Filters</button>
    </form>

    <!-- Attendance Table -->
    <div class="overflow-x-auto">
      <table class="w-full table-auto border border-gray-300 dark:border-gray-700">
        <thead class="bg-gray-100 dark:bg-gray-800">
          <tr>
            <th class="px-4 py-2">Employee ID</th>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Time In</th>
            <th class="px-4 py-2">Time Out</th>
            <th class="px-4 py-2">Working Hours</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($row = $result->fetch_assoc()) {
            $in = new DateTime($row['in_time']);
            $out = new DateTime($row['out_time']);
            $now = new DateTime();
            $interval = $row['out_time'] == "00:00:00" ? $in->diff($now) : $in->diff($out);
            $statusClass = $row['status'] == 'On-Time' ? 'bg-green-500' : 'bg-orange-500';
            $out_time = $row['out_time'] == "00:00:00" ? "-" : $row['out_time'];

            echo "<tr class='border-t dark:border-gray-700'>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($row["employee_id"]) . "</td>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($row["attendance_date"]) . "</td>";
            echo "<td class='px-4 py-2'><span class='inline-block w-3 h-3 rounded-full mr-2 $statusClass'></span>" . htmlspecialchars($row["in_time"]) . "</td>";
            echo "<td class='px-4 py-2'>" . $out_time . "</td>";
            echo "<td class='px-4 py-2'>" . $interval->format('%h hr %i min') . "</td>";
            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Legend -->
    <div class="mt-4 flex gap-6">
      <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500"></span> On Time</span>
      <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500"></span> Late</span>
    </div>
  </main>

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