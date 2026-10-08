<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header("Location: admin/dashboard.php");
    exit;
}
if (isEmployer()) {
    header("Location: employer/dashboard.php");
    exit;
}
if (isUniversity()) {
    header("Location: university/dashboard.php");
    exit;
}

$user = currentUser();
$isGraduate = (isset($user['user_type']) && $user['user_type'] === 'graduate');
$pageTitle = $isGraduate ? 'Fresh Graduate Dashboard' : 'Student Dashboard';

$stats = getDashboardStats($_SESSION['user_id']);
$upcoming = getUpcomingDeadlines($_SESSION['user_id'], 30);

$recent_apps_stmt = $conn->prepare(
    "SELECT a.*, s.scholarship_name, s.university_name
     FROM applications a
     INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
     WHERE a.user_id = ? ORDER BY a.applied_at DESC LIMIT 5"
);
$recent_apps_stmt->bind_param("i", $_SESSION['user_id']);
$recent_apps_stmt->execute();
$recent_apps = $recent_apps_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Jobs & internships activity
$jobApps = getUserJobApplications($_SESSION['user_id']);
$savedPostings = getSavedPostings($_SESSION['user_id']);
$recentJobApps = array_slice($jobApps, 0, 5);

include '../includes/header.php';
?>
<div class="container py-4">
  <!-- Top Administrator Header Bar -->
  <div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- User Avatar & Title -->
        <div class="d-flex align-items-center gap-3">
          <div class="univ-logo-box bg-light border rounded p-1 text-center d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; min-width: 64px;">
            <?php if ($isGraduate): ?>
              <i class="fas fa-graduation-cap fa-2x text-success"></i>
            <?php else: ?>
              <i class="fas fa-user-graduate fa-2x text-primary"></i>
            <?php endif; ?>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="mb-0 fw-bold"><?php echo e($user['name']); ?></h3>
              <?php if ($isGraduate): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Fresh Graduate</span>
                <span class="badge bg-success">Active Member</span>
              <?php else: ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Student</span>
                <span class="badge bg-primary">Active Student</span>
              <?php endif; ?>
            </div>
            <p class="text-muted mb-0 small mt-1">
              <?php if (!empty($user['university'])): ?>
                <i class="fas fa-university text-primary me-1"></i><?php echo e($user['university']); ?> &middot;
              <?php endif; ?>
              <?php if (!empty($user['degree']) || !empty($user['field_of_study'])): ?>
                <i class="fas fa-book-reader text-secondary me-1"></i><?php echo e(trim(($user['degree'] ? $user['degree'] . ', ' : '') . ($user['field_of_study'] ?? ''))); ?> &middot;
              <?php endif; ?>
              <i class="fas fa-envelope text-muted me-1"></i><?php echo e($user['email']); ?>
            </p>
          </div>
        </div>

        <!-- Top Controls: Profile, Notifications, Logout -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <!-- Profile Link -->
          <a href="profile.php" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="fas fa-user-circle"></i> <span>Edit Profile</span>
          </a>

          <!-- Notifications Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm position-relative dropdown-toggle d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-bell"></i> <span>Notifications</span>
              <?php $notifCount = count($upcoming) + (!$user['profile_complete'] ? 1 : 0); ?>
              <?php if ($notifCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?php echo (int)$notifCount; ?>
                  <span class="visually-hidden">unread alerts</span>
                </span>
              <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 290px;">
              <li class="dropdown-header fw-bold text-uppercase small text-muted">Opportunity Alerts</li>
              <li><hr class="dropdown-divider my-1"></li>
              <?php if (count($upcoming) > 0): ?>
                <li>
                  <a class="dropdown-item py-2" href="saved-scholarships.php">
                    <i class="fas fa-clock text-warning me-2"></i>
                    <strong><?php echo count($upcoming); ?> Upcoming Scholarship Deadline(s)</strong>
                    <div class="small text-muted">Expiring in the next 30 days</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if (!$user['profile_complete']): ?>
                <li>
                  <a class="dropdown-item py-2" href="profile.php">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <strong>Incomplete Profile</strong>
                    <div class="small text-muted">Complete your details for better recommendations</div>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($notifCount === 0): ?>
                <li><span class="dropdown-item text-muted small py-2 text-center">No urgent alerts. Your applications are up to date!</span></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider my-1"></li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="saved-opportunities.php">View Saved Opportunities &rarr;</a></li>
            </ul>
          </div>

          <!-- Logout Button -->
          <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 shadow-sm" data-confirm="Are you sure you want to log out?">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!$user['profile_complete']): ?>
    <!-- Incomplete Profile Banner Card -->
    <div class="card shadow-sm mb-4 border-0 border-start border-4 border-warning bg-white">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h4 class="text-warning mb-1 fw-bold"><i class="fas fa-user-edit me-2"></i> Complete Your <?php echo $isGraduate ? 'Graduate' : 'Student'; ?> Profile</h4>
            <p class="text-muted mb-0">
              Your profile is incomplete. Add your university, field of study, skills, and target countries to unlock higher-accuracy scholarship and job recommendations.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="profile.php" class="btn btn-warning shadow-sm fw-bold">
              <i class="fas fa-edit me-1"></i> Complete Profile Now
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
          <h4 class="mb-1 fw-bold text-dark">
            <?php if ($isGraduate): ?>
              <i class="fas fa-briefcase text-primary me-2"></i> Career &amp; Opportunity Portal
            <?php else: ?>
              <i class="fas fa-graduation-cap text-primary me-2"></i> Scholarship &amp; Career Portal
            <?php endif; ?>
          </h4>
          <p class="text-muted mb-0 small">
            <?php if ($isGraduate): ?>
              Explore entry-level jobs, internships, graduate programs, and postgraduate scholarships
            <?php else: ?>
              Discover global scholarships, student internships, and track your application milestones
            <?php endif; ?>
          </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <?php if ($isGraduate): ?>
            <a href="jobs.php" class="btn btn-primary btn-lg px-4 shadow-sm fw-bold">
              <i class="fas fa-briefcase me-2"></i> Browse Jobs
            </a>
            <a href="internships.php" class="btn btn-success btn-lg px-3 shadow-sm">
              <i class="fas fa-laptop-code me-1"></i> Graduate Internships
            </a>
            <a href="scholarships.php" class="btn btn-outline-info btn-lg px-3 shadow-sm bg-white">
              <i class="fas fa-graduation-cap me-1"></i> Scholarships
            </a>
          <?php else: ?>
            <a href="scholarships.php" class="btn btn-primary btn-lg px-4 shadow-sm fw-bold">
              <i class="fas fa-graduation-cap me-2"></i> Browse Scholarships
            </a>
            <a href="internships.php" class="btn btn-success btn-lg px-3 shadow-sm">
              <i class="fas fa-user-graduate me-1"></i> Internships
            </a>
            <a href="jobs.php" class="btn btn-outline-info btn-lg px-3 shadow-sm bg-white">
              <i class="fas fa-briefcase me-1"></i> Jobs &amp; Careers
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard Summary Cards (Exact 7 Cards matching University style) -->
  <h5 class="fw-bold mb-3"><i class="fas fa-chart-line text-secondary me-2"></i> Activity &amp; Statistics Overview</h5>
  <div class="row g-3 mb-4">
    <!-- 1. Saved Scholarships -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-primary mb-2"><i class="fas fa-bookmark fa-2x"></i></div>
          <h3 class="fw-bold text-primary mb-0"><?php echo (int)$stats['saved']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Saved Scholarships</p>
          <small class="text-muted">Bookmarked grants</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="saved-scholarships.php" class="small text-primary text-decoration-none">View Saved &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 2. Scholarship Applications -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-info mb-2"><i class="fas fa-file-signature fa-2x"></i></div>
          <h3 class="fw-bold text-info mb-0"><?php echo (int)$stats['applications']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Scholarship Apps</p>
          <small class="text-muted">Submitted</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php" class="small text-info text-decoration-none">View Apps &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 3. Accepted Scholarships -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-success mb-2"><i class="fas fa-award fa-2x"></i></div>
          <h3 class="fw-bold text-success mb-0"><?php echo (int)$stats['accepted']; ?></h3>
          <p class="fw-semibold text-dark small mb-0">Accepted Awards</p>
          <small class="text-muted">Grant offers</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="applications.php" class="small text-success text-decoration-none">View Accepted &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 4. Upcoming Deadlines -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-warning mb-2"><i class="fas fa-hourglass-half fa-2x"></i></div>
          <h3 class="fw-bold text-warning mb-0"><?php echo count($upcoming); ?></h3>
          <p class="fw-semibold text-dark small mb-0">Upcoming Deadlines</p>
          <small class="text-muted">Next 30 days</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="saved-scholarships.php" class="small text-warning text-decoration-none">Check Dates &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 5. Job & Internship Applications -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-danger mb-2"><i class="fas fa-paper-plane fa-2x"></i></div>
          <h3 class="fw-bold text-danger mb-0"><?php echo count($jobApps); ?></h3>
          <p class="fw-semibold text-dark small mb-0">Job / Internship Apps</p>
          <small class="text-muted">Active job submissions</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="my-job-applications.php" class="small text-danger text-decoration-none">View All &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 6. Saved Opportunities -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="text-secondary mb-2"><i class="fas fa-heart fa-2x"></i></div>
          <h3 class="fw-bold text-secondary mb-0"><?php echo count($savedPostings); ?></h3>
          <p class="fw-semibold text-dark small mb-0">Saved Postings</p>
          <small class="text-muted">Jobs &amp; internships</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="saved-opportunities.php" class="small text-secondary text-decoration-none">View Saved &rarr;</a>
        </div>
      </div>
    </div>

    <!-- 7. Profile Readiness -->
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat-card shadow-sm border-0 h-100 text-center">
        <div class="card-body p-3">
          <div class="<?php echo $user['profile_complete'] ? 'text-success' : 'text-warning'; ?> mb-2">
            <i class="fas <?php echo $user['profile_complete'] ? 'fa-user-check' : 'fa-user-clock'; ?> fa-2x"></i>
          </div>
          <h3 class="fw-bold <?php echo $user['profile_complete'] ? 'text-success' : 'text-warning'; ?> mb-0">
            <?php echo $user['profile_complete'] ? '100%' : 'Pending'; ?>
          </h3>
          <p class="fw-semibold text-dark small mb-0">Profile Status</p>
          <small class="text-muted"><?php echo $user['profile_complete'] ? 'Ready for matching' : 'Incomplete'; ?></small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2">
          <a href="profile.php" class="small <?php echo $user['profile_complete'] ? 'text-success' : 'text-warning'; ?> text-decoration-none">Profile Settings &rarr;</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Activity Grids: Job Applications & Scholarship Applications -->
  <div class="row">
    <!-- Left: Job & Internship Applications -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase text-primary me-2"></i> Recent Job &amp; Internship Applications</h6>
          <a href="my-job-applications.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recentJobApps)): ?>
            <div class="text-center py-5">
              <i class="fas fa-briefcase fa-3x text-muted mb-3 opacity-50"></i>
              <p class="text-muted mb-2">No job or internship applications submitted yet.</p>
              <div class="d-flex justify-content-center gap-2">
                <a href="jobs.php" class="btn btn-sm btn-primary">Browse Jobs</a>
                <a href="internships.php" class="btn btn-sm btn-outline-primary">Browse Internships</a>
              </div>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recentJobApps as $ja): ?>
                <a href="posting-details.php?id=<?php echo (int)$ja['posting_id']; ?>" class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <h6 class="mb-1 fw-bold text-dark"><?php echo e($ja['title']); ?></h6>
                    <small class="text-muted">
                      <i class="fas fa-building me-1"></i><?php echo e($ja['org_name']); ?> &middot;
                      <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($ja['applied_at'])); ?>
                    </small>
                  </div>
                  <div>
                    <span class="badge bg-<?php echo jobAppStatusBadgeColor($ja['application_status']); ?>">
                      <?php echo e($ja['application_status']); ?>
                    </span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <div class="p-3 text-center border-top bg-light">
              <a href="my-job-applications.php" class="btn btn-sm btn-primary w-100 fw-bold">
                <i class="fas fa-paper-plane me-1"></i> View All Job &amp; Internship Applications
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: Recent Scholarship Applications -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-graduation-cap text-info me-2"></i> Recent Scholarship Applications</h6>
          <a href="applications.php" class="btn btn-sm btn-outline-info">View All</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recent_apps)): ?>
            <div class="text-center py-5">
              <i class="fas fa-award fa-3x text-muted mb-3 opacity-50"></i>
              <p class="text-muted mb-2">No scholarship applications submitted yet.</p>
              <a href="scholarships.php" class="btn btn-sm btn-info text-white">Explore Scholarships</a>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recent_apps as $app): ?>
                <a href="scholarship-details.php?id=<?php echo (int)$app['scholarship_id']; ?>" class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <h6 class="mb-1 fw-bold text-dark"><?php echo e($app['scholarship_name']); ?></h6>
                    <small class="text-muted">
                      <i class="fas fa-university me-1"></i><?php echo e($app['university_name']); ?> &middot;
                      <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                    </small>
                  </div>
                  <div>
                    <span class="badge bg-<?php echo statusBadgeColor($app['application_status']); ?>">
                      <?php echo e($app['application_status']); ?>
                    </span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <div class="p-3 text-center border-top bg-light">
              <a href="applications.php" class="btn btn-sm btn-info text-white w-100 fw-bold">
                <i class="fas fa-list me-1"></i> View All Scholarship Applications
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($upcoming)): ?>
    <!-- Upcoming Deadlines Grid -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clock text-warning me-2"></i> Upcoming Deadlines Among Saved Scholarships (Next 30 Days)</h6>
        <a href="saved-scholarships.php" class="btn btn-sm btn-outline-warning text-dark">All Saved</a>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php foreach ($upcoming as $d): ?>
            <a href="scholarship-details.php?id=<?php echo (int)$d['scholarship_id']; ?>" class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h6 class="mb-1 fw-bold text-dark"><?php echo e($d['scholarship_name']); ?></h6>
                <small class="text-muted"><i class="fas fa-university me-1"></i><?php echo e($d['university_name']); ?></small>
              </div>
              <div>
                <span class="badge bg-danger fs-6 py-2 px-3">
                  <i class="fas fa-hourglass-end me-1"></i> <?php echo (int)$d['days_left']; ?> days left
                </span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Quick Actions Card (Matching University design) -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
      <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-bolt text-success me-2"></i> Quick Actions &amp; Navigation</h6>
    </div>
    <div class="card-body p-4">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <a href="jobs.php" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-briefcase"></i> Browse Jobs
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="internships.php" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-user-graduate"></i> Browse Internships
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="scholarships.php" class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-graduation-cap"></i> Scholarships
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="saved-opportunities.php" class="btn btn-outline-info w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-heart"></i> Saved Jobs (<?php echo count($savedPostings); ?>)
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="my-job-applications.php" class="btn btn-outline-success w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-paper-plane"></i> My Job Apps
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="saved-scholarships.php" class="btn btn-outline-info w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-bookmark"></i> Saved Grants (<?php echo (int)$stats['saved']; ?>)
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="applications.php" class="btn btn-outline-success w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="fas fa-list"></i> Scholarship Apps
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="profile.php" class="btn btn-outline-warning w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm text-dark">
            <i class="fas fa-user-edit"></i> Edit Profile
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
