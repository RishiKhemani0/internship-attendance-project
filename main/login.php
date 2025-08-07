<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

date_default_timezone_set('Asia/Kolkata');

// Check if a company ID is set in the session for the device
if (!isset($_SESSION['company_id'])) {
    // Redirect to login or device setup if the company is not identified
    header('Location: company-login.php');
    exit;
}

// Retrieve company ID from session
$company_id_from_session = $_SESSION['company_id'];
$emp_id = $_GET['emp_id'] ?? '';

// Check if employee ID is provided and is not empty
if (empty($emp_id)) {
    // Redirect back to the index page if no employee ID is provided
    header('Location: index.php');
    exit;
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use prepared statement to fetch employee details for the specific company
$sql = "SELECT first_name, company_id FROM employees WHERE employee_id = ? AND company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $emp_id, $company_id_from_session);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row == null) {
    // Employee not found or does not belong to the company
    header('Location: index.php?error=employee_not_found');
    exit;
}

$first_name = $row["first_name"];
$company_id = $row["company_id"];

$current_datetime = new DateTime();
$date = $current_datetime->format('Y-m-d');
$time = $current_datetime->format('H:i:s');
$datetime_string = $current_datetime->format('Y-m-d H:i:s');

// Check for an existing attendance record with a NULL out_time for this employee and company
$sql = "SELECT * FROM attendance WHERE employee_id = ? AND company_id = ? AND out_time IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $emp_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

$status_to_display = "";

if ($result->num_rows == 0) {
    // No open shift found, so this is a Punch In
    $status = $time > "13:00:00" ? "late" : "on-time";
    $sql = "INSERT INTO attendance(company_id, employee_id, shift_start_date, `status`, in_time, out_time) VALUES (?, ?, ?, ?, ?, NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $company_id, $emp_id, $date, $status, $datetime_string);
    $stmt->execute();
    $status_to_display = "In";
} else {
    // Open shift found, so this is a Punch Out
    $sql = "UPDATE attendance SET out_time = ? WHERE employee_id = ? AND company_id = ? AND out_time IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $datetime_string, $emp_id, $company_id);
    $stmt->execute();
    $status_to_display = "Out";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punch</title>
    <style>
        body {
            text-align: center;
        }

        img {
            width: 15%;
            padding-bottom: 5rem;
        }

        h1 {
            padding-bottom: 5rem;
        }

        div {
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>Punch - <?php echo htmlspecialchars($status_to_display); ?> Successful</h1>
    <img src="../images/Group 1.png" alt="Tick">
    <div>
        <p>Employee Name : <?php echo htmlspecialchars($first_name); ?></p>
        <p>Employee ID : <?php echo htmlspecialchars($emp_id); ?></p>
        <p>Punch - <?php echo htmlspecialchars($status_to_display); ?> Time : <?php echo htmlspecialchars($time); ?></p>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = "index.php";
        }, 5000);
    </script>
</body>

</html>