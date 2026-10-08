-- =========================================================
-- OPPORTUNITYHUB - JOBS & INTERNSHIPS UPGRADE
-- Team 08 : CSE 3522
--
-- USE THIS FILE ONLY IF you already have an opportunityhub
-- database (with users/scholarships data you want to KEEP).
--
-- It adds the jobs & internships module on top of the existing
-- scholarship database without deleting anything.
--
-- If you do NOT need to keep existing data, import
-- database/opportunityhub.sql instead - do not import both.
-- =========================================================

USE opportunityhub;

-- Add the 'employer' account type to the existing users table.
ALTER TABLE users MODIFY COLUMN user_type ENUM('student','graduate','admin','employer') DEFAULT 'student';

-- Remove any partial/previous attempt at the jobs module.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS saved_postings;
DROP TABLE IF EXISTS job_applications;
DROP TABLE IF EXISTS postings;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS organizations;
SET FOREIGN_KEY_CHECKS = 1;

-- Remove demo employer accounts from a previous run of this file.
DELETE FROM users WHERE email IN ('hr@bracbank.com','hr@brainstation23.com','hr@pathao.com');

-- =========================================================
-- JOBS & INTERNSHIPS MODULE (v2 addition)
-- Adds: employer role, organizations, categories, postings,
--       job/internship applications, saved postings
-- Safe to run on a fresh database along with the schema above.
-- =========================================================

-- ====================================
-- ORGANIZATIONS (Employers)
-- ====================================
CREATE TABLE organizations (
    organization_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    org_name VARCHAR(150) NOT NULL,
    industry VARCHAR(100),
    website VARCHAR(255),
    location VARCHAR(150),
    org_size VARCHAR(50),
    founded_year INT,
    description TEXT,
    status ENUM('Pending','Approved','Rejected','Suspended') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_owner (user_id),
    INDEX idx_org_status (status)
);

-- ====================================
-- CATEGORIES (shared by jobs & internships)
-- ====================================
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (category_name) VALUES
('Software Engineering'), ('Data Science'), ('Marketing'), ('Finance & Accounting'),
('Design (UI/UX)'), ('Customer Support'), ('Human Resources'), ('Sales & Business Development'),
('Content Writing'), ('Research & Development'), ('Operations'), ('Electrical & Civil Engineering');

