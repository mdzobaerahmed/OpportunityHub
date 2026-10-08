<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$scholarship_id = (int) ($_POST['scholarship_id'] ?? 0);
$status = $_POST['status'] ?? 'Applied';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

if (isAdmin() || isEmployer() || isUniversity()) {
    header("Location: scholarship-details.php?id=" . $scholarship_id);
    exit;
}

$allowed = ['Saved', 'Applied', 'Under Review', 'Shortlisted', 'Accepted', 'Rejected'];
if ($scholarship_id && in_array($status, $allowed, true)) {
    trackApplication($_SESSION['user_id'], $scholarship_id, $status, $notes);
}

header("Location: scholarship-details.php?id=" . $scholarship_id);
exit;
