<?php
/**
 * SEHS4517 Web Application Development and Management
 * CineMax Theatre - Dynamic Homepage
 * Fetches movie data from MySQL database
 */

// Include database configuration
require_once 'includes/config.php';

// Fetch movies from database
$conn = getDBConnection();
$sql = "SELECT movie_id, movie_title, genre, duration, rating, description FROM movies ORDER BY movie_id ASC";
$result = $conn->query($sql);

$movies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CineMax Theatre - Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="assets/css/layout.css" />
    <link rel="stylesheet" href="assets/css/components.css" />
    <link rel="stylesheet" href="assets/css/responsive.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Promotional Banner -->
    <div class="promo-banner">
        Join CineMax A-List! Get exclusive benefits and save up to 4 movies every week. <a href="register.html">Register Now</a>
    </div>

    <!-- Header Navigation -->
    <header>
        <div class="header-content">
            <a href="index.php" style="text-decoration: none;">
                <div class="logo-container">
                    <img src="assets/images/logo.svg" alt="CineMax Theatre Logo" class="logo" />
                    <h1>CINEMAX</h1>
                </div>
            </a>
            <nav>
                <a href="register.html">Register</a>
                <a href="login.html">Sign In</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <main class="fade-in">
            <!-- Movies Section -->
            <h2>Movies at CineMax</h2>

            <!-- Movie Tabs -->
            <div class="movie-tabs">
                <a href="#" class="active">Now Playing</a>
                <a href="#">Coming Soon</a>
                <a href="#">On Demand</a>
            </div>

            <!-- Movie Carousel -->
            <div class="movie-carousel-container">
                <button class="carousel-btn carousel-prev">‹</button>
                <div class="movie-carousel">
                    <div class="movie-track">
                        <?php if (count($movies) > 0): ?>
                            <?php foreach ($movies as $movie): ?>
                                <!-- Movie Card -->
                                <div class="movie-card"
                                     data-title="<?php echo htmlspecialchars($movie['movie_title']); ?>"
                                     data-description="<?php echo htmlspecialchars($movie['description']); ?>"
                                     data-duration="<?php echo htmlspecialchars($movie['duration']); ?>"
                                     data-rating="<?php echo htmlspecialchars($movie['rating']); ?>"
                                     data-genre="<?php echo htmlspecialchars($movie['genre']); ?>">
                                    <?php
                                    // Generate poster image filename from movie title, follow rules and added by Jacky
                                    $posterFile = 'assets/images/' . str_replace(' ', '_', $movie['movie_title']) . '.jpg';

                                    // Check if poster image exists
                                    if (file_exists($posterFile)):
                                    ?>
                                        <img src="<?php echo htmlspecialchars($posterFile); ?>" alt="<?php echo htmlspecialchars($movie['movie_title']); ?>" class="movie-poster" />
                                    <?php else: ?>
                                        <div class="movie-poster">🎬</div>
                                    <?php endif; ?>

                                    <div class="movie-info">
                                        <h4><?php echo htmlspecialchars($movie['movie_title']); ?></h4>
                                        <div class="movie-meta">
                                            <span><?php echo htmlspecialchars($movie['duration']); ?></span>
                                            <span>| <?php echo htmlspecialchars($movie['rating']); ?></span>
                                        </div>
                                        <span class="genre-tag"><?php echo htmlspecialchars($movie['genre']); ?></span>
                                        <p style="color: #999; font-size: 13px;">Released November, 2025</p>
                                        <p class="movie-description"><?php echo htmlspecialchars($movie['description']); ?></p>
                                        <a href="login.html" class="get-tickets-btn">Get Tickets</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- No movies available -->
                            <div class="message message-info" style="margin: 40px auto; max-width: 600px;">
                                <h3>No Movies Available</h3>
                                <p>Please check back later for upcoming movies!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="carousel-btn carousel-next">›</button>
            </div>

            <!-- About Section -->
            <div class="intro-text">
                <h3 style="text-align: center; color: #ffffff; margin-top: 60px;">Welcome to CineMax Theatre</h3>
                <p>
                    Welcome to CineMax Theatre, Hong Kong's premier destination for cinematic excellence and unforgettable entertainment experiences. Since our establishment in 2018, we have been dedicated to providing movie lovers with state-of-the-art facilities, exceptional sound systems, and crystal-clear projection technology that brings every frame to life.
                </p>
                <p>
                    Our modern complex features three distinctive screening halls, each designed to deliver an immersive viewing experience. Hall 1 offers premium seating with luxury recliners and VIP services, perfect for those seeking ultimate comfort. Hall 2 provides standard comfortable seating suitable for all audiences, while Hall 3 showcases our pride and joy - an authentic IMAX theater with the largest screen in the region and cutting-edge sound technology that places you right in the heart of the action.
                </p>
                <p>
                    At CineMax Theatre, we understand that great cinema is more than just watching a film. It is about creating memorable moments and shared experiences. Our carefully curated selection includes the latest blockbusters, independent films, international cinema, and special screenings of classic movies. We pride ourselves on offering diverse programming that caters to all tastes and ages.
                </p>
                <p>
                    Our commitment to customer satisfaction extends beyond the screen. We have invested in comfortable seating with optimal viewing angles, superior acoustics engineered by industry experts, and climate-controlled environments to ensure maximum comfort throughout your visit. Our friendly staff members are trained to provide excellent service and assist with any needs you may have during your cinema experience.
                </p>
                <p>
                    To enhance convenience, we have launched our innovative online seat reservation system. This platform allows you to browse available movies, select your preferred showtime, choose your ideal seat from our interactive seating chart, and secure your booking instantly. No more waiting in long queues or worrying about sold-out shows. Simply register as a member, log in, and reserve your seats from the comfort of your home or on the go.
                </p>
                <p>
                    Join thousands of satisfied moviegoers who have made CineMax Theatre their preferred entertainment destination. Register today to unlock exclusive benefits, receive notifications about upcoming releases, and enjoy seamless booking experiences. Your cinematic journey awaits!
                </p>
            </div>

            <!-- Features -->
            <div class="features">
                <div class="feature-card">
                    <h3>Latest Blockbusters</h3>
                    <p>Experience the newest releases on the big screen with premium sound and picture quality</p>
                </div>
                <div class="feature-card">
                    <h3>IMAX Experience</h3>
                    <p>Immerse yourself in larger-than-life cinema with our state-of-the-art IMAX theater</p>
                </div>
                <div class="feature-card">
                    <h3>Online Booking</h3>
                    <p>Reserve your favorite seats in advance through our easy and secure online system</p>
                </div>
                <div class="feature-card">
                    <h3>Luxury Seating</h3>
                    <p>Enjoy plush recliners and spacious VIP seats for the ultimate comfort experience</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer>
            <p>&copy; 2025 CineMax Theatre. All rights reserved.</p>
            <p>SEHS4517 Web Application Development and Management</p>
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
