-- SEHS4517 Web Application Development and Management
-- MySQL Database Schema for CineMax Theatre Reservation System
-- Create database and tables for the cinema seat reservation system

-- Create database
CREATE DATABASE IF NOT EXISTS cinema_reservation;
USE cinema_reservation;

-- Drop existing tables (in reverse order due to foreign key constraints)
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (seat_id) REFERENCES seats(seat_id)
);

-- Insert movie data
INSERT INTO movies (movie_title, genre, duration, rating, description) VALUES
('Warriors of Future', 'Sci-Fi', '1 HR 40 MIN', 'IIB', 'In 2055, a meteorite strikes Earth and brings with it a mysterious plant species that threatens humanity.');

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
('Hall 3', 'B1', 'IMAX', 'IMAX center position'),
('Hall 3', 'B2', 'IMAX', 'IMAX center position'),
('Hall 3', 'B3', 'IMAX', 'IMAX center position'),
('Hall 3', 'B4', 'IMAX', 'IMAX center position'),
('Hall 3', 'C1', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C2', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C3', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'C4', 'IMAX', 'IMAX optimal viewing'),
('Hall 3', 'D1', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D2', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D3', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'D4', 'IMAX', 'IMAX luxury seating'),
('Hall 3', 'E1', 'IMAX', 'IMAX back row premium'),
('Hall 3', 'E2', 'IMAX', 'IMAX back row premium'),
('Hall 3', 'E3', 'IMAX', 'IMAX back row premium'),
('Hall 3', 'E4', 'IMAX', 'IMAX back row premium');

-- Members will be added through the registration form
