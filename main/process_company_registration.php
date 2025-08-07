
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

    // Store old form data in session in case of an error
    $_SESSION['old_form_data'] = [
        'company_name' => $company_name,
        'phone_num' => $phone_num,
        'company_size' => $company_size,
        'company_type' => $company_type
    ];

    // Check for duplicate email
    $check_email_sql = "SELECT id FROM companies WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();

    if ($check_email_stmt->num_rows > 0) {
        $_SESSION['error_message'] = "This email is already registered. Please use a different email or log in.";
        $check_email_stmt->close();
        $conn->close();
        header("Location: company-login.php?tab=register");
        exit();
    }
    $check_email_stmt->close();
    
    // Hash the password for security
    $hashed_password = password_hash($pass_word, PASSWORD_DEFAULT);

    // Insert new company into the database
    $sql = "INSERT INTO companies (name, email, phone_num, size, type, pass_word, employee_id_counter) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Check if the prepare statement was successful
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    // The employee_id_counter is initialized to 0
    $initial_counter = 0;
    $stmt->bind_param("sssiisi", $company_name, $email, $phone_num, $company_size, $company_type, $pass_word, $initial_counter);

    if ($stmt->execute()) {
        $_SESSION['company_id'] = $stmt->insert_id;
        $_SESSION['company_name'] = $company_name;
        // Clear old form data upon successful registration
        unset($_SESSION['old_form_data']);
        // Redirect to the setup page for departments and roles
        header("Location: ../admin-dashboard/setup.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error during registration: " . $stmt->error;
        header("Location: company-login.php?tab=register");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>