<?php
// Start session
session_start();

$pdo = include(__DIR__ . '/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $captcha_input = trim($_POST['captcha']);
    $captcha_session = trim($_POST['captcha_session']);
    
    // Validate input
    if (empty($email) || empty($password) || empty($captcha_input)) {
        $_SESSION['error'] = "Please fill in all fields";
        $_SESSION['old_email'] = $email;
        header("Location: ../login.php");
        exit();
    }
    
    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        $_SESSION['old_email'] = $email;
        header("Location: ../login.php");
        exit();
    }
    
    // Verify CAPTCHA
    if (strtoupper($captcha_input) !== strtoupper($captcha_session)) {
        $_SESSION['error'] = "CAPTCHA verification failed";
        $_SESSION['old_email'] = $email;
        header("Location: ../login.php");
        exit();
    }
    
    // Prepare SQL statement
    $sql = "SELECT id, name, email, password FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    // Check if user exists
    if ($stmt->rowCount() == 1) {
        // Fetch user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password (plain text comparison — should use password_hash in production)
        if ($password === $user['password']) {
            // Password is correct, start a new session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['loggedin'] = true;

            // Clear any error data
            unset($_SESSION['error']);
            unset($_SESSION['old_email']);
            
            // Redirect to Registers.php page
            header("Location: ../Registers.php");
            exit();
        } else {
            // Password is not valid
            $_SESSION['error'] = "Invalid email or password";
            $_SESSION['old_email'] = $email;
            header("Location: ../login.php");
            exit();
        }
    } else {
        // User doesn't exist
        $_SESSION['error'] = "Invalid email or password";
        $_SESSION['old_email'] = $email;
        header("Location: ../login.php");
        exit();
    }
    
    // Close statement
    unset($stmt);
} else {
    // Not a POST request, redirect to login page
    header("Location: ../login.php");
    exit();
}

// Close connection
unset($pdo);
?>