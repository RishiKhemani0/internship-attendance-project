<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$statsQuery = "
  SELECT 
    attendance_date, 
    SUM(CASE WHEN status = 'on-time' THEN 1 ELSE 0 END) AS on_time,
    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late
  FROM attendance
  GROUP BY attendance_date
  ORDER BY attendance_date ASC
";

$monthlyPercentageQuery = "
  SELECT 
    DATE_FORMAT(attendance_date, '%Y-%m') AS month,
    COUNT(DISTINCT employee_id) AS total_employees,
    SUM(CASE WHEN status IS NOT NULL THEN 1 ELSE 0 END) / COUNT(DISTINCT employee_id) AS days_present_total
  FROM attendance
  GROUP BY month
  ORDER BY month ASC
";

$statsResult = $conn->query($statsQuery);
$monthlyResult = $conn->query($monthlyPercentageQuery);

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

while ($row = $monthlyResult->fetch_assoc()) {
  $months[] = $row['month'];
  $monthlyPercentages[] = round(($row['days_present_total'] / 22) * 100, 2);
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

  <!-- Navbar -->
  <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md px-6 py-4">
    <div class="max-w-full flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Reports</span>
      </div>
      <div class="flex items-center gap-4">
        <button onclick="document.documentElement.classList.toggle('dark')" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
          </svg>
        </button>
        <a href="./register_employee/register.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow text-sm font-medium">
          + Add Employee
        </a>
      </div>
    </div>
  </nav>

  <div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 p-6 border-r border-gray-200 dark:border-gray-700 hidden md:block">
      <ul class="space-y-4">
        <li><a href="./reports.php" class="block px-4 py-2 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-medium"><i class="fa-solid nav-icon fa-chart-simple"></i> Attendance Report</a></li>
        <li><a href="./dashboard.php" class="block px-4 py-2 rounded hover:bg-blue-50 dark:hover:bg-gray-700"><i class="fa-solid nav-icon fa-tablet"></i> Dashboard</a></li>
        <li><a href="#" class="block px-4 py-2 rounded hover:bg-blue-50 dark:hover:bg-gray-700"><i class="fa-solid nav-icon fa-user"></i> Company Info</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-auto">
      <h2 class="text-2xl font-semibold mb-6">📊 Attendance Reports</h2>

      <!-- Date Filter -->
      <div class="mb-6">
        <label for="filterDate" class="block mb-2 font-medium">Filter by Date:</label>
        <input type="date" id="filterDate" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded px-4 py-2" />
      </div>

      <!-- Summary Cards -->
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

      <!-- Line Chart -->
      <div class="bg-white dark:bg-gray-800 rounded shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Daily On-Time vs Late Attendance</h3>
        <canvas id="lineChart" height="100"></canvas>
      </div>

      <!-- Bar Chart -->
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
