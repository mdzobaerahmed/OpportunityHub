<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Job & Internship Applications';

$applications = getAllJobApplicationsAdmin();
$filter = trim($_GET['status'] ?? '');
if ($filter) {
    $applications = array_filter($applications, fn($a) => $a['application_status'] === $filter);
}
$statuses = ['Applied', 'Under Review', 'Shortlisted', 'Interview', 'Accepted', 'Rejected'];
$all = getAllJobApplicationsAdmin();

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Job &amp; Internship Applications <small class="text-muted fs-6">(<?php echo count($applications); ?>)</small></h1>
  <p class="text-muted">Platform-wide monitoring. Status changes are handled by each organization.</p>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="btn-group flex-wrap">
        <a href="manage-job-applications.php" class="btn btn-outline-primary <?php echo !$filter ? 'active' : ''; ?>">All (<?php echo count($all); ?>)</a>
        <?php foreach ($statuses as $s): $c = count(array_filter($all, fn($a) => $a['application_status'] === $s)); ?>
          <a href="manage-job-applications.php?status=<?php echo urlencode($s); ?>" class="btn btn-outline-secondary <?php echo ($filter === $s) ? 'active' : ''; ?>"><?php echo $s; ?> (<?php echo $c; ?>)</a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead>
        <tr><th>Applicant</th><th>Position</th><th>Organization</th><th>Type</th><th>Status</th><th>Applied</th><th>Posting</th></tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $a): ?>
          <tr>
            <td><?php echo e($a['applicant_name']); ?><br><small class="text-muted"><?php echo e($a['applicant_email']); ?></small></td>
            <td><?php echo e(mb_strimwidth($a['title'], 0, 30, '...')); ?></td>
            <td><?php echo e($a['org_name']); ?></td>
            <td><span class="badge bg-secondary"><?php echo e($a['posting_type']); ?></span></td>
            <td><span class="badge bg-<?php echo jobAppStatusBadgeColor($a['application_status']); ?>"><?php echo e($a['application_status']); ?></span></td>
            <td><?php echo date('M d, Y', strtotime($a['applied_at'])); ?></td>
            <td><a href="../posting-details.php?id=<?php echo (int)$a['posting_id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($applications)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No applications found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
