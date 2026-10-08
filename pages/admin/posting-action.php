<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id     = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$back   = $_GET['back'] ?? 'manage-postings.php';
$back   = in_array($back, ['manage-postings.php', 'expired-postings.php'], true) ? $back : 'manage-postings.php';

if ($id) {
    $posting = getPostingById($id);
    switch ($action) {
        case 'approve':
            // Refuse to activate something already past its deadline.
            if ($posting && !isPostingExpired($posting)) {
                setPostingStatus($id, 'Active');
            } else {
                setPostingStatus($id, 'Expired');
            }
            break;
        case 'reject':     setPostingStatus($id, 'Rejected'); break;
        case 'deactivate': setPostingStatus($id, 'Closed'); break;
        case 'close':      setPostingStatus($id, 'Closed'); break;
        case 'delete':
            deletePosting($id);
            header("Location: " . $back . "?deleted=1");
            exit;
    }
}

header("Location: " . $back . "?updated=1");
exit;
