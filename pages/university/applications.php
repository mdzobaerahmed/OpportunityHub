<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();
$pageTitle = 'Student Applications';

$scholarship_id = (int) ($_GET['scholarship_id'] ?? 0);
$statusFilter = trim($_GET['status'] ?? '');
$departmentFilter = trim($_GET['department'] ?? '');
$dateFilter = trim($_GET['date'] ?? '');

// If scholarship_id is supplied, confirm it belongs to this university
if ($scholarship_id && !scholarshipBelongsToUniversity($scholarship_id, $_SESSION['user_id'])) {
    header("Location: applications.php?err=denied");
    exit;
}

$applications = getUniversityApplications(
    $_SESSION['user_id'],
    $scholarship_id ?: null,
    $statusFilter ?: null,
    $departmentFilter ?: null,
    $dateFilter ?: null
);

$scholarships = getUniversityScholarships($_SESSION['user_id']);

// Application statuses matching prompt: Pending, Under Review, Approved, Rejected
$filterStatuses = ['Pending', 'Under Review', 'Approved', 'Rejected'];

include '../../includes/header.php';
include '../../includes/university-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="mb-0"><i class="fas fa-file-signature text-primary me-2"></i> Student Applications <small class="text-muted fs-6">(<?php echo count($applications); ?>)</small></h1>
      <p class="text-muted mb-0">Collect, review, and evaluate student applications submitted for your scholarships</p>
    </div>
    <div class="d-flex gap-2">
      <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
      </a>
      <a href="my-scholarships.php" class="btn btn-primary">
        <i class="fas fa-graduation-cap me-1"></i> Manage Scholarships
      </a>
    </div>
  </div>

  <?php if (isset($_GET['err'])): ?><div class="alert alert-danger auto-dismiss">You are not authorized to perform that action.</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Application review status updated successfully!</div><?php endif; ?>

  <!-- Filtering Options: Scholarship, Application status, Date, Department -->
  <form method="GET" class="card shadow-sm p-3 mb-4 border-0">
    <div class="row g-2 align-items-end">
      <!-- 1. Filter by Scholarship -->
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">Scholarship</label>
        <select class="form-select" name="scholarship_id">
          <option value="">All Scholarships</option>
          <?php foreach ($scholarships as $s): ?>
            <option value="<?php echo (int)$s['scholarship_id']; ?>" <?php echo ($scholarship_id === (int)$s['scholarship_id']) ? 'selected' : ''; ?>>
              <?php echo e(mb_strimwidth($s['scholarship_name'], 0, 36, '...')); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 2. Filter by Application Status -->
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">Application Status</label>
        <select class="form-select" name="status">
          <option value="">All Statuses</option>
          <?php foreach ($filterStatuses as $st): ?>
            <option value="<?php echo $st; ?>" <?php echo ($statusFilter === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 3. Filter by Date -->
      <div class="col-md-2">
        <label class="form-label fw-bold small text-muted">Application Date</label>
        <input type="date" class="form-control" name="date" value="<?php echo e($dateFilter); ?>">
      </div>

      <!-- 4. Filter by Department -->
      <div class="col-md-2">
        <label class="form-label fw-bold small text-muted">Department / Field</label>
        <input type="text" class="form-control" name="department" placeholder="e.g. Computer Science" value="<?php echo e($departmentFilter); ?>">
      </div>

      <!-- Action Buttons -->
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
        <a href="applications.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <?php if (empty($applications)): ?>
    <div class="alert alert-info text-center py-5 shadow-sm border-0 bg-white">
      <i class="fas fa-inbox fa-3x text-primary mb-3 opacity-50"></i>
      <h5 class="fw-bold">No applications found</h5>
      <p class="text-muted mb-0">No student applications match your filter selection.</p>
      <small class="text-muted">Applications will appear here automatically when students apply to your scholarships.</small>
    </div>
  <?php else: ?>
    <!-- Applications Table/List with all fields requested -->
    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="min-width: 180px;">Applicant Name</th>
              <th style="min-width: 200px;">Scholarship Name</th>
              <th>Application Date</th>
              <th>Application Status</th>
              <th style="max-width: 220px;">Required Documents</th>
              <th>Review Status</th>
              <th class="text-end" style="min-width: 140px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($applications as $a): 
              $appId = (int)$a['application_id'];
              $status = $a['application_status'];
              // Normalize status for badge
              $displayStatus = ($status === 'Applied') ? 'Pending' : (($status === 'Accepted') ? 'Approved' : $status);
            ?>
              <tr>
                <!-- 1. Applicant Name -->
                <td>
                  <strong class="text-dark"><?php echo e($a['applicant_name']); ?></strong>
                  <br>
                  <small class="text-muted">
                    <i class="fas fa-envelope me-1"></i><?php echo e($a['applicant_email']); ?>
                    <?php if (!empty($a['applicant_phone'])): ?>
                      <br><i class="fas fa-phone me-1"></i><?php echo e($a['applicant_phone']); ?>
                    <?php endif; ?>
                    <?php if (!empty($a['applicant_department'])): ?>
                      <br><span class="badge bg-light text-dark border"><i class="fas fa-book me-1"></i><?php echo e($a['applicant_department']); ?></span>
                    <?php endif; ?>
                  </small>
                </td>

                <!-- 2. Scholarship Name -->
                <td>
                  <strong><?php echo e($a['scholarship_name']); ?></strong>
                  <br>
                  <small class="text-muted">
                    <span class="badge bg-secondary-subtle text-secondary border"><?php echo e($a['degree_level']); ?></span>
                    <?php if (!empty($a['scholarship_department'])): ?>
                      &middot; <?php echo e($a['scholarship_department']); ?>
                    <?php endif; ?>
                  </small>
                </td>

                <!-- 3. Application Date -->
                <td>
                  <span class="fw-semibold"><?php echo date('M d, Y', strtotime($a['applied_at'])); ?></span>
                  <br>
                  <small class="text-muted"><?php echo date('h:i A', strtotime($a['applied_at'])); ?></small>
                </td>

                <!-- 4. Application Status -->
                <td>
                  <span class="badge bg-<?php echo statusBadgeColor($status); ?> fs-6">
                    <?php echo e($displayStatus); ?>
                  </span>
                </td>

                <!-- 5. Required Documents -->
                <td>
                  <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="<?php echo e($a['required_documents']); ?>">
                    <i class="fas fa-file-alt text-primary me-1"></i>
                    <?php echo e($a['required_documents'] ?: 'Transcripts, SOP, Recommendation'); ?>
                  </small>
                  <?php if (!empty($a['notes'])): ?>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mt-1">
                      <i class="fas fa-comment-alt me-1"></i> Statement Submitted
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($a['document_path'])): ?>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mt-1">
                      <i class="fas fa-paperclip me-1"></i> Document Attached
                    </span>
                  <?php endif; ?>
                </td>

                <!-- 6. Review Status -->
                <td>
                  <?php if ($status === 'Under Review'): ?>
                    <span class="badge bg-info text-dark"><i class="fas fa-search me-1"></i> Under Review</span>
                  <?php elseif ($status === 'Approved' || $status === 'Accepted'): ?>
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Approved</span>
                  <?php elseif ($status === 'Rejected'): ?>
                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending Review</span>
                  <?php endif; ?>
                </td>

                <!-- 7. Actions: Open application to view submitted information and documents -->
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#appModal_<?php echo $appId; ?>">
                    <i class="fas fa-folder-open me-1"></i> View Application
                  </button>
                </td>
              </tr>

              <!-- Detailed Modal for this application -->
              <div class="modal fade" id="appModal_<?php echo $appId; ?>" tabindex="-1" aria-labelledby="modalLabel_<?php echo $appId; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                  <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                      <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalLabel_<?php echo $appId; ?>">
                          <i class="fas fa-user-graduate text-primary me-2"></i> Application Details &mdash; <?php echo e($a['applicant_name']); ?>
                        </h5>
                        <small class="text-muted">Applied for: <?php echo e($a['scholarship_name']); ?></small>
                      </div>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                      <!-- Status Banner -->
                      <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded bg-light border">
                        <div>
                          <span class="text-muted small">Current Application Status:</span>
                          <span class="badge bg-<?php echo statusBadgeColor($status); ?> fs-6 ms-2"><?php echo e($displayStatus); ?></span>
                        </div>
                        <div class="small text-muted">
                          <i class="fas fa-calendar-alt me-1"></i> Submitted: <?php echo date('F j, Y \a\t g:i A', strtotime($a['applied_at'])); ?>
                        </div>
                      </div>

                      <div class="row g-3">
                        <!-- Applicant Profile Information -->
                        <div class="col-md-6">
                          <div class="card h-100 border bg-light">
                            <div class="card-header bg-white fw-bold">
                              <i class="fas fa-user me-1 text-primary"></i> Applicant Profile
                            </div>
                            <div class="card-body">
                              <p class="mb-1"><strong>Full Name:</strong> <?php echo e($a['applicant_name']); ?></p>
                              <p class="mb-1"><strong>Email:</strong> <a href="mailto:<?php echo e($a['applicant_email']); ?>"><?php echo e($a['applicant_email']); ?></a></p>
                              <p class="mb-1"><strong>Phone:</strong> <?php echo e($a['applicant_phone'] ?: 'Not provided'); ?></p>
                              <p class="mb-1"><strong>Current University:</strong> <?php echo e($a['applicant_university'] ?: 'Not specified'); ?></p>
                              <p class="mb-1"><strong>Degree / Level:</strong> <?php echo e($a['applicant_degree'] ?: 'Not specified'); ?></p>
                              <p class="mb-1"><strong>Department / Major:</strong> <?php echo e($a['applicant_department'] ?: 'Not specified'); ?></p>
                              <?php if (!empty($a['applicant_skills'])): ?>
                                <p class="mb-1"><strong>Skills:</strong> <?php echo e($a['applicant_skills']); ?></p>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>

                        <!-- Scholarship Criteria & Requirements -->
                        <div class="col-md-6">
                          <div class="card h-100 border bg-light">
                            <div class="card-header bg-white fw-bold">
                              <i class="fas fa-award me-1 text-primary"></i> Scholarship Requirements
                            </div>
                            <div class="card-body">
                              <p class="mb-1"><strong>Scholarship:</strong> <?php echo e($a['scholarship_name']); ?></p>
                              <p class="mb-1"><strong>Study Level:</strong> <?php echo e($a['degree_level']); ?></p>
                              <p class="mb-1"><strong>Required Documents:</strong></p>
                              <div class="p-2 bg-white rounded border small text-muted mb-2">
                                <?php echo nl2br(e($a['required_documents'] ?: 'Standard academic documents required.')); ?>
                              </div>
                              <p class="mb-0"><strong>Deadline:</strong> <?php echo date('M d, Y', strtotime($a['application_deadline'])); ?></p>
                            </div>
                          </div>
                        </div>

                        <!-- Applicant Submitted Information & Documents -->
                        <div class="col-12">
                          <div class="card border">
                            <div class="card-header bg-white fw-bold">
                              <i class="fas fa-file-alt me-1 text-primary"></i> Submitted Information &amp; Documents
                            </div>
                            <div class="card-body">
                              <h6 class="fw-bold mb-2">Applicant Statement / Cover Note:</h6>
                              <?php if (!empty($a['notes'])): ?>
                                <div class="p-3 bg-light rounded border mb-3" style="white-space: pre-wrap; font-size: 0.95rem;">
                                  <?php echo e($a['notes']); ?>
                                </div>
                              <?php else: ?>
                                <p class="text-muted font-italic mb-3">No personal statement submitted with this application.</p>
                              <?php endif; ?>

                              <?php if (!empty($a['applicant_bio'])): ?>
                                <h6 class="fw-bold mb-2">Applicant Background / Bio:</h6>
                                <div class="p-3 bg-light rounded border mb-3" style="white-space: pre-wrap; font-size: 0.95rem;">
                                  <?php echo e($a['applicant_bio']); ?>
                                </div>
                              <?php endif; ?>

                              <?php if (!empty($a['document_path'])): ?>
                                <h6 class="fw-bold mb-2">Submitted Supporting Document:</h6>
                                <a href="<?php echo BASE_URL . '/' . e($a['document_path']); ?>" target="_blank" class="btn btn-outline-primary btn-sm mb-3">
                                  <i class="fas fa-file-download me-1"></i> View / Download Document
                                </a>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>

                        <!-- Status Update Section -->
                        <div class="col-12">
                          <div class="card border border-primary bg-light">
                            <div class="card-header bg-primary text-white fw-bold">
                              <i class="fas fa-stamp me-1"></i> Update Review Status &amp; Feedback
                            </div>
                            <div class="card-body p-3">
                              <form method="POST" action="update-application-status.php">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="application_id" value="<?php echo $appId; ?>">
                                <input type="hidden" name="redirect_scholarship_id" value="<?php echo $scholarship_id; ?>">
                                <input type="hidden" name="redirect_status" value="<?php echo e($statusFilter); ?>">
                                <input type="hidden" name="redirect_department" value="<?php echo e($departmentFilter); ?>">
                                <input type="hidden" name="redirect_date" value="<?php echo e($dateFilter); ?>">

                                <div class="row g-2 align-items-end">
                                  <div class="col-md-5">
                                    <label class="form-label fw-bold small">Application Status</label>
                                    <select name="status" class="form-select" required>
                                      <option value="Pending" <?php echo ($status === 'Pending' || $status === 'Applied') ? 'selected' : ''; ?>>Pending</option>
                                      <option value="Under Review" <?php echo ($status === 'Under Review') ? 'selected' : ''; ?>>Under Review</option>
                                      <option value="Approved" <?php echo ($status === 'Approved' || $status === 'Accepted') ? 'selected' : ''; ?>>Approved</option>
                                      <option value="Rejected" <?php echo ($status === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                  </div>
                                  <div class="col-md-5">
                                    <label class="form-label fw-bold small">Reviewer Note / Feedback (Optional)</label>
                                    <input type="text" name="review_notes" class="form-control" placeholder="e.g. Shortlisted for interview; Missing transcript">
                                  </div>
                                  <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                                      Save Status
                                    </button>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="modal-footer bg-light">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
