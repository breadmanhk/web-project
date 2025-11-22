<?php
/**
 * SEHS4517 Web Application Development and Management
 * Reserve Seat PHP Script
 * Processes seat reservation, stores in MySQL, and forwards to Node.js Express server
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id']) || !isset($_SESSION['email'])) {
    header('Location: login.html');
    exit();
}

// Include database configuration
require_once '../config.php';

// Get user information from session
$userEmail = $_SESSION['email'];

// Initialize variables
$movieId = '';
$movieTitle = '';
$seatId = '';
$seatNumber = '';
$hallName = '';
$reservationDate = '';
$timeSlot = '';
$errorMessage = '';
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $movieId = intval($_POST['movieId']);
    $movieTitle = trim($_POST['movieTitle']);
    $seatId = intval($_POST['seatId']);
    $seatNumber = trim($_POST['seatNumber']);
    $hallName = trim($_POST['hallName']);
    $reservationDate = trim($_POST['reservationDate']);
    $timeSlot = trim($_POST['timeSlot']);

    // Server-side validation
    $isValid = true;

    // Validate movie selection
    if (empty($movieId)) {
        $errorMessage = 'Movie selection is required';
        $isValid = false;
    }

    // Validate date
    if (empty($reservationDate) && $isValid) {
        $errorMessage = 'Reservation date is required';
        $isValid = false;
    }

    // Validate time slot
    if (empty($timeSlot) && $isValid) {
        $errorMessage = 'Time slot is required';
        $isValid = false;
    }

    // Validate seat selection
    if (empty($seatId) && $isValid) {
        $errorMessage = 'Please select a seat';
        $isValid = false;
    }

    // If validation passes, process reservation
    if ($isValid) {
        // Get database connection
        $conn = getDBConnection();

        // Check if seat is still available (only check active reservations)
        $checkSql = "SELECT reservation_id FROM reservations WHERE movie_id = ? AND seat_id = ? AND reservation_date = ? AND time_slot = ? AND status = 'active'";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("iiss", $movieId, $seatId, $reservationDate, $timeSlot);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errorMessage = 'Sorry, this seat has already been reserved for the selected movie and time';
            $isValid = false;
        } else {
            // Insert reservation into database with active status
            $insertSql = "INSERT INTO reservations (member_email, movie_id, movie_title, seat_id, seat_number, hall_name, reservation_date, time_slot, status)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("sisissss", $userEmail, $movieId, $movieTitle, $seatId, $seatNumber, $hallName, $reservationDate, $timeSlot);

            if ($insertStmt->execute()) {
                $success = true;

                // Send data to Node.js Express server
                $nodeServerUrl = 'http://localhost:3000/thankyou';

                // Prepare data to send
                $postData = array(
                    'email' => $userEmail,
                    'movieTitle' => $movieTitle,
                    'seatNumber' => $seatNumber,
                    'hallName' => $hallName,
                    'reservationDate' => $reservationDate,
                    'timeSlot' => $timeSlot
                );

                // Initialize cURL session
                $ch = curl_init($nodeServerUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                // Execute cURL request
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Check if Node.js server responded successfully
                if ($httpCode == 200) {
                    // Display the response from Node.js server (Thank You page)
                    echo $response;
                    exit();
                } else {
                    // If Node.js server is not available, display error
                    $errorMessage = 'Reservation saved but unable to connect to confirmation server. Please contact support.';
                    $success = false;
                }
            } else {
                $errorMessage = 'Failed to save reservation. Please try again.';
            }

            $insertStmt->close();
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
    <title>Reservation Error - CineMax Theatre</title>
    <link rel="stylesheet" href="../../assets/css/base.css" />
    <link rel="stylesheet" href="../../assets/css/layout.css" />
    <link rel="stylesheet" href="../../assets/css/components.css" />
    <link rel="stylesheet" href="../../assets/css/responsive.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="../../assets/images/logo.svg" alt="CineMax Theatre Logo" class="logo" />
                <h1>CineMax Theatre</h1>
            </div>
        </div>
    </header>

    <div class="container">
        <main class="fade-in">
            <h2>Reservation Error</h2>

            <div class="message message-error">
                <h3>Unable to Complete Reservation</h3>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>

            <div class="btn-group">
                <a href="../../includes/booking/reservation.php" class="btn btn-primary">Try Again</a>
                <a href="../../index.php" class="btn btn-secondary">Back to Home</a>
            </div>
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
