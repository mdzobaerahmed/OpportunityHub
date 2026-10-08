<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin() || isEmployer() || isUniversity()) { header("Location: jobs.php"); exit; }

$pageTitle = 'My Job Applications';
$apps = getUserJobApplications($_SESSION['user_id']);

$filter = $_GET['status'] ?? '';
if ($filter) {
    $apps = array_filter($apps, fn($a) => $a['application_status'] === $filter);
}
$statuses = ['Applied', 'Under Review', 'Shortlisted', 'Interview', 'Accepted', 'Rejected'];
$all = getUserJobApplications($_SESSION['user_id']);

include '../includes/header.php';
?>
<div class="bg-white py-4 border-bottom">
  <div class="container">
    <h1 class="mb-0">My Job &amp; Internship Applications</h1>
    <p class="text-muted mb-0">Track the status of every opportunity you've applied to</p>
  </div>
</div>

<div class="container py-5">
  <?php if (isset($_GET['applied'])): ?>
    <div class="alert alert-success auto-dismiss">Your application was submitted successfully.</div>
  <?php endif; ?>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="btn-group flex-wrap" role="group">
        <a href="my-job-applications.php" class="btn btn-outline-primary <?php echo !$filter ? 'active' : ''; ?>">All (<?php echo count($all); ?>)</a>
        <?php foreach ($statuses as $s): $c = count(array_filter($all, fn($a) => $a['application_status'] === $s)); ?>
          <a href="my-job-applications.php?status=<?php echo urlencode($s); ?>"
             class="btn btn-outline-secondary <?php echo ($filter === $s) ? 'active' : ''; ?>"><?php echo $s; ?> (<?php echo $c; ?>)</a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php if (empty($apps)): ?>
    <div class="alert alert-info text-center py-5">
      <i class="fas fa-inbox fa-3x mb-3"></i>
      <p>No applications found.</p>
      <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
      <a href="internships.php" class="btn btn-outline-primary">Browse Internships</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover bg-white align-middle">
        <thead>
          <tr><th>Position</th><th>Organization</th><th>Type</th><th>Status</th><th>Applied On</th><th>Deadline</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($apps as $a): $days = daysLeft($a['application_deadline']); ?>
            <tr>
              <td><strong><?php echo e($a['title']); ?></strong></td>
              <td><?php echo e($a['org_name']); ?></td>
              <td><span class="badge bg-secondary"><?php echo e($a['posting_type']); ?></span></td>
              <td><span class="badge bg-<?php echo jobAppStatusBadgeColor($a['application_status']); ?>"><?php echo e($a['application_status']); ?></span></td>
              <td><?php echo date('M d, Y', strtotime($a['applied_at'])); ?></td>
              <td><span class="badge bg-<?php echo $days < 0 ? 'danger' : ($days < 7 ? 'warning text-dark' : 'success'); ?>"><?php echo $days < 0 ? 'Expired' : $days . ' days'; ?></span></td>
              <td><a href="posting-details.php?id=<?php echo (int)$a['posting_id']; ?>" class="btn btn-sm btn-primary">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
