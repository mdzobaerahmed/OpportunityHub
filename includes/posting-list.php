<?php
/**
 * Shared listing + filter UI for Jobs and Internships.
 * Expects: $type ('Job'|'Internship'), $postings, $filters, $categories, $locations
 */
$type = $type ?? 'Job';
$postings = $postings ?? [];
$filters = $filters ?? [
    'search' => '',
    'organization' => '',
    'location' => '',
    'work_type' => '',
    'job_type' => '',
    'paid_status' => '',
    'category_id' => '',
];
$categories = $categories ?? [];
$locations = $locations ?? [];

$isInternship = ($type === 'Internship');
$selfPage = $isInternship ? 'internships.php' : 'jobs.php';
?>
<div class="bg-white py-4 border-bottom">
    <div class="container">
        <h1 class="mb-0"><?php echo $isInternship ? 'Internships in Bangladesh' : 'Jobs in Bangladesh'; ?></h1>
        <p class="text-muted mb-0">Found <?php echo count($postings); ?> open <?php echo $isInternship ? 'internships' : 'jobs'; ?></p>
    </div>
</div>

<div class="container py-5">
  <div class="row">
    <div class="col-lg-3 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5></div>
        <div class="card-body">
          <form method="GET" action="">
            <div class="mb-3">
              <label class="form-label fw-bold">Search</label>
              <input type="text" class="form-control" name="search" placeholder="Title, company, skills..." value="<?php echo e($filters['search']); ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Organization</label>
              <input type="text" class="form-control" name="organization" value="<?php echo e($filters['organization']); ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Location</label>
              <select class="form-select" name="location">
                <option value="">All Locations</option>
                <?php foreach ($locations as $l): ?>
                  <option value="<?php echo e($l['location']); ?>" <?php echo ($filters['location'] === $l['location']) ? 'selected' : ''; ?>><?php echo e($l['location']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Work Type</label>
              <select class="form-select" name="work_type">
                <option value="">All</option>
                <?php foreach (['Remote','On-site','Hybrid'] as $w): ?>
                  <option value="<?php echo $w; ?>" <?php echo ($filters['work_type'] === $w) ? 'selected' : ''; ?>><?php echo $w; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if (!$isInternship): ?>
            <div class="mb-3">
              <label class="form-label fw-bold">Job Type</label>
              <select class="form-select" name="job_type">
                <option value="">All</option>
                <?php foreach (['Full-time','Part-time','Contract','Temporary'] as $j): ?>
                  <option value="<?php echo $j; ?>" <?php echo ($filters['job_type'] === $j) ? 'selected' : ''; ?>><?php echo $j; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php else: ?>
            <div class="mb-3">
              <label class="form-label fw-bold">Stipend</label>
              <select class="form-select" name="paid_status">
                <option value="">All</option>
                <?php foreach (['Paid','Unpaid'] as $p): ?>
                  <option value="<?php echo $p; ?>" <?php echo ($filters['paid_status'] === $p) ? 'selected' : ''; ?>><?php echo $p; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            <div class="mb-3">
              <label class="form-label fw-bold">Category</label>
              <select class="form-select" name="category_id">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                  <option value="<?php echo (int)$c['category_id']; ?>" <?php echo ((int)$filters['category_id'] === (int)$c['category_id']) ? 'selected' : ''; ?>><?php echo e($c['category_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Apply Filters</button>
            <a href="<?php echo $selfPage; ?>" class="btn btn-outline-secondary w-100">Clear Filters</a>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-9">
      <?php if (empty($postings)): ?>
        <div class="alert alert-info text-center py-5">
          <i class="fas fa-search fa-3x mb-3"></i>
          <p>No <?php echo $isInternship ? 'internships' : 'jobs'; ?> found matching your filters.</p>
          <a href="<?php echo $selfPage; ?>" class="btn btn-primary">Reset Filters</a>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($postings as $p): $days = daysLeft($p['application_deadline']); ?>
            <div class="col-12">
              <div class="card scholarship-card shadow-sm">
                <div class="row g-0">
                  <div class="col-md-8">
                    <div class="card-body">
                      <h5 class="card-title text-primary"><?php echo e($p['title']); ?></h5>
                      <p class="card-text text-muted mb-2"><strong><?php echo e($p['org_name']); ?></strong></p>
                      <div class="mb-2">
                        <span class="badge bg-secondary"><?php echo e($p['location']); ?></span>
                        <span class="badge bg-info text-dark"><?php echo e($p['work_type']); ?></span>
                        <?php if ($isInternship): ?>
                          <span class="badge bg-<?php echo $p['paid_status'] === 'Paid' ? 'success' : 'secondary'; ?>"><?php echo e($p['paid_status']); ?></span>
                          <?php if ($p['duration']): ?><span class="badge bg-light text-dark"><?php echo e($p['duration']); ?></span><?php endif; ?>
                        <?php else: ?>
                          <span class="badge bg-success"><?php echo e($p['job_type']); ?></span>
                        <?php endif; ?>
                        <?php if ($p['category_name']): ?><span class="badge bg-dark"><?php echo e($p['category_name']); ?></span><?php endif; ?>
                      </div>
                      <p class="card-text"><?php echo e(mb_strimwidth($p['description'] ?? '', 0, 150, '...')); ?></p>
                      <small class="<?php echo $days < 7 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                        <i class="fas fa-calendar"></i> Deadline: <?php echo date('M d, Y', strtotime($p['application_deadline'])); ?>
                        (<?php echo $days >= 0 ? $days . ' days left' : 'expired'; ?>)
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4 bg-light d-flex flex-column justify-content-center align-items-center p-3">
                    <h6 class="text-muted mb-2"><?php echo $isInternship ? 'Stipend' : 'Salary'; ?></h6>
                    <p class="h6 text-primary mb-3 text-center"><?php echo e(formatSalary($p)); ?></p>
                    <a href="posting-details.php?id=<?php echo (int)$p['posting_id']; ?>" class="btn btn-primary btn-sm mb-2 w-100">View Details</a>
                    <?php if (isLoggedIn() && !isAdmin() && !isEmployer()): ?>
                      <?php $saved = isPostingSaved($_SESSION['user_id'], $p['posting_id']); ?>
                      <a href="save-posting.php?id=<?php echo (int)$p['posting_id']; ?>&redirect=<?php echo $isInternship ? 'internships' : 'jobs'; ?>" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-heart" style="color: <?php echo $saved ? '#dc3545' : '#adb5bd'; ?>"></i> <?php echo $saved ? 'Saved' : 'Save'; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
