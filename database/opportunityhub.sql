-- =========================================================
-- OPPORTUNITYHUB DATABASE SCHEMA
-- Team 08 : CSE 3522 - Database Management Systems Laboratory
-- Team Members: Zobaer Ahmed, Wahidul Alam Rahi, Anjan Kundu
-- Focus: Global Scholarships & Study Abroad Platform
-- =========================================================

CREATE DATABASE IF NOT EXISTS opportunityhub
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE opportunityhub;

-- ---------------------------------------------------------
-- Clean slate.
-- This makes the file safe to import more than once, and safe
-- to import over an older version of the OpportunityHub database.
-- WARNING: this deletes existing OpportunityHub data.
-- If you have data you want to keep, import
-- database/jobs_module_upgrade.sql instead of this file.
-- ---------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS saved_postings;
DROP TABLE IF EXISTS job_applications;
DROP TABLE IF EXISTS postings;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS universities;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS saved_scholarships;
DROP TABLE IF EXISTS scholarships;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ====================================
-- USERS TABLE
-- ====================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('student', 'graduate', 'admin', 'employer', 'university') DEFAULT 'student',
    university VARCHAR(100),
    degree VARCHAR(50),
    field_of_study VARCHAR(100),
    skills TEXT,
    experience_years INT DEFAULT 0,
    preferred_country VARCHAR(50),
    preferred_field VARCHAR(100),
    phone VARCHAR(20),
    bio TEXT,
    profile_complete BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
);

-- ====================================
-- SCHOLARSHIPS TABLE (Main table)
-- ====================================
CREATE TABLE scholarships (
    scholarship_id INT PRIMARY KEY AUTO_INCREMENT,
    scholarship_name VARCHAR(255) NOT NULL,
    university_name VARCHAR(150) NOT NULL,
    university_website VARCHAR(255),
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100),
    degree_level ENUM('Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate') NOT NULL,
    field_of_study VARCHAR(100),
    funding_type VARCHAR(100),
    funding_coverage ENUM('Fully Funded', 'Partially Funded', 'Tuition Only', 'Accommodation Only') DEFAULT 'Partially Funded',
    scholarship_amount VARCHAR(100),
    description TEXT,
    eligibility_requirements TEXT,
    required_documents TEXT,
    language_requirements VARCHAR(255),
    gpa_requirement DECIMAL(3,2),
    application_deadline DATE NOT NULL,
    start_date DATE,
    intake_month VARCHAR(20),
    status ENUM('Active', 'Published', 'Draft', 'Upcoming', 'Closed', 'Expired') DEFAULT 'Active',
    application_link VARCHAR(255),
    application_method VARCHAR(100),
    total_spots INT,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    image_url VARCHAR(255),
    views INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_country (country),
    INDEX idx_degree (degree_level),
    INDEX idx_field (field_of_study),
    INDEX idx_deadline (application_deadline),
    INDEX idx_status (status),
    FULLTEXT INDEX ft_search (scholarship_name, university_name, field_of_study, description)
);

