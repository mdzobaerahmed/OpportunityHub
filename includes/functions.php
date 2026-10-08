<?php
/**
 * OpportunityHub - Core Functions
 * Team 08 - CSE 3522 DBMS Lab
 *
 * All reusable backend logic (auth, scholarships, bookmarks, applications, admin)
 * lives here. Every page includes config/database.php then this file.
 */

// ======================= AUTH =======================

function registerUser($name, $email, $password, $user_type = 'student') {
    global $conn;
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $password_hash, $user_type);
    return $stmt->execute();
}

function emailExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function loginUser($email, $password) {
    global $conn;
    $sql = "SELECT user_id, name, email, password, user_type, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] === 'suspended') {
            return 'suspended';
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            return true;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/pages/login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: " . BASE_URL . "/pages/login.php");
        exit;
    }
}

function currentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ======================= SCHOLARSHIPS =======================

function getScholarships($filters = [], $onlyActive = true) {
    global $conn;

    $sql = "SELECT * FROM scholarships WHERE 1=1";
    if ($onlyActive) {
        $sql .= " AND status IN ('Active','Upcoming')";
    }
    $params = [];
    $types = "";

    if (!empty($filters['search'])) {
        $sql .= " AND (scholarship_name LIKE ? OR university_name LIKE ? OR field_of_study LIKE ?)";
        $like = "%" . $filters['search'] . "%";
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types .= "sss";
    }
    if (!empty($filters['country'])) {
        $sql .= " AND country = ?";
        $params[] = $filters['country'];
        $types .= "s";
    }
    if (!empty($filters['degree_level'])) {
        $sql .= " AND degree_level = ?";
        $params[] = $filters['degree_level'];
        $types .= "s";
    }
    if (!empty($filters['field_of_study'])) {
        $sql .= " AND field_of_study LIKE ?";
        $params[] = "%" . $filters['field_of_study'] . "%";
        $types .= "s";
    }
    if (!empty($filters['funding_coverage'])) {
        $sql .= " AND funding_coverage = ?";
        $params[] = $filters['funding_coverage'];
        $types .= "s";
    }

    $sql .= " ORDER BY application_deadline ASC LIMIT 100";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getScholarshipById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM scholarships WHERE scholarship_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function incrementScholarshipViews($id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE scholarships SET views = views + 1 WHERE scholarship_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

function getDistinctCountries() {
    global $conn;
    $res = $conn->query("SELECT DISTINCT country FROM scholarships ORDER BY country ASC");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function getDistinctFields() {
    global $conn;
    $res = $conn->query("SELECT DISTINCT field_of_study FROM scholarships WHERE field_of_study IS NOT NULL AND field_of_study <> '' ORDER BY field_of_study ASC");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function addScholarship($data, $created_by) {
    global $conn;
    ensureUniversitySchema();
    $imageUrl = $data['image_url'] ?? null;
    $sql = "INSERT INTO scholarships
        (scholarship_name, university_name, university_website, country, city, degree_level, field_of_study,
         funding_type, funding_coverage, scholarship_amount, description, eligibility_requirements,
         required_documents, language_requirements, gpa_requirement, application_deadline, start_date,
         intake_month, status, application_link, application_method, total_spots, contact_email, contact_phone, created_by, image_url)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "ssssssssssssssdssssssissis",
            $data['scholarship_name'], $data['university_name'], $data['university_website'], $data['country'],
            $data['city'], $data['degree_level'], $data['field_of_study'], $data['funding_type'],
            $data['funding_coverage'], $data['scholarship_amount'], $data['description'],
            $data['eligibility_requirements'], $data['required_documents'], $data['language_requirements'],
            $data['gpa_requirement'], $data['application_deadline'], $data['start_date'], $data['intake_month'],
            $data['status'], $data['application_link'], $data['application_method'], $data['total_spots'],
            $data['contact_email'], $data['contact_phone'], $created_by, $imageUrl
        );
        return $stmt->execute();
    }
    // Fallback if image_url column not supported
    $sqlFallback = "INSERT INTO scholarships
        (scholarship_name, university_name, university_website, country, city, degree_level, field_of_study,
         funding_type, funding_coverage, scholarship_amount, description, eligibility_requirements,
         required_documents, language_requirements, gpa_requirement, application_deadline, start_date,
         intake_month, status, application_link, application_method, total_spots, contact_email, contact_phone, created_by)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmtFallback = $conn->prepare($sqlFallback);
    $stmtFallback->bind_param(
        "ssssssssssssssdssssssissi",
        $data['scholarship_name'], $data['university_name'], $data['university_website'], $data['country'],
        $data['city'], $data['degree_level'], $data['field_of_study'], $data['funding_type'],
        $data['funding_coverage'], $data['scholarship_amount'], $data['description'],
        $data['eligibility_requirements'], $data['required_documents'], $data['language_requirements'],
        $data['gpa_requirement'], $data['application_deadline'], $data['start_date'], $data['intake_month'],
        $data['status'], $data['application_link'], $data['application_method'], $data['total_spots'],
        $data['contact_email'], $data['contact_phone'], $created_by
    );
    return $stmtFallback->execute();
}

function updateScholarship($id, $data) {
    global $conn;
    ensureUniversitySchema();
    $imageUrl = $data['image_url'] ?? null;
    $sql = "UPDATE scholarships SET
        scholarship_name=?, university_name=?, university_website=?, country=?, city=?, degree_level=?,
        field_of_study=?, funding_type=?, funding_coverage=?, scholarship_amount=?, description=?,
        eligibility_requirements=?, required_documents=?, language_requirements=?, gpa_requirement=?,
        application_deadline=?, start_date=?, intake_month=?, status=?, application_link=?,
        application_method=?, total_spots=?, contact_email=?, contact_phone=?, image_url=?, updated_at=NOW()
        WHERE scholarship_id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "ssssssssssssssdssssssissii",
            $data['scholarship_name'], $data['university_name'], $data['university_website'], $data['country'],
            $data['city'], $data['degree_level'], $data['field_of_study'], $data['funding_type'],
            $data['funding_coverage'], $data['scholarship_amount'], $data['description'],
            $data['eligibility_requirements'], $data['required_documents'], $data['language_requirements'],
            $data['gpa_requirement'], $data['application_deadline'], $data['start_date'], $data['intake_month'],
            $data['status'], $data['application_link'], $data['application_method'], $data['total_spots'],
            $data['contact_email'], $data['contact_phone'], $imageUrl, $id
        );
        return $stmt->execute();
    }
    // Fallback without image_url
    $sqlFallback = "UPDATE scholarships SET
        scholarship_name=?, university_name=?, university_website=?, country=?, city=?, degree_level=?,
        field_of_study=?, funding_type=?, funding_coverage=?, scholarship_amount=?, description=?,
        eligibility_requirements=?, required_documents=?, language_requirements=?, gpa_requirement=?,
        application_deadline=?, start_date=?, intake_month=?, status=?, application_link=?,
        application_method=?, total_spots=?, contact_email=?, contact_phone=?, updated_at=NOW()
        WHERE scholarship_id=?";
    $stmtFallback = $conn->prepare($sqlFallback);
    $stmtFallback->bind_param(
        "ssssssssssssssdssssssissi",
        $data['scholarship_name'], $data['university_name'], $data['university_website'], $data['country'],
        $data['city'], $data['degree_level'], $data['field_of_study'], $data['funding_type'],
        $data['funding_coverage'], $data['scholarship_amount'], $data['description'],
        $data['eligibility_requirements'], $data['required_documents'], $data['language_requirements'],
        $data['gpa_requirement'], $data['application_deadline'], $data['start_date'], $data['intake_month'],
        $data['status'], $data['application_link'], $data['application_method'], $data['total_spots'],
        $data['contact_email'], $data['contact_phone'], $id
    );
    return $stmtFallback->execute();
}

