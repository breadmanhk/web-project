<?php
/**
 * SEHS4517 Web Application Development and Management
 * Class: Group 103
 * Group: 1
 * Reserve Seat PHP Script (Multi-Seat Version)
 * Processes multiple seat reservations, stores in MySQL, and forwards to Node.js
 * Developed by Chan Suet Ying
 */

// Session mechanism: Start session, we will use session mechanism to store logged-in user info
session_start();

// Session mechanism: This will check if user is logged in
if (!isset($_SESSION['member_id']) || !isset($_SESSION['email'])) {
    header('Location: login.html');
    exit();
}

// PHP function: Include database configuration file
require_once '../config.php';

// Session mechanism: This will get user information from session
$userEmail = $_SESSION['email'];

// PHP function: Initialize variables we will use later
$movieId = '';
$movieTitle = '';
$hallName = '';
$reservationDate = '';
$timeSlot = '';
$errorMessage = '';
$success = false;

// PHP function: Rule 1 - Set maximum tickets allowed per transaction (Server-side constraint)
$MAX_TICKETS = 4;

// PHP POST method: This will check if form is submitted with POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // PHP POST method: This will get basic form data
    $movieId = intval($_POST['movieId']);
    $movieTitle = trim($_POST['movieTitle']);
    $reservationDate = trim($_POST['reservationDate']);
    $timeSlot = trim($_POST['timeSlot']);
    
    // PHP POST method: Receive JSON string for multiple seats and decode it
    $selectedSeatsData = isset($_POST['selectedSeatsData']) ? $_POST['selectedSeatsData'] : '[]';
    $selectedSeats = json_decode($selectedSeatsData, true); // Convert to PHP Array

    // PHP function: Server-side validation and message handling
    $isValid = true;

    if (empty($movieId)) {
        $errorMessage = 'Movie selection is required';
        $isValid = false;
    }

    if (empty($reservationDate) && $isValid) {
        $errorMessage = 'Reservation date is required';
        $isValid = false;
    }

    if (empty($timeSlot) && $isValid) {
        $errorMessage = 'Time slot is required';
        $isValid = false;
    }

    // PHP function: Check if at least one seat is selected
    if ((!is_array($selectedSeats) || count($selectedSeats) === 0) && $isValid) {
        $errorMessage = 'Please select at least one seat';
        $isValid = false;
    }

    // PHP function: Rule 1 - Check: Enforce maximum ticket limit
    if (count($selectedSeats) > $MAX_TICKETS && $isValid) {
        $errorMessage = 'You can only reserve a maximum of ' . $MAX_TICKETS . ' tickets per transaction.';
        $isValid = false;
    }

    // PHP function: Rule 2 - Check: Enforce single-hall rule
    if ($isValid) {
        $firstHallName = null;
        foreach ($selectedSeats as $seat) {
            if ($firstHallName === null) {
                // Store the hall name of the first selected seat
                $firstHallName = $seat['hall'];
            } else if ($seat['hall'] !== $firstHallName) {
                // If any subsequent seat is in a different hall, validation fails
                $errorMessage = 'All selected seats must be in the same hall. Tickets span different halls: ' . htmlspecialchars($firstHallName) . ' vs ' . htmlspecialchars($seat['hall']);
                $isValid = false;
                break; // Exit loop immediately if a conflict is found
            }
        }
    }


    // PHP function: If validation passes, process reservation
    if ($isValid) {
        // MySQL function: Get database connection
        $conn = getDBConnection();
        
        // MySQL function: Step 1 - Race Condition Check
        // Check if ANY of the selected seats have been taken by someone else just now
        $alreadyReservedSeats = [];
        
        $checkSql = "SELECT seat_number FROM reservations WHERE movie_id = ? AND seat_id = ? AND reservation_date = ? AND time_slot = ? AND status = 'active'";
        $checkStmt = $conn->prepare($checkSql);

        foreach ($selectedSeats as $seat) {
            $currentSeatId = intval($seat['id']);
            $checkStmt->bind_param("iiss", $movieId, $currentSeatId, $reservationDate, $timeSlot);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                // PHP function: If found, this seat is already taken
                $alreadyReservedSeats[] = $seat['number'];
            }
        }
        $checkStmt->close();

        if (count($alreadyReservedSeats) > 0) {
            // PHP function: If any seats were taken, block the whole transaction
            $errorMessage = 'Error: The following seats have just been booked by someone else: ' . implode(', ', $alreadyReservedSeats);
            $isValid = false;
        } else {
            // MySQL function: Step 2: Insert reservations into database
            $insertSql = "INSERT INTO reservations (member_email, movie_id, movie_title, seat_id, seat_number, hall_name, reservation_date, time_slot, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            $insertStmt = $conn->prepare($insertSql);
            
            // PHP function: Arrays to collect data for Node.js response
            $allSeatNumbers = [];
            $finalHallName = ''; // Capture one hall name for the receipt
            $allInserted = true;

            // PHP function: Loop through each selected seat and insert record
            foreach ($selectedSeats as $seat) {
                $currentSeatId = intval($seat['id']);
                $currentSeatNum = $seat['number'];
                $currentHall = $seat['hall'];
                
                $finalHallName = $currentHall; 
                $allSeatNumbers[] = $currentSeatNum;

                $insertStmt->bind_param("sisissss", $userEmail, $movieId, $movieTitle, $currentSeatId, $currentSeatNum, $currentHall, $reservationDate, $timeSlot);
                
                if (!$insertStmt->execute()) {
                    $allInserted = false;
                    // Note: In a production environment, transaction rollback should be implemented here
                }
            }
            $insertStmt->close();

            if ($allInserted) {
                $success = true;

                // PHP function: Prepare and send data to Node.js Express server
                // Combine all seat numbers into a string (e.g., "A1, A2, B5")
                $seatNumberString = implode(', ', $allSeatNumbers);
                $nodeServerUrl = 'http://localhost:3000/thankyou';

                // Prepare data payload
                $postData = array(
                    'email' => $userEmail,
                    'movieTitle' => $movieTitle,
                    'seatNumber' => $seatNumberString, // Node.js will display this combined string
                    'hallName' => $finalHallName,
                    'reservationDate' => $reservationDate,
                    'timeSlot' => $timeSlot
                );

                // PHP cURL function: Initialize cURL session
                $ch = curl_init($nodeServerUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                // PHP cURL function: Execute cURL request
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // PHP cURL function: Check if Node.js server responded successfully
                if ($httpCode == 200) {
                    // PHP cURL function: Display the response from Node.js server (Thank You page)
                    echo $response;
                    exit();
                } else {
                    $errorMessage = 'Reservation saved but unable to connect to confirmation server. Please contact support.';
                    $success = false;
                }
            } else {
                $errorMessage = 'Failed to save some reservations. Please contact support.';
            }
        }
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
        // jQuery fade-in animation
        $(document).ready(function() {
            $('main').addClass('fade-in');
        });
    </script>
</body>
</html>