-- ====================================
-- SAVED SCHOLARSHIPS (Bookmarks)
-- ====================================
CREATE TABLE saved_scholarships (
    save_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(scholarship_id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (user_id, scholarship_id),
    INDEX idx_user (user_id)
);

-- ====================================
-- APPLICATIONS TRACKING
-- ====================================
CREATE TABLE applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    application_status ENUM('Saved', 'Pending', 'Applied', 'Under Review', 'Shortlisted', 'Approved', 'Accepted', 'Rejected') DEFAULT 'Pending',
    document_path VARCHAR(255),
    notes TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(scholarship_id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (user_id, scholarship_id),
    INDEX idx_user_status (user_id, application_status)
);

-- ====================================
-- SAMPLE DATA: SCHOLARSHIPS
-- ====================================
INSERT INTO scholarships
(scholarship_name, university_name, university_website, country, city, degree_level, field_of_study, funding_type, funding_coverage, scholarship_amount, description, eligibility_requirements, required_documents, language_requirements, gpa_requirement, application_deadline, start_date, intake_month, status, application_link, application_method, total_spots, contact_email, contact_phone)
VALUES
('Chevening Scholarship', 'Various UK Universities', 'https://www.chevening.org', 'UK', 'London', 'Master', 'All Fields', 'Government Funded', 'Fully Funded', 'Full tuition + living costs', 'Prestigious UK government scholarship for future leaders from around the world.', 'Bachelor degree, 2+ years work experience, IELTS 7.5 overall', 'Passport, 2 references, personal statement, CV', 'IELTS 7.5+', 3.00, '2027-01-15', '2027-09-01', 'September', 'Active', 'https://www.chevening.org/apply', 'Online Portal', 1500, 'info@chevening.org', '+44-20-0000000'),
('Fulbright Foreign Student Program', 'Various US Universities', 'https://foreign.fulbrightonline.org', 'USA', 'Washington DC', 'Master', 'All Fields', 'Government Funded', 'Fully Funded', 'Full tuition + stipend + airfare', 'US government funded scholarship for graduate study, research or teaching.', 'Bachelor degree, TOEFL 80+, strong academic record', 'Transcripts, essays, 3 references', 'TOEFL 80+ / IELTS 6.5+', 3.00, '2026-10-15', '2027-08-01', 'August', 'Active', 'https://foreign.fulbrightonline.org', 'Online Portal', 800, 'apply@fulbright.org', '+1-202-0000000'),
('DAAD Scholarship', 'Various German Universities', 'https://www.daad.de', 'Germany', 'Bonn', 'Master', 'Engineering', 'Government Funded', 'Fully Funded', 'EUR 934/month + insurance', 'German Academic Exchange Service scholarship for international students.', 'Bachelor degree in relevant field, German or English proficiency', 'CV, motivation letter, transcripts, references', 'IELTS 6.5+ or German B2', 3.20, '2026-11-01', '2027-10-01', 'October', 'Active', 'https://www.daad.de/en/study-and-research-in-germany/scholarships', 'Online Portal', 500, 'info@daad.de', '+49-228-000000'),
('Australia Awards Scholarship', 'Australian Universities', 'https://www.dfat.gov.au', 'Australia', 'Canberra', 'Master', 'All Fields', 'Government Funded', 'Fully Funded', 'Full tuition + living allowance', 'Long-term development scholarship supporting global leaders in Bangladesh.', 'Bachelor degree, 2+ years work experience, IELTS 6.5+', 'Passport, transcripts, references, medical checkup', 'IELTS 6.5+', 2.80, '2027-04-30', '2028-02-01', 'February', 'Active', 'https://www.dfat.gov.au/people-to-people/australia-awards', 'Online Portal', 300, 'info@australiaawards.gov.au', '+61-2-00000000'),
('Erasmus Mundus Joint Master Degree', 'Consortium of European Universities', 'https://erasmus-plus.ec.europa.eu', 'Netherlands', 'Amsterdam', 'Master', 'Science & Technology', 'EU Funded', 'Fully Funded', 'EUR 1400/month + tuition waiver', 'Prestigious joint European Masters programme across multiple universities.', 'Bachelor degree, English proficiency, relevant background', 'CV, motivation letter, 2 references', 'IELTS 6.0+', 3.00, '2027-01-31', '2027-09-01', 'September', 'Active', 'https://erasmus-plus.ec.europa.eu', 'Online Portal', 100, 'emjmd@ec.europa.eu', '+32-2-0000000'),
('MEXT Scholarship (Japan)', 'Various Japanese Universities', 'https://www.studyinjapan.go.jp', 'Japan', 'Tokyo', 'Master', 'Engineering', 'Government Funded', 'Fully Funded', 'JPY 144,000/month + tuition + airfare', 'Japanese government scholarship for international graduate students.', 'Bachelor degree, under 35 years old, good academic record', 'Application form, transcripts, health certificate', 'None required initially', 3.00, '2027-05-31', '2028-04-01', 'April', 'Active', 'https://www.studyinjapan.go.jp', 'Embassy Application', 200, 'mext@studyinjapan.go.jp', '+81-3-0000000'),
('Swiss Government Excellence Scholarship', 'Swiss Universities', 'https://www.sbfi.admin.ch', 'Switzerland', 'Bern', 'PhD', 'All Fields', 'Government Funded', 'Fully Funded', 'CHF 1920/month + tuition waiver', 'Scholarship for foreign researchers and artists to pursue further studies in Switzerland.', 'Masters degree, strong research proposal, under 35 years', 'Research proposal, CV, references, transcripts', 'English or French/German B2', 3.30, '2026-12-15', '2027-09-15', 'September', 'Active', 'https://www.sbfi.admin.ch/scholarships', 'Embassy Application', 50, 'scholarships@sbfi.admin.ch', '+41-31-0000000'),
('Commonwealth Shared Scholarship', 'Various UK Universities', 'https://cscuk.fcdo.gov.uk', 'UK', 'London', 'Master', 'Development Studies', 'Government Funded', 'Fully Funded', 'Full tuition + living costs + airfare', 'For students from developing Commonwealth countries to study in the UK.', 'Bachelor degree (First Class or 2:1), cannot afford to study without support', 'Transcripts, references, nomination form', 'IELTS 6.5+', 3.20, '2027-01-10', '2027-09-01', 'September', 'Active', 'https://cscuk.fcdo.gov.uk/apply', 'Online Portal', 250, 'info@cscuk.fcdo.gov.uk', '+44-20-1111111'),
('Eiffel Excellence Scholarship', 'Various French Universities', 'https://www.campusfrance.org', 'France', 'Paris', 'Master', 'Engineering', 'Government Funded', 'Fully Funded', 'EUR 1181/month + travel + health cover', 'French government scholarship for top international students.', 'Under 25 for Masters, nominated by a French institution', 'CV, transcripts, motivation letter, nomination', 'French B1 or English B2', 3.20, '2027-01-07', '2027-09-01', 'September', 'Upcoming', 'https://www.campusfrance.org/en/eiffel-scholarship-program', 'Institution Nomination', 400, 'eiffel@campusfrance.org', '+33-1-0000000'),
('New Zealand Development Scholarship', 'New Zealand Universities', 'https://www.mfat.govt.nz', 'New Zealand', 'Wellington', 'Master', 'Agriculture', 'Government Funded', 'Fully Funded', 'Full tuition + living allowance + airfare', 'For candidates from developing countries including Bangladesh to study in NZ.', 'Bachelor degree, 2+ years relevant work experience', 'CV, transcripts, references, medical checkup', 'IELTS 6.5+', 2.80, '2027-02-28', '2028-02-01', 'February', 'Active', 'https://www.mfat.govt.nz/scholarships', 'Online Portal', 120, 'scholarships@mfat.govt.nz', '+64-4-0000000'),
('KAIST International Scholarship', 'Korea Advanced Institute of Science & Technology', 'https://www.kaist.ac.kr', 'South Korea', 'Daejeon', 'PhD', 'Science & Technology', 'University Funded', 'Fully Funded', 'Full tuition + monthly stipend', 'Merit scholarship for outstanding international PhD applicants at KAIST.', 'Masters degree, strong research background, English proficiency', 'Research proposal, transcripts, references', 'TOEFL 80+ or IELTS 6.5+', 3.30, '2026-10-31', '2027-03-01', 'March', 'Active', 'https://admission.kaist.ac.kr', 'Online Portal', 60, 'admission@kaist.ac.kr', '+82-42-0000000'),
('Singapore International Graduate Award', 'A*STAR / NTU / NUS', 'https://www.a-star.edu.sg', 'Singapore', 'Singapore', 'PhD', 'Science & Technology', 'Government Funded', 'Fully Funded', 'Full tuition + SGD 2000-2800/month', 'Joint scholarship for PhD study and research in Singapore institutions.', 'Bachelor or Masters degree with strong research aptitude', 'CV, transcripts, research proposal, references', 'IELTS 6.5+ or TOEFL 85+', 3.40, '2026-12-01', '2027-08-01', 'August', 'Active', 'https://www.a-star.edu.sg/Scholarships', 'Online Portal', 80, 'siga@a-star.edu.sg', '+65-6000-0000');

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
-- UNIVERSITIES (Higher Education Entities)
-- ====================================
CREATE TABLE universities (
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

-- ====================================
-- SAMPLE DATA: UNIVERSITIES
-- Demo university logins: passwords set by setup.php (run it once after import)
--   admissions@oxford.ac.uk / scholarships@harvard.edu
-- ====================================
INSERT INTO users (name, email, password, user_type, profile_complete, status) VALUES
('University of Oxford', 'admissions@oxford.ac.uk', 'SET_BY_SETUP_SCRIPT', 'university', 1, 'active'),
('Harvard University', 'scholarships@harvard.edu', 'SET_BY_SETUP_SCRIPT', 'university', 1, 'active');

INSERT INTO universities (user_id, univ_name, country, city, address, website, logo_url, ranking, established_year, description, contact_email, contact_phone, social_linkedin, social_twitter, social_facebook, social_youtube, status) VALUES
((SELECT user_id FROM users WHERE email='admissions@oxford.ac.uk'), 'University of Oxford', 'UK', 'Oxford',
 'University Offices, Wellington Square, Oxford, OX1 2JD, United Kingdom', 'https://www.ox.ac.uk',
 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=200&h=200&fit=crop', 'QS #3', 1096,
 'Collegiate research university in Oxford, England, offering world-class undergraduate and postgraduate degrees.',
 'admissions@oxford.ac.uk', '+44 1865 270000', 'https://linkedin.com/school/oxford', 'https://x.com/uniofoxford', 'https://facebook.com/the.university.of.oxford', 'https://youtube.com/oxford', 'Approved'),
((SELECT user_id FROM users WHERE email='scholarships@harvard.edu'), 'Harvard University', 'USA', 'Cambridge, MA',
 'Massachusetts Hall, Cambridge, MA 02138, United States', 'https://www.harvard.edu',
 'https://images.unsplash.com/photo-1562774053-701939374585?w=200&h=200&fit=crop', 'QS #4', 1636,
 'Private Ivy League research university devoted to excellence in teaching, learning, and research.',
 'scholarships@harvard.edu', '+1 617 495 1000', 'https://linkedin.com/school/harvard', 'https://x.com/harvard', 'https://facebook.com/Harvard', 'https://youtube.com/harvard', 'Approved');

UPDATE scholarships 
SET created_by = (SELECT user_id FROM users WHERE email='admissions@oxford.ac.uk')
WHERE scholarship_name IN ('Chevening Scholarship', 'Commonwealth Shared Scholarship');

UPDATE scholarships 
SET created_by = (SELECT user_id FROM users WHERE email='scholarships@harvard.edu')
WHERE scholarship_name = 'Fulbright Foreign Student Program';

