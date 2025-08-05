<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";
$result = $emp_id = $password = $row = $first_name = $date = $time = $status = $company_id = "";

date_default_timezone_set('Asia/Kolkata');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$emp_id = $_GET['emp_id'] ?? '';

$sql = "SELECT first_name, company_id FROM employees where employee_id=$emp_id";
$result = $conn->query($sql);

$row = $result->fetch_assoc();

if ($row == null) {
    exit;
}
$first_name = $row["first_name"];
$company_id = $row["company_id"];

$current_datetime = new DateTime();
$date = $current_datetime->format('Y-m-d');
$time = $current_datetime->format('H:i:s');
$datetime_string = $current_datetime->format('Y-m-d H:i:s');

// Check for an existing attendance record with a NULL out_time
$sql = "SELECT * FROM attendance where employee_id=$emp_id and out_time IS NULL";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$status = $time > "13:00:00" ? "late" : "on-time";

if ($result->num_rows == 0) {
    // No open shift found, so this is a Punch In
    $sql = "INSERT INTO attendance(company_id, employee_id, shift_start_date, `status`, in_time, out_time) VALUES ('$company_id', '$emp_id', '$date', '$status', '$datetime_string', NULL)";
    $status = "In";
} else {
    // Open shift found, so this is a Punch Out
    $sql = "UPDATE attendance
    SET out_time = '$datetime_string'
    WHERE employee_id = '$emp_id' AND out_time IS NULL;";
    $status = "Out";
}

$conn->query($sql);

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

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
    <h1>Punch - <?php echo $status ?> Successful</h1>
    <img src="./Images/Group 1.png" alt="Tick">
    <div>
        <p>Employee Name : <?php echo $first_name ?></p>
        <p>Employee ID : <?php echo $emp_id ?></p>
        <p>Punch - <?php echo $status ?> Time : <?php echo $time ?></p>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = "index.php";
        }, 5000);
    </script>
</body>

</html>