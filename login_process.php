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
    header("Location: index.php");
    exit();
}

// Get form data WITHOUT sanitization (VULNERABLE to SQL injection)
$username = $_POST['username'];
$password = $_POST['password'];

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['message'] = "Username and password are required";
    header("Location: index.php");
    exit();
}

// Print the actual value being injected (for debugging)
echo "<!-- Debug: Username input: " . htmlspecialchars($username) . " -->";

// VULNERABLE QUERY - Direct insertion of user input into SQL query
$sql = "SELECT id, username, password FROM users WHERE username = '$username'";

// Print the actual SQL query being executed (for debugging)
echo "<!-- Debug: Executing query: " . htmlspecialchars($sql) . " -->";

// Execute the query and check for errors
try {
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    if ($result->num_rows === 1) {
        // User exists, verify password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct, start a new session
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
            // Redirect to welcome page
            header("Location: welcome.php");
            exit();
        } else {
            // Password is incorrect
            $_SESSION['message'] = "Invalid username or password";
            header("Location: index.php");
            exit();
        }
    } else {
        // User doesn't exist
        $_SESSION['message'] = "Invalid username or password";
        header("Location: index.php");
        exit();
    }
} catch (Exception $e) {
    // Display the error for educational purposes
    echo "<div style='color: red; background: #ffeeee; padding: 10px; margin: 10px; border: 1px solid #ffaaaa;'>";
    echo "<h3>SQL Error (For Educational Purposes)</h3>";
    echo "<p>This error is displayed because this is a demonstration of SQL injection vulnerabilities.</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>SQL Query:</strong> " . htmlspecialchars($sql) . "</p>";
    echo "<p><strong>Tips:</strong> Try SQL injection payloads like <code>' OR '1'='1</code> or <code>admin' --</code></p>";
    echo "</div>";
}

// Close connection
$conn->close();
?>