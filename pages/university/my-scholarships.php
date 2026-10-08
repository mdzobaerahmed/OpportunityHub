<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();
$pageTitle = 'Manage Scholarships';

$statusFilter = trim($_GET['status'] ?? '');
$degreeFilter = trim($_GET['degree_level'] ?? '');
$searchFilter = trim($_GET['search'] ?? '');

$filters = [];
if ($statusFilter !== '') {
    $filters['status'] = $statusFilter;
}
if ($degreeFilter !== '') {
    $filters['degree_level'] = $degreeFilter;
}

$scholarships = getUniversityScholarships($_SESSION['user_id'], $filters);

// Optional text search filter
if ($searchFilter !== '') {
    $scholarships = array_filter($scholarships, function($s) use ($searchFilter) {
        return stripos($s['scholarship_name'], $searchFilter) !== false ||
               stripos($s['field_of_study'] ?? '', $searchFilter) !== false ||
               stripos($s['degree_level'] ?? '', $searchFilter) !== false;
    });
}

include '../../includes/header.php';
include '../../includes/university-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="mb-0"><i class="fas fa-graduation-cap text-primary me-2"></i> Manage Scholarships <small class="text-muted fs-6">(<?php echo count($scholarships); ?>)</small></h1>
      <p class="text-muted mb-0">Review posted scholarships, update criteria or deadlines, and change publication status</p>
    </div>
    <div class="d-flex gap-2">
      <a href="post-scholarship.php" class="btn btn-success shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Add New Scholarship
      </a>
      <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
      </a>
    </div>
  </div>

  <?php if (isset($_GET['created'])): ?><div class="alert alert-success auto-dismiss">Scholarship published successfully!</div><?php endif; ?>
  <?php if (isset($_GET['draft_saved'])): ?><div class="alert alert-info auto-dismiss">Scholarship saved as Draft successfully.</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Scholarship updated successfully!</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">Scholarship deleted.</div><?php endif; ?>
  <?php if (isset($_GET['status_changed'])): ?><div class="alert alert-success auto-dismiss">Scholarship status changed successfully.</div><?php endif; ?>
  <?php if (isset($_GET['err'])): ?><div class="alert alert-danger auto-dismiss">You are not authorized to perform that action.</div><?php endif; ?>

  <!-- Filter & Search Controls -->
  <form method="GET" class="card shadow-sm p-3 mb-4 border-0">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-bold small text-muted">Search Scholarship</label>
        <input type="text" class="form-control" name="search" placeholder="Title, department, or keyword" value="<?php echo e($searchFilter); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">Filter by Status</label>
        <select class="form-select" name="status">
          <option value="">All Statuses</option>
          <option value="Active" <?php echo ($statusFilter === 'Active') ? 'selected' : ''; ?>>Published / Active</option>
          <option value="Draft" <?php echo ($statusFilter === 'Draft') ? 'selected' : ''; ?>>Draft</option>
          <option value="Closed" <?php echo ($statusFilter === 'Closed') ? 'selected' : ''; ?>>Closed</option>
          <option value="Expired" <?php echo ($statusFilter === 'Expired') ? 'selected' : ''; ?>>Expired (Past Deadline)</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">Study Level</label>
        <select class="form-select" name="degree_level">
          <option value="">All Levels</option>
          <?php foreach (['Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate'] as $lvl): ?>
            <option value="<?php echo $lvl; ?>" <?php echo ($degreeFilter === $lvl) ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="my-scholarships.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <?php if (empty($scholarships)): ?>
    <div class="alert alert-info text-center py-5 shadow-sm border-0 bg-white">
      <i class="fas fa-graduation-cap fa-3x text-primary mb-3 opacity-50"></i>
      <h5 class="fw-bold">No scholarships found</h5>
      <p class="text-muted mb-3">No scholarship records match your current filter settings.</p>
      <a href="post-scholarship.php" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Add New Scholarship
      </a>
    </div>
  <?php else: ?>
    <!-- Table listing all posted scholarships according to Requirement 3 -->
    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="min-width: 200px;">Scholarship Name</th>
              <th>Status</th>
              <th>Application Deadline</th>
              <th class="text-center">Number of Applications</th>
              <th>Date Posted</th>
              <th>Last Updated</th>
              <th class="text-end" style="min-width: 170px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($scholarships as $s): 
              $sid = (int)$s['scholarship_id'];
              $days = daysLeft($s['application_deadline']);
              $isExpired = ($days < 0 && $s['status'] !== 'Draft');
            ?>
              <tr>
                <!-- 1. Scholarship Name -->
                <td>
                  <strong>
                    <a href="edit-scholarship.php?id=<?php echo $sid; ?>" class="text-dark text-decoration-none">
                      <?php echo e($s['scholarship_name']); ?>
                    </a>
                  </strong>
                  <br>
                  <small class="text-muted">
                    <span class="badge bg-secondary-subtle text-secondary border"><?php echo e($s['degree_level']); ?></span>
                    <?php if (!empty($s['field_of_study'])): ?>
                      &middot; <?php echo e($s['field_of_study']); ?>
                    <?php endif; ?>
                    <?php if (!empty($s['scholarship_amount'])): ?>
                      &middot; <span class="text-success"><?php echo e($s['scholarship_amount']); ?></span>
                    <?php endif; ?>
                  </small>
                </td>

                <!-- 2. Status -->
                <td>
                  <?php if ($isExpired && $s['status'] === 'Active'): ?>
                    <span class="badge bg-danger">Expired</span>
                  <?php else: ?>
                    <span class="badge bg-<?php echo scholarshipStatusBadgeColor($s['status']); ?>">
                      <?php echo $s['status'] === 'Active' ? 'Published' : e($s['status']); ?>
                    </span>
                  <?php endif; ?>
                </td>

                <!-- 3. Application Deadline -->
                <td>
                  <span class="fw-semibold"><?php echo date('M d, Y', strtotime($s['application_deadline'])); ?></span>
                  <br>
                  <small class="<?php echo $days < 0 ? 'text-danger fw-bold' : ($days < 14 ? 'text-warning fw-bold' : 'text-muted'); ?>">
                    <?php if ($days < 0): ?>
                      <i class="fas fa-times-circle me-1"></i>Expired
                    <?php elseif ($days === 0): ?>
                      <i class="fas fa-exclamation-triangle me-1"></i>Deadline Today
                    <?php else: ?>
                      <i class="fas fa-clock me-1"></i><?php echo $days; ?> days left
                    <?php endif; ?>
                  </small>
                </td>

                <!-- 4. Number of Applications -->
                <td class="text-center">
                  <a href="applications.php?scholarship_id=<?php echo $sid; ?>" class="btn btn-sm btn-outline-info position-relative fw-bold" title="Click to view applicants">
                    <i class="fas fa-users me-1"></i> <?php echo (int)$s['applications_count']; ?>
                  </a>
                </td>

                <!-- 5. Date Posted -->
                <td class="small text-muted">
                  <?php echo date('M d, Y', strtotime($s['created_at'])); ?>
                  <br>
                  <span class="text-secondary"><?php echo date('h:i A', strtotime($s['created_at'])); ?></span>
                </td>

                <!-- 6. Last Updated -->
                <td class="small text-muted">
                  <?php echo date('M d, Y', strtotime($s['updated_at'])); ?>
                  <br>
                  <span class="text-secondary"><?php echo date('h:i A', strtotime($s['updated_at'])); ?></span>
                </td>

                <!-- 7. Action Buttons: View, Edit, Change Status, Delete -->
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <!-- View -->
                    <a href="../scholarship-details.php?id=<?php echo $sid; ?>" target="_blank" class="btn btn-outline-secondary" title="View Public Page">
                      <i class="fas fa-eye"></i> View
                    </a>

                    <!-- Edit -->
                    <a href="edit-scholarship.php?id=<?php echo $sid; ?>" class="btn btn-outline-primary" title="Edit Scholarship Information">
                      <i class="fas fa-pencil-alt"></i> Edit
                    </a>

                    <!-- Change Status Dropdown -->
                    <div class="btn-group btn-group-sm" role="group">
                      <button type="button" class="btn btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Change Status">
                        <i class="fas fa-exchange-alt"></i> Status
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="dropdown-header small text-muted">Change Status</li>
                        <?php if ($s['status'] !== 'Active'): ?>
                          <li>
                            <a class="dropdown-item small" href="scholarship-action.php?id=<?php echo $sid; ?>&action=publish">
                              <i class="fas fa-globe text-success me-2"></i> Publish / Make Active
                            </a>
                          </li>
                        <?php endif; ?>
                        <?php if ($s['status'] !== 'Draft'): ?>
                          <li>
                            <a class="dropdown-item small" href="scholarship-action.php?id=<?php echo $sid; ?>&action=unpublish">
                              <i class="fas fa-eye-slash text-secondary me-2"></i> Save as Draft (Unpublish)
                            </a>
                          </li>
                        <?php endif; ?>
                        <?php if ($s['status'] !== 'Closed'): ?>
                          <li>
                            <a class="dropdown-item small" href="scholarship-action.php?id=<?php echo $sid; ?>&action=close">
                              <i class="fas fa-ban text-danger me-2"></i> Close Scholarship
                            </a>
                          </li>
                        <?php endif; ?>
                        <?php if ($isExpired && $s['status'] !== 'Closed'): ?>
                          <li>
                            <a class="dropdown-item small text-danger fw-bold" href="scholarship-action.php?id=<?php echo $sid; ?>&action=close_expired">
                              <i class="fas fa-hourglass-end me-2"></i> Close Expired Scholarship
                            </a>
                          </li>
                        <?php endif; ?>
                      </ul>
                    </div>

                    <!-- Delete with confirmation dialog -->
                    <a href="scholarship-action.php?id=<?php echo $sid; ?>&action=delete" 
                       class="btn btn-outline-danger" 
                       title="Delete Scholarship" 
                       data-confirm="Are you sure you want to permanently delete '<?php echo e(addslashes($s['scholarship_name'])); ?>'? All student applications submitted for this scholarship will be permanently deleted. This cannot be undone.">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
