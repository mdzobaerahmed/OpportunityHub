<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Admin Dashboard';
$user = currentUser();

$stats  = getAdminStats();
$jstats = getJobsAdminStats();

$recent_postings = $conn->query("SELECT p.*, o.org_name FROM postings p
    INNER JOIN organizations o ON p.organization_id = o.organization_id
    ORDER BY p.posted_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$pending_orgs = getAllOrganizations(['status' => 'Pending']);
$recent_users = $conn->query("SELECT user_id, name, email, user_type, created_at FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <!-- Top Administrator Header Bar -->
  <div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- Admin Icon & Title -->
        <div class="d-flex align-items-center gap-3">
          <div class="univ-logo-box bg-light border rounded p-1 text-center d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; min-width: 64px;">
            <i class="fas fa-shield-alt fa-2x text-primary"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="mb-0 fw-bold"><?php echo e($user['name'] ?: 'System Administrator'); ?></h3>
              <span class="badge bg-danger-subtle text-danger border border-danger-subtle">System Administrator</span>
              <span class="badge bg-success">Platform Active</span>
            </div>
            <p class="text-muted mb-0 small mt-1">
              <i class="fas fa-server text-muted me-1"></i>Platform Oversight &middot;
              <i class="fas fa-envelope text-muted me-1"></i><?php echo e($user['email']); ?> &middot;
              <i class="fas fa-database text-muted me-1"></i>OpportunityHub Core System
            </p>
          </div>
        </div>

        <!-- Top Controls: Manage Users, Notifications, Logout -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <!-- Quick Manage Users Link -->
          <a href="manage-users.php" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="fas fa-users-cog"></i> <span>Manage Users</span>
          </a>

          <!-- Notifications Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm position-relative dropdown-toggle d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-bell"></i> <span>Notifications</span>
              <?php $notifCount = count($pending_orgs) + (int)$jstats['pending_postings'] + (int)$jstats['expired_postings']; ?>
              <?php if ($notifCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?php echo (int)$notifCount; ?>
                  <span class="visually-hidden">unread alerts</span>
                </span>
              <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 290px;">
              <li class="dropdown-header fw-bold text-uppercase small text-muted">Administrative Alerts</li>
              <li><hr class="dropdown-divider my-1"></li>
              <?php if (!empty($pending_orgs)): ?>
                <li>
                  <a class="dropdown-item py-2" href="manage-organizations.php">
                    <i class="fas fa-building text-warning me-2"></i>
                    <strong><?php echo count($pending_orgs); ?> Organization(s) Awaiting Approval</strong>
                    <div class="small text-muted">Review credentials and verify company</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($jstats['pending_postings'] > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="manage-postings.php">
                    <i class="fas fa-clock text-info me-2"></i>
                    <strong><?php echo (int)$jstats['pending_postings']; ?> Pending Posting(s)</strong>
                    <div class="small text-muted">Review opportunity listings</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($jstats['expired_postings'] > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="expired-postings.php">
                    <i class="fas fa-hourglass-end text-danger me-2"></i>
                    <strong><?php echo (int)$jstats['expired_postings']; ?> Expired Posting(s)</strong>
                    <div class="small text-muted">Postings past their application deadline</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($notifCount === 0): ?>
                <li><span class="dropdown-item text-muted small py-2 text-center">No pending alerts. All platform entities verified!</span></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider my-1"></li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="manage-organizations.php">Review Organizations &rarr;</a></li>
            </ul>
          </div>

          <!-- Logout Button -->
          <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 shadow-sm" data-confirm="Are you sure you want to log out of the admin console?">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($pending_orgs)): ?>
    <!-- Pending Approvals Banner Card -->
    <div class="card shadow-sm mb-4 border-0 border-start border-4 border-warning bg-white">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h4 class="text-warning mb-1 fw-bold"><i class="fas fa-clock me-2"></i> Organizations Awaiting Approval (<?php echo count($pending_orgs); ?>)</h4>
            <p class="text-muted mb-0">
              New employer organizations have registered and require administrator verification before their postings become visible to students and graduates.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="manage-organizations.php" class="btn btn-warning shadow-sm fw-bold">
              <i class="fas fa-tasks me-1"></i> Review Organizations
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Prominent Action Bar -->
  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h4 class="mb-1 fw-bold text-dark"><i class="fas fa-cogs text-primary me-2"></i> System Administration Controls</h4>
          <p class="text-muted mb-0 small">Direct management of registered users, employer organizations, job vacancies, and global scholarships</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="manage-users.php" class="btn btn-primary btn-lg px-4 shadow-sm fw-bold">
            <i class="fas fa-users me-2"></i> Manage Users
          </a>
          <a href="manage-organizations.php" class="btn btn-success btn-lg px-3 shadow-sm">
            <i class="fas fa-building me-1"></i> Organizations (<?php echo (int)$jstats['total_organizations']; ?>)
          </a>
          <a href="manage-postings.php" class="btn btn-outline-info btn-lg px-3 shadow-sm bg-white">
            <i class="fas fa-briefcase me-1"></i> Jobs &amp; Postings
          </a>
          <a href="manage-scholarships.php" class="btn btn-outline-secondary btn-lg px-3 shadow-sm bg-white">
            <i class="fas fa-graduation-cap me-1"></i> Scholarships
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard Summary Cards (Activity & Statistics Overview) -->
  <h5 class="fw-bold mb-3"><i class="fas fa-chart-line text-secondary me-2"></i> Activity &amp; Statistics Overview</h5>
  <div class="row g-3 mb-4">
    <!-- 1. Total Users -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-primary mb-2"><i class="fas fa-users fa-2x"></i></div>
          <h3 class="fw-bold text-primary mb-0"><?php echo (int)$stats['total_users']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Total Users</p>
          <small class="text-muted">Registered accounts</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-users.php" class="small text-primary text-decoration-none">Manage Users &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 2. Total Organizations -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-info mb-2"><i class="fas fa-building fa-2x"></i></div>
          <h3 class="fw-bold text-info mb-0"><?php echo (int)$jstats['total_organizations']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Total Organizations</p>
          <small class="text-muted">Partner employers</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-organizations.php" class="small text-info text-decoration-none">View All &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 3. Pending Organizations -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-warning mb-2"><i class="fas fa-clock fa-2x"></i></div>
          <h3 class="fw-bold text-warning mb-0"><?php echo (int)$jstats['pending_organizations']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Pending Orgs</p>
          <small class="text-muted">Needs verification</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-organizations.php" class="small text-warning text-decoration-none">Evaluate &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 4. Active Postings -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$jstats['active_postings']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Active Postings</p>
          <small class="text-muted">Live opportunities</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-postings.php" class="small text-success text-decoration-none">View Postings &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 5. Job Applications -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-danger mb-2"><i class="fas fa-file-invoice fa-2x"></i></div>
          <h3 class="fw-bold text-danger mb-0"><?php echo (int)$jstats['total_job_applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Job Applications</p>
          <small class="text-muted">Candidate submissions</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-job-applications.php" class="small text-danger text-decoration-none">Review Apps &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 6. Expired Postings -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-secondary mb-2"><i class="fas fa-hourglass-end fa-2x"></i></div>
          <h3 class="fw-bold text-secondary mb-0"><?php echo (int)$jstats['expired_postings']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Expired Postings</p>
          <small class="text-muted">Past deadline</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="expired-postings.php" class="small text-secondary text-decoration-none">View Expired &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 7. Total Scholarships -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-primary mb-2"><i class="fas fa-graduation-cap fa-2x"></i></div>
          <h3 class="fw-bold text-primary mb-0"><?php echo (int)$stats['total_scholarships']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Total Scholarships</p>
          <small class="text-muted">Global grant awards</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-scholarships.php" class="small text-primary text-decoration-none">Manage &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 8. Scholarship Applications -->
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-file-signature fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$stats['total_applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Scholarship Apps</p>
          <small class="text-muted">Student submissions</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="manage-scholarships.php" class="small text-success text-decoration-none">View All &rarr;</a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($pending_orgs)): ?>
    <!-- Pending Organization Approval Quick Table -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-warning"><i class="fas fa-building me-2"></i> Organizations Awaiting Approval</h6>
        <a href="manage-organizations.php" class="btn btn-sm btn-outline-warning text-dark">All Organizations</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="small text-muted">
                <th>Organization Name</th>
                <th>Owner Email</th>
                <th>Industry</th>
                <th>Location</th>
                <th class="text-end">Verification Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_orgs as $o): ?>
                <tr>
                  <td class="fw-semibold text-dark"><?php echo e($o['org_name']); ?></td>
                  <td class="small text-muted"><?php echo e($o['owner_email']); ?></td>
                  <td><span class="badge bg-light text-dark border"><?php echo e($o['industry'] ?: 'General'); ?></span></td>
                  <td class="small text-muted"><?php echo e($o['location'] ?: 'Not Specified'); ?></td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <a href="organization-action.php?id=<?php echo (int)$o['organization_id']; ?>&action=approve" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Approve
                      </a>
                      <a href="organization-action.php?id=<?php echo (int)$o['organization_id']; ?>&action=reject" class="btn btn-outline-danger" data-confirm="Reject this organization?">
                        <i class="fas fa-times me-1"></i> Reject
                      </a>
                      <a href="manage-organizations.php" class="btn btn-outline-secondary">
                        Details
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Detailed Activity Grids: Recent Postings & Recently Registered Users -->
  <div class="row">
    <!-- Left: Recent Postings -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase text-primary me-2"></i> Recent Opportunity Postings</h6>
          <a href="manage-postings.php" class="btn btn-sm btn-outline-primary">Manage Postings</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recent_postings)): ?>
            <div class="text-center py-5">
              <i class="fas fa-briefcase fa-3x text-muted mb-3 opacity-50"></i>
              <p class="text-muted mb-0">No postings recorded yet.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr class="small text-muted">
                    <th>Title &amp; Organization</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_postings as $p): ?>
                    <tr>
                      <td>
                        <span class="fw-semibold text-dark"><?php echo e(mb_strimwidth($p['title'], 0, 30, '...')); ?></span>
                        <br>
                        <small class="text-muted"><?php echo e($p['org_name']); ?></small>
                      </td>
                      <td>
                        <span class="badge bg-secondary-subtle text-dark border">
                          <?php echo e($p['posting_type']); ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-<?php echo postingStatusBadgeColor($p['posting_status']); ?>">
                          <?php echo e($p['posting_status']); ?>
                        </span>
                      </td>
                      <td class="text-end">
                        <a href="edit-posting.php?id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem; padding: 2px 6px;">
                          Edit
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="p-3 text-center border-top bg-light">
              <a href="manage-postings.php" class="btn btn-sm btn-primary w-100 fw-bold">
                <i class="fas fa-briefcase me-1"></i> View All Platform Postings
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: Recently Registered Users -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-users text-info me-2"></i> Recently Registered Users</h6>
          <a href="manage-users.php" class="btn btn-sm btn-outline-info">All Users</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recent_users)): ?>
            <div class="text-center py-5">
              <i class="fas fa-users fa-3x text-muted mb-3 opacity-50"></i>
              <p class="text-muted mb-0">No users registered yet.</p>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recent_users as $u): ?>
                <div class="list-group-item p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <h6 class="mb-1 fw-bold text-dark">
                      <i class="fas fa-user text-primary me-1"></i> <?php echo e($u['name']); ?>
                    </h6>
                    <small class="text-muted">
                      <i class="fas fa-envelope me-1"></i><?php echo e($u['email']); ?> &middot;
                      <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                    </small>
                  </div>
                  <div class="text-end">
                    <span class="badge bg-secondary mb-1 d-block">
                      <?php echo e(ucfirst($u['user_type'])); ?>
                    </span>
                    <a href="manage-users.php?search=<?php echo urlencode($u['email']); ?>" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem; padding: 2px 6px;">
                      Manage
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="p-3 text-center border-top bg-light">
              <a href="manage-users.php" class="btn btn-sm btn-info text-white w-100 fw-bold">
                <i class="fas fa-users-cog me-1"></i> Open User Management Console
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
