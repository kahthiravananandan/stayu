# StayU - Verified Off-Campus Accommodation Platform

> A full-stack web application connecting UKM students with verified landlords through property listings, search & filter, booking flows, and real-time chat.

![PHP](https://img.shields.io/badge/PHP-8-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?logo=mysql&logoColor=white)
![Firebase](https://img.shields.io/badge/Firebase-Realtime_DB-FFCA28?logo=firebase&logoColor=black)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla_ES6-F7DF1E?logo=javascript&logoColor=black)
![Status](https://img.shields.io/badge/Status-In_Progress-orange)

---

## Overview

StayU solves a real problem at UKM: students have no reliable way to find and verify off-campus housing. The platform introduces a gatekeeping layer - landlords must register and submit documents before listing - and surfaces location data so students can make informed decisions.

Built as a Final Year Project with a full SDLC approach: requirements gathering, ERD and normalisation, MVC architecture, implementation, and manual testing.

---

## Features

| Area | What it does |
|---|---|
| **Authentication** | Secure login/register for three roles - student, owner (individual & corporate), admin |
| **Property listings** | Owners post listings with photos, price, location, and amenities |
| **Search & filter** | Students search by location, price range, and property type |
| **Booking flow** | Students submit booking requests; owners accept or decline |
| **Live chat** | Real-time messaging between student and owner using Firebase |
| **Maps** | Google Maps JavaScript API shows property location on every listing page |
| **Admin panel** | Approve/suspend owner accounts and moderate listings |
| **Corporate owners** | Corporate accounts upload business documents; individual accounts do not — gated by `owner_type` column |

---

## Tech Stack

### Backend
- **PHP 8** - custom MVC architecture (no framework). Front controller in `index.php` parses the URL and routes to the correct controller class.
- **PDO** with prepared statements throughout - no raw queries, no SQL injection surface.
- **Session-based RBAC** - `require_role()` helper in `helpers/rbac.php` reads `$_SESSION['role']` and redirects to 403 on mismatch.

### Security hardening
- `session_regenerate_id(true)` on login
- 30-minute inactivity timeout
- `HttpOnly` and `SameSite=Lax` cookie flags
- 5-minute database account-status recheck (catches suspended sessions mid-session)
- `password_hash(PASSWORD_DEFAULT)` (bcrypt) + `password_verify()`

### Database
- **MySQL / MariaDB** (via XAMPP) - 10 tables, InnoDB engine, `utf8mb4` charset
- **Firebase Realtime Database** - used exclusively for live chat messages (no chat history in MySQL)

### Frontend
- Vanilla **HTML5, CSS3, JavaScript** - no frameworks, no npm
- **Google Maps JavaScript API** via CDN script tag
- **Firebase SDK v10** via ESM CDN module imports in view files

---

## Architecture

```
stayu/
├── index.php              # Front controller — URL parsing & routing
├── controllers/           # One controller per feature area
├── models/                # PDO database logic
├── views/                 # HTML templates
├── helpers/
│   └── rbac.php           # require_role() access guard
├── config/
│   └── database.php       # PDO connection
└── public/
    └── assets/            # CSS, JS, images
```

### Database schema (10 tables)

`users` · `properties` · `property_photos` · `amenities` · `property_amenities` · `bookings` · `reviews` · `documents` · `sessions` · `notifications`

> Full ERD available on request / see `/docs` folder.

---

## Local Setup

### Prerequisites
- XAMPP (PHP 8 + MariaDB)
- A Firebase project with Realtime Database enabled
- A Google Maps JavaScript API key

### Steps

```bash
# 1. Clone the repo
git clone https://github.com/kahthiravananandan/stayu.git
cd stayu

# 2. Import the database
# Open phpMyAdmin, create a database named `stayu`, import /database/stayu.sql

# 3. Configure environment
cp config/database.example.php config/database.php
# Edit database.php — set DB host, name, user, password

# 4. Add Firebase config
# In views/chat.php, replace the Firebase config object with your project credentials

# 5. Add Google Maps API key
# In views/listing.php, replace YOUR_API_KEY in the script src

# 6. Start Apache & MySQL via XAMPP, navigate to:
http://localhost/stayu
```

---

## Author

**Kahthiravan Anandan** — [LinkedIn](https://linkedin.com/in/kahthiravananandan) · [Email](mailto:kahthiravananandan@gmail.com)

Bachelor of Software Engineering (Information System Development), UKM · Final Year Project 2026
