<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$pageTitle = 'Employer Dashboard';

$org = getOrganizationByUser($_SESSION['user_id']);
$user = currentUser();

$stats = $org ? getOrganizationStats($org['organization_id']) : [
    'total_postings' => 0,
    'active_postings' => 0,
    'expired_postings' => 0,
    'total_applications' => 0,
];

// Breakdown of application statuses for stats overview
$pending_apps_count = 0;
$accepted_apps_count = 0;
$rejected_apps_count = 0;
if ($org) {
    $stmt = $conn->prepare("SELECT 
        SUM(CASE WHEN ja.application_status IN ('Applied', 'Under Review') THEN 1 ELSE 0 END) AS pending_c,
        SUM(CASE WHEN ja.application_status IN ('Shortlisted', 'Interview', 'Accepted') THEN 1 ELSE 0 END) AS accepted_c,
        SUM(CASE WHEN ja.application_status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_c
        FROM job_applications ja
        INNER JOIN postings p ON ja.posting_id = p.posting_id
        WHERE p.organization_id = ?");
    $stmt->bind_param("i", $org['organization_id']);
    $stmt->execute();
    $appRow = $stmt->get_result()->fetch_assoc();
    if ($appRow) {
        $pending_apps_count = (int)($appRow['pending_c'] ?? 0);
        $accepted_apps_count = (int)($appRow['accepted_c'] ?? 0);
        $rejected_apps_count = (int)($appRow['rejected_c'] ?? 0);
    }
}

$recent = $org ? array_slice(getOrganizationPostings($org['organization_id']), 0, 5) : [];
$recentApps = $org ? array_slice(getOrganizationApplications($org['organization_id']), 0, 6) : [];

include '../../includes/header.php';
include '../../includes/employer-nav.php';
?>
<div class="container py-4">
  <!-- Top Administrator Header Bar -->
  <div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- Organization Logo & Name -->
        <div class="d-flex align-items-center gap-3">
          <div class="univ-logo-box bg-light border rounded p-1 text-center d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; min-width: 64px;">
            <i class="fas fa-building fa-2x text-primary"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="mb-0 fw-bold"><?php echo e($org['org_name'] ?? $user['name']); ?></h3>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Employer / Organization</span>
              <?php if ($org && !empty($org['status'])): ?>
                <span class="badge bg-<?php echo orgStatusBadgeColor($org['status']); ?>"><?php echo e($org['status']); ?></span>
              <?php endif; ?>
            </div>
            <p class="text-muted mb-0 small mt-1">
              <?php if ($org && !empty($org['location'])): ?>
                <i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo e($org['location']); ?> &middot;
              <?php endif; ?>
              <?php if ($org && !empty($org['industry'])): ?>
                <i class="fas fa-briefcase text-secondary me-1"></i><?php echo e($org['industry']); ?> &middot;
              <?php endif; ?>
              <i class="fas fa-envelope text-muted me-1"></i><?php echo e($user['email']); ?>
              <?php if ($org && !empty($org['website'])): ?>
                &middot; <a href="<?php echo e($org['website']); ?>" target="_blank" rel="noopener" class="text-decoration-none"><i class="fas fa-globe me-1"></i>Website</a>
              <?php endif; ?>
            </p>
          </div>
        </div>

        <!-- Top Controls: Profile/Account, Notifications, Logout -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <?php if ($org): ?>
            <!-- Organization Profile (Only shown when organization exists; upper create button removed) -->
            <a href="organization.php" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
              <i class="fas fa-building"></i> <span>Organization Profile</span>
            </a>
          <?php endif; ?>

          <!-- Notifications Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm position-relative dropdown-toggle d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-bell"></i> <span>Notifications</span>
              <?php $notifCount = $pending_apps_count + ($stats['expired_postings'] ?? 0) + (($org && $org['status'] === 'Pending') ? 1 : 0); ?>
              <?php if ($notifCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?php echo (int)$notifCount; ?>
                  <span class="visually-hidden">unread alerts</span>
                </span>
              <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 290px;">
              <li class="dropdown-header fw-bold text-uppercase small text-muted">Recruitment Activity Alerts</li>
              <li><hr class="dropdown-divider my-1"></li>
              <?php if ($pending_apps_count > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="applications.php">
                    <i class="fas fa-clock text-warning me-2"></i>
                    <strong><?php echo (int)$pending_apps_count; ?> Pending Application(s)</strong>
                    <div class="small text-muted">Awaiting candidate evaluation</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if (($stats['expired_postings'] ?? 0) > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="my-postings.php">
                    <i class="fas fa-hourglass-end text-danger me-2"></i>
                    <strong><?php echo (int)$stats['expired_postings']; ?> Expired Posting(s)</strong>
                    <div class="small text-muted">Review deadlines or repost vacancies</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($org && $org['status'] === 'Pending'): ?>
                <li>
                  <a class="dropdown-item py-2" href="organization.php">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    <strong>Organization Approval Pending</strong>
                    <div class="small text-muted">Awaiting administrator verification</div>
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
          <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 shadow-sm" data-confirm="Are you sure you want to log out of your employer account?">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!$org): ?>
    <!-- Set Up Profile Banner Card (Single prominent Create Organization Dashboard button) -->
    <div class="card shadow-sm mb-4 border-0 border-start border-4 border-primary">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h4 class="text-primary mb-1"><i class="fas fa-building me-2"></i> Set Up Your Organization Profile</h4>
            <p class="text-muted mb-0">
              Welcome to OpportunityHub! Set up your organization profile, industry, website, and company description to begin publishing job openings and internships.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="organization.php" class="btn btn-primary btn-lg shadow-sm">
              <i class="fas fa-plus-circle me-1"></i> Create Organization Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php elseif ($org['status'] !== 'Approved'): ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 border-start border-4 border-warning">
      <div>
        <i class="fas fa-clock me-2 fa-lg text-warning"></i>
        Your organization status is <strong><?php echo e($org['status']); ?></strong>. Postings will be visible to candidates once approved by an administrator.
      </div>
      <a href="organization.php" class="btn btn-sm btn-outline-warning text-dark">View Organization Profile</a>
    </div>
  <?php endif; ?>

  <!-- Prominent Action Bar -->
  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h4 class="mb-1 fw-bold text-dark"><i class="fas fa-briefcase text-primary me-2"></i> Recruitment Operations</h4>
          <p class="text-muted mb-0 small">Publish job &amp; internship openings, manage active listings, and review candidate applications</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="my-postings.php" class="btn btn-primary btn-lg px-4 shadow-sm fw-bold">
            <i class="fas fa-briefcase me-2"></i> Manage Postings
          </a>
          <a href="post-opportunity.php" class="btn btn-success btn-lg px-3 shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Post Opportunity
          </a>
          <a href="applications.php" class="btn btn-outline-info btn-lg px-3 shadow-sm bg-white">
            <i class="fas fa-file-signature me-1"></i> Applications (<?php echo (int)$stats['total_applications']; ?>)
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard Summary Cards (All 7 Cards matching University style) -->
  <h5 class="fw-bold mb-3"><i class="fas fa-chart-line text-secondary me-2"></i> Activity &amp; Statistics Overview</h5>
  <div class="row g-3 mb-4">
    <!-- 1. Total Postings -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-primary mb-2"><i class="fas fa-briefcase fa-2x"></i></div>
          <h3 class="fw-bold text-primary mb-0"><?php echo (int)$stats['total_postings']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Total Postings</p>
          <small class="text-muted">Jobs &amp; internships</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-postings.php" class="small text-primary text-decoration-none">View All &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 2. Active Postings -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$stats['active_postings']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Active Postings</p>
          <small class="text-muted">Live &amp; accepting</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-postings.php" class="small text-success text-decoration-none">View Active &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 3. Expired Postings -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-danger mb-2"><i class="fas fa-hourglass-end fa-2x"></i></div>
          <h3 class="fw-bold text-danger mb-0"><?php echo (int)$stats['expired_postings']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Expired Postings</p>
          <small class="text-muted">Past deadline</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-postings.php" class="small text-danger text-decoration-none">Review &rarr;</a>
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
          <small class="text-muted">From candidates</small>
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
          <h3 class="fw-bold text-warning mb-0"><?php echo (int)$pending_apps_count; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Pending Review</p>
          <small class="text-muted">Awaiting evaluation</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php" class="small text-warning text-decoration-none">Evaluate &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 6. Shortlisted / Accepted -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-user-check fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$accepted_apps_count; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Shortlisted / Hired</p>
          <small class="text-muted">Qualified talent</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php" class="small text-success text-decoration-none">View Candidates &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 7. Declined Submissions -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-secondary mb-2"><i class="fas fa-user-times fa-2x"></i></div>
          <h3 class="fw-bold text-secondary mb-0"><?php echo (int)$rejected_apps_count; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Declined Submissions</p>
          <small class="text-muted">Processed candidates</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php" class="small text-secondary text-decoration-none">View Archive &rarr;</a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!$org): ?>
    <!-- Getting Started Guide for Employers without profile yet -->
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="rounded-circle bg-light d-inline-flex p-3 mb-3 text-primary">
              <i class="fas fa-building fa-2x"></i>
            </div>
            <h5>1. Create Organization</h5>
            <p class="text-muted small">Click the "Create Organization Dashboard" button in the card above to register your company profile.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="rounded-circle bg-light d-inline-flex p-3 mb-3 text-success">
              <i class="fas fa-briefcase fa-2x"></i>
            </div>
            <h5>2. Post Opportunities</h5>
            <p class="text-muted small">Publish Full-time jobs or Internship opportunities targeting students and recent graduates.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center p-4">
            <div class="rounded-circle bg-light d-inline-flex p-3 mb-3 text-warning">
              <i class="fas fa-users fa-2x"></i>
            </div>
            <h5>3. Manage Applicants</h5>
            <p class="text-muted small">Receive candidate CVs, review qualifications, and update recruitment statuses in real time.</p>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Detailed Activity Grids: Recent Postings & Applications -->
    <div class="row">
      <!-- Left: Recent Postings -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase text-primary me-2"></i> Recent Opportunity Postings</h6>
            <a href="my-postings.php" class="btn btn-sm btn-outline-primary">Manage Postings</a>
          </div>
          <div class="card-body p-0">
            <?php if (empty($recent)): ?>
              <div class="text-center py-5">
                <i class="fas fa-briefcase fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-2">No postings yet.</p>
                <a href="post-opportunity.php" class="btn btn-sm btn-primary">Create Your First Posting</a>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr class="small text-muted">
                      <th>Role &amp; Opportunity</th>
                      <th>Type</th>
                      <th>Deadline</th>
                      <th>Status</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recent as $p): 
                      $pid = (int)$p['posting_id'];
                      $days = daysLeft($p['application_deadline']);
                    ?>
                      <tr>
                        <td>
                          <a href="edit-posting.php?id=<?php echo $pid; ?>" class="fw-semibold text-dark text-decoration-none">
                            <?php echo e(mb_strimwidth($p['title'], 0, 30, '...')); ?>
                          </a>
                          <br>
                          <small class="text-muted"><?php echo e($p['location'] ?: 'Remote'); ?></small>
                        </td>
                        <td>
                          <span class="badge bg-secondary-subtle text-dark border">
                            <?php echo e($p['posting_type']); ?>
                          </span>
                        </td>
                        <td class="small">
                          <?php echo date('M d, Y', strtotime($p['application_deadline'])); ?>
                          <br>
                          <span class="<?php echo $days < 0 ? 'text-danger fw-bold' : ($days < 14 ? 'text-warning fw-bold' : 'text-muted'); ?>">
                            <?php echo $days < 0 ? 'Expired' : ($days === 0 ? 'Today' : $days . 'd left'); ?>
                          </span>
                        </td>
                        <td>
                          <span class="badge bg-<?php echo postingStatusBadgeColor($p['posting_status']); ?>">
                            <?php echo e($p['posting_status']); ?>
                          </span>
                        </td>
                        <td class="text-end">
                          <div class="btn-group btn-group-sm">
                            <a href="../posting-details.php?id=<?php echo $pid; ?>" target="_blank" class="btn btn-outline-secondary" title="View Public Page">
                              <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit-posting.php?id=<?php echo $pid; ?>" class="btn btn-outline-primary" title="Edit">
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
                <a href="my-postings.php" class="btn btn-sm btn-primary w-100 fw-bold">
                  <i class="fas fa-briefcase me-1"></i> Open Postings Management Section
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right: Recent Candidate Applications -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-user-graduate text-info me-2"></i> Recent Applications Received</h6>
            <a href="applications.php" class="btn btn-sm btn-outline-info">All Applications</a>
          </div>
          <div class="card-body p-0">
            <?php if (empty($recentApps)): ?>
              <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-0">No candidate applications received yet.</p>
                <small class="text-muted">Applications from job seekers will appear here in real-time.</small>
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
                        Applied for: <strong><?php echo e(mb_strimwidth($a['title'], 0, 32, '...')); ?></strong>
                        <span class="badge bg-secondary-subtle text-dark border ms-1"><?php echo e($a['posting_type']); ?></span>
                      </p>
                      <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($a['applied_at'])); ?>
                        <?php if (!empty($a['university'])): ?>
                          &middot; <i class="fas fa-graduation-cap me-1"></i><?php echo e($a['university']); ?>
                        <?php endif; ?>
                      </small>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-<?php echo jobAppStatusBadgeColor($a['application_status']); ?> d-block mb-1">
                        <?php echo e($a['application_status']); ?>
                      </span>
                      <a href="applications.php?posting_id=<?php echo (int)$a['posting_id']; ?>" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem; padding: 2px 6px;">
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
  <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
