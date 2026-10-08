<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
if ($id) {
    deleteScholarship($id);
}

header("Location: manage-scholarships.php?deleted=1");
exit;
