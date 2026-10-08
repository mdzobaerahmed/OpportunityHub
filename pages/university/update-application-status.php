<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfCheck()) {
    header("Location: applications.php?err=token");
    exit;
}

$application_id = (int) ($_POST['application_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$reviewNotes = trim($_POST['review_notes'] ?? '');

$redirectScholarshipId = (int) ($_POST['redirect_scholarship_id'] ?? 0);
$redirectStatus = trim($_POST['redirect_status'] ?? '');
$redirectDept = trim($_POST['redirect_department'] ?? '');
$redirectDate = trim($_POST['redirect_date'] ?? '');

// Support prompt statuses: Pending, Under Review, Approved, Rejected (+ legacy)
$allowed = ['Pending', 'Applied', 'Under Review', 'Shortlisted', 'Approved', 'Accepted', 'Rejected'];
if ($application_id && in_array($status, $allowed, true)) {
    if (updateScholarshipApplicationStatusByUniversity($application_id, $_SESSION['user_id'], $status, $reviewNotes)) {
        $qs = [];
        if ($redirectScholarshipId) $qs[] = 'scholarship_id=' . $redirectScholarshipId;
        if ($redirectStatus !== '') $qs[] = 'status=' . urlencode($redirectStatus);
        if ($redirectDept !== '') $qs[] = 'department=' . urlencode($redirectDept);
        if ($redirectDate !== '') $qs[] = 'date=' . urlencode($redirectDate);
        $qs[] = 'updated=1';
        header("Location: applications.php?" . implode('&', $qs));
        exit;
    }
}

header("Location: applications.php?err=denied");
exit;
