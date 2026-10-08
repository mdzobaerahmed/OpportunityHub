<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin() || isEmployer() || isUniversity()) { header("Location: jobs.php"); exit; }

$id = (int) ($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? '';

if ($id) {
    toggleSavePosting($_SESSION['user_id'], $id);
}

switch ($redirect) {
    case 'details':     header("Location: posting-details.php?id=" . $id); break;
    case 'internships': header("Location: internships.php"); break;
    case 'saved':       header("Location: saved-opportunities.php"); break;
    default:            header("Location: jobs.php");
}
exit;
