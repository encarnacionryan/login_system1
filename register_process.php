<?php
// Start the session
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP default password is empty
$dbname = "login_system"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $_SESSION['message'] = "Connection failed: " . $conn->connect_error;
    header("Location: register.php");
    exit();
}

// Get form data WITHOUT sanitization (VULNERABLE to SQL injection)
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate input
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['message'] = "All fields are required";
    header("Location: register.php");
    exit();
}

// Check if passwords match
if ($password !== $confirm_password) {
    $_SESSION['message'] = "Passwords do not match";
    header("Location: register.php");
    exit();
}

// VULNERABLE QUERY - Direct insertion of user input into SQL query
$check_user_query = "SELECT id FROM users WHERE username = '$username'";
$result = $conn->query($check_user_query);

// For educational purposes, we'll echo the query that was executed
echo "<!-- Debug: Executing query: $check_user_query -->";

if ($result->num_rows > 0) {
    $_SESSION['message'] = "Username already exists";
    header("Location: register.php");
    exit();
}

// VULNERABLE QUERY - Direct insertion of user input into SQL query
$check_email_query = "SELECT id FROM users WHERE email = '$email'";
$result = $conn->query($check_email_query);

// For educational purposes, we'll echo the query that was executed
echo "<!-- Debug: Executing query: $check_email_query -->";

if ($result->num_rows > 0) {
    $_SESSION['message'] = "Email already exists";
    header("Location: register.php");
    exit();
}

// Hash the password (we'll still hash passwords for basic security)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// VULNERABLE QUERY - Direct insertion of user input into SQL query
$insert_query = "INSERT INTO users (username, email, password, created_at) 
                VALUES ('$username', '$email', '$hashed_password', NOW())";
$result = $conn->query($insert_query);

// For educational purposes, we'll echo the query that was executed
echo "<!-- Debug: Executing query: $insert_query -->";

if ($result) {
    $_SESSION['message'] = "Registration successful! You can now login.";
    header("Location: index.php");
} else {
    $_SESSION['message'] = "Error: " . $conn->error;
    header("Location: register.php");
}

// Close connection
$conn->close();
?>