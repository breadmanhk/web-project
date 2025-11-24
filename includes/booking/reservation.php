<?php
/**
 * SEHS4517 Web Application Development and Management
 * Class: Group 103
 * Group: 1
 * Cinema Seat Reservation Page PHP Script (Multi-Seat Version)
 * Allows logged-in members to reserve MULTIPLE cinema seats
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
$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];

// PHP function: Initialize variables we will use later
$selectedMovieId = '';
$selectedMovieTitle = '';
$selectedDate = '';
$selectedTime = '';
$selectedMoviePoster = '';
$availableSeats = array();
$showSeats = false;

if (isset($_GET['movie_id'])){
    $selectedMovieId = intval($_GET['movie_id']);
}

// PHP function: This will define available time slots
$timeSlots = array(
    '10:00-12:30', '13:00-15:30', '16:00-18:30',
    '19:00-21:30', '22:00-00:30'
);

// MySQL function: Get all movies from database
$conn = getDBConnection();
$moviesSql = "SELECT movie_id, movie_title, genre, duration, rating, description FROM movies ORDER BY movie_id ASC";
$moviesResult = $conn->query($moviesSql);
$movies = array();
if ($moviesResult->num_rows > 0) {
    while ($row = $moviesResult->fetch_assoc()) {
        $movies[] = $row;
    }
}

// PHP POST method: This will check if movie, date and time are submitted
if (isset($_POST['searchSeats']) && isset($_POST['movieId']) && isset($_POST['reservationDate']) && isset($_POST['timeSlot'])) {
    $selectedMovieId = intval($_POST['movieId']);
    $selectedDate = $_POST['reservationDate'];
    $selectedTime = $_POST['timeSlot'];

    // PHP function: Get selected movie title and poster path
    foreach ($movies as $movie) {
        if ($movie['movie_id'] == $selectedMovieId) {
            $selectedMovieTitle = $movie['movie_title'];
            $posterFileName = str_replace(' ', '_', $movie['movie_title']) . '.jpg';
            $posterPath = '../../assets/images/' . $posterFileName;
            
            // PHP function: This will check if poster image exists
            if (file_exists($posterPath)) {
                $selectedMoviePoster = $posterPath;
            } else {
                $selectedMoviePoster = ''; // Keep it empty if no image is found
            }
            
            break;
        }
    }

    // MySQL function: Get all seats and check their availability status
    $seatsSql = "SELECT seat_id, hall_name, seat_number, seat_type, description FROM seats ORDER BY hall_name, seat_number";
    $seatsResult = $conn->query($seatsSql);

    if ($seatsResult->num_rows > 0) {
        while ($row = $seatsResult->fetch_assoc()) {
            // MySQL function: Check if this seat is already reserved
            $checkSql = "SELECT reservation_id FROM reservations WHERE movie_id = ? AND seat_id = ? AND reservation_date = ? AND time_slot = ? AND status = 'active'";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("iiss", $selectedMovieId, $row['seat_id'], $selectedDate, $selectedTime);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            // MySQL function: Add availability status to the seat data
            $row['is_booked'] = ($checkResult->num_rows > 0);
            $availableSeats[] = $row; 

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
    <style>
        /* CSS Style for selected seats */
        .cinema-seat.selected .seat-icon {
            color: #ff8c00; 
            transform: scale(1.1);
        }
        .cinema-seat.selected {
            border-color: #ff8c00;
        }
        .cinema-seat-grid .cinema-seat .seat-icon {
            font-size: 24px;
            line-height: 1;
        }
    </style>
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
            <h2>Cinema Seat Reservation</h2>

            <div class="message message-info">
                <p>Welcome, <strong><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></strong>!</p>
                <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
            </div>

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
                <form id="reservationForm" method="post" action="reserve.php">
                    <h3>Step 2: Select Your Seats</h3>

                    <input type="hidden" name="movieId" value="<?php echo htmlspecialchars($selectedMovieId); ?>" />
                    <input type="hidden" name="movieTitle" value="<?php echo htmlspecialchars($selectedMovieTitle); ?>" />
                    <input type="hidden" name="reservationDate" value="<?php echo htmlspecialchars($selectedDate); ?>" />
                    <input type="hidden" name="timeSlot" value="<?php echo htmlspecialchars($selectedTime); ?>" />

                    <input type="hidden" id="selectedSeatsData" name="selectedSeatsData" value="" />
                    <input type="hidden" id="selectedHallName" name="hallName" value="" />

                    <?php if (count($availableSeats) > 0):
                        // Count available vs booked seats
                        $totalSeats = count($availableSeats);
                        $bookedSeats = 0;
                        foreach ($availableSeats as $seat) {
                            if ($seat['is_booked']) $bookedSeats++;
                        }
                        $availableCount = $totalSeats - $bookedSeats;
                    ?>
                        <div class="hero-banner" style="display: flex; align-items: center; gap: 30px;">
                            <?php if (!empty($selectedMoviePoster)): ?>
                            <img src="<?php echo htmlspecialchars($selectedMoviePoster); ?>" 
                                 alt="<?php echo htmlspecialchars($selectedMovieTitle); ?> Poster" 
                                 style="width: 100px; height: 150px; object-fit: cover; border-radius: 6px; border: 2px solid #d4af37;">
                            <?php endif; ?>
                            
                            <div> <p><strong>Movie:</strong> <?php echo htmlspecialchars($selectedMovieTitle); ?></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($selectedDate); ?> |
                                   <strong>Time:</strong> <?php echo htmlspecialchars($selectedTime); ?></p>
                                <p class="price-tag"><strong><?php echo $availableCount; ?></strong> available / <strong><?php echo $totalSeats; ?></strong> total seats</p>
                            </div>
                        </div>

                        <div class="cinema-screen">
                            <div class="screen-bar">SCREEN</div>
                        </div>

                        <div style="text-align: center; margin: 30px auto; max-width: 700px;">
                            <div style="display: inline-flex; gap: 20px; align-items: center; flex-wrap: wrap; justify-content: center;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #2ecc71 0%, #27ae60 100%); border: 3px solid #2ecc71; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:20px;"></div>
                                    <span style="color: #ccc;">Available</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #9b59b6 0%, #8e44ad 100%); border: 3px solid #9b59b6; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:20px;"></div>
                                    <span style="color: #ccc;">VIP</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #ff8c00 0%, #ff6600 100%); border: 3px solid #ff8c00; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:20px;"></div>
                                    <span style="color: #ff8c00; font-weight: 600;">Selected</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(180deg, #555 0%, #333 100%); border: 3px solid #666; border-radius: 8px; opacity: 0.6; display:flex; align-items:center; justify-content:center; font-size:20px;"></div>
                                    <span style="color: #999;">Occupied</span>
                                </div>
                            </div>
                            <p style="color: #d4af37; margin-top: 15px;">👆 Click on available seats to select multiple tickets</p>
                        </div>

                        <?php
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
                                        if ($isBooked) $seatClass .= ' booked-seat';
                                        
                                        $titleText = htmlspecialchars($seat['hall_name']) . ' - Seat ' . htmlspecialchars($seat['seat_number']);
                                        if ($isBooked) $titleText .= ' - OCCUPIED';
                                    ?>
                                        <div class="cinema-seat <?php echo $seatClass; ?>"
                                             data-seat-id="<?php echo $seat['seat_id']; ?>"
                                             data-seat-number="<?php echo htmlspecialchars($seat['seat_number']); ?>"
                                             data-hall-name="<?php echo htmlspecialchars($seat['hall_name']); ?>"
                                             data-seat-type="<?php echo htmlspecialchars($seat['seat_type']); ?>"
                                             data-is-booked="<?php echo $isBooked ? 'true' : 'false'; ?>"
                                             title="<?php echo $titleText; ?>">
                                            <div class="seat-icon">
                                                <?php 
                                                    if ($isBooked) {
                                                        echo '🚫'; // Occupied
                                                    } else {
                                                        echo '💺'; // Available
                                                    }
                                                ?>
                                            </div>
                                            <div class="seat-label"><?php echo htmlspecialchars($seat['seat_number']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div id="selectionMessage" style="display: none;" class="message message-success">
                            <p>Selected Seats: <strong id="selectedSeatDisplay"></strong></p>
                            <p style="font-size: 0.9em; margin-top: 5px;">Total Tickets: <strong id="ticketCount">0</strong> (Maximum 4 tickets)</p>
                        </div>

                        <div class="btn-group">
                            <button type="submit" id="reserveBtn" class="btn btn-success" disabled="disabled">Reserve Selected Seats</button>
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
        //jQuery function part
        $(document).ready(function() {
            // Javascript: Rule 1 - Maximum tickets allowed per transaction (Client-side check)
            var selectedSeats = []; 
            const MAX_TICKETS = 4;
            
            // jQuery: Set minimum date to today
            var today = new Date().toISOString().split('T')[0];
            $('#reservationDate').attr('min', today);

            // jQuery function: Clear search form button
            $('#clearSearchBtn').on('click', function() {
                $('#movieId').val('');
                $('#reservationDate').val('');
                $('#timeSlot').val('');
            });

            // jQuery function: Cancel button
            $('#cancelBtn').on('click', function() {
                window.location.href = '../../index.php';
            });

            // jQuery function: Cinema seat click handler (Multi-select logic with MAX_TICKETS check)
            $('.cinema-seat').on('click', function() {
                var isBooked = $(this).data('is-booked');
                if (isBooked === 'true' || $(this).hasClass('booked-seat')) {
                    alert('This seat is already occupied. Please select another seat.');
                    return;
                }

                var seatId = $(this).data('seat-id');
                var seatNumber = $(this).data('seat-number');
                var hallName = $(this).data('hall-name');
                
                // jQuery function: Check if seat is already in the selected array
                var existingIndex = selectedSeats.findIndex(function(s) {
                    return s.id === seatId;
                });

                if (existingIndex !== -1) {
                    // If exists -> Remove it (Deselect)
                    selectedSeats.splice(existingIndex, 1);
                    $(this).removeClass('selected');
                } else {
                    // Javascript: Rule 2 - Single Hall Check
                    if (selectedSeats.length > 0) {
                        var firstHall = selectedSeats[0].hall;
                        if (hallName !== firstHall) {
                            alert('You can only select seats within the same Hall (' + firstHall + ') for a single reservation.');
                            return; 
                        }
                    }
                    
                    // Javascript: Rule 3 - Maximum Ticket Check
                    if (selectedSeats.length >= MAX_TICKETS) {
                        alert('You can only reserve a maximum of ' + MAX_TICKETS + ' tickets per transaction.');
                        return; 
                    }
                    
                    // If not exists -> Add it (Select)
                    selectedSeats.push({
                        id: seatId,
                        number: seatNumber,
                        hall: hallName
                    });
                    $(this).addClass('selected');
                }

                updateUI();
            });

            // jQuery function: Function to update UI elements and hidden form fields
            function updateUI() {
                // 1. Update hidden JSON input field
                $('#selectedSeatsData').val(JSON.stringify(selectedSeats));
                
                // 2. Update Hall Name (Use the first seat's hall for reference)
                if (selectedSeats.length > 0) {
                    $('#selectedHallName').val(selectedSeats[0].hall);
                } else {
                    $('#selectedHallName').val('');
                }

                // 3. Update display text and buttons
                if (selectedSeats.length > 0) {
                    // Join all seat numbers with commas (e.g., "A1, A2, B5")
                    var numbers = selectedSeats.map(function(s) { return s.number; }).join(', ');
                    $('#selectedSeatDisplay').text(numbers);
                    $('#ticketCount').text(selectedSeats.length);
                    
                    $('#selectionMessage').fadeIn();
                    $('#reserveBtn').prop('disabled', false);
                } else {
                    $('#selectionMessage').fadeOut();
                    $('#reserveBtn').prop('disabled', true);
                }
            }

            // jQuery function: Clear selection button
            $('#clearSelectionBtn').on('click', function() {
                selectedSeats = [];
                $('.cinema-seat').removeClass('selected');
                updateUI();
            });

            // jQuery function: Form validation before submission
            $('#reservationForm').on('submit', function(e) {
                if (selectedSeats.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one seat to reserve.');
                    return false;
                }
                
                // jQuery function: Final check on submit (in case JavaScript manipulation happened)
                if (selectedSeats.length > MAX_TICKETS) {
                    e.preventDefault();
                    alert('Maximum of ' + MAX_TICKETS + ' tickets allowed. Please adjust your selection.');
                    return false;
                }

                return true;
            });
        });
    </script>
</body>
</html>