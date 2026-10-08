<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$org = getOrganizationByUser($_SESSION['user_id']);
if (!$org) { header("Location: organization.php"); exit; }

$pageTitle = 'My Postings';
$filters = [
    'posting_type'   => trim($_GET['posting_type'] ?? ''),
    'posting_status' => trim($_GET['posting_status'] ?? ''),
];
$postings = getOrganizationPostings($org['organization_id'], $filters);

include '../../includes/header.php';
include '../../includes/employer-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="mb-0">My Postings <small class="text-muted fs-6">(<?php echo count($postings); ?>)</small></h1>
    <a href="post-opportunity.php" class="btn btn-success"><i class="fas fa-plus"></i> Post New Opportunity</a>
  </div>

  <?php if (isset($_GET['created'])): ?><div class="alert alert-success auto-dismiss">Posting created successfully.</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Posting updated successfully.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">Posting deleted.</div><?php endif; ?>
  <?php if (isset($_GET['err'])): ?><div class="alert alert-danger">You are not allowed to manage that posting.</div><?php endif; ?>

  <form method="GET" class="card shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Type</label>
        <select class="form-select" name="posting_type">
          <option value="">All</option>
          <?php foreach (['Job','Internship'] as $t): ?>
            <option value="<?php echo $t; ?>" <?php echo ($filters['posting_type'] === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="posting_status">
          <option value="">All</option>
          <?php foreach (['Pending','Active','Rejected','Closed','Expired'] as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo ($filters['posting_status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="my-postings.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead>
        <tr><th>Title</th><th>Type</th><th>Deadline</th><th>Status</th><th>Applications</th><th>Views</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($postings as $p):
            $appCount = count(getOrganizationApplications($org['organization_id'], $p['posting_id']));
            $days = daysLeft($p['application_deadline']);
        ?>
          <tr>
            <td><strong><?php echo e(mb_strimwidth($p['title'], 0, 40, '...')); ?></strong></td>
            <td><span class="badge bg-secondary"><?php echo e($p['posting_type']); ?></span></td>
            <td>
              <?php echo date('M d, Y', strtotime($p['application_deadline'])); ?>
              <br><small class="<?php echo $days < 0 ? 'text-danger' : 'text-muted'; ?>"><?php echo $days < 0 ? 'expired' : $days . ' days left'; ?></small>
            </td>
            <td><span class="badge bg-<?php echo postingStatusBadgeColor($p['posting_status']); ?>"><?php echo e($p['posting_status']); ?></span></td>
            <td><a href="applications.php?posting_id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-sm btn-outline-info"><?php echo $appCount; ?> received</a></td>
            <td><?php echo (int)$p['views']; ?></td>
            <td>
              <a href="../posting-details.php?id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-sm btn-outline-secondary">View</a>
              <a href="edit-posting.php?id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <?php if ($p['posting_status'] === 'Active'): ?>
                <a href="posting-action.php?id=<?php echo (int)$p['posting_id']; ?>&action=close" class="btn btn-sm btn-outline-warning" data-confirm="Close this posting?">Close</a>
              <?php elseif (in_array($p['posting_status'], ['Closed'], true) && $days >= 0): ?>
                <a href="posting-action.php?id=<?php echo (int)$p['posting_id']; ?>&action=reopen" class="btn btn-sm btn-outline-success">Reopen</a>
              <?php endif; ?>
              <a href="posting-action.php?id=<?php echo (int)$p['posting_id']; ?>&action=delete" class="btn btn-sm btn-outline-danger" data-confirm="Delete this posting permanently?">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($postings)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No postings yet. <a href="post-opportunity.php">Create your first posting</a>.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
