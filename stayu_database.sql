-- ============================================================
-- StayU : Sistem Carian Penginapan Luar Kampus Berpengesahan
-- Database Schema for MySQL / MariaDB (phpMyAdmin)
-- Author : Kahthiravan Anandan (A202584)
-- Engine : InnoDB | Charset : utf8mb4
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS stayu_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE stayu_db;

-- ============================================================
-- TABLE 1 : users
-- Central account table for all three roles.
-- role          : pelajar | pemilik | admin
-- owner_type    : individu | korporat  (only used when role = pemilik)
-- Students log in with matric_number, owners with ic_number,
-- admin with email. Unused identifier columns stay NULL.
-- ============================================================
CREATE TABLE users (
    user_id         INT AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('pelajar','pemilik','admin') NOT NULL,
    owner_type      ENUM('individu','korporat') DEFAULT NULL,
    full_name       VARCHAR(150) NOT NULL,
    matric_number   VARCHAR(20)  DEFAULT NULL UNIQUE,
    ic_number       VARCHAR(20)  DEFAULT NULL UNIQUE,
    email           VARCHAR(150) DEFAULT NULL UNIQUE,
    phone_number    VARCHAR(20)  DEFAULT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    profile_photo   VARCHAR(255) DEFAULT NULL,
    status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2 : listings
-- One row per property advertisement.
-- status flow (Individu) : in_review -> active / rejected
-- status flow (Korporat) : active (published immediately)
-- Owner can set : in_negotiation | unavailable
-- Admin can set : suspended
-- distance_km is computed once via Google Maps API at creation.
-- ============================================================
CREATE TABLE listings (
    listing_id      INT AUTO_INCREMENT PRIMARY KEY,
    owner_id        INT NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    property_type   ENUM('room','whole_unit','shared_room') NOT NULL,
    monthly_rent    DECIMAL(10,2) NOT NULL,
    deposit         DECIMAL(10,2) DEFAULT NULL,
    gender_pref     ENUM('male','female','any') NOT NULL DEFAULT 'any',
    address         VARCHAR(255) DEFAULT NULL,
    latitude        DECIMAL(10,7) DEFAULT NULL,
    longitude       DECIMAL(10,7) DEFAULT NULL,
    distance_km     DECIMAL(5,2)  DEFAULT NULL,
    status          ENUM('in_review','active','rejected','in_negotiation','unavailable','suspended')
                    NOT NULL DEFAULT 'in_review',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_listing_owner
        FOREIGN KEY (owner_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_listing_status (status),
    INDEX idx_listing_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3 : listing_photos
-- Multiple photos per listing. File paths only (files on server).
-- ============================================================
CREATE TABLE listing_photos (
    photo_id        INT AUTO_INCREMENT PRIMARY KEY,
    listing_id      INT NOT NULL,
    photo_path      VARCHAR(255) NOT NULL,
    is_cover        BOOLEAN NOT NULL DEFAULT FALSE,
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photo_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_photo_listing (listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 4 : amenities
-- Master lookup list of available facilities.
-- ============================================================
CREATE TABLE amenities (
    amenity_id      INT AUTO_INCREMENT PRIMARY KEY,
    amenity_name    VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 5 : listing_amenities
-- Junction table : many-to-many between listings and amenities.
-- ============================================================
CREATE TABLE listing_amenities (
    listing_id      INT NOT NULL,
    amenity_id      INT NOT NULL,
    PRIMARY KEY (listing_id, amenity_id),
    CONSTRAINT fk_la_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_la_amenity
        FOREIGN KEY (amenity_id) REFERENCES amenities(amenity_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 6 : verification_documents
-- Submitted by Pemilik Individu PER LISTING (UC12 / UC13).
-- Reviewed by admin (UC20). Korporat listings never appear here.
-- status : pending -> approved / rejected
-- ============================================================
CREATE TABLE verification_documents (
    document_id     INT AUTO_INCREMENT PRIMARY KEY,
    listing_id      INT NOT NULL,
    owner_id        INT NOT NULL,
    ic_doc_path     VARCHAR(255) NOT NULL,
    grant_doc_path  VARCHAR(255) NOT NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rejection_reason TEXT DEFAULT NULL,
    reviewed_by     INT DEFAULT NULL,
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at     TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_doc_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_doc_owner
        FOREIGN KEY (owner_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_doc_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_doc_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 7 : viewing_requests
-- Physical viewing appointments (UC09 request, UC15 confirm).
-- status : pending -> confirmed / rejected
-- ============================================================
CREATE TABLE viewing_requests (
    request_id      INT AUTO_INCREMENT PRIMARY KEY,
    listing_id      INT NOT NULL,
    student_id      INT NOT NULL,
    proposed_date   DATE NOT NULL,
    proposed_time   TIME NOT NULL,
    status          ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vr_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_vr_student
        FOREIGN KEY (student_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_vr_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 8 : conversations
-- Reference link only. Actual messages live in Firebase
-- Realtime Database, keyed by firebase_session_id (UC10 / UC16).
-- ============================================================
CREATE TABLE conversations (
    conversation_id     INT AUTO_INCREMENT PRIMARY KEY,
    listing_id          INT NOT NULL,
    student_id          INT NOT NULL,
    owner_id            INT NOT NULL,
    firebase_session_id VARCHAR(255) NOT NULL UNIQUE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conv_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_conv_student
        FOREIGN KEY (student_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_conv_owner
        FOREIGN KEY (owner_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 9 : complaints
-- Student files (UC17), owner defends (UC18), admin resolves (UC21).
-- status : open -> under_review -> resolved
-- ============================================================
CREATE TABLE complaints (
    complaint_id        INT AUTO_INCREMENT PRIMARY KEY,
    complainant_id      INT NOT NULL,
    reported_owner_id   INT NOT NULL,
    listing_id          INT DEFAULT NULL,
    category            VARCHAR(100) NOT NULL,
    description         TEXT NOT NULL,
    owner_defense       TEXT DEFAULT NULL,
    defense_evidence    VARCHAR(255) DEFAULT NULL,
    status              ENUM('open','under_review','resolved') NOT NULL DEFAULT 'open',
    action_taken        VARCHAR(255) DEFAULT NULL,
    handled_by          INT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at         TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_comp_complainant
        FOREIGN KEY (complainant_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comp_owner
        FOREIGN KEY (reported_owner_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comp_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_comp_handler
        FOREIGN KEY (handled_by) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_comp_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 10 : notifications
-- System generated alerts for any user (UC05).
-- ============================================================
CREATE TABLE notifications (
    notification_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT NOT NULL,
    type                VARCHAR(50) NOT NULL,
    message             VARCHAR(255) NOT NULL,
    is_read             BOOLEAN NOT NULL DEFAULT FALSE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Master amenity list
INSERT INTO amenities (amenity_name) VALUES
('Wi-Fi'),
('Air Conditioning'),
('Water Heater'),
('Parking'),
('Furnished'),
('Washing Machine'),
('Kitchen'),
('Refrigerator'),
('Security / CCTV'),
('Near Public Transport');

-- Default administrator account.
-- IMPORTANT: the password_hash below is a bcrypt hash for the
-- temporary password "admin123". Change it after first login.
INSERT INTO users (role, full_name, email, password_hash, status)
VALUES (
    'admin',
    'System Administrator',
    'admin@stayu.ukm.my',
    '$2y$10$.8OyMePPobvIU3sqGeyYDeu9E33tiGxlbfoMGaeHGQj6wd8Pg5i22',
    'active'
);

-- Example corporate owner (UKM Real Estate). Created by admin only.
-- Temporary password "korporat123" (bcrypt). Change after first login.
INSERT INTO users (role, owner_type, full_name, ic_number, email, phone_number, password_hash, status)
VALUES (
    'pemilik',
    'korporat',
    'UKM Real Estate Sdn. Bhd.',
    '900101145555',
    'realestate@ukm.my',
    '0389215000',
    '$2y$10$gFsu6uzYYaxO4DbohdesVe1TwLEoKw1vRxOSUvfA0fqPr83y67Wx6',
    'active'
);
