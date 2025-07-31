<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";
$result = $emp_id = $password = $row = $first_name = $date = $time = $status = "";

date_default_timezone_set('Asia/Kolkata');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$emp_id = $_GET['emp_id'] ?? '';

$sql = "SELECT * FROM employees where employee_id=$emp_id";
$result = $conn->query($sql);

$row = $result->fetch_assoc();

if ($row == null) {
    exit;
}
$first_name = $row["first_name"];

$date = date('Y-m-d');
$time = date("H:i:s");

$sql = "SELECT * FROM attendance where employee_id=$emp_id and attendance_date='$date'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$status = $time > "13:00:00" ? "late" : "on-time";

if ($result->num_rows == 0) {
    $sql = "INSERT INTO attendance(employee_id, attendance_date, `status`, in_time, out_time) VALUES ('$emp_id','$date','$status','$time', 'NULL')";
    $status = "In";
} else if ($row["out_time"] != "00:00:00") {
    return;
} else {
    $sql = "UPDATE attendance
    SET out_time = '$time'
    WHERE employee_id = '$emp_id';";
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