-- =========================================================
-- UNIVERSITY ENTITY UPGRADE SCRIPT
-- Adds 'university' role, creates/upgrades universities table,
-- extends scholarships with image_url and Draft/Published statuses,
-- and ensures applications support document_path and Pending/Approved.
-- Safe to run on an existing OpportunityHub database without data loss.
-- =========================================================

-- 1. Extend user_type ENUM in users table
ALTER TABLE users 
MODIFY COLUMN user_type ENUM('student', 'graduate', 'admin', 'employer', 'university') DEFAULT 'student';

-- 2. Create universities table if not exists
CREATE TABLE IF NOT EXISTS universities (
    university_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    univ_name VARCHAR(150) NOT NULL,
    country VARCHAR(100),
    city VARCHAR(100),
    address TEXT,
    website VARCHAR(255),
    logo_url VARCHAR(255),
    ranking VARCHAR(50),
    established_year INT,
    description TEXT,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(50),
    social_linkedin VARCHAR(255),
    social_twitter VARCHAR(255),
    social_facebook VARCHAR(255),
    social_youtube VARCHAR(255),
    status ENUM('Pending','Approved','Rejected','Suspended') DEFAULT 'Approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_univ_owner (user_id),
    INDEX idx_univ_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Extend scholarships table if needed
ALTER TABLE scholarships 
MODIFY COLUMN status ENUM('Active', 'Published', 'Draft', 'Upcoming', 'Closed', 'Expired') DEFAULT 'Active';

-- Add image_url to scholarships if not present
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME='scholarships' AND COLUMN_NAME='image_url' AND TABLE_SCHEMA=DATABASE());
SET @stmt = IF(@col_exists=0, 'ALTER TABLE scholarships ADD COLUMN image_url VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt FROM @stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Extend applications table if needed
ALTER TABLE applications 
MODIFY COLUMN application_status ENUM('Saved', 'Pending', 'Applied', 'Under Review', 'Shortlisted', 'Approved', 'Accepted', 'Rejected') DEFAULT 'Pending';

-- Add document_path to applications if not present
SET @app_col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME='applications' AND COLUMN_NAME='document_path' AND TABLE_SCHEMA=DATABASE());
SET @app_stmt = IF(@app_col_exists=0, 'ALTER TABLE applications ADD COLUMN document_path VARCHAR(255) NULL', 'SELECT 1');
PREPARE app_stmt FROM @app_stmt; EXECUTE app_stmt; DEALLOCATE PREPARE app_stmt;

-- 5. Insert Demo University Users if not already present
INSERT IGNORE INTO users (name, email, password, user_type, profile_complete, status) 
VALUES 
('University of Oxford', 'admissions@oxford.ac.uk', 'SET_BY_SETUP_SCRIPT', 'university', 1, 'active'),
('Harvard University', 'scholarships@harvard.edu', 'SET_BY_SETUP_SCRIPT', 'university', 1, 'active');

-- 6. Insert Demo University Profiles
INSERT IGNORE INTO universities (user_id, univ_name, country, city, address, website, logo_url, ranking, established_year, description, contact_email, contact_phone, social_linkedin, social_twitter, social_facebook, social_youtube, status)
SELECT user_id, 'University of Oxford', 'UK', 'Oxford',
       'University Offices, Wellington Square, Oxford, OX1 2JD, United Kingdom', 'https://www.ox.ac.uk',
       'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=200&h=200&fit=crop', 'QS #3', 1096,
       'Collegiate research university in Oxford, England, offering world-class undergraduate and postgraduate degrees.',
       'admissions@oxford.ac.uk', '+44 1865 270000', 'https://linkedin.com/school/oxford', 'https://x.com/uniofoxford', 'https://facebook.com/the.university.of.oxford', 'https://youtube.com/oxford', 'Approved'
FROM users WHERE email = 'admissions@oxford.ac.uk'
ON DUPLICATE KEY UPDATE univ_name = VALUES(univ_name);

INSERT IGNORE INTO universities (user_id, univ_name, country, city, address, website, logo_url, ranking, established_year, description, contact_email, contact_phone, social_linkedin, social_twitter, social_facebook, social_youtube, status)
SELECT user_id, 'Harvard University', 'USA', 'Cambridge, MA',
       'Massachusetts Hall, Cambridge, MA 02138, United States', 'https://www.harvard.edu',
       'https://images.unsplash.com/photo-1562774053-701939374585?w=200&h=200&fit=crop', 'QS #4', 1636,
       'Private Ivy League research university devoted to excellence in teaching, learning, and research.',
       'scholarships@harvard.edu', '+1 617 495 1000', 'https://linkedin.com/school/harvard', 'https://x.com/harvard', 'https://facebook.com/Harvard', 'https://youtube.com/harvard', 'Approved'
FROM users WHERE email = 'scholarships@harvard.edu'
ON DUPLICATE KEY UPDATE univ_name = VALUES(univ_name);

-- 7. Associate sample scholarships with demo universities
UPDATE scholarships 
SET created_by = (SELECT user_id FROM users WHERE email = 'admissions@oxford.ac.uk')
WHERE scholarship_name IN ('Chevening Scholarship', 'Commonwealth Shared Scholarship');

UPDATE scholarships 
SET created_by = (SELECT user_id FROM users WHERE email = 'scholarships@harvard.edu')
WHERE scholarship_name = 'Fulbright Foreign Student Program';
