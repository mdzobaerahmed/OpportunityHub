<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();
$pageTitle = 'University Administrator Dashboard';

$user = currentUser();
$univ = getUniversityByUser($_SESSION['user_id']);
$stats = getUniversityStats($_SESSION['user_id']);
$recent = array_slice(getUniversityScholarships($_SESSION['user_id']), 0, 5);
$recentApps = array_slice(getUniversityApplications($_SESSION['user_id']), 0, 6);

include '../../includes/header.php';
include '../../includes/university-nav.php';
?>
<div class="container py-4">
  <!-- Top Administrator Header Bar -->
  <div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- University Logo & Name -->
        <div class="d-flex align-items-center gap-3">
          <div class="univ-logo-box bg-light border rounded p-1 text-center d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; min-width: 64px;">
            <?php if (!empty($univ['logo_url'])): ?>
              <img src="<?php echo e($univ['logo_url']); ?>" alt="<?php echo e($univ['univ_name'] ?? 'University'); ?>" class="img-fluid rounded" style="max-height: 56px; max-width: 56px; object-fit: contain;">
            <?php else: ?>
              <i class="fas fa-university fa-2x text-primary"></i>
            <?php endif; ?>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="mb-0 fw-bold"><?php echo e($univ['univ_name'] ?? $user['name']); ?></h3>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle">University Administrator</span>
              <?php if ($univ && !empty($univ['status'])): ?>
                <span class="badge bg-<?php echo univStatusBadgeColor($univ['status']); ?>"><?php echo e($univ['status']); ?></span>
              <?php endif; ?>
            </div>
            <p class="text-muted mb-0 small mt-1">
              <?php if ($univ && (!empty($univ['city']) || !empty($univ['country']))): ?>
                <i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo e(trim(($univ['city'] ? $univ['city'] . ', ' : '') . ($univ['country'] ?? ''))); ?> &middot;
              <?php endif; ?>
              <i class="fas fa-envelope text-muted me-1"></i><?php echo e($univ['contact_email'] ?? $user['email']); ?>
              <?php if (!empty($univ['website'])): ?>
                &middot; <a href="<?php echo e($univ['website']); ?>" target="_blank" rel="noopener" class="text-decoration-none"><i class="fas fa-globe me-1"></i>Website</a>
              <?php endif; ?>
            </p>
          </div>
        </div>

        <!-- Top Controls: Profile/Account, Notifications, Logout -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <!-- University Profile / Account -->
          <a href="profile.php" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="fas fa-user-circle"></i> <span>University Profile</span>
          </a>

          <!-- Notifications Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm position-relative dropdown-toggle d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-bell"></i> <span>Notifications</span>
              <?php $notifCount = $stats['pending_applications'] + $stats['expired_scholarships']; ?>
              <?php if ($notifCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?php echo (int)$notifCount; ?>
                  <span class="visually-hidden">unread alerts</span>
                </span>
              <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 290px;">
              <li class="dropdown-header fw-bold text-uppercase small text-muted">University Activity Alerts</li>
              <li><hr class="dropdown-divider my-1"></li>
              <?php if ($stats['pending_applications'] > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="applications.php?status=Pending">
                    <i class="fas fa-clock text-warning me-2"></i>
                    <strong><?php echo (int)$stats['pending_applications']; ?> Pending Application(s)</strong>
                    <div class="small text-muted">Awaiting evaluation and review</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($stats['expired_scholarships'] > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="my-scholarships.php?status=Expired">
                    <i class="fas fa-hourglass-end text-danger me-2"></i>
                    <strong><?php echo (int)$stats['expired_scholarships']; ?> Expired Scholarship(s)</strong>
                    <div class="small text-muted">Check deadlines and extend or close</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($notifCount === 0): ?>
                <li><span class="dropdown-item text-muted small py-2 text-center">No new notifications. Everything is up to date!</span></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider my-1"></li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="applications.php">Go to Applications Section &rarr;</a></li>
            </ul>
          </div>

          <!-- Logout Button -->
          <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 shadow-sm" data-confirm="Are you sure you want to log out of your university administrator account?">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!$univ): ?>
    <div class="card shadow-sm mb-4 border-0 border-start border-4 border-primary">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h4 class="text-primary mb-1"><i class="fas fa-university me-2"></i> Set Up Your University Profile</h4>
            <p class="text-muted mb-0">
              Welcome to OpportunityHub! Add your university logo, location, official email, phone number, address, and social links to complete your institutional branding.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="profile.php" class="btn btn-primary shadow-sm">
              <i class="fas fa-edit me-1"></i> Complete Profile Now
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Prominent Action Bar with "Manage Scholarships" -->
  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h4 class="mb-1 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i> Scholarship Operations</h4>
          <p class="text-muted mb-0 small">Create new grants, manage live postings, and review student applications</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <!-- Prominent "Manage Scholarships" Button -->
          <a href="my-scholarships.php" class="btn btn-primary btn-lg px-4 shadow-sm fw-bold">
            <i class="fas fa-graduation-cap me-2"></i> Manage Scholarships
          </a>
          <a href="post-scholarship.php" class="btn btn-success btn-lg px-3 shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Add New Scholarship
          </a>
          <a href="applications.php" class="btn btn-outline-info btn-lg px-3 shadow-sm bg-white">
            <i class="fas fa-file-signature me-1"></i> Applications (<?php echo (int)$stats['total_applications']; ?>)
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard Summary Cards (All 7 Cards) -->
  <h5 class="fw-bold mb-3"><i class="fas fa-chart-line text-secondary me-2"></i> Activity &amp; Statistics Overview</h5>
  <div class="row g-3 mb-4">
    <!-- 1. Total Scholarships Posted -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-primary mb-2"><i class="fas fa-graduation-cap fa-2x"></i></div>
          <h3 class="fw-bold text-primary mb-0"><?php echo (int)$stats['total_scholarships']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Total Scholarships</p>
          <small class="text-muted">Posted to date</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-scholarships.php" class="small text-primary text-decoration-none">View All &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 2. Active Scholarships -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$stats['active_scholarships']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Active Scholarships</p>
          <small class="text-muted">Live &amp; accepting</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-scholarships.php?status=Active" class="small text-success text-decoration-none">View Active &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 3. Expired Scholarships -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-danger mb-2"><i class="fas fa-hourglass-end fa-2x"></i></div>
          <h3 class="fw-bold text-danger mb-0"><?php echo (int)$stats['expired_scholarships']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Expired Scholarships</p>
          <small class="text-muted">Past deadline</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-scholarships.php?status=Expired" class="small text-danger text-decoration-none">Review &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 4. Total Applications Received -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-info mb-2"><i class="fas fa-file-invoice fa-2x"></i></div>
          <h3 class="fw-bold text-info mb-0"><?php echo (int)$stats['total_applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Total Applications</p>
          <small class="text-muted">Received from students</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php" class="small text-info text-decoration-none">View All &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 5. Pending Applications -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-warning mb-2"><i class="fas fa-clock fa-2x"></i></div>
          <h3 class="fw-bold text-warning mb-0"><?php echo (int)$stats['pending_applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Pending Applications</p>
          <small class="text-muted">Awaiting review</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php?status=Pending" class="small text-warning text-decoration-none">Evaluate &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 6. Approved Applications -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-user-check fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$stats['approved_applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Approved Applications</p>
          <small class="text-muted">Accepted candidates</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php?status=Approved" class="small text-success text-decoration-none">View Approved &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 7. Rejected Applications -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-secondary mb-2"><i class="fas fa-user-times fa-2x"></i></div>
          <h3 class="fw-bold text-secondary mb-0"><?php echo (int)$stats['rejected_applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Rejected Applications</p>
          <small class="text-muted">Declined submissions</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php?status=Rejected" class="small text-secondary text-decoration-none">View Rejected &rarr;</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Activity Grids: Recent Postings & Applications -->
  <div class="row">
    <!-- Left: Posted Scholarships -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-award text-primary me-2"></i> Recent Scholarship Postings</h6>
          <a href="my-scholarships.php" class="btn btn-sm btn-outline-primary">Manage Scholarships</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recent)): ?>
            <div class="text-center py-5">
              <i class="fas fa-award fa-3x text-muted mb-3 opacity-50"></i>
              <p class="text-muted mb-2">No scholarships posted yet.</p>
              <a href="post-scholarship.php" class="btn btn-sm btn-primary">Post Your First Scholarship</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr class="small text-muted">
                    <th>Scholarship</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Apps</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent as $s): 
                    $sid = (int)$s['scholarship_id'];
                    $days = daysLeft($s['application_deadline']);
                  ?>
                    <tr>
                      <td>
                        <a href="edit-scholarship.php?id=<?php echo $sid; ?>" class="fw-semibold text-dark text-decoration-none">
                          <?php echo e(mb_strimwidth($s['scholarship_name'], 0, 30, '...')); ?>
                        </a>
                        <br>
                        <small class="text-muted"><?php echo e($s['degree_level']); ?></small>
                      </td>
                      <td class="small">
                        <?php echo date('M d, Y', strtotime($s['application_deadline'])); ?>
                        <br>
                        <span class="<?php echo $days < 0 ? 'text-danger fw-bold' : ($days < 14 ? 'text-warning fw-bold' : 'text-muted'); ?>">
                          <?php echo $days < 0 ? 'Expired' : ($days === 0 ? 'Today' : $days . 'd left'); ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-<?php echo scholarshipStatusBadgeColor($s['status']); ?>">
                          <?php echo e($s['status']); ?>
                        </span>
                      </td>
                      <td>
                        <a href="applications.php?scholarship_id=<?php echo $sid; ?>" class="badge bg-info-subtle text-info-emphasis border border-info-subtle text-decoration-none">
                          <?php echo (int)$s['applications_count']; ?>
                        </a>
                      </td>
                      <td class="text-end">
                        <div class="btn-group btn-group-sm">
                          <a href="../scholarship-details.php?id=<?php echo $sid; ?>" target="_blank" class="btn btn-outline-secondary" title="View Public Page">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="edit-scholarship.php?id=<?php echo $sid; ?>" class="btn btn-outline-primary" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="p-3 text-center border-top bg-light">
              <a href="my-scholarships.php" class="btn btn-sm btn-primary w-100 fw-bold">
                <i class="fas fa-graduation-cap me-1"></i> Open Scholarship Management Section
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: Student Applications -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-user-graduate text-info me-2"></i> Recent Student Applications</h6>
          <a href="applications.php" class="btn btn-sm btn-outline-info">All Applications</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recentApps)): ?>
            <div class="text-center py-5">
              <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
              <p class="text-muted mb-0">No student applications received yet.</p>
              <small class="text-muted">New student submissions will appear here in real-time.</small>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recentApps as $a): ?>
                <div class="list-group-item p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <h6 class="mb-1 fw-bold text-dark">
                      <i class="fas fa-user text-primary me-1"></i> <?php echo e($a['applicant_name']); ?>
                    </h6>
                    <p class="mb-0 small text-muted">
                      Applied for: <strong><?php echo e(mb_strimwidth($a['scholarship_name'], 0, 32, '...')); ?></strong>
                    </p>
                    <small class="text-muted">
                      <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($a['applied_at'])); ?>
                      <?php if (!empty($a['applicant_department'])): ?>
                        &middot; <i class="fas fa-book me-1"></i><?php echo e($a['applicant_department']); ?>
                      <?php endif; ?>
                    </small>
                  </div>
                  <div class="text-end">
                    <span class="badge bg-<?php echo statusBadgeColor($a['application_status']); ?> d-block mb-1">
                      <?php echo e($a['application_status']); ?>
                    </span>
                    <a href="applications.php?scholarship_id=<?php echo (int)$a['scholarship_id']; ?>" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem; padding: 2px 6px;">
                      Review Details
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="p-3 text-center border-top bg-light">
              <a href="applications.php" class="btn btn-sm btn-info text-white w-100 fw-bold">
                <i class="fas fa-file-signature me-1"></i> Review &amp; Evaluate All Applications
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
