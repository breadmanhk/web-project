<?php
/**
 * SEHS4517 Web Application Development and Management
 * Member Registration PHP Script
 * Handles member registration form submission and stores data in MySQL database
 */

// Include database configuration
require_once '../config.php';

// Initialize variables
$firstName = '';
$lastName = '';
$mailingAddress = '';
$contactPhone = '';
$email = '';
$password = '';
$errorMessage = '';
$successMessage = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize inputs
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $mailingAddress = trim($_POST['mailingAddress']);
    $contactPhone = trim($_POST['contactPhone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Server-side validation
    $isValid = true;

    // Validate first name
    if (empty($firstName)) {
        $errorMessage = 'First name is required';
        $isValid = false;
    }

    // Validate last name
    if (empty($lastName) && $isValid) {
        $errorMessage = 'Last name is required';
        $isValid = false;
    }

    // Validate mailing address
    if (empty($mailingAddress) && $isValid) {
        $errorMessage = 'Mailing address is required';
        $isValid = false;
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{8}$/', $contactPhone) && $isValid) {
        $errorMessage = 'Please enter a valid 8-digit phone number';
        $isValid = false;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && $isValid) {
        $errorMessage = 'Please enter a valid email address';
        $isValid = false;
    }

    // Validate password
    if (strlen($password) < 6 && $isValid) {
        $errorMessage = 'Password must be at least 6 characters long';
        $isValid = false;
    }

    // If validation passes, insert into database
    if ($isValid) {
        // Get database connection
        $conn = getDBConnection();

        // Check if email already exists
        $checkSql = "SELECT email FROM members WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errorMessage = 'This email address is already registered. Please use a different email or login.';
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare SQL statement to prevent SQL injection
            $sql = "INSERT INTO members (first_name, last_name, mailing_address, contact_phone, email, password)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $firstName, $lastName, $mailingAddress, $contactPhone, $email, $hashedPassword);

            // Execute the statement
            if ($stmt->execute()) {
                $successMessage = 'Registration successful! You can now login with your email and password.';
                // Clear form data
                $firstName = '';
                $lastName = '';
                $mailingAddress = '';
                $contactPhone = '';
                $email = '';
            } else {
                $errorMessage = 'Registration failed. Please try again later.';
            }

            $stmt->close();
        }

        $checkStmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registration Result - CineMax Theatre</title>
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
            <h2>Registration Result</h2>

            <?php if (!empty($successMessage)): ?>
                <div class="message message-success">
                    <h3>Success!</h3>
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
                <nav style="text-align: center; margin-top: 20px;">
                    <a href="../../login.html" class="btn btn-primary">Go to Login</a>
                    <a href="../../index.php" class="btn btn-secondary">Back to Home</a>
                </nav>
            <?php elseif (!empty($errorMessage)): ?>
                <div class="message message-error">
                    <h3>Registration Failed</h3>
                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
                <nav style="text-align: center; margin-top: 20px;">
                    <a href="../../register.html" class="btn btn-primary">Try Again</a>
                    <a href="../../index.php" class="btn btn-secondary">Back to Home</a>
                </nav>
            <?php else: ?>
                <div class="message message-error">
                    <h3>Invalid Request</h3>
                    <p>Please submit the registration form properly.</p>
                </div>
                <nav style="text-align: center; margin-top: 20px;">
                    <a href="../../register.html" class="btn btn-primary">Go to Registration</a>
                    <a href="../../index.php" class="btn btn-secondary">Back to Home</a>
                </nav>
            <?php endif; ?>
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
