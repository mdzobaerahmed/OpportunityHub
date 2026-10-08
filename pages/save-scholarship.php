<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin() || isEmployer() || isUniversity()) {
    header("Location: scholarships.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? '';

if ($id) {
    toggleSaveScholarship($_SESSION['user_id'], $id);
}

if ($redirect === 'details') {
    header("Location: scholarship-details.php?id=" . $id);
} elseif ($redirect === 'saved') {
    header("Location: saved-scholarships.php");
} else {
    header("Location: scholarships.php");
}
exit;
