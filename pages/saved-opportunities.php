<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin() || isEmployer() || isUniversity()) { header("Location: jobs.php"); exit; }

$pageTitle = 'Saved Opportunities';
$saved = getSavedPostings($_SESSION['user_id']);

include '../includes/header.php';
?>
<div class="bg-white py-4 border-bottom">
  <div class="container">
    <h1 class="mb-0">Saved Jobs &amp; Internships</h1>
    <p class="text-muted mb-0">Opportunities you bookmarked to review later</p>
  </div>
</div>

<div class="container py-5">
  <?php if (empty($saved)): ?>
    <div class="alert alert-info text-center py-5">
      <i class="fas fa-heart fa-3x mb-3"></i>
      <p>You haven't saved any jobs or internships yet.</p>
      <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
      <a href="internships.php" class="btn btn-outline-primary">Browse Internships</a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($saved as $p): $days = daysLeft($p['application_deadline']); ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm hover-card">
            <div class="card-body">
              <h5 class="card-title text-primary"><?php echo e($p['title']); ?></h5>
              <p class="text-muted mb-2"><?php echo e($p['org_name']); ?></p>
              <span class="badge bg-secondary"><?php echo e($p['posting_type']); ?></span>
              <span class="badge bg-info text-dark"><?php echo e($p['location']); ?></span>
              <span class="badge bg-<?php echo postingStatusBadgeColor($p['posting_status']); ?>"><?php echo e($p['posting_status']); ?></span>
              <p class="small mt-2 <?php echo $days < 7 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                Deadline: <?php echo date('M d, Y', strtotime($p['application_deadline'])); ?>
                <?php echo $days < 0 ? ' (expired)' : ''; ?>
              </p>
            </div>
            <div class="card-footer bg-white border-0 d-flex gap-2">
              <a href="posting-details.php?id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-primary btn-sm flex-fill">View</a>
              <a href="save-posting.php?id=<?php echo (int)$p['posting_id']; ?>&redirect=saved" class="btn btn-outline-danger btn-sm flex-fill" data-confirm="Remove from saved?">Remove</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
