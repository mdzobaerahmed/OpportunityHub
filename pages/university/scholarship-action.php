<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!$id || !scholarshipBelongsToUniversity($id, $_SESSION['user_id'])) {
    header("Location: my-scholarships.php?err=denied");
    exit;
}

if ($action === 'publish') {
    $stmt = $conn->prepare("UPDATE scholarships SET status = 'Active', updated_at = NOW() WHERE scholarship_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: my-scholarships.php?status_changed=1");
    exit;
}

if ($action === 'unpublish' || $action === 'draft') {
    $stmt = $conn->prepare("UPDATE scholarships SET status = 'Draft', updated_at = NOW() WHERE scholarship_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: my-scholarships.php?status_changed=1");
    exit;
}

if ($action === 'close' || $action === 'close_expired') {
    $stmt = $conn->prepare("UPDATE scholarships SET status = 'Closed', updated_at = NOW() WHERE scholarship_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: my-scholarships.php?status_changed=1");
    exit;
}

if ($action === 'status') {
    $newStatus = trim($_POST['status'] ?? $_GET['status'] ?? '');
    $allowed = ['Active', 'Draft', 'Closed', 'Upcoming', 'Expired'];
    if (in_array($newStatus, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE scholarships SET status = ?, updated_at = NOW() WHERE scholarship_id = ? AND created_by = ?");
        $stmt->bind_param("sii", $newStatus, $id, $_SESSION['user_id']);
        $stmt->execute();
        header("Location: my-scholarships.php?status_changed=1");
        exit;
    }
}

if ($action === 'toggle') {
    $existing = getScholarshipById($id);
    if ($existing) {
        $newStatus = ($existing['status'] === 'Active') ? 'Closed' : 'Active';
        $stmt = $conn->prepare("UPDATE scholarships SET status = ?, updated_at = NOW() WHERE scholarship_id = ? AND created_by = ?");
        $stmt->bind_param("sii", $newStatus, $id, $_SESSION['user_id']);
        $stmt->execute();
    }
    header("Location: my-scholarships.php?status_changed=1");
    exit;
}

if ($action === 'delete') {
    deleteScholarship($id);
    header("Location: my-scholarships.php?deleted=1");
    exit;
}

header("Location: my-scholarships.php");
exit;
