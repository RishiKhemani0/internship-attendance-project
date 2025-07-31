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
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Reports</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .chart-container {
      margin: 40px auto;
      max-width: 900px;
    }

    body {
      background-color: #f5faff;
    }

    .sidebar {
      height: 100vh;
      background-color: #ffffff;
      padding-top: 30px;
      border-right: 1px solid #e0e0e0;
    }

    .sidebar .nav-link {
      color: #333;
      padding: 12px 20px;
    }

    .nav-icon {
      margin-right: 1rem;
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
      background-color: #2a74f9;
      color: #fff;
    }

    .table-status {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 6px;
      vertical-align: middle;
    }

    .on-time {
      background-color: green;
    }

    .late {
      background-color: orange;
    }

    .search-box {
      max-width: 200px;
    }

    .legend span {
      margin-right: 20px;
    }

    .main-content {
      margin-left: 1rem;
      padding: 20px;
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../images/Logo.png" alt="Attentify Logo">
        <span class="fw-bold">Attentify</span>
      </a>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <nav class="col-md-2 d-md-block sidebar">
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link active" href="./reports.php"><i class="fa-solid nav-icon fa-chart-simple"></i>Attendance Report</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./dashboard.php"><i class="fa-solid nav-icon fa-tablet"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa-solid nav-icon fa-user"></i>Company Info</a>
          </li>
        </ul>
      </nav>

      <div class="col-md-10">
        <div class="main-content">
          <<div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h2 class="mb-0"><i class="fa-solid fa-chart-simple"></i> Attendance Reports</h2>
              <a href="./register_employee/register.php" class="btn btn-success"> Add Employee</a>
            </div>

            <div class="chart-container">
              <h5>Daily On-Time vs Late Attendance</h5>
              <canvas id="lineChart"></canvas>
            </div>

            <div class="chart-container">
              <h5>Monthly Presence Percentage</h5>
              <canvas id="monthlyChart"></canvas>
            </div>
        </div>
      </div>
    </div>
  </div>
  </div>

  <script>
    const dates = <?php echo json_encode($dates); ?>;
    const onTime = <?php echo json_encode($onTimeData); ?>;
    const late = <?php echo json_encode($lateData); ?>;
    const months = <?php echo json_encode($months); ?>;
    const monthlyPercentages = <?php echo json_encode($monthlyPercentages); ?>;

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
            tension: 0.4
          },
          {
            label: "Late",
            data: late,
            borderColor: "orange",
            backgroundColor: "rgba(255, 165, 0, 0.2)",
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          },
          title: {
            display: true,
            text: 'On-Time vs Late per Date'
          }
        }
      }
    });

    new Chart(document.getElementById("monthlyChart"), {
      type: "bar",
      data: {
        labels: months,
        datasets: [{
          label: "% Present",
          data: monthlyPercentages,
          backgroundColor: "#2a74f9"
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: {
              display: true,
              text: 'Percentage Present'
            }
          }
        },
        plugins: {
          title: {
            display: true,
            text: 'Monthly Employee Presence Percentage'
          }
        }
      }
    });
  </script>
</body>

</html>