-- ====================================
-- POSTINGS (Jobs & Internships - unified table)
-- posting_type distinguishes a Job from an Internship while sharing
-- one schema, one admin workflow and one expiry mechanism.
-- ====================================
CREATE TABLE postings (
    posting_id INT PRIMARY KEY AUTO_INCREMENT,
    organization_id INT NOT NULL,
    posting_type ENUM('Job','Internship') NOT NULL,
    title VARCHAR(200) NOT NULL,
    category_id INT,
    description TEXT,
    responsibilities TEXT,
    requirements TEXT,
    qualifications TEXT,
    skills VARCHAR(255),
    location VARCHAR(150),
    work_type ENUM('Remote','On-site','Hybrid') DEFAULT 'On-site',
    job_type ENUM('Full-time','Part-time','Contract','Temporary') DEFAULT 'Full-time',
    paid_status ENUM('Paid','Unpaid') DEFAULT 'Paid',
    salary_min INT,
    salary_max INT,
    salary_currency VARCHAR(10) DEFAULT 'BDT',
    duration VARCHAR(50),
    vacancies INT DEFAULT 1,
    application_deadline DATE NOT NULL,
    application_method VARCHAR(100) DEFAULT 'Apply on OpportunityHub',
    application_link VARCHAR(255),
    posting_status ENUM('Pending','Active','Rejected','Closed','Expired') DEFAULT 'Pending',
    views INT DEFAULT 0,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(organization_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_posting_type (posting_type),
    INDEX idx_posting_status (posting_status),
    INDEX idx_posting_deadline (application_deadline),
    INDEX idx_posting_location (location),
    FULLTEXT INDEX ft_posting_search (title, description, skills)
);

-- ====================================
-- JOB / INTERNSHIP APPLICATIONS
-- ====================================
CREATE TABLE job_applications (
    job_application_id INT PRIMARY KEY AUTO_INCREMENT,
    posting_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_note TEXT,
    resume_path VARCHAR(255),
    application_status ENUM('Applied','Under Review','Shortlisted','Interview','Accepted','Rejected') DEFAULT 'Applied',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posting_id) REFERENCES postings(posting_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_job_application (posting_id, user_id),
    INDEX idx_ja_user (user_id),
    INDEX idx_ja_posting (posting_id)
);

-- ====================================
-- SAVED POSTINGS (Bookmarks for jobs & internships)
-- ====================================
CREATE TABLE saved_postings (
    save_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    posting_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (posting_id) REFERENCES postings(posting_id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved_posting (user_id, posting_id),
    INDEX idx_sp_user (user_id)
);

-- ====================================
-- SAMPLE DATA: EMPLOYERS, ORGANIZATIONS, POSTINGS
-- Demo employer logins: passwords are hashed by setup.php (run it once after import)
--   hr@bracbank.com / hr@brainstation23.com / hr@pathao.com
-- ====================================
INSERT INTO users (name, email, password, user_type, profile_complete, status) VALUES
('BRAC Bank HR', 'hr@bracbank.com', 'SET_BY_SETUP_SCRIPT', 'employer', 1, 'active'),
('Brain Station 23 HR', 'hr@brainstation23.com', 'SET_BY_SETUP_SCRIPT', 'employer', 1, 'active'),
('Pathao Talent', 'hr@pathao.com', 'SET_BY_SETUP_SCRIPT', 'employer', 1, 'active');

INSERT INTO organizations (user_id, org_name, industry, website, location, org_size, founded_year, description, status) VALUES
((SELECT user_id FROM users WHERE email='hr@bracbank.com'), 'BRAC Bank PLC', 'Banking & Finance', 'https://www.bracbank.com', 'Dhaka', '1000+', 2001,
 'One of the leading private commercial banks in Bangladesh, focused on SME and retail banking.', 'Approved'),
((SELECT user_id FROM users WHERE email='hr@brainstation23.com'), 'Brain Station 23', 'Software & IT', 'https://brainstation-23.com', 'Dhaka', '501-1000', 2006,
 'A large software development company in Bangladesh delivering enterprise solutions worldwide.', 'Approved'),
((SELECT user_id FROM users WHERE email='hr@pathao.com'), 'Pathao Limited', 'Technology / Logistics', 'https://pathao.com', 'Dhaka', '501-1000', 2015,
 'Bangladesh''s largest tech-enabled ride sharing, delivery and fintech platform.', 'Pending');

INSERT INTO postings
(organization_id, posting_type, title, category_id, description, responsibilities, requirements, qualifications, skills,
 location, work_type, job_type, paid_status, salary_min, salary_max, salary_currency, duration, vacancies,
 application_deadline, application_method, application_link, posting_status)
VALUES
-- BRAC Bank (Approved org)
((SELECT organization_id FROM organizations WHERE org_name='BRAC Bank PLC'), 'Job', 'Junior Software Engineer',
 (SELECT category_id FROM categories WHERE category_name='Software Engineering'),
 'Join the core banking technology team to build and maintain internal banking applications.',
 'Develop new features; fix defects; write unit tests; participate in code reviews.',
 'Fresh graduate or up to 2 years experience in software development.',
 'BSc in CSE/CS/SWE from a recognised university.',
 'Java, Spring Boot, SQL, Git', 'Dhaka', 'On-site', 'Full-time', 'Paid', 45000, 60000, 'BDT', NULL, 3,
 '2026-10-30', 'Apply on OpportunityHub', NULL, 'Active'),

((SELECT organization_id FROM organizations WHERE org_name='BRAC Bank PLC'), 'Internship', 'Finance Intern',
 (SELECT category_id FROM categories WHERE category_name='Finance & Accounting'),
 'Three-month internship with the corporate finance team supporting reporting and reconciliation.',
 'Assist with monthly reports; reconcile ledgers; support the audit team.',
 'Currently enrolled in a BBA/MBA programme, strong Excel skills.',
 'Final year student in Finance/Accounting.',
 'Excel, Financial Analysis, Reporting', 'Dhaka', 'On-site', 'Full-time', 'Paid', 15000, 15000, 'BDT', '3 months', 5,
 '2026-10-05', 'Apply on OpportunityHub', NULL, 'Active'),

-- Brain Station 23 (Approved org)
((SELECT organization_id FROM organizations WHERE org_name='Brain Station 23'), 'Job', 'Frontend Developer (React)',
 (SELECT category_id FROM categories WHERE category_name='Software Engineering'),
 'Build responsive web interfaces for international enterprise clients.',
 'Implement UI components; integrate REST APIs; optimise performance.',
 'At least 1 year of React experience or an exceptional portfolio.',
 'BSc in CSE or equivalent practical experience.',
 'React, JavaScript, HTML5, CSS3, Bootstrap', 'Dhaka', 'Hybrid', 'Full-time', 'Paid', 50000, 80000, 'BDT', NULL, 2,
 '2026-10-15', 'Apply on OpportunityHub', NULL, 'Active'),

((SELECT organization_id FROM organizations WHERE org_name='Brain Station 23'), 'Internship', 'UI/UX Design Intern',
 (SELECT category_id FROM categories WHERE category_name='Design (UI/UX)'),
 'Work alongside senior designers on real client products.',
 'Create wireframes and prototypes; support user research; prepare design handoffs.',
 'Portfolio demonstrating UI work; familiarity with Figma.',
 'Student or recent graduate in any discipline with design skills.',
 'Figma, Wireframing, Prototyping', 'Dhaka', 'Remote', 'Part-time', 'Unpaid', NULL, NULL, 'BDT', '4 months', 2,
 '2026-09-27', 'Apply on OpportunityHub', NULL, 'Active'),

((SELECT organization_id FROM organizations WHERE org_name='Brain Station 23'), 'Job', 'QA Engineer (Expired Demo)',
 (SELECT category_id FROM categories WHERE category_name='Software Engineering'),
 'This posting demonstrates automatic expiry: its deadline is in the past, so the system marks it Expired and blocks new applications.',
 'Write and execute test cases; report defects.',
 '1+ year of manual or automation testing experience.',
 'BSc in CSE or related field.',
 'Selenium, Test Cases, JIRA', 'Dhaka', 'On-site', 'Full-time', 'Paid', 40000, 55000, 'BDT', NULL, 1,
 '2026-09-05', 'Apply on OpportunityHub', NULL, 'Active'),

-- Pathao (Pending org - postings stay Pending until admin approves the organization)
((SELECT organization_id FROM organizations WHERE org_name='Pathao Limited'), 'Job', 'Data Analyst',
 (SELECT category_id FROM categories WHERE category_name='Data Science'),
 'Analyse rider and merchant data to support business decisions.',
 'Build dashboards; run SQL analyses; present findings to stakeholders.',
 'Strong SQL and spreadsheet skills; 0-2 years experience.',
 'BSc in CSE, Statistics, Economics or related field.',
 'SQL, Python, Excel, Data Visualization', 'Dhaka', 'Hybrid', 'Full-time', 'Paid', 55000, 75000, 'BDT', NULL, 2,
 '2026-10-25', 'Apply on OpportunityHub', NULL, 'Pending');
