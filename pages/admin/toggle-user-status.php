<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
if ($id) {
    toggleUserStatus($id);
}

header("Location: manage-users.php");
exit;
