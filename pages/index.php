<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Home';
sweepExpiredPostings();

$featuredJobs        = array_slice(getPostings('Job', [], true), 0, 3);
$featuredInternships = array_slice(getPostings('Internship', [], true), 0, 3);
$featuredScholarships = array_slice(getScholarships([], true), 0, 3);

$total_jobs = $conn->query("SELECT COUNT(*) c FROM postings p INNER JOIN organizations o ON p.organization_id=o.organization_id
                            WHERE p.posting_type='Job' AND p.posting_status='Active' AND o.status='Approved'")->fetch_assoc()['c'];
$total_internships = $conn->query("SELECT COUNT(*) c FROM postings p INNER JOIN organizations o ON p.organization_id=o.organization_id
                            WHERE p.posting_type='Internship' AND p.posting_status='Active' AND o.status='Approved'")->fetch_assoc()['c'];
$total_scholarships = $conn->query("SELECT COUNT(*) c FROM scholarships WHERE status IN ('Active','Upcoming')")->fetch_assoc()['c'];
$total_orgs = $conn->query("SELECT COUNT(*) c FROM organizations WHERE status='Approved'")->fetch_assoc()['c'];

include '../includes/header.php';
?>

<section class="hero py-5">
  <div class="container text-center text-white">
    <h1 class="display-5 fw-bold mb-3">Find Jobs, Internships &amp; Scholarships</h1>
    <p class="lead mb-4">Bangladesh jobs and internships plus global scholarships &mdash; all in one place.</p>
    <form method="GET" action="jobs.php" class="row g-2 justify-content-center bg-white bg-opacity-10 p-3 rounded-4">
      <div class="col-md-5">
        <input type="text" class="form-control" name="search" placeholder="Job title, company or skill...">
      </div>
      <div class="col-md-3">
        <select class="form-select" name="work_type">
          <option value="">All Work Types</option>
          <option value="On-site">On-site</option>
          <option value="Remote">Remote</option>
          <option value="Hybrid">Hybrid</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-warning w-100 fw-bold"><i class="fas fa-search"></i> Search Jobs</button>
      </div>
    </form>
    <div class="mt-3">
      <a href="internships.php" class="btn btn-outline-light btn-sm">Looking for internships?</a>
      <a href="scholarships.php" class="btn btn-outline-light btn-sm">Looking for scholarships?</a>
    </div>
  </div>
</section>

<section class="stats py-5 bg-white border-bottom">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-3"><h2 class="display-6 text-primary fw-bold"><?php echo $total_jobs; ?></h2><p class="lead text-muted">Open Jobs</p></div>
      <div class="col-md-3 mb-3"><h2 class="display-6 text-success fw-bold"><?php echo $total_internships; ?></h2><p class="lead text-muted">Internships</p></div>
      <div class="col-md-3 mb-3"><h2 class="display-6 text-info fw-bold"><?php echo $total_scholarships; ?></h2><p class="lead text-muted">Scholarships</p></div>
      <div class="col-md-3 mb-3"><h2 class="display-6 text-warning fw-bold"><?php echo $total_orgs; ?></h2><p class="lead text-muted">Organizations</p></div>
    </div>
  </div>
</section>

<?php
function renderPostingCards($items, $emptyMsg) {
    if (empty($items)) { echo '<p class="text-muted">' . e($emptyMsg) . '</p>'; return; }
    foreach ($items as $p) { ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm hover-card">
          <div class="card-body">
            <h5 class="card-title text-primary"><?php echo e($p['title']); ?></h5>
            <p class="card-text text-muted mb-1"><?php echo e($p['org_name']); ?></p>
            <p class="card-text mb-2">
              <span class="badge bg-secondary"><?php echo e($p['location']); ?></span>
              <span class="badge bg-info text-dark"><?php echo e($p['work_type']); ?></span>
            </p>
            <p class="card-text"><span class="badge bg-success"><?php echo e(formatSalary($p)); ?></span></p>
            <p class="small text-danger mb-0"><i class="fas fa-calendar"></i> Deadline:
              <strong><?php echo date('M d, Y', strtotime($p['application_deadline'])); ?></strong></p>
          </div>
          <div class="card-footer bg-white border-0">
            <a href="posting-details.php?id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-primary btn-sm w-100">View Details</a>
          </div>
        </div>
      </div>
    <?php }
}
?>

<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Latest Jobs</h2>
      <a href="jobs.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="row g-4"><?php renderPostingCards($featuredJobs, 'No jobs posted yet. Check back soon.'); ?></div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Latest Internships</h2>
      <a href="internships.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="row g-4"><?php renderPostingCards($featuredInternships, 'No internships posted yet. Check back soon.'); ?></div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Featured Scholarships</h2>
      <a href="scholarships.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($featuredScholarships as $s): ?>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm hover-card scholarship-card">
            <div class="card-body">
              <h5 class="card-title text-primary"><?php echo e($s['scholarship_name']); ?></h5>
              <p class="card-text text-muted mb-1"><?php echo e($s['university_name']); ?></p>
              <p class="card-text mb-2">
                <span class="badge bg-secondary"><?php echo e($s['country']); ?></span>
                <span class="badge bg-info text-dark"><?php echo e($s['degree_level']); ?></span>
              </p>
              <p class="card-text"><span class="badge bg-success"><?php echo e($s['funding_coverage']); ?></span></p>
              <p class="small text-danger mb-0"><i class="fas fa-calendar"></i> Deadline:
                <strong><?php echo date('M d, Y', strtotime($s['application_deadline'])); ?></strong></p>
            </div>
            <div class="card-footer bg-white border-0">
              <a href="scholarship-details.php?id=<?php echo (int)$s['scholarship_id']; ?>" class="btn btn-primary btn-sm w-100">View Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top">
  <div class="container text-center">
    <h3>Are you an employer?</h3>
    <p class="text-muted">Register an organization account to post jobs and internships and manage applications.</p>
    <a href="register.php" class="btn btn-primary">Create Employer Account</a>
  </div>
</section>

<?php include '../includes/footer.php'; ?>
