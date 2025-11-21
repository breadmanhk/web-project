<?php
/**
 * SEHS4517 Web Application Development and Management
 * Cinema Seat Reservation Page PHP Script
 * Allows logged-in members to reserve cinema seats for movies
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id']) || !isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header('Location: login.html');
    exit();
}

// Include database configuration
require_once '../config.php';

// Get user information from session
$userEmail = $_SESSION['email'];
$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];

// Initialize variables
$selectedMovieId = '';
$selectedMovieTitle = '';
$selectedDate = '';
$selectedTime = '';
$availableSeats = array();
$showSeats = false;

// Time slots available for cinema
$timeSlots = array(
    '10:00-12:30', '13:00-15:30', '16:00-18:30',
    '19:00-21:30', '22:00-00:30'
);

// Get all movies from database
$conn = getDBConnection();
$moviesSql = "SELECT movie_id, movie_title, genre, duration, rating, description FROM movies ORDER BY movie_title";
$moviesResult = $conn->query($moviesSql);
$movies = array();
if ($moviesResult->num_rows > 0) {
    while ($row = $moviesResult->fetch_assoc()) {
        $movies[] = $row;
    }
}

// Check if movie, date and time are submitted
if (isset($_POST['searchSeats']) && isset($_POST['movieId']) && isset($_POST['reservationDate']) && isset($_POST['timeSlot'])) {
    $selectedMovieId = intval($_POST['movieId']);
    $selectedDate = $_POST['reservationDate'];
    $selectedTime = $_POST['timeSlot'];

    // Get selected movie title
    foreach ($movies as $movie) {
        if ($movie['movie_id'] == $selectedMovieId) {
            $selectedMovieTitle = $movie['movie_title'];
            break;
        }
    }

    // Get all seats
    $seatsSql = "SELECT seat_id, hall_name, seat_number, seat_type, description FROM seats ORDER BY hall_name, seat_number";
    $seatsResult = $conn->query($seatsSql);

    if ($seatsResult->num_rows > 0) {
        while ($row = $seatsResult->fetch_assoc()) {
            // Check if this seat is already reserved for the selected movie, date and time
            $checkSql = "SELECT reservation_id FROM reservations WHERE movie_id = ? AND seat_id = ? AND reservation_date = ? AND time_slot = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("iiss", $selectedMovieId, $row['seat_id'], $selectedDate, $selectedTime);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            // If not reserved, add to available seats
            if ($checkResult->num_rows == 0) {
                $availableSeats[] = $row;
            }

            $checkStmt->close();
        }
    }

    $showSeats = true;
}

$conn->close();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Seat Reservation - CineMax Theatre</title>
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
            <h2>Cinema Seat Reservation</h2>

            <div class="message message-info">
                <p>Welcome, <strong><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></strong>!</p>
                <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
            </div>

            <!-- Movie, Date and Time Selection Form -->
            <form id="searchForm" method="post" action="reservation.php">
                <h3>Step 1: Select Movie, Date and Time</h3>

                <div class="form-group">
                    <label for="movieId">Select Movie: <span style="color: #dc1f26;">*</span></label>
                    <select id="movieId" name="movieId" required="required">
                        <option value="">-- Select a Movie --</option>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?php echo $movie['movie_id']; ?>"
                                <?php echo ($selectedMovieId == $movie['movie_id']) ? 'selected="selected"' : ''; ?>>
                                <?php echo htmlspecialchars($movie['movie_title']) . ' (' . htmlspecialchars($movie['genre']) . ') - ' . htmlspecialchars($movie['duration']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reservationDate">Screening Date: <span style="color: #dc1f26;">*</span></label>
                    <input type="date" id="reservationDate" name="reservationDate" required="required"
                           value="<?php echo htmlspecialchars($selectedDate); ?>"
                           min="<?php echo date('Y-m-d'); ?>" />
                </div>

                <div class="form-group">
                    <label for="timeSlot">Showtime: <span style="color: #dc1f26;">*</span></label>
                    <select id="timeSlot" name="timeSlot" required="required">
                        <option value="">-- Select Showtime --</option>
                        <?php foreach ($timeSlots as $slot): ?>
                            <option value="<?php echo $slot; ?>"
                                <?php echo ($selectedTime == $slot) ? 'selected="selected"' : ''; ?>>
                                <?php echo $slot; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" name="searchSeats" class="btn btn-primary">Search Available Seats</button>
                    <button type="button" id="clearSearchBtn" class="btn btn-secondary">Clear</button>
                    <button type="button" id="cancelBtn" class="btn btn-danger">Cancel</button>
                </div>
            </form>

            <?php if ($showSeats): ?>
                <!-- Seat Selection Form -->
                <form id="reservationForm" method="post" action="reserve.php">
                    <h3>Step 2: Select Your Seat</h3>

                    <input type="hidden" name="movieId" value="<?php echo htmlspecialchars($selectedMovieId); ?>" />
                    <input type="hidden" name="movieTitle" value="<?php echo htmlspecialchars($selectedMovieTitle); ?>" />
                    <input type="hidden" name="reservationDate" value="<?php echo htmlspecialchars($selectedDate); ?>" />
                    <input type="hidden" name="timeSlot" value="<?php echo htmlspecialchars($selectedTime); ?>" />

                    <?php if (count($availableSeats) > 0): ?>
                        <div class="hero-banner">
                            <p><strong>Movie:</strong> <?php echo htmlspecialchars($selectedMovieTitle); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($selectedDate); ?> |
                               <strong>Time:</strong> <?php echo htmlspecialchars($selectedTime); ?></p>
                            <p class="price-tag"><strong><?php echo count($availableSeats); ?></strong> seats available</p>
                        </div>

                        <p style="text-align: center; color: #d4af37; margin: 20px 0;">Click on a seat to select it</p>

                        <div class="seat-grid">
                            <?php foreach ($availableSeats as $seat): ?>
                                <div class="seat-card"
                                     data-seat-id="<?php echo $seat['seat_id']; ?>"
                                     data-seat-number="<?php echo htmlspecialchars($seat['seat_number']); ?>"
                                     data-hall-name="<?php echo htmlspecialchars($seat['hall_name']); ?>">
                                    <h4><?php echo htmlspecialchars($seat['hall_name']) . ' - ' . htmlspecialchars($seat['seat_number']); ?></h4>
                                    <span class="seat-type <?php echo ($seat['seat_type'] == 'VIP') ? 'vip-badge' : ''; ?>">
                                        <?php echo htmlspecialchars($seat['seat_type']); ?>
                                    </span>
                                    <p><?php echo htmlspecialchars($seat['description']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" id="selectedSeatId" name="seatId" value="" />
                        <input type="hidden" id="selectedSeatNumber" name="seatNumber" value="" />
                        <input type="hidden" id="selectedHallName" name="hallName" value="" />

                        <div id="selectionMessage" style="display: none;" class="message message-success">
                            <p>Selected: <strong id="selectedSeatDisplay"></strong></p>
                        </div>

                        <div class="btn-group">
                            <button type="submit" id="reserveBtn" class="btn btn-success" disabled="disabled">Reserve</button>
                            <button type="button" id="clearSelectionBtn" class="btn btn-secondary">Clear Selection</button>
                        </div>
                    <?php else: ?>
                        <div class="message message-error">
                            <p>Sorry, no seats are available for this movie, date and time combination.</p>
                            <p>Please try a different showtime.</p>
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 CineMax Theatre. All rights reserved.</p>
            <p>SEHS4517 Web Application Development and Management</p>
        </footer>
    </div>

    <script>
        /**
         * Reservation page JavaScript and jQuery functionality
         */
        $(document).ready(function() {
            var selectedSeatId = '';
            var selectedSeatNumber = '';
            var selectedHallName = '';

            // Clear search form button
            $('#clearSearchBtn').on('click', function() {
                $('#movieId').val('');
                $('#reservationDate').val('');
                $('#timeSlot').val('');
            });

            // Cancel button - return to home page
            $('#cancelBtn').on('click', function() {
                window.location.href = '../../index.php';
            });

            // Seat card click handler
            $('.seat-card').on('click', function() {
                // Remove selection from other cards
                $('.seat-card').removeClass('selected');

                // Add selection to clicked card
                $(this).addClass('selected');

                // Get seat information
                selectedSeatId = $(this).data('seat-id');
                selectedSeatNumber = $(this).data('seat-number');
                selectedHallName = $(this).data('hall-name');

                // Update hidden form fields
                $('#selectedSeatId').val(selectedSeatId);
                $('#selectedSeatNumber').val(selectedSeatNumber);
                $('#selectedHallName').val(selectedHallName);

                // Display selection message
                $('#selectedSeatDisplay').text(selectedHallName + ' - Seat ' + selectedSeatNumber);
                $('#selectionMessage').fadeIn();

                // Enable reserve button
                $('#reserveBtn').prop('disabled', false);
            });

            // Clear selection button
            $('#clearSelectionBtn').on('click', function() {
                $('.seat-card').removeClass('selected');
                $('#selectedSeatId').val('');
                $('#selectedSeatNumber').val('');
                $('#selectedHallName').val('');
                $('#selectionMessage').fadeOut();
                $('#reserveBtn').prop('disabled', true);
            });

            // Form validation before submission
            $('#reservationForm').on('submit', function(e) {
                if ($('#selectedSeatId').val() === '') {
                    e.preventDefault();
                    alert('Please select a seat to reserve');
                    return false;
                }
                return true;
            });

            // Set minimum date to today
            var today = new Date().toISOString().split('T')[0];
            $('#reservationDate').attr('min', today);
        });
    </script>
</body>
</html>
