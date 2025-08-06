<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $company_name = $_POST['company_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_num = $_POST['phone_num'] ?? '';
    $company_size = $_POST['company_size'] ?? 0;
    $company_type = $_POST['company_type'] ?? '';
    $pass_word = $_POST['password'] ?? '';

    // Sanitize input
    $company_name = $conn->real_escape_string($company_name);
    $email = $conn->real_escape_string($email);
    $phone_num = $conn->real_escape_string($phone_num);
    $company_size = intval($company_size);
    $company_type = $conn->real_escape_string($company_type);
    $pass_word = $conn->real_escape_string($pass_word);

    // Insert new company into the database
    $sql = "INSERT INTO companies (name, email, phone_num, size, type, pass_word) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Check if the prepare statement was successful
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("sssiis", $company_name, $email, $phone_num, $company_size, $company_type, $pass_word);

    if ($stmt->execute()) {
        $_SESSION['company_id'] = $stmt->insert_id;
        $_SESSION['company_name'] = $company_name;
        // Redirect to the setup page for departments and roles
        header("Location: ../admin-dashboard/setup.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>