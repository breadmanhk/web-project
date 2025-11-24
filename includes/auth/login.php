<?php
/**
 * SEHS4517 Web Application Development and Management
 * Class: Group 103
 * Group: 1
 * Login Authentication PHP Script
 * Validates user credentials against MySQL database
 * On success: redirects to reservation page
 * On failure: displays error message
 */

// Start session to store user information, LEE Cheuk Him
session_start();

// Include database configuration
require_once '../config.php';

// Initialize variables
$email = '';
$password = '';
$loginSuccess = false;
$errorMessage = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Server-side validation
    $isValid = true;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address';
        $isValid = false;
    }

    // Validate password is not empty
    if (empty($password) && $isValid) {
        $errorMessage = 'Please enter your password';
        $isValid = false;
    }

    // If validation passes, check credentials in database
    if ($isValid) {
        // Get database connection
        $conn = getDBConnection();

        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT member_id, first_name, last_name, email, password FROM members WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $loginSuccess = true;

                // Store user information in session
                $_SESSION['member_id'] = $user['member_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                // Redirect to reservation page
                header('Location: ../../includes/booking/reservation.php');
                exit();
            } else {
                // Invalid password
                $errorMessage = 'Invalid email or password';
            }
        } else {
            // User not found
            $errorMessage = 'Invalid email or password';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Failed - CineMax Theatre</title>
    <link rel="stylesheet" href="../../assets/css/base.css" />
    <link rel="stylesheet" href="../../assets/css/layout.css" />
    <link rel="stylesheet" href="../../assets/css/components.css" />
    <link rel="stylesheet" href="../../assets/css/responsive.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="../../index.php" style="text-decoration: none;">
                <div class="logo-container">
                    <img src="../../assets/images/logo.svg" alt="CineMax Theatre Logo" class="logo" />
                    <h1>CINEMAX</h1>
                </div>
            </a>
        </div>
    </header>

    <div class="container">
        <main class="fade-in">
            <h2>Login Failed</h2>

            <div class="message message-error">
                <h3>Sorry, login failed!</h3>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
                <p>Please check your email and password and try again.</p>
            </div>

            <nav style="text-align: center; margin-top: 20px;">
                <a href="../../login.html" class="btn btn-primary">Try Again</a>
                <a href="../../index.php" class="btn btn-secondary">Back to Home</a>
                <a href="../../register.html" class="btn btn-success">Register New Account</a>
            </nav>
        </main>

        <footer>
            <p>&copy; 2025 CineMax Theatre. All rights reserved.</p>
            <p>SEHS4517 Web Application Development and Management</p>
        </footer>
    </div>

    <script>
        /**
         * jQuery fade-in animation
         */
        $(document).ready(function() {
            $('main').addClass('fade-in');
        });
    </script>
</body>
</html>
