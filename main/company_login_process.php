
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
    $email = $_POST['email'] ?? '';
    $pass_word = $_POST['password'] ?? '';

    // Sanitize input
    $email = $conn->real_escape_string($email);
    $pass_word = $conn->real_escape_string($pass_word);

    // Find the company with the provided email and password
    $sql = "SELECT id, name FROM companies WHERE email = ? AND pass_word = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $pass_word);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['company_id'] = $row['id'];
        $_SESSION['company_name'] = $row['name'];
        
        // Redirect to the dashboard
        header("Location: ../admin-dashboard/dashboard.php");
        exit();
    } else {
        // Handle failed login
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: company-login.php");
        exit();
    }
    // $stmt->close();
}
$conn->close();
?>