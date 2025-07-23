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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $emp_id = test_input($_POST["emp_id"]);
        $password = test_input($_POST["password"]);

        $sql = "SELECT * FROM employees where employee_id='$emp_id' and password='$password'";
        $result = $conn->query($sql);

        $row = $result->fetch_assoc();
        $first_name = $row["first_name"];

        if($row == null) {
            echo '<script>javascript:history.go(-1)</script>';
        }

        $date = date('Y-m-d');
        $time = date("H:i:s");
        
        $sql = "SELECT * FROM attendance where employee_id='$emp_id' and attendance_date='$date'";
        $result = $conn->query($sql);

        if($result->num_rows == 0) {
            $sql = "INSERT INTO attendance(employee_id, attendance_date, `status`, in_time, out_time) VALUES ('$emp_id','$date','present','$time', 'NULL')";
            $status = "In";
        } else {
            $sql = "UPDATE attendance
                    SET out_time = '$time'
                    WHERE employee_id = '$emp_id';
                    ";
            $status = "Out";
        }

        $conn->query($sql);
    }

    function test_input($data) {
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
        body{
            text-align: center;
        }
        img{
            width: 15%;
            padding-bottom: 5rem;
        }
        h1{
            padding-bottom: 5rem;
        }
        div{
            text-align: center;
        }

    </style>
</head>
<body>
    <h1>Punch -  <?php echo $status ?> Successful</h1>
    <img src="./Images/Group 1.png" alt="Tick">
    <div>
        <p>Employee Name : <?php echo $first_name ?></p>
        <p>Employee ID : <?php echo $emp_id ?></p>  
        <p>Punch - <?php echo $status ?> Time : <?php echo $time ?></p>
    </div>
</body>
</html>