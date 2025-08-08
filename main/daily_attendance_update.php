<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Mark all employees as absent for the current day
$sql_insert_absent = "INSERT INTO attendance (company_id, employee_id, shift_start_date, status, in_time, out_time)
                      SELECT id, employee_id, ?, 'absent', NULL, NULL
                      FROM employees
                      ON DUPLICATE KEY UPDATE status = IF(in_time IS NULL, 'absent', status)";
$stmt_insert_absent = $conn->prepare($sql_insert_absent);
$stmt_insert_absent->bind_param("s", $today);
$stmt_insert_absent->execute();
$stmt_insert_absent->close();

// Process deductions for employees who were absent yesterday
$sql_absent_yesterday = "SELECT e.employee_id, e.company_id, e.leaves, e.pr_salary, e.salary
                         FROM employees e
                         LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.shift_start_date = ?
                         WHERE a.status = 'absent'";
$stmt_absent_yesterday = $conn->prepare($sql_absent_yesterday);
$stmt_absent_yesterday->bind_param("s", $yesterday);
$stmt_absent_yesterday->execute();
$result_absent = $stmt_absent_yesterday->get_result();

while ($employee = $result_absent->fetch_assoc()) {
    if ($employee['leaves'] > 0) {
        // Deduct a leave
        $sql_update_leaves = "UPDATE employees SET leaves = leaves - 1 WHERE employee_id = ? AND company_id = ?";
        $stmt_update_leaves = $conn->prepare($sql_update_leaves);
        $stmt_update_leaves->bind_param("ii", $employee['employee_id'], $employee['company_id']);
        $stmt_update_leaves->execute();
        $stmt_update_leaves->close();
    } else {
        // Deduct from prorated salary
        $daily_salary = $employee['salary'] / 30; // Assuming 30 days in a month for simplicity
        $new_pr_salary = $employee['pr_salary'] - $daily_salary;
        
        $sql_update_pr_salary = "UPDATE employees SET pr_salary = ? WHERE employee_id = ? AND company_id = ?";
        $stmt_update_pr_salary = $conn->prepare($sql_update_pr_salary);
        $stmt_update_pr_salary->bind_param("dii", $new_pr_salary, $employee['employee_id'], $employee['company_id']);
        $stmt_update_pr_salary->execute();
        $stmt_update_pr_salary->close();
    }
}

$stmt_absent_yesterday->close();
$conn->close();

echo "Daily attendance update completed successfully.";
?>