<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $pass_word = $_POST['password'] ?? '';

    $sql = "SELECT * FROM employees WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $pass_word);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $_SESSION['employee_data'] = $employee;
        header("Location: employee_dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: employee_login.php");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>