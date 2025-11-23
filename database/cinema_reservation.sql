-- SEHS4517 Web Application Development and Management
-- MySQL Database Schema for CineMax Theatre Reservation System
-- Create database and tables for the cinema seat reservation system

-- Create database and Images, Jacky
CREATE DATABASE IF NOT EXISTS cinema_reservation;
USE cinema_reservation;

-- Drop existing tables (in reverse order due to foreign key constraints) , Jacky
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS members;

-- Table for storing member registration information
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    mailing_address VARCHAR(200) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing movies and screening information
CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_title VARCHAR(150) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    duration VARCHAR(20) NOT NULL,
    rating VARCHAR(10) NOT NULL,
    description TEXT
);

-- Table for storing cinema halls and seats
CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    hall_name VARCHAR(50) NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    seat_type VARCHAR(30) NOT NULL,
    description VARCHAR(255)
);

-- Table for storing reservations
CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    member_email VARCHAR(100) NOT NULL,
    movie_id INT NOT NULL,
    movie_title VARCHAR(150) NOT NULL,
    seat_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    hall_name VARCHAR(50) NOT NULL,
    reservation_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (seat_id) REFERENCES seats(seat_id)
);
-- Insert member data, Jacky
INSERT INTO members (first_name, last_name, mailing_address, contact_phone, email, password) VALUES
('test', 'test', 'test','12345678','test@test.com','$2y$10$b9tUsaircu29XuPcJ4tmVu06vuJkP0xB3etHoD96XtHCcVvMsOJA6');

