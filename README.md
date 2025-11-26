# CineMax Theatre - Cinema Seat Reservation System

**SEHS4517 Web Application Development and Management**
Semester 1, 2025-2026

## 📁 Project Structure

```
web project/
│
├── index.php                    # Homepage (dynamic movie display)
├── login.html                   # Login form page
├── register.html                # Registration form page
│
├── includes/                    # PHP Backend Files
│   ├── config.php              # Database configuration
│   ├── auth/                   # Authentication modules
│   │   ├── login.php          # Login processing
│   │   └── register.php       # Registration processing
│   └── booking/                # Booking modules
│       ├── reservation.php    # Seat reservation page
│       └── reserve.php        # Reservation processing
│
├── assets/                      # Public Assets
│   ├── css/                    # Stylesheets
│   │   ├── base.css           # Base styles & typography
│   │   ├── layout.css         # Layout & structure
│   │   ├── components.css     # UI components
│   │   └── responsive.css     # Responsive design
│   ├── js/                     # JavaScript
│   │   └── main.js            # Main JS (carousel, animations)
│   └── images/                 # Images & media
│       ├── logo.svg           # CineMax logo
│       └── Warriors_of_Future.jpg  # Movie poster
│
├── database/                    # Database Files
│   └── cinema_reservation.sql  # MySQL schema & data
│
├── server/                      # Node.js Express Server
│   ├── server.js              # Express server (Thank You page)
│   ├── package.json           # Node dependencies
│   └── node_modules/          # Node packages
│
└── README.md                    # Project documentation
```

##  Quick Start

### 1. Start Apache & MySQL (XAMPP)
### 2. Import `database/cinema_reservation.sql` to phpMyAdmin
### 3. Start Node.js server: `cd server && node server.js`
### 4. Access: `http://localhost/web-project/index.php`

## Initial Test Account

The database includes a pre-created test account for immediate testing:

| Field        | Value           |
|--------------|-----------------|
| **Email**    | `test@test.com` |
| **Password** | `123456`          |
| **Name**     | Test Test       |

**Usage:**
1. Click "Login" on the homepage
2. Enter the credentials above
3. Start making reservations!

> **Note:** The password is securely hashed using bcrypt in the database.

##  Features

- Dynamic movie loading from database
- Secure user authentication (bcrypt password hashing)
- Visual cinema seat selection layout
- Real-time seat availability (shows occupied/available seats)
- Status-based reservation system (active/cancelled/completed)
- Professional cinema theme with responsive design
- jQuery animations and interactive elements
- Node.js Express.js integration for confirmation page

---
**© 2025 CineMax Theatre**
