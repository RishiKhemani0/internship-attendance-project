<?php
session_start();
if (!isset($_SESSION['employee_data'])) {
    header('Location: employee_login.php');
    exit;
}
$employee = $_SESSION['employee_data'];

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "attendance-db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [$employee['employee_id'], $employee['company_id']];
$paramTypes = 'ii';

if (!empty($from) && !empty($to)) {
    $where[] = "DATE(in_time) BETWEEN ?";
    $params[] = $from;
    $paramTypes .= 's';

    $where[] = "?";
    $params[] = $to;
    $paramTypes .= 's';
}
if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
    $paramTypes .= 's';
}

$whereClause = count($where) > 0 ? ' AND ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM attendance WHERE employee_id = ? AND company_id = ? $whereClause ORDER BY in_time DESC";
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
    <title>Employee Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans">
    <nav class='bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md px-6 py-4 w-full'>
        <div class='max-w-full flex justify-between items-center'>
            <div class='flex items-center space-x-3'>
                <img src='../images/transparent-logo.png' alt='Logo' class='w-8 h-8' />
                <span class='text-xl font-semibold text-gray-800 dark:text-white'>Attentify</span>
            </div>
            <a href="employee_logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Logout</a>
        </div>
    </nav>
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Welcome, <?= htmlspecialchars($employee['first_name']) ?>!</h1>
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2 dark:border-gray-600">Your Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-lg">
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Employee ID:</strong> <span><?= htmlspecialchars($employee['employee_id']) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Full Name:</strong> <span><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name']) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Email:</strong> <span><?= htmlspecialchars($employee['email']) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Phone:</strong> <span><?= htmlspecialchars($employee['phone_num']) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Hire Date:</strong> <span><?= htmlspecialchars($employee['hire_date']) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Gender:</strong> <span><?= htmlspecialchars($employee['gender']) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Salary:</strong> <span>₹<?= htmlspecialchars(number_format($employee['salary'], 2)) ?></span></div>
                <div class="flex flex-col"><strong class="text-gray-500 dark:text-gray-400">Reduced Salary:</strong> <span>₹<?= htmlspecialchars(number_format($employee['pr_salary'], 2)) ?></span></div>
            </div>
        </div>

        <h3 class="text-2xl font-bold mb-6">Attendance History</h3>
        <form method="GET" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg mb-8">
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
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">Apply</button>
                <a href="employee_dashboard.php" class="w-full bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white px-4 py-2 rounded-xl text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">Reset</a>
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
                      $out = $row['out_time'] ? new DateTime($row['out_time']) : null;
                      $interval = $out ? $in->diff($out) : null;
                      $statusClass = $row['status'] === 'on-time' ? 'bg-green-500' : ($row['status'] === 'late' ? 'bg-orange-500' : 'bg-red-500');
                      $out_time_display = $out ? $out->format('Y-m-d H:i:s') : "Not punched out";
                      $working_hours = $interval ? $interval->format('%h hr %i min') : 'Ongoing';
    
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
    </div>
</body>
</html>