<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

sweepExpiredPostings();

$id = (int) ($_GET['id'] ?? 0);
$p  = $id ? getPostingById($id) : null;

// Only publicly visible if approved org + not pending/rejected, unless viewer is admin or the owning employer.
$viewerIsOwner = isLoggedIn() && isEmployer() && $p && (int)$p['org_owner_id'] === (int)$_SESSION['user_id'];
$viewerIsAdmin = isLoggedIn() && isAdmin();
$publiclyVisible = $p && $p['org_status'] === 'Approved'
    && in_array($p['posting_status'], ['Active', 'Closed', 'Expired'], true);

if (!$p || (!$publiclyVisible && !$viewerIsOwner && !$viewerIsAdmin)) {
    header("Location: jobs.php");
    exit;
}

incrementPostingViews($id);
$pageTitle  = $p['title'];
$expired    = isPostingExpired($p) || $p['posting_status'] === 'Expired';
$closed     = $p['posting_status'] === 'Closed';
$canApply   = !$expired && !$closed && $p['posting_status'] === 'Active';
$days       = daysLeft($p['application_deadline']);
$backPage   = $p['posting_type'] === 'Internship' ? 'internships.php' : 'jobs.php';

$isSaved = false; $existingApp = null;
if (isLoggedIn() && !isAdmin() && !isEmployer()) {
    $isSaved = isPostingSaved($_SESSION['user_id'], $id);
    $existingApp = hasUserAppliedToPosting($_SESSION['user_id'], $id);
}

