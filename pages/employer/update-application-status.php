<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$org = getOrganizationByUser($_SESSION['user_id']);
if (!$org) { header("Location: organization.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfCheck()) {
    header("Location: applications.php");
    exit;
}

$appId  = (int) ($_POST['job_application_id'] ?? 0);
$status = $_POST['status'] ?? '';

// updateJobApplicationStatusByOrg re-verifies that the application belongs
// to a posting owned by this organization before writing anything.
if ($appId && updateJobApplicationStatusByOrg($appId, $org['organization_id'], $status)) {
    header("Location: applications.php?updated=1");
} else {
    header("Location: applications.php?err=denied");
}
exit;
