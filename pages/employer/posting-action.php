<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$org = getOrganizationByUser($_SESSION['user_id']);
if (!$org) { header("Location: organization.php"); exit; }

$id     = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

// Never trust the posting id from the URL - verify ownership first.
if (!$id || !postingBelongsToOrganization($id, $org['organization_id'])) {
    header("Location: my-postings.php?err=denied");
    exit;
}

$posting = getPostingById($id);

switch ($action) {
    case 'close':
        setPostingStatus($id, 'Closed');
        break;
    case 'reopen':
        if (!isPostingExpired($posting)) {
            setPostingStatus($id, organizationIsApproved($org) ? 'Active' : 'Pending');
        }
        break;
    case 'delete':
        deletePosting($id);
        header("Location: my-postings.php?deleted=1");
        exit;
}

header("Location: my-postings.php?updated=1");
exit;
