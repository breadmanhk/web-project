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

    // Get all seats and check their availability status
    $seatsSql = "SELECT seat_id, hall_name, seat_number, seat_type, description FROM seats ORDER BY hall_name, seat_number";
    $seatsResult = $conn->query($seatsSql);

    if ($seatsResult->num_rows > 0) {
        while ($row = $seatsResult->fetch_assoc()) {
            // Check if this seat is already reserved (status='active') for the selected movie, date and time
            $checkSql = "SELECT reservation_id FROM reservations WHERE movie_id = ? AND seat_id = ? AND reservation_date = ? AND time_slot = ? AND status = 'active'";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("iiss", $selectedMovieId, $row['seat_id'], $selectedDate, $selectedTime);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            // Add availability status to the seat data
            $row['is_booked'] = ($checkResult->num_rows > 0);
            $availableSeats[] = $row; // Now includes ALL seats (booked and available)

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

                    <?php if (count($availableSeats) > 0):
                        // Count available vs booked seats
                        $totalSeats = count($availableSeats);
                        $bookedSeats = 0;
                        foreach ($availableSeats as $seat) {
                            if ($seat['is_booked']) {
                                $bookedSeats++;
                            }
                        }
                        $availableCount = $totalSeats - $bookedSeats;
                    ?>
                        <div class="hero-banner">
                            <p><strong>Movie:</strong> <?php echo htmlspecialchars($selectedMovieTitle); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($selectedDate); ?> |
                               <strong>Time:</strong> <?php echo htmlspecialchars($selectedTime); ?></p>
                            <p class="price-tag"><strong><?php echo $availableCount; ?></strong> available / <strong><?php echo $totalSeats; ?></strong> total seats</p>
                        </div>

                        <!-- Cinema Screen Indicator -->
                        <div class="cinema-screen">
                            <div class="screen-bar">SCREEN</div>
                        </div>

                        <div style="text-align: center; margin: 30px auto; max-width: 700px;">
                            <div style="display: inline-flex; gap: 20px; align-items: center; flex-wrap: wrap; justify-content: center;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #2ecc71 0%, #27ae60 100%); border: 3px solid #2ecc71; border-radius: 8px;"></div>
                                    <span style="color: #ccc;">Available</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #9b59b6 0%, #8e44ad 100%); border: 3px solid #9b59b6; border-radius: 8px;"></div>
                                    <span style="color: #ccc;">VIP</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #ff8c00 0%, #ff6600 100%); border: 3px solid #ff8c00; border-radius: 8px; box-shadow: 0 0 10px rgba(255, 140, 0, 0.6);"></div>
                                    <span style="color: #ff8c00; font-weight: 600;">Selected</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #555 0%, #333 100%); border: 3px solid #666; border-radius: 8px; opacity: 0.6;"></div>
                                    <span style="color: #999;">Occupied</span>
                                </div>
                            </div>
                            <p style="color: #d4af37; margin-top: 15px; font-size: 14px;">💡 Click on any available seat to select it</p>
                        </div>

                        <!-- Organize seats by hall and create cinema layout -->
                        <?php
                        // Group seats by hall
                        $seatsByHall = [];
                        foreach ($availableSeats as $seat) {
                            $seatsByHall[$seat['hall_name']][] = $seat;
                        }

                        foreach ($seatsByHall as $hallName => $hallSeats):
                        ?>
                            <div class="hall-section">
                                <h3 style="color: #d4af37; text-align: center; margin: 30px 0 20px 0;">
                                    <?php echo htmlspecialchars($hallName); ?>
                                </h3>

                                <div class="cinema-seat-grid">
                                    <?php foreach ($hallSeats as $seat):
                                        $isBooked = $seat['is_booked'];
                                        $seatClass = ($seat['seat_type'] == 'VIP') ? 'vip-seat' : 'standard-seat';
                                        if ($isBooked) {
                                            $seatClass .= ' booked-seat';
                                        }
                                        $titleText = htmlspecialchars($seat['hall_name']) . ' - Seat ' . htmlspecialchars($seat['seat_number']) . ' (' . htmlspecialchars($seat['seat_type']) . ')';
                                        if ($isBooked) {
                                            $titleText .= ' - OCCUPIED';
                                        }
                                    ?>
                                        <div class="cinema-seat <?php echo $seatClass; ?>"
                                             data-seat-id="<?php echo $seat['seat_id']; ?>"
                                             data-seat-number="<?php echo htmlspecialchars($seat['seat_number']); ?>"
                                             data-hall-name="<?php echo htmlspecialchars($seat['hall_name']); ?>"
                                             data-seat-type="<?php echo htmlspecialchars($seat['seat_type']); ?>"
                                             data-description="<?php echo htmlspecialchars($seat['description']); ?>"
                                             data-is-booked="<?php echo $isBooked ? 'true' : 'false'; ?>"
                                             title="<?php echo $titleText; ?>">
                                            <div class="seat-icon"><?php echo $isBooked ? '🚫' : '💺'; ?></div>
                                            <div class="seat-label"><?php echo htmlspecialchars($seat['seat_number']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Selected Seat Info Box -->
                        <div id="seat-info-box" style="display: none;" class="seat-info-card">
                            <h4>Selected Seat Information</h4>
                            <p><strong>Hall:</strong> <span id="info-hall"></span></p>
                            <p><strong>Seat:</strong> <span id="info-seat"></span></p>
                            <p><strong>Type:</strong> <span id="info-type"></span></p>
                            <p><strong>Details:</strong> <span id="info-desc"></span></p>
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

            // Cinema seat click handler
            $('.cinema-seat').on('click', function() {
                // Check if seat is already booked
                var isBooked = $(this).data('is-booked');
                if (isBooked === 'true' || $(this).hasClass('booked-seat')) {
                    // Show alert for occupied seat
                    alert('This seat is already occupied. Please select another seat.');
                    return; // Stop execution
                }

                // Remove selection from other seats
                $('.cinema-seat').removeClass('selected');

                // Add selection to clicked seat
                $(this).addClass('selected');

                // Get seat information
                selectedSeatId = $(this).data('seat-id');
                selectedSeatNumber = $(this).data('seat-number');
                selectedHallName = $(this).data('hall-name');
                var seatType = $(this).data('seat-type');
                var seatDescription = $(this).data('description');

                // Update hidden form fields
                $('#selectedSeatId').val(selectedSeatId);
                $('#selectedSeatNumber').val(selectedSeatNumber);
                $('#selectedHallName').val(selectedHallName);

                // Update seat info box
                $('#info-hall').text(selectedHallName);
                $('#info-seat').text(selectedSeatNumber);
                $('#info-type').text(seatType);
                $('#info-desc').text(seatDescription);
                $('#seat-info-box').fadeIn();

                // Display selection message
                $('#selectedSeatDisplay').text(selectedHallName + ' - Seat ' + selectedSeatNumber);
                $('#selectionMessage').fadeIn();

                // Enable reserve button
                $('#reserveBtn').prop('disabled', false);

                // Smooth scroll to seat info
                $('html, body').animate({
                    scrollTop: $('#seat-info-box').offset().top - 100
                }, 500);
            });

            // Clear selection button
            $('#clearSelectionBtn').on('click', function() {
                $('.cinema-seat').removeClass('selected');
                $('#selectedSeatId').val('');
                $('#selectedSeatNumber').val('');
                $('#selectedHallName').val('');
                $('#selectionMessage').fadeOut();
                $('#seat-info-box').fadeOut();
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
