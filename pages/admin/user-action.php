<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id     = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!$id) { header("Location: manage-users.php?err=1"); exit; }

// Admin accounts cannot be demoted or deleted through this screen.
$stmt = $conn->prepare("SELECT user_type FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$target = $stmt->get_result()->fetch_assoc();

if (!$target || $target['user_type'] === 'admin' || $id === (int)$_SESSION['user_id']) {
    header("Location: manage-users.php?err=1");
    exit;
}

if ($action === 'role') {
    if (!csrfCheck()) { header("Location: manage-users.php?err=1"); exit; }
    $newType = $_POST['user_type'] ?? '';
    if (in_array($newType, ['student', 'graduate', 'employer', 'university'], true)) {
        $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newType, $id);
        $stmt->execute();
    }
    header("Location: manage-users.php?updated=1");
    exit;
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage-users.php?deleted=1");
    exit;
}

header("Location: manage-users.php");
exit;
