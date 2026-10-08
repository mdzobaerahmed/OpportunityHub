<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id     = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id) {
    switch ($action) {
        case 'approve': setOrganizationStatus($id, 'Approved'); break;
        case 'reject':  setOrganizationStatus($id, 'Rejected'); break;
        case 'suspend': setOrganizationStatus($id, 'Suspended'); break;
        case 'activate': setOrganizationStatus($id, 'Approved'); break;
        case 'delete':
            deleteOrganization($id);
            header("Location: manage-organizations.php?deleted=1");
            exit;
    }
}

header("Location: manage-organizations.php?updated=1");
exit;