function deleteScholarship($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM scholarships WHERE scholarship_id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// ======================= BOOKMARKS (SAVED SCHOLARSHIPS) =======================

function isScholarshipSaved($user_id, $scholarship_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT save_id FROM saved_scholarships WHERE user_id = ? AND scholarship_id = ?");
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function toggleSaveScholarship($user_id, $scholarship_id) {
    global $conn;
    if (isScholarshipSaved($user_id, $scholarship_id)) {
        $stmt = $conn->prepare("DELETE FROM saved_scholarships WHERE user_id = ? AND scholarship_id = ?");
        $stmt->bind_param("ii", $user_id, $scholarship_id);
        $stmt->execute();
        return 'removed';
    } else {
        $stmt = $conn->prepare("INSERT INTO saved_scholarships (user_id, scholarship_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $scholarship_id);
        $stmt->execute();
        return 'saved';
    }
}

function getSavedScholarships($user_id) {
    global $conn;
    $sql = "SELECT s.*, ss.saved_at FROM scholarships s
            INNER JOIN saved_scholarships ss ON s.scholarship_id = ss.scholarship_id
            WHERE ss.user_id = ?
            ORDER BY ss.saved_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ======================= APPLICATIONS =======================

function trackApplication($user_id, $scholarship_id, $status = 'Applied', $notes = null) {
    global $conn;
    if ($notes !== null && $notes !== '') {
        $sql = "INSERT INTO applications (user_id, scholarship_id, application_status, notes)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE application_status = VALUES(application_status), notes = VALUES(notes), updated_at = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $scholarship_id, $status, $notes);
    } else {
        $sql = "INSERT INTO applications (user_id, scholarship_id, application_status)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE application_status = VALUES(application_status), updated_at = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $scholarship_id, $status);
    }
    return $stmt->execute();
}

function updateApplicationStatus($application_id, $user_id, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE applications SET application_status = ? WHERE application_id = ? AND user_id = ?");
    $stmt->bind_param("sii", $status, $application_id, $user_id);
    return $stmt->execute();
}

function getUserApplications($user_id) {
    global $conn;
    $sql = "SELECT a.*, s.scholarship_name, s.university_name, s.country, s.application_deadline
            FROM applications a
            INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
            WHERE a.user_id = ?
            ORDER BY a.applied_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getApplicationForScholarship($user_id, $scholarship_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM applications WHERE user_id = ? AND scholarship_id = ?");
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ======================= DASHBOARD / STATS =======================

function getDashboardStats($user_id) {
    global $conn;
    $stats = [];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM saved_scholarships WHERE user_id = ?");
    $stmt->bind_param("i", $user_id); $stmt->execute();
    $stats['saved'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM applications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id); $stmt->execute();
    $stats['applications'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM applications WHERE user_id = ? AND application_status = 'Accepted'");
    $stmt->bind_param("i", $user_id); $stmt->execute();
    $stats['accepted'] = $stmt->get_result()->fetch_assoc()['c'];

    return $stats;
}

function getUpcomingDeadlines($user_id, $days = 30) {
    global $conn;
    $sql = "SELECT s.scholarship_id, s.scholarship_name, s.university_name, s.application_deadline,
                   DATEDIFF(s.application_deadline, CURDATE()) as days_left
            FROM saved_scholarships ss
            INNER JOIN scholarships s ON ss.scholarship_id = s.scholarship_id
            WHERE ss.user_id = ?
              AND s.application_deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY s.application_deadline ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $days);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAdminStats() {
    global $conn;
    $stats = [];
    $stats['total_scholarships'] = $conn->query("SELECT COUNT(*) c FROM scholarships")->fetch_assoc()['c'];
    $stats['active_scholarships'] = $conn->query("SELECT COUNT(*) c FROM scholarships WHERE status='Active'")->fetch_assoc()['c'];
    $stats['total_users'] = $conn->query("SELECT COUNT(*) c FROM users WHERE user_type != 'admin'")->fetch_assoc()['c'];
    $stats['total_applications'] = $conn->query("SELECT COUNT(*) c FROM applications")->fetch_assoc()['c'];
    return $stats;
}

// ======================= USER PROFILE =======================

function updateUserProfile($user_id, $data) {
    global $conn;
    $sql = "UPDATE users SET
            university = ?, degree = ?, field_of_study = ?, skills = ?, experience_years = ?,
            preferred_country = ?, preferred_field = ?, phone = ?, bio = ?, profile_complete = 1, updated_at = NOW()
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssissssi",
        $data['university'], $data['degree'], $data['field_of_study'], $data['skills'],
        $data['experience_years'], $data['preferred_country'], $data['preferred_field'],
        $data['phone'], $data['bio'], $user_id
    );
    return $stmt->execute();
}

// ======================= ADMIN: USERS =======================

function getAllUsers() {
    global $conn;
    $res = $conn->query("SELECT user_id, name, email, user_type, status, created_at FROM users ORDER BY created_at DESC");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function toggleUserStatus($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    if (!$current) return false;
    $new_status = $current['status'] === 'active' ? 'suspended' : 'active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    return $stmt->execute();
}

// ======================= HELPERS =======================

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function daysLeft($deadline) {
    return (int) ceil((strtotime($deadline) - time()) / 86400);
}

function statusBadgeColor($status) {
    $map = [
        'Saved' => 'secondary',
        'Pending' => 'warning text-dark',
        'Applied' => 'info',
        'Under Review' => 'warning text-dark',
        'Shortlisted' => 'primary',
        'Approved' => 'success',
        'Accepted' => 'success',
        'Rejected' => 'danger',
    ];
    return $map[$status] ?? 'secondary';
}

function scholarshipStatusBadgeColor($status) {
    $map = [
        'Active' => 'success',
        'Published' => 'success',
        'Draft' => 'secondary',
        'Upcoming' => 'info',
        'Closed' => 'dark',
        'Expired' => 'danger',
    ];
    return $map[$status] ?? 'secondary';
}

// =========================================================
// JOBS & INTERNSHIPS MODULE (v2 addition)
// Everything below supports: organizations/employers, unified
// job+internship postings, applications, admin moderation.
// =========================================================

// ======================= ROLE HELPERS =======================

function isEmployer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employer';
}

function requireEmployer() {
    if (!isLoggedIn() || !isEmployer()) {
        header("Location: " . BASE_URL . "/pages/login.php");
        exit;
    }
}

function isUniversity() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'university';
}

function requireUniversity() {
    if (!isLoggedIn() || !isUniversity()) {
        header("Location: " . BASE_URL . "/pages/login.php");
        exit;
    }
}

// ======================= CSRF PROTECTION =======================

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function csrfCheck() {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


 
function sweepExpiredPostings() {
    global $conn;
    $conn->query("UPDATE postings SET posting_status = 'Expired'
                   WHERE application_deadline < CURDATE()
                   AND posting_status IN ('Active', 'Pending')");
}

function isPostingExpired($posting) {
    return strtotime($posting['application_deadline']) < strtotime(date('Y-m-d'));
}



function createOrganization($user_id, $data) {
    global $conn;
    $sql = "INSERT INTO organizations (user_id, org_name, industry, website, location, org_size, founded_year, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssssis",
        $user_id, $data['org_name'], $data['industry'], $data['website'], $data['location'],
        $data['org_size'], $data['founded_year'], $data['description']
    );
    return $stmt->execute();
}

function updateOrganization($organization_id, $data) {
    global $conn;
    $sql = "UPDATE organizations SET org_name=?, industry=?, website=?, location=?, org_size=?, founded_year=?, description=?
            WHERE organization_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssisi",
        $data['org_name'], $data['industry'], $data['website'], $data['location'],
        $data['org_size'], $data['founded_year'], $data['description'], $organization_id
    );
    return $stmt->execute();
}

function getOrganizationByUser($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM organizations WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getOrganizationById($organization_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT o.*, u.name AS owner_name, u.email AS owner_email
                             FROM organizations o INNER JOIN users u ON o.user_id = u.user_id
                             WHERE o.organization_id = ?");
    $stmt->bind_param("i", $organization_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function organizationIsApproved($organization) {
    return $organization && $organization['status'] === 'Approved';
}

function getAllOrganizations($filters = []) {
    global $conn;
    $sql = "SELECT o.*, u.name AS owner_name, u.email AS owner_email,
                   (SELECT COUNT(*) FROM postings p WHERE p.organization_id = o.organization_id) AS total_postings
            FROM organizations o INNER JOIN users u ON o.user_id = u.user_id WHERE 1=1";
    $params = []; $types = "";

    if (!empty($filters['search'])) {
        $sql .= " AND (o.org_name LIKE ? OR u.email LIKE ?)";
        $like = "%" . $filters['search'] . "%";
        $params[] = $like; $params[] = $like; $types .= "ss";
    }
    if (!empty($filters['status'])) {
        $sql .= " AND o.status = ?";
        $params[] = $filters['status']; $types .= "s";
    }
    $sql .= " ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function setOrganizationStatus($organization_id, $status) {
    global $conn;
    $allowed = ['Pending', 'Approved', 'Rejected', 'Suspended'];
    if (!in_array($status, $allowed, true)) return false;
    $stmt = $conn->prepare("UPDATE organizations SET status = ? WHERE organization_id = ?");
    $stmt->bind_param("si", $status, $organization_id);
    return $stmt->execute();
}

function deleteOrganization($organization_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM organizations WHERE organization_id = ?");
    $stmt->bind_param("i", $organization_id);
    return $stmt->execute();
}

function getOrganizationStats($organization_id) {
    global $conn;
    $stats = [];
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM postings WHERE organization_id = ?");
    $stmt->bind_param("i", $organization_id); $stmt->execute();
    $stats['total_postings'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM postings WHERE organization_id = ? AND posting_status = 'Active'");
    $stmt->bind_param("i", $organization_id); $stmt->execute();
    $stats['active_postings'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM postings WHERE organization_id = ? AND posting_status = 'Expired'");
    $stmt->bind_param("i", $organization_id); $stmt->execute();
    $stats['expired_postings'] = $stmt->get_result()->fetch_assoc()['c'];

    $stmt = $conn->prepare("SELECT COUNT(*) c FROM job_applications ja
                             INNER JOIN postings p ON ja.posting_id = p.posting_id
                             WHERE p.organization_id = ?");
    $stmt->bind_param("i", $organization_id); $stmt->execute();
    $stats['total_applications'] = $stmt->get_result()->fetch_assoc()['c'];

    return $stats;
}

// ======================= CATEGORIES =======================

function getAllCategories() {
    global $conn;
    $res = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function addCategory($name) {
    global $conn;
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    return $stmt->execute();
}

function deleteCategory($category_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    return $stmt->execute();
}

// ======================= POSTINGS (JOBS & INTERNSHIPS) =======================

/**
 * $type is 'Job' or 'Internship'. $onlyPublic restricts results to
 * postings a student should be able to browse (Active + org Approved).
 */
function getPostings($type, $filters = [], $onlyPublic = true) {
    global $conn;
    sweepExpiredPostings();

    $sql = "SELECT p.*, o.org_name, o.location AS org_location, o.status AS org_status, c.category_name
            FROM postings p
            INNER JOIN organizations o ON p.organization_id = o.organization_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.posting_type = ?";
    $params = [$type]; $types = "s";

    if ($onlyPublic) {
        $sql .= " AND p.posting_status = 'Active' AND o.status = 'Approved'";
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (p.title LIKE ? OR o.org_name LIKE ? OR p.skills LIKE ?)";
        $like = "%" . $filters['search'] . "%";
        $params[] = $like; $params[] = $like; $params[] = $like; $types .= "sss";
    }
    if (!empty($filters['organization'])) {
        $sql .= " AND o.org_name LIKE ?";
        $params[] = "%" . $filters['organization'] . "%"; $types .= "s";
    }
    if (!empty($filters['location'])) {
        $sql .= " AND p.location LIKE ?";
        $params[] = "%" . $filters['location'] . "%"; $types .= "s";
    }
    if (!empty($filters['work_type'])) {
        $sql .= " AND p.work_type = ?";
        $params[] = $filters['work_type']; $types .= "s";
    }
    if (!empty($filters['job_type'])) {
        $sql .= " AND p.job_type = ?";
        $params[] = $filters['job_type']; $types .= "s";
    }
    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = (int) $filters['category_id']; $types .= "i";
    }
    if (!empty($filters['paid_status'])) {
        $sql .= " AND p.paid_status = ?";
        $params[] = $filters['paid_status']; $types .= "s";
    }

    $sql .= " ORDER BY p.posted_at DESC LIMIT 200";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPostingById($posting_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT p.*, o.org_name, o.location AS org_location, o.website AS org_website,
                                    o.status AS org_status, o.user_id AS org_owner_id, o.description AS org_description,
                                    c.category_name
                             FROM postings p
                             INNER JOIN organizations o ON p.organization_id = o.organization_id
                             LEFT JOIN categories c ON p.category_id = c.category_id
                             WHERE p.posting_id = ?");
    $stmt->bind_param("i", $posting_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function incrementPostingViews($posting_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE postings SET views = views + 1 WHERE posting_id = ?");
    $stmt->bind_param("i", $posting_id);
    $stmt->execute();
}

function addPosting($organization_id, $data) {
    global $conn;
    $sql = "INSERT INTO postings
        (organization_id, posting_type, title, category_id, description, responsibilities, requirements,
         qualifications, skills, location, work_type, job_type, paid_status, salary_min, salary_max,
         salary_currency, duration, vacancies, application_deadline, application_method, application_link, posting_status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ississsssssssiississss",
        $organization_id, $data['posting_type'], $data['title'], $data['category_id'], $data['description'],
        $data['responsibilities'], $data['requirements'], $data['qualifications'], $data['skills'],
        $data['location'], $data['work_type'], $data['job_type'], $data['paid_status'],
        $data['salary_min'], $data['salary_max'], $data['salary_currency'], $data['duration'],
        $data['vacancies'], $data['application_deadline'], $data['application_method'],
        $data['application_link'], $data['posting_status']
    );
    return $stmt->execute();
}

function updatePosting($posting_id, $data) {
    global $conn;
    $sql = "UPDATE postings SET
        title=?, category_id=?, description=?, responsibilities=?, requirements=?, qualifications=?, skills=?,
        location=?, work_type=?, job_type=?, paid_status=?, salary_min=?, salary_max=?, salary_currency=?,
        duration=?, vacancies=?, application_deadline=?, application_method=?, application_link=?
        WHERE posting_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sisssssssssiississsi",
        $data['title'], $data['category_id'], $data['description'], $data['responsibilities'],
        $data['requirements'], $data['qualifications'], $data['skills'], $data['location'],
        $data['work_type'], $data['job_type'], $data['paid_status'], $data['salary_min'],
        $data['salary_max'], $data['salary_currency'], $data['duration'], $data['vacancies'],
        $data['application_deadline'], $data['application_method'], $data['application_link'], $posting_id
    );
    return $stmt->execute();
}

function setPostingStatus($posting_id, $status) {
    global $conn;
    $allowed = ['Pending', 'Active', 'Rejected', 'Closed', 'Expired'];
    if (!in_array($status, $allowed, true)) return false;
    $stmt = $conn->prepare("UPDATE postings SET posting_status = ? WHERE posting_id = ?");
    $stmt->bind_param("si", $status, $posting_id);
    return $stmt->execute();
}

function deletePosting($posting_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM postings WHERE posting_id = ?");
    $stmt->bind_param("i", $posting_id);
    return $stmt->execute();
}

function getOrganizationPostings($organization_id, $filters = []) {
    global $conn;
    sweepExpiredPostings();

    $sql = "SELECT p.*, c.category_name FROM postings p LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.organization_id = ?";
    $params = [$organization_id]; $types = "i";

    if (!empty($filters['posting_type'])) {
        $sql .= " AND p.posting_type = ?";
        $params[] = $filters['posting_type']; $types .= "s";
    }
    if (!empty($filters['posting_status'])) {
        $sql .= " AND p.posting_status = ?";
        $params[] = $filters['posting_status']; $types .= "s";
    }
    $sql .= " ORDER BY p.posted_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** Ownership check used before letting an employer edit/delete/manage a posting. */
function postingBelongsToOrganization($posting_id, $organization_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT posting_id FROM postings WHERE posting_id = ? AND organization_id = ?");
    $stmt->bind_param("ii", $posting_id, $organization_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getAllPostingsAdmin($filters = []) {
    global $conn;
    sweepExpiredPostings();

    $sql = "SELECT p.*, o.org_name, o.status AS org_status, c.category_name
            FROM postings p
            INNER JOIN organizations o ON p.organization_id = o.organization_id
            LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
    $params = []; $types = "";

    if (!empty($filters['search'])) {
        $sql .= " AND (p.title LIKE ? OR o.org_name LIKE ?)";
        $like = "%" . $filters['search'] . "%";
        $params[] = $like; $params[] = $like; $types .= "ss";
    }
    if (!empty($filters['posting_type'])) {
        $sql .= " AND p.posting_type = ?";
        $params[] = $filters['posting_type']; $types .= "s";
    }
    if (!empty($filters['posting_status'])) {
        $sql .= " AND p.posting_status = ?";
        $params[] = $filters['posting_status']; $types .= "s";
    }
    $sql .= " ORDER BY p.posted_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getDistinctPostingLocations() {
    global $conn;
    $res = $conn->query("SELECT DISTINCT location FROM postings WHERE location IS NOT NULL AND location <> '' ORDER BY location ASC");
    return $res->fetch_all(MYSQLI_ASSOC);
}

// ======================= JOB / INTERNSHIP APPLICATIONS =======================

function handleResumeUpload($fileField = 'resume') {
    if (empty($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null]; // no file uploaded, not an error
    }
    $file = $_FILES[$fileField];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'File upload failed. Please try again.'];
    }

    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        return [null, 'Resume file is too large. Maximum size is 2MB.'];
    }

    $allowedExt = ['pdf', 'doc', 'docx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return [null, 'Resume must be a PDF, DOC or DOCX file.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($mime, $allowedMime, true)) {
        return [null, 'Invalid file type detected. Please upload a genuine PDF/DOC/DOCX.'];
    }

    $uploadDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $safeName = 'resume_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destination = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [null, 'Could not save the uploaded file.'];
    }

    return ['uploads/resumes/' . $safeName, null];
}

function hasUserAppliedToPosting($user_id, $posting_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT job_application_id FROM job_applications WHERE user_id = ? AND posting_id = ?");
    $stmt->bind_param("ii", $user_id, $posting_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function applyToPosting($user_id, $posting_id, $cover_note, $resume_path) {
    global $conn;
    $sql = "INSERT INTO job_applications (posting_id, user_id, cover_note, resume_path, application_status)
            VALUES (?, ?, ?, ?, 'Applied')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $posting_id, $user_id, $cover_note, $resume_path);
    return $stmt->execute();
}

function getUserJobApplications($user_id) {
    global $conn;
    sweepExpiredPostings();
    $sql = "SELECT ja.*, p.title, p.posting_type, p.application_deadline, p.posting_status, o.org_name
            FROM job_applications ja
            INNER JOIN postings p ON ja.posting_id = p.posting_id
            INNER JOIN organizations o ON p.organization_id = o.organization_id
            WHERE ja.user_id = ? ORDER BY ja.applied_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getOrganizationApplications($organization_id, $posting_id = null) {
    global $conn;
    $sql = "SELECT ja.*, p.title, p.posting_type, p.organization_id, u.name AS applicant_name, u.email AS applicant_email,
                   u.university, u.degree, u.skills AS applicant_skills, u.phone
            FROM job_applications ja
            INNER JOIN postings p ON ja.posting_id = p.posting_id
            INNER JOIN users u ON ja.user_id = u.user_id
            WHERE p.organization_id = ?";
    $params = [$organization_id]; $types = "i";
    if ($posting_id) {
        $sql .= " AND ja.posting_id = ?";
        $params[] = $posting_id; $types .= "i";
    }
    $sql .= " ORDER BY ja.applied_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllJobApplicationsAdmin() {
    global $conn;
    $sql = "SELECT ja.*, p.title, p.posting_type, o.org_name, u.name AS applicant_name, u.email AS applicant_email
            FROM job_applications ja
            INNER JOIN postings p ON ja.posting_id = p.posting_id
            INNER JOIN organizations o ON p.organization_id = o.organization_id
            INNER JOIN users u ON ja.user_id = u.user_id
            ORDER BY ja.applied_at DESC LIMIT 300";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/** Returns the application row only if it belongs to a posting owned by $organization_id, else null. */
function getOrgApplicationById($job_application_id, $organization_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT ja.* FROM job_applications ja
                             INNER JOIN postings p ON ja.posting_id = p.posting_id
                             WHERE ja.job_application_id = ? AND p.organization_id = ?");
    $stmt->bind_param("ii", $job_application_id, $organization_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateJobApplicationStatusByOrg($job_application_id, $organization_id, $status) {
    global $conn;
    $allowed = ['Applied', 'Under Review', 'Shortlisted', 'Interview', 'Accepted', 'Rejected'];
    if (!in_array($status, $allowed, true)) return false;
    if (!getOrgApplicationById($job_application_id, $organization_id)) return false;

    $stmt = $conn->prepare("UPDATE job_applications SET application_status = ? WHERE job_application_id = ?");
    $stmt->bind_param("si", $status, $job_application_id);
    return $stmt->execute();
}

// ======================= SAVED POSTINGS =======================

function isPostingSaved($user_id, $posting_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT save_id FROM saved_postings WHERE user_id = ? AND posting_id = ?");
    $stmt->bind_param("ii", $user_id, $posting_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function toggleSavePosting($user_id, $posting_id) {
    global $conn;
    if (isPostingSaved($user_id, $posting_id)) {
        $stmt = $conn->prepare("DELETE FROM saved_postings WHERE user_id = ? AND posting_id = ?");
        $stmt->bind_param("ii", $user_id, $posting_id);
        $stmt->execute();
        return 'removed';
    }
    $stmt = $conn->prepare("INSERT INTO saved_postings (user_id, posting_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $posting_id);
    $stmt->execute();
    return 'saved';
}

function getSavedPostings($user_id) {
    global $conn;
    sweepExpiredPostings();
    $sql = "SELECT p.*, o.org_name, sp.saved_at FROM postings p
            INNER JOIN saved_postings sp ON p.posting_id = sp.posting_id
            INNER JOIN organizations o ON p.organization_id = o.organization_id
            WHERE sp.user_id = ? ORDER BY sp.saved_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ======================= ADMIN STATS (Jobs/Internships/Orgs) =======================

function getJobsAdminStats() {
    global $conn;
    sweepExpiredPostings();
    $stats = [];
    $stats['total_organizations']    = $conn->query("SELECT COUNT(*) c FROM organizations")->fetch_assoc()['c'];
    $stats['pending_organizations']  = $conn->query("SELECT COUNT(*) c FROM organizations WHERE status='Pending'")->fetch_assoc()['c'];
    $stats['approved_organizations'] = $conn->query("SELECT COUNT(*) c FROM organizations WHERE status='Approved'")->fetch_assoc()['c'];
    $stats['total_jobs']        = $conn->query("SELECT COUNT(*) c FROM postings WHERE posting_type='Job'")->fetch_assoc()['c'];
    $stats['total_internships'] = $conn->query("SELECT COUNT(*) c FROM postings WHERE posting_type='Internship'")->fetch_assoc()['c'];
    $stats['active_postings']   = $conn->query("SELECT COUNT(*) c FROM postings WHERE posting_status='Active'")->fetch_assoc()['c'];
    $stats['pending_postings']  = $conn->query("SELECT COUNT(*) c FROM postings WHERE posting_status='Pending'")->fetch_assoc()['c'];
    $stats['expired_postings']  = $conn->query("SELECT COUNT(*) c FROM postings WHERE posting_status='Expired'")->fetch_assoc()['c'];
    $stats['total_job_applications'] = $conn->query("SELECT COUNT(*) c FROM job_applications")->fetch_assoc()['c'];
    return $stats;
}

// ======================= UI HELPERS (Jobs/Internships) =======================

function postingStatusBadgeColor($status) {
    $map = [
        'Pending' => 'warning text-dark',
        'Active' => 'success',
        'Rejected' => 'danger',
        'Closed' => 'secondary',
        'Expired' => 'dark',
    ];
    return $map[$status] ?? 'secondary';
}

function orgStatusBadgeColor($status) {
    $map = [
        'Pending' => 'warning text-dark',
        'Approved' => 'success',
        'Rejected' => 'danger',
        'Suspended' => 'dark',
    ];
    return $map[$status] ?? 'secondary';
}

function jobAppStatusBadgeColor($status) {
    $map = [
        'Applied' => 'info',
        'Under Review' => 'warning text-dark',
        'Shortlisted' => 'primary',
        'Interview' => 'primary',
        'Accepted' => 'success',
        'Rejected' => 'danger',
    ];
    return $map[$status] ?? 'secondary';
}

function formatSalary($posting) {
    if ($posting['posting_type'] === 'Internship') {
        if ($posting['paid_status'] === 'Unpaid') return 'Unpaid';
        if ($posting['salary_min'] || $posting['salary_max']) {
            return $posting['salary_currency'] . ' ' . number_format($posting['salary_min']) .
                   ($posting['salary_max'] ? ' - ' . number_format($posting['salary_max']) : '') . '/month';
        }
        return 'Paid (amount not disclosed)';
    }
    if (!$posting['salary_min'] && !$posting['salary_max']) return 'Not disclosed';
    if ($posting['salary_min'] && $posting['salary_max']) {
        return $posting['salary_currency'] . ' ' . number_format($posting['salary_min']) . ' - ' . number_format($posting['salary_max']);
    }
    return $posting['salary_currency'] . ' ' . number_format($posting['salary_min'] ?: $posting['salary_max']);
}

// =========================================================
// UNIVERSITY ENTITY MODULE
// Supports: university entity profile, posting/editing scholarships,
// collecting student applications, updating application statuses.
// =========================================================

function ensureUniversitySchema() {
    global $conn;
    static $ensured = false;
    if ($ensured) return;
    $ensured = true;

    // 1. Ensure universities table exists
    $tableRes = $conn->query("SHOW TABLES LIKE 'universities'");
    if ($tableRes && $tableRes->num_rows === 0) {
        $conn->query("CREATE TABLE IF NOT EXISTS universities (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Ensure all new profile columns exist in existing table
        $cols = [];
        $colCheck = $conn->query("SHOW COLUMNS FROM universities");
        if ($colCheck) {
            while ($r = $colCheck->fetch_assoc()) $cols[] = $r['Field'];
        }
        if (!in_array('logo_url', $cols)) $conn->query("ALTER TABLE universities ADD COLUMN logo_url VARCHAR(255) NULL AFTER website");
        if (!in_array('address', $cols)) $conn->query("ALTER TABLE universities ADD COLUMN address TEXT NULL AFTER city");
        if (!in_array('social_linkedin', $cols)) $conn->query("ALTER TABLE universities ADD COLUMN social_linkedin VARCHAR(255) NULL AFTER contact_phone");
        if (!in_array('social_twitter', $cols)) $conn->query("ALTER TABLE universities ADD COLUMN social_twitter VARCHAR(255) NULL AFTER social_linkedin");
        if (!in_array('social_facebook', $cols)) $conn->query("ALTER TABLE universities ADD COLUMN social_facebook VARCHAR(255) NULL AFTER social_twitter");
        if (!in_array('social_youtube', $cols)) $conn->query("ALTER TABLE universities ADD COLUMN social_youtube VARCHAR(255) NULL AFTER social_facebook");
    }

    // 2. Ensure user_type enum has 'university'
    $userColRes = $conn->query("SHOW COLUMNS FROM users LIKE 'user_type'");
    if ($userColRes && $row = $userColRes->fetch_assoc()) {
        if (strpos($row['Type'], "'university'") === false) {
            $conn->query("ALTER TABLE users MODIFY COLUMN user_type ENUM('student', 'graduate', 'admin', 'employer', 'university') DEFAULT 'student'");
        }
    }

    // 3. Ensure scholarships table has image_url and Draft/Published statuses
    $schCols = [];
    $schCheck = $conn->query("SHOW COLUMNS FROM scholarships");
    if ($schCheck) {
        while ($r = $schCheck->fetch_assoc()) $schCols[] = $r['Field'];
    }
    if (!in_array('image_url', $schCols)) {
        $conn->query("ALTER TABLE scholarships ADD COLUMN image_url VARCHAR(255) NULL");
    }

    // 4. Ensure applications table has document_path and Pending/Approved statuses
    $appCols = [];
    $appCheck = $conn->query("SHOW COLUMNS FROM applications");
    if ($appCheck) {
        while ($r = $appCheck->fetch_assoc()) $appCols[] = $r['Field'];
    }
    if (!in_array('document_path', $appCols)) {
        $conn->query("ALTER TABLE applications ADD COLUMN document_path VARCHAR(255) NULL");
    }
}

function getUniversityByUser($user_id) {
    global $conn;
    ensureUniversitySchema();
    $stmt = $conn->prepare("SELECT * FROM universities WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUniversityById($university_id) {
    global $conn;
    ensureUniversitySchema();
    $stmt = $conn->prepare("SELECT * FROM universities WHERE university_id = ?");
    $stmt->bind_param("i", $university_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createUniversity($user_id, $data) {
    global $conn;
    ensureUniversitySchema();
    $logo = $data['logo_url'] ?? null;
    $addr = $data['address'] ?? null;
    $li   = $data['social_linkedin'] ?? null;
    $tw   = $data['social_twitter'] ?? null;
    $fb   = $data['social_facebook'] ?? null;
    $yt   = $data['social_youtube'] ?? null;

    $sql = "INSERT INTO universities 
            (user_id, univ_name, country, city, address, website, logo_url, ranking, established_year, description, contact_email, contact_phone, social_linkedin, social_twitter, social_facebook, social_youtube, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Approved')";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "isssssssissssssss",
            $user_id,
            $data['univ_name'],
            $data['country'],
            $data['city'],
            $addr,
            $data['website'],
            $logo,
            $data['ranking'],
            $data['established_year'],
            $data['description'],
            $data['contact_email'],
            $data['contact_phone'],
            $li,
            $tw,
            $fb,
            $yt
        );
        return $stmt->execute();
    }
    return false;
}

function updateUniversity($university_id, $data) {
    global $conn;
    ensureUniversitySchema();
    $logo = $data['logo_url'] ?? null;
    $addr = $data['address'] ?? null;
    $li   = $data['social_linkedin'] ?? null;
    $tw   = $data['social_twitter'] ?? null;
    $fb   = $data['social_facebook'] ?? null;
    $yt   = $data['social_youtube'] ?? null;

    $sql = "UPDATE universities SET
            univ_name = ?, country = ?, city = ?, address = ?, website = ?, logo_url = ?, ranking = ?,
            established_year = ?, description = ?, contact_email = ?, contact_phone = ?,
            social_linkedin = ?, social_twitter = ?, social_facebook = ?, social_youtube = ?, updated_at = NOW()
            WHERE university_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "sssssssisssssssi",
            $data['univ_name'],
            $data['country'],
            $data['city'],
            $addr,
            $data['website'],
            $logo,
            $data['ranking'],
            $data['established_year'],
            $data['description'],
            $data['contact_email'],
            $data['contact_phone'],
            $li,
            $tw,
            $fb,
            $yt,
            $university_id
        );
        return $stmt->execute();
    }
    return false;
}

function getUniversityScholarships($user_id, $filters = []) {
    global $conn;
    ensureUniversitySchema();
    $sql = "SELECT s.*, 
            (SELECT COUNT(*) FROM applications a WHERE a.scholarship_id = s.scholarship_id) AS applications_count
            FROM scholarships s
            WHERE s.created_by = ?";
    $params = [$user_id];
    $types = "i";

    if (!empty($filters['status'])) {
        if ($filters['status'] === 'Expired') {
            $sql .= " AND (s.status = 'Expired' OR (s.application_deadline < CURDATE() AND s.status != 'Draft'))";
        } elseif ($filters['status'] === 'Active' || $filters['status'] === 'Published') {
            $sql .= " AND s.status IN ('Active', 'Published') AND s.application_deadline >= CURDATE()";
        } else {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
    }

    if (!empty($filters['degree_level'])) {
        $sql .= " AND s.degree_level = ?";
        $params[] = $filters['degree_level'];
        $types .= "s";
    }

    $sql .= " ORDER BY s.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function scholarshipBelongsToUniversity($scholarship_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT scholarship_id FROM scholarships WHERE scholarship_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $scholarship_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getUniversityApplications($user_id, $scholarship_id = null, $status = null, $department = null, $date = null) {
    global $conn;
    ensureUniversitySchema();
    $sql = "SELECT a.*, 
                   u.name AS applicant_name, 
                   u.email AS applicant_email, 
                   u.phone AS applicant_phone,
                   u.university AS applicant_university,
                   u.degree AS applicant_degree,
                   u.field_of_study AS applicant_department,
                   u.skills AS applicant_skills,
                   u.bio AS applicant_bio,
                   u.experience_years AS applicant_experience,
                   s.scholarship_id,
                   s.scholarship_name,
                   s.degree_level,
                   s.field_of_study AS scholarship_department,
                   s.country,
                   s.application_deadline,
                   s.required_documents,
                   s.scholarship_amount,
                   s.status AS scholarship_status
            FROM applications a
            INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
            INNER JOIN users u ON a.user_id = u.user_id
            WHERE s.created_by = ?";
    $params = [$user_id];
    $types = "i";

    if ($scholarship_id) {
        $sql .= " AND a.scholarship_id = ?";
        $params[] = (int)$scholarship_id;
        $types .= "i";
    }

    if (!empty($status)) {
        if ($status === 'Pending') {
            $sql .= " AND a.application_status IN ('Pending', 'Applied')";
        } elseif ($status === 'Approved') {
            $sql .= " AND a.application_status IN ('Approved', 'Accepted')";
        } else {
            $sql .= " AND a.application_status = ?";
            $params[] = $status;
            $types .= "s";
        }
    }

    if (!empty($department)) {
        $sql .= " AND (u.field_of_study LIKE ? OR s.field_of_study LIKE ?)";
        $deptParam = "%" . $department . "%";
        $params[] = $deptParam;
        $params[] = $deptParam;
        $types .= "ss";
    }

    if (!empty($date)) {
        $sql .= " AND DATE(a.applied_at) = ?";
        $params[] = $date;
        $types .= "s";
    }

    $sql .= " ORDER BY a.applied_at DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function updateScholarshipApplicationStatusByUniversity($application_id, $user_id, $status, $review_notes = null) {
    global $conn;
    ensureUniversitySchema();
    $checkSql = "SELECT a.application_id 
                 FROM applications a 
                 INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id 
                 WHERE a.application_id = ? AND s.created_by = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $application_id, $user_id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        return false;
    }

    if ($review_notes !== null && $review_notes !== '') {
        $sql = "UPDATE applications SET application_status = ?, notes = CONCAT(IFNULL(notes, ''), '\n[University Review]: ', ?), updated_at = NOW() WHERE application_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $review_notes, $application_id);
    } else {
        $sql = "UPDATE applications SET application_status = ?, updated_at = NOW() WHERE application_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $application_id);
    }
    return $stmt->execute();
}

function getUniversityStats($user_id) {
    global $conn;
    ensureUniversitySchema();
    $stats = [
        'total_scholarships'    => 0,
        'active_scholarships'   => 0,
        'expired_scholarships'  => 0,
        'total_applications'    => 0,
        'pending_applications'  => 0,
        'approved_applications' => 0,
        'rejected_applications' => 0,
    ];

    // 1. Total Scholarships Posted
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM scholarships WHERE created_by = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['total_scholarships'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    // 2. Active Scholarships (Status Active/Published and deadline today or future)
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM scholarships WHERE created_by = ? AND status IN ('Active', 'Published') AND application_deadline >= CURDATE()");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['active_scholarships'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    // 3. Expired Scholarships (Status Expired OR deadline in past while not Draft)
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM scholarships WHERE created_by = ? AND (status = 'Expired' OR (application_deadline < CURDATE() AND status != 'Draft'))");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['expired_scholarships'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    // 4. Total Applications Received
    $stmt = $conn->prepare(
        "SELECT COUNT(a.application_id) c 
         FROM applications a 
         INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id 
         WHERE s.created_by = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['total_applications'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    // 5. Pending Applications (Status in 'Pending', 'Applied', 'Under Review')
    $stmt = $conn->prepare(
        "SELECT COUNT(a.application_id) c 
         FROM applications a 
         INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id 
         WHERE s.created_by = ? AND a.application_status IN ('Pending', 'Applied', 'Under Review')"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['pending_applications'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    // 6. Approved Applications (Status in 'Approved', 'Accepted', 'Shortlisted')
    $stmt = $conn->prepare(
        "SELECT COUNT(a.application_id) c 
         FROM applications a 
         INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id 
         WHERE s.created_by = ? AND a.application_status IN ('Approved', 'Accepted')"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['approved_applications'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    // 7. Rejected Applications (Status = 'Rejected')
    $stmt = $conn->prepare(
        "SELECT COUNT(a.application_id) c 
         FROM applications a 
         INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id 
         WHERE s.created_by = ? AND a.application_status = 'Rejected'"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['rejected_applications'] = (int) $stmt->get_result()->fetch_assoc()['c'];

    return $stats;
}

function univStatusBadgeColor($status) {
    $map = [
        'Approved' => 'success',
        'Pending' => 'warning text-dark',
        'Rejected' => 'danger',
        'Suspended' => 'dark',
    ];
    return $map[$status] ?? 'secondary';
}

