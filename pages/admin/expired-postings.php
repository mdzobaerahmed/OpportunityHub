<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Expired Postings';

sweepExpiredPostings();
$expired = getAllPostingsAdmin(['posting_status' => 'Expired']);

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Expired Postings <small class="text-muted fs-6">(<?php echo count($expired); ?>)</small></h1>
  <p class="text-muted">Postings whose application deadline has passed. They no longer appear to students and cannot receive new applications, but stay here for history and management.</p>

  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Posting updated.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">Posting deleted.</div><?php endif; ?>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead>
        <tr><th>Title</th><th>Organization</th><th>Type</th><th>Deadline</th><th>Expired For</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($expired as $p): $days = abs(daysLeft($p['application_deadline'])); $pid = (int)$p['posting_id']; ?>
          <tr>
            <td><strong><?php echo e(mb_strimwidth($p['title'], 0, 35, '...')); ?></strong></td>
            <td><?php echo e($p['org_name']); ?></td>
            <td><span class="badge bg-secondary"><?php echo e($p['posting_type']); ?></span></td>
            <td><?php echo date('M d, Y', strtotime($p['application_deadline'])); ?></td>
            <td><span class="badge bg-dark"><?php echo $days; ?> days</span></td>
            <td>
              <a href="../posting-details.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-outline-secondary">View</a>
              <a href="edit-posting.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-outline-primary">Edit / Extend Deadline</a>
              <a href="posting-action.php?id=<?php echo $pid; ?>&action=delete&back=expired-postings.php" class="btn btn-sm btn-outline-danger" data-confirm="Delete this expired posting permanently?">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($expired)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No expired postings.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