include '../includes/header.php';
?>
<div class="container py-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="<?php echo $backPage; ?>"><?php echo e($p['posting_type']); ?>s</a></li>
      <li class="breadcrumb-item active"><?php echo e($p['title']); ?></li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h2 class="text-primary"><?php echo e($p['title']); ?></h2>
          <h5 class="text-muted mb-3"><?php echo e($p['org_name']); ?><?php echo $p['location'] ? ', ' . e($p['location']) : ''; ?></h5>

          <div class="mb-3">
            <span class="badge bg-secondary"><?php echo e($p['posting_type']); ?></span>
            <span class="badge bg-info text-dark"><?php echo e($p['work_type']); ?></span>
            <?php if ($p['posting_type'] === 'Job'): ?>
              <span class="badge bg-success"><?php echo e($p['job_type']); ?></span>
            <?php else: ?>
              <span class="badge bg-<?php echo $p['paid_status'] === 'Paid' ? 'success' : 'secondary'; ?>"><?php echo e($p['paid_status']); ?></span>
            <?php endif; ?>
            <?php if ($p['category_name']): ?><span class="badge bg-dark"><?php echo e($p['category_name']); ?></span><?php endif; ?>
            <span class="badge bg-<?php echo postingStatusBadgeColor($p['posting_status']); ?>"><?php echo e($p['posting_status']); ?></span>
          </div>

          <?php if ($expired): ?>
            <div class="alert alert-dark"><i class="fas fa-hourglass-end"></i> This opportunity has expired and is no longer accepting applications.</div>
          <?php elseif ($closed): ?>
            <div class="alert alert-secondary"><i class="fas fa-lock"></i> This posting has been closed by the organization.</div>
          <?php endif; ?>

          <?php if ($p['description']): ?><h5>Description</h5><p><?php echo nl2br(e($p['description'])); ?></p><?php endif; ?>
          <?php if ($p['responsibilities']): ?><h5>Responsibilities</h5><p><?php echo nl2br(e($p['responsibilities'])); ?></p><?php endif; ?>
          <?php if ($p['requirements']): ?><h5>Requirements</h5><p><?php echo nl2br(e($p['requirements'])); ?></p><?php endif; ?>
          <?php if ($p['qualifications']): ?><h5>Qualifications</h5><p><?php echo nl2br(e($p['qualifications'])); ?></p><?php endif; ?>
          <?php if ($p['skills']): ?><h5>Skills</h5><p><?php echo e($p['skills']); ?></p><?php endif; ?>

          <div class="row mt-4">
            <div class="col-md-6 mb-2"><strong>Vacancies:</strong> <?php echo (int)$p['vacancies']; ?></div>
            <div class="col-md-6 mb-2"><strong>Posted:</strong> <?php echo date('M d, Y', strtotime($p['posted_at'])); ?></div>
            <?php if ($p['duration']): ?><div class="col-md-6 mb-2"><strong>Duration:</strong> <?php echo e($p['duration']); ?></div><?php endif; ?>
            <div class="col-md-6 mb-2"><strong><?php echo $p['posting_type'] === 'Internship' ? 'Stipend' : 'Salary'; ?>:</strong> <?php echo e(formatSalary($p)); ?></div>
            <?php if ($p['application_method']): ?><div class="col-md-6 mb-2"><strong>How to Apply:</strong> <?php echo e($p['application_method']); ?></div><?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($p['org_description']): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-light"><strong>About <?php echo e($p['org_name']); ?></strong></div>
        <div class="card-body">
          <p class="mb-2"><?php echo nl2br(e($p['org_description'])); ?></p>
          <?php if ($p['org_website']): ?>
            <a href="<?php echo e($p['org_website']); ?>" target="_blank" rel="noopener">Company Website <i class="fas fa-external-link-alt"></i></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
          <h6 class="text-muted">Application Deadline</h6>
          <h4 class="<?php echo ($days < 7) ? 'text-danger' : 'text-primary'; ?>">
            <?php echo date('M d, Y', strtotime($p['application_deadline'])); ?>
          </h4>
          <p class="mb-3 <?php echo ($days < 7) ? 'text-danger fw-bold' : 'text-muted'; ?>">
            <?php echo $days >= 0 ? $days . ' days remaining' : 'Applications closed'; ?>
          </p>

          <?php if (isLoggedIn() && !isAdmin() && !isEmployer()): ?>
            <a href="save-posting.php?id=<?php echo $id; ?>&redirect=details" class="btn btn-outline-secondary w-100 mb-2">
              <i class="fas fa-heart" style="color: <?php echo $isSaved ? '#dc3545' : '#adb5bd'; ?>"></i>
              <?php echo $isSaved ? 'Remove from Saved' : 'Save Opportunity'; ?>
            </a>

            <?php if ($existingApp): ?>
              <div class="alert alert-<?php echo jobAppStatusBadgeColor($existingApp['application_status']); ?> py-2 mb-2">
                You applied on <?php echo date('M d, Y', strtotime($existingApp['applied_at'])); ?><br>
                Status: <strong><?php echo e($existingApp['application_status']); ?></strong>
              </div>
              <a href="my-job-applications.php" class="btn btn-sm btn-primary w-100">View My Applications</a>
            <?php elseif ($canApply): ?>
              <a href="apply-job.php?id=<?php echo $id; ?>" class="btn btn-success w-100">
                <i class="fas fa-paper-plane"></i> Apply Now
              </a>
            <?php else: ?>
              <button class="btn btn-secondary w-100" disabled>Applications Closed</button>
            <?php endif; ?>

          <?php elseif (!isLoggedIn()): ?>
            <a href="login.php" class="btn btn-outline-primary w-100">Login to Apply / Save</a>
          <?php endif; ?>

          <?php if ($p['application_link']): ?>
            <a href="<?php echo e($p['application_link']); ?>" target="_blank" rel="noopener" class="btn btn-warning w-100 mt-2 fw-bold">
              <i class="fas fa-external-link-alt"></i> External Application Page
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h6>Quick Info</h6>
          <p class="mb-1"><i class="fas fa-map-marker-alt text-muted"></i> <?php echo e($p['location'] ?: 'Not specified'); ?></p>
          <p class="mb-1"><i class="fas fa-eye text-muted"></i> <?php echo (int)$p['views']; ?> views</p>
          <p class="mb-0"><i class="fas fa-building text-muted"></i> <?php echo e($p['org_name']); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
