<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

if (!isset($_SESSION['company_id'])) {
    header('Location: ../main/company-login.php');
    exit;
}
$company_id = $_SESSION['company_id'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the number of working days in the current month
$currentMonth = date('Y-m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
$workingDays = 0;
for ($i = 1; $i <= $daysInMonth; $i++) {
    $date = new DateTime("$currentMonth-$i");
    $dayOfWeek = $date->format('N');
    if ($dayOfWeek < 6) {
        $workingDays++;
    }
}

$dateFilter = " WHERE attendance.company_id = $company_id";
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $conn->real_escape_string($_GET['from']);
    $to = $conn->real_escape_string($_GET['to']);
    $dateFilter .= " AND DATE(in_time) BETWEEN '$from' AND '$to'";
}

$statsQuery = "
    SELECT 
      DATE(in_time) as attendance_date,
      SUM(CASE WHEN status = 'on-time' THEN 1 ELSE 0 END) AS on_time,
      SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late
    FROM attendance
    $dateFilter
    GROUP BY attendance_date
    ORDER BY attendance_date ASC
";

$monthlyStatsQuery = "
    SELECT 
      DATE_FORMAT(in_time, '%Y-%m') AS month,
      COUNT(DISTINCT employee_id) AS present_employees
    FROM attendance
    WHERE company_id = $company_id
    GROUP BY month
    ORDER BY month ASC
";

$statsResult = $conn->query($statsQuery);
if ($statsResult === false) {
    die("Error fetching daily stats: " . $conn->error);
}

$monthlyResult = $conn->query($monthlyStatsQuery);
if ($monthlyResult === false) {
    die("Error fetching monthly stats: " . $conn->error);
}

$dates = [];
$onTimeData = [];
$lateData = [];

while ($row = $statsResult->fetch_assoc()) {
    $dates[] = $row['attendance_date'];
    $onTimeData[] = (int) $row['on_time'];
    $lateData[] = (int) $row['late'];
}

$months = [];
$monthlyPercentages = [];
$totalEmployees = $conn->query("SELECT COUNT(*) FROM employees WHERE company_id = $company_id")->fetch_row()[0];

while ($row = $monthlyResult->fetch_assoc()) {
    $months[] = $row['month'];
    if ($totalEmployees > 0) {
        $monthlyPercentages[] = round(($row['present_employees'] / $totalEmployees) * 100, 2);
    } else {
        $monthlyPercentages[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attendance Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script>
    tailwind.config = {
      darkMode: 'class',
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans transition-all duration-300">

  <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="max-w-full flex justify-between items-center">
          <a class="flex items-center space-x-3" href="dashboard.php">
            <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
            <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
          </a>

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

  <div class="flex">
    <aside class="w-64 bg-white dark:bg-gray-800 p-6 shadow-md md:block min-h-screen border-r border-gray-200 dark:border-gray-700">
      <ul class="space-y-3">
        <li>
          <a href="./reports.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-100 dark:bg-indigo-900 font-semibold text-indigo-800 dark:text-indigo-200">
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
        <li>
          <a href="./register_employee/register.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-user-plus text-lg"></i>
            <span>Add Employee</span>
          </a>
        </li>
      </ul>
    </aside>

    <main class="flex-1 p-6 overflow-auto">
      <h2 class="text-2xl font-semibold mb-6"><i class="fa-solid nav-icon fa-chart-simple"></i> Attendance Reports</h2>

      <form method="GET" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <div>
            <label for="from" class="block mb-2 font-medium">From:</label>
            <input type="date" id="from" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>" class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label for="to" class="block mb-2 font-medium">To:</label>
            <input type="date" id="to" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>" class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div class="flex gap-2">
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply</button>
            <a href="reports.php" class="w-full bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white px-4 py-2 rounded text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">Reset</a>
          </div>
        </div>
      </form>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
          <div class="text-gray-500 dark:text-gray-400 text-sm">Total Days</div>
          <div class="text-xl font-semibold"><?= count($dates) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
          <div class="text-gray-500 dark:text-gray-400 text-sm">Total On-Time Entries</div>
          <div class="text-xl font-semibold"><?= array_sum($onTimeData) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
          <div class="text-gray-500 dark:text-gray-400 text-sm">Total Late Entries</div>
          <div class="text-xl font-semibold"><?= array_sum($lateData) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
          <div class="text-gray-500 dark:text-gray-400 text-sm">Months Recorded</div>
          <div class="text-xl font-semibold"><?= count($months) ?></div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Daily On-Time vs Late Attendance</h3>
        <canvas id="lineChart" height="100"></canvas>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Monthly Presence Percentage</h3>
        <canvas id="monthlyChart" height="100"></canvas>
      </div>
    </main>
  </div>

  <script>
    const dates = <?= json_encode($dates); ?>;
    const onTime = <?= json_encode($onTimeData); ?>;
    const late = <?= json_encode($lateData); ?>;
    const months = <?= json_encode($months); ?>;
    const monthlyPercentages = <?= json_encode($monthlyPercentages); ?>;

    new Chart(document.getElementById("lineChart"), {
      type: "line",
      data: {
        labels: dates,
        datasets: [
          {
            label: "On Time",
            data: onTime,
            borderColor: "green",
            backgroundColor: "rgba(0, 128, 0, 0.2)",
            tension: 0.4,
          },
          {
            label: "Late",
            data: late,
            borderColor: "orange",
            backgroundColor: "rgba(255, 165, 0, 0.2)",
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: "top",
          },
        },
      },
    });

    new Chart(document.getElementById("monthlyChart"), {
      type: "bar",
      data: {
        labels: months,
        datasets: [
          {
            label: "% Present",
            data: monthlyPercentages,
            backgroundColor: "#2a74f9",
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: {
              display: true,
              text: "Percentage Present",
            },
          },
        },
      },
    });
  </script>

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