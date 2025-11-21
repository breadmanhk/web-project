/**
 * SEHS4517 Web Application Development and Management
 * Node.js Express Server for CineMax Theatre
 * Receives reservation data from PHP and generates thank you page
 */

// Import required modules
const express = require('express');
const bodyParser = require('body-parser');
const path = require('path');

// Create Express application
const app = express();

// Configure middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Serve static files (CSS, images)
app.use(express.static(path.join(__dirname, '..')));

// Set port number
const PORT = 3000;

/**
 * POST route to handle thank you page generation
 * Receives reservation data from PHP and generates HTML response
 */
app.post('/thankyou', (req, res) => {
    // Extract data from request body
    const email = req.body.email;
    const movieTitle = req.body.movieTitle;
    const seatNumber = req.body.seatNumber;
    const hallName = req.body.hallName;
    const reservationDate = req.body.reservationDate;
    const timeSlot = req.body.timeSlot;

    // Validate received data
    if (!email || !movieTitle || !seatNumber || !hallName || !reservationDate || !timeSlot) {
        res.status(400).send('Missing required reservation information');
        return;
    }

    // Generate thank you page HTML
    const htmlResponse = `
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservation Confirmed - CineMax Theatre</title>
    <link rel="stylesheet" href="../assets/css/base.css" />
    <link rel="stylesheet" href="../assets/css/layout.css" />
    <link rel="stylesheet" href="../assets/css/components.css" />
    <link rel="stylesheet" href="../assets/css/responsive.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="../assets/images/logo.svg" alt="CineMax Theatre Logo" class="logo" />
                <h1>CineMax Theatre</h1>
            </div>
        </div>
    </header>

    <div class="container">
        <main class="fade-in">
            <h2>Reservation Confirmed!</h2>

            <div class="message message-success">
                <h3>Thank You for Your Reservation!</h3>
                <p>Your cinema seat has been successfully reserved.</p>
            </div>

            <div style="background: linear-gradient(135deg, #2d0a0a 0%, #1a1a1a 100%); padding: 30px; border-radius: 15px; margin: 25px 0; border: 2px solid #dc1f26;">
                <h3 style="color: #ffd700; margin-bottom: 20px; text-align: center;">Reservation Details:</h3>

                <table style="width: 100%; background-color: #2a2a2a; border-radius: 10px;">
                    <tr>
                        <td style="padding: 15px; font-weight: bold; width: 40%; color: #d4af37;">Member Email:</td>
                        <td style="padding: 15px; color: #e0e0e0;">${escapeHtml(email)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px; font-weight: bold; color: #d4af37;">Movie:</td>
                        <td style="padding: 15px; color: #e0e0e0;">${escapeHtml(movieTitle)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px; font-weight: bold; color: #d4af37;">Cinema Hall:</td>
                        <td style="padding: 15px; color: #e0e0e0;">${escapeHtml(hallName)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px; font-weight: bold; color: #d4af37;">Seat Number:</td>
                        <td style="padding: 15px; color: #e0e0e0;">${escapeHtml(seatNumber)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px; font-weight: bold; color: #d4af37;">Screening Date:</td>
                        <td style="padding: 15px; color: #e0e0e0;">${escapeHtml(reservationDate)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px; font-weight: bold; color: #d4af37;">Showtime:</td>
                        <td style="padding: 15px; color: #e0e0e0;">${escapeHtml(timeSlot)}</td>
                    </tr>
                </table>
            </div>

            <div style="background-color: #2a1a1a; padding: 20px; border-radius: 10px; border-left: 5px solid #d4af37; margin: 25px 0;">
                <h4 style="color: #ffd700; margin-bottom: 15px;">Important Information:</h4>
                <ul style="color: #c0c0c0; line-height: 1.9;">
                    <li>Please arrive at least 15 minutes before the showtime</li>
                    <li>Bring a valid ID for verification at the entrance</li>
                    <li>Your reservation number has been sent to your email</li>
                    <li>Collect your ticket from the counter or self-service kiosk</li>
                    <li>Outside food and beverages are not permitted</li>
                    <li>For cancellations or changes, contact us at least 2 hours before showtime</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 35px; padding: 25px; background: linear-gradient(135deg, #dc1f26 0%, #8b0000 100%); border-radius: 15px;">
                <p style="font-size: 1.2em; color: white; margin-bottom: 10px;">Enjoy Your Movie!</p>
                <p style="color: #ffd700; font-size: 0.95em;">We look forward to seeing you at CineMax Theatre</p>
            </div>

            <nav style="text-align: center; margin-top: 30px;">
                <button onclick="window.location.href='../index.php'" class="btn btn-primary">OK</button>
            </nav>
        </main>

        <footer>
            <p>&copy; 2025 CineMax Theatre. All rights reserved.</p>
            <p>SEHS4517 Web Application Development and Management</p>
        </footer>
    </div>

    <script>
        /**
         * jQuery animations and effects for thank you page
         */
        $(document).ready(function() {
            // Fade-in animation
            $('main').addClass('fade-in');

            // Success message animation
            $('.message-success').hide().fadeIn(1200);

            // Log confirmation
            console.log('Cinema seat reservation confirmed successfully!');

            // Smooth scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    </script>
</body>
</html>
    `;

    // Send HTML response
    res.status(200).send(htmlResponse);

    // Log reservation to console
    console.log('Cinema Reservation confirmed:');
    console.log(`  Email: ${email}`);
    console.log(`  Movie: ${movieTitle}`);
    console.log(`  Hall: ${hallName}`);
    console.log(`  Seat: ${seatNumber}`);
    console.log(`  Date: ${reservationDate}`);
    console.log(`  Time: ${timeSlot}`);
});

/**
 * Helper function to escape HTML special characters
 * Prevents XSS attacks
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * GET route for testing server status
 */
app.get('/', (req, res) => {
    res.send('CineMax Theatre - Node.js Express Server is running');
});

/**
 * Start the server
 */
app.listen(PORT, () => {
    console.log('=================================================');
    console.log('CineMax Theatre - Node.js Express Server');
    console.log('SEHS4517 Web Application Development and Management');
    console.log('=================================================');
    console.log(`Server is running on http://localhost:${PORT}`);
    console.log('Waiting for seat reservation requests...');
    console.log('=================================================');
});
