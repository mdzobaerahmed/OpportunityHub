<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$org = getOrganizationByUser($_SESSION['user_id']);
if (!$org) { header("Location: organization.php"); exit; }

$pageTitle = 'Applications Received';
$posting_id = (int) ($_GET['posting_id'] ?? 0);

// If a posting_id is supplied, confirm it belongs to this organization before filtering by it.
if ($posting_id && !postingBelongsToOrganization($posting_id, $org['organization_id'])) {
    header("Location: applications.php?err=denied");
    exit;
}

$applications = getOrganizationApplications($org['organization_id'], $posting_id ?: null);
$postings = getOrganizationPostings($org['organization_id']);
$statuses = ['Applied', 'Under Review', 'Shortlisted', 'Interview', 'Accepted', 'Rejected'];

include '../../includes/header.php';
include '../../includes/employer-nav.php';
?>
<div class="container py-4">
  <h1>Applications Received <small class="text-muted fs-6">(<?php echo count($applications); ?>)</small></h1>

  <?php if (isset($_GET['err'])): ?><div class="alert alert-danger">You are not allowed to view those applications.</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Application status updated.</div><?php endif; ?>

  <form method="GET" class="card shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-8">
        <label class="form-label">Filter by posting</label>
        <select class="form-select" name="posting_id">
          <option value="">All postings</option>
          <?php foreach ($postings as $p): ?>
            <option value="<?php echo (int)$p['posting_id']; ?>" <?php echo ($posting_id === (int)$p['posting_id']) ? 'selected' : ''; ?>>
              [<?php echo e($p['posting_type']); ?>] <?php echo e($p['title']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="applications.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <?php if (empty($applications)): ?>
    <div class="alert alert-info text-center py-5">
      <i class="fas fa-inbox fa-3x mb-3"></i>
      <p>No applications received yet.</p>
    </div>
  <?php else: ?>
    <?php foreach ($applications as $a): ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
              <h5 class="mb-1"><?php echo e($a['applicant_name']); ?></h5>
              <p class="text-muted mb-1"><?php echo e($a['applicant_email']); ?><?php echo $a['phone'] ? ' &middot; ' . e($a['phone']) : ''; ?></p>
              <p class="mb-1"><small class="text-muted">Applied for:</small> <strong><?php echo e($a['title']); ?></strong>
                 <span class="badge bg-secondary"><?php echo e($a['posting_type']); ?></span></p>
            </div>
            <div class="text-end">
              <span class="badge bg-<?php echo jobAppStatusBadgeColor($a['application_status']); ?> mb-2"><?php echo e($a['application_status']); ?></span>
              <p class="small text-muted mb-0">Applied <?php echo date('M d, Y', strtotime($a['applied_at'])); ?></p>
            </div>
          </div>

          <hr>
          <div class="row">
            <div class="col-md-8">
              <?php if ($a['university'] || $a['degree']): ?>
                <p class="mb-1"><strong>Education:</strong> <?php echo e(trim($a['degree'] . ' — ' . $a['university'], ' —')); ?></p>
              <?php endif; ?>
              <?php if ($a['applicant_skills']): ?>
                <p class="mb-1"><strong>Skills:</strong> <?php echo e($a['applicant_skills']); ?></p>
              <?php endif; ?>
              <?php if ($a['cover_note']): ?>
                <p class="mb-1"><strong>Cover Note:</strong><br><?php echo nl2br(e($a['cover_note'])); ?></p>
              <?php endif; ?>
              <?php if ($a['resume_path']): ?>
                <a href="<?php echo BASE_URL . '/' . e($a['resume_path']); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2">
                  <i class="fas fa-file-pdf"></i> Download CV
                </a>
              <?php else: ?>
                <p class="small text-muted mt-2 mb-0">No CV uploaded.</p>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <form method="POST" action="update-application-status.php" class="mt-3 mt-md-0">
                <?php echo csrfField(); ?>
                <input type="hidden" name="job_application_id" value="<?php echo (int)$a['job_application_id']; ?>">
                <label class="form-label fw-bold">Update Status</label>
                <select name="status" class="form-select form-select-sm mb-2">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo ($a['application_status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary w-100">Save Status</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