-- Insert movie data, Jacky
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Warriors of Future', 'Sci-Fi', '1 HR 40 MIN', 'IIB', 'In 2055, a meteorite strikes Earth and brings with it a mysterious plant species that threatens humanity.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Table For Six', 'Comedy', '1 HR 59 MIN', 'IIA', 'A family reunion dinner turns awkward when Bernard arrives with his new girlfriend, who happens to be big brother Steve old flame.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('The Sparring Partner', 'thriller', '2 HR 18 MIN', 'III', 'The intricate story begins when a young man partners with his friend to murder and dismember his parents.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Where The Wind Blows', 'Drama', '2 HR 24 MIN', 'III', 'The film is however set firmly in the past ranging from the 1920s to the 1980s and inspired by the “Four Great Sergeants” of post-war Hong Kong who amassed great personal wealth while working as police officers.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Now You See Me3', 'Crime Thriller', '1 HR 52 MIN', 'IIA', 'A diamond heist reunites retired Horsemen illusionists with new performers Greenblatt, Smith and Sessa as they target dangerous criminals.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Demon Slayer Kimetsu no Yaiba Infinity Castle', 'Animation', '2 HR 35 MIN', 'IIA', 'The Demon Slayer Corps are drawn into the Infinity Castle, where Tanjiro and the Hashira face terrifying Upper Rank demons in a desperate fight as the final battle against Muzan Kibutsuji begins.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Doraemon the Movie Nobita s Art World Tales', 'Animation', '1 HR 45 MIN', 'I', 'Set in the magnificent world of medieval Europe depicted in paintings. Doraemon and his friends jump into the "world of the painting" joined by Claire and her friends Milo and Chai as they emabark on a great adventure!.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('F1 The Movie', 'Sports', '2 HR 36 MIN', 'IIA', 'A Formula One driver comes out of retirement to mentor and team up with a younger driver.');
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('InitialD', 'Action', '2 HR 10 MIN', 'IIA', 'After winning his first competition, Takumi focuses his attention on drift racing, a sport he has unknowingly perfected while delivering tofu in his fathers Toyota AE86.');


-- Insert sample seat data for multiple halls
-- Hall 1: Premium Hall (30 seats)
INSERT INTO seats (hall_name, seat_number, seat_type, description) VALUES
('Hall 1', 'A1', 'Premium', 'Front row center with reclining seats'),
('Hall 1', 'A2', 'Premium', 'Front row center with reclining seats'),
('Hall 1', 'A3', 'Premium', 'Front row center with reclining seats'),
('Hall 1', 'A4', 'Premium', 'Front row center with reclining seats'),
('Hall 1', 'A5', 'Premium', 'Front row center with reclining seats'),
('Hall 1', 'B1', 'Premium', 'Second row with excellent view'),
('Hall 1', 'B2', 'Premium', 'Second row with excellent view'),
('Hall 1', 'B3', 'Premium', 'Second row with excellent view'),
('Hall 1', 'B4', 'Premium', 'Second row with excellent view'),
('Hall 1', 'B5', 'Premium', 'Second row with excellent view'),
('Hall 1', 'C1', 'Premium', 'Third row center position'),
('Hall 1', 'C2', 'Premium', 'Third row center position'),
('Hall 1', 'C3', 'Premium', 'Third row center position'),
('Hall 1', 'C4', 'Premium', 'Third row center position'),
('Hall 1', 'C5', 'Premium', 'Third row center position'),
('Hall 1', 'D1', 'Premium', 'Fourth row optimal viewing'),
('Hall 1', 'D2', 'Premium', 'Fourth row optimal viewing'),
('Hall 1', 'D3', 'Premium', 'Fourth row optimal viewing'),
('Hall 1', 'D4', 'Premium', 'Fourth row optimal viewing'),
('Hall 1', 'D5', 'Premium', 'Fourth row optimal viewing'),
('Hall 1', 'E1', 'Premium', 'Back row with extra legroom'),
('Hall 1', 'E2', 'Premium', 'Back row with extra legroom'),
('Hall 1', 'E3', 'Premium', 'Back row with extra legroom'),
('Hall 1', 'E4', 'Premium', 'Back row with extra legroom'),
('Hall 1', 'E5', 'Premium', 'Back row with extra legroom'),
('Hall 1', 'F1', 'VIP', 'VIP row with luxury recliners'),
('Hall 1', 'F2', 'VIP', 'VIP row with luxury recliners'),
('Hall 1', 'F3', 'VIP', 'VIP row with luxury recliners'),
('Hall 1', 'F4', 'VIP', 'VIP row with luxury recliners'),
('Hall 1', 'F5', 'VIP', 'VIP row with luxury recliners');

-- Hall 2: Standard Hall (30 seats)
INSERT INTO seats (hall_name, seat_number, seat_type, description) VALUES
('Hall 2', 'A1', 'Standard', 'Front row standard seating'),
('Hall 2', 'A2', 'Standard', 'Front row standard seating'),
('Hall 2', 'A3', 'Standard', 'Front row standard seating'),
('Hall 2', 'A4', 'Standard', 'Front row standard seating'),
('Hall 2', 'A5', 'Standard', 'Front row standard seating'),
('Hall 2', 'B1', 'Standard', 'Second row comfortable seats'),
('Hall 2', 'B2', 'Standard', 'Second row comfortable seats'),
('Hall 2', 'B3', 'Standard', 'Second row comfortable seats'),
('Hall 2', 'B4', 'Standard', 'Second row comfortable seats'),
('Hall 2', 'B5', 'Standard', 'Second row comfortable seats'),
('Hall 2', 'C1', 'Standard', 'Third row center position'),
('Hall 2', 'C2', 'Standard', 'Third row center position'),
('Hall 2', 'C3', 'Standard', 'Third row center position'),
('Hall 2', 'C4', 'Standard', 'Third row center position'),
('Hall 2', 'C5', 'Standard', 'Third row center position'),
('Hall 2', 'D1', 'Standard', 'Fourth row good viewing angle'),
('Hall 2', 'D2', 'Standard', 'Fourth row good viewing angle'),
('Hall 2', 'D3', 'Standard', 'Fourth row good viewing angle'),
('Hall 2', 'D4', 'Standard', 'Fourth row good viewing angle'),
('Hall 2', 'D5', 'Standard', 'Fourth row good viewing angle'),
('Hall 2', 'E1', 'Standard', 'Fifth row standard seating'),
('Hall 2', 'E2', 'Standard', 'Fifth row standard seating'),
('Hall 2', 'E3', 'Standard', 'Fifth row standard seating'),
('Hall 2', 'E4', 'Standard', 'Fifth row standard seating'),
('Hall 2', 'E5', 'Standard', 'Fifth row standard seating'),
('Hall 2', 'F1', 'Standard', 'Back row with good view'),
('Hall 2', 'F2', 'Standard', 'Back row with good view'),
('Hall 2', 'F3', 'Standard', 'Back row with good view'),
('Hall 2', 'F4', 'Standard', 'Back row with good view'),
('Hall 2', 'F5', 'Standard', 'Back row with good view');

-- Hall 3: IMAX Hall (20 seats)
INSERT INTO seats (hall_name, seat_number, seat_type, description) VALUES
('Hall 3', 'A1', 'IMAX', 'IMAX premium front row'),
('Hall 3', 'A2', 'IMAX', 'IMAX premium front row'),
('Hall 3', 'A3', 'IMAX', 'IMAX premium front row'),
('Hall 3', 'A4', 'IMAX', 'IMAX premium front row'),
('Hall 3', 'A5', 'IMAX', 'IMAX premium front row'),
('Hall 3', 'B1', 'IMAX', 'IMAX center position'),
('Hall 3', 'B2', 'IMAX', 'IMAX center position'),
('Hall 3', 'B3', 'IMAX', 'IMAX center position'),
('Hall 3', 'B4', 'IMAX', 'IMAX center position'),
('Hall 3', 'B5', 'IMAX', 'IMAX center position'),
('Hall 3', 'C1', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C2', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C3', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C4', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C5', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'D1', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D2', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D3', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D4', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D5', 'IMAX', 'IMAX luxury seating');

-- Members will be added through the registration form
