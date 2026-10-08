<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Browse Scholarships';

$filters = [
    'search'           => trim($_GET['search'] ?? ''),
    'country'          => trim($_GET['country'] ?? ''),
    'degree_level'     => trim($_GET['degree_level'] ?? ''),
    'field_of_study'   => trim($_GET['field_of_study'] ?? ''),
    'funding_coverage' => trim($_GET['funding_coverage'] ?? ''),
];

$scholarships = getScholarships($filters, true);
$countries = getDistinctCountries();
$fields = getDistinctFields();

include '../includes/header.php';
?>

<div class="bg-white py-4 border-bottom">
    <div class="container">
        <h1 class="mb-0">Browse Scholarships</h1>
        <p class="text-muted mb-0">Found <?php echo count($scholarships); ?> opportunities</p>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Name, university, field..." value="<?php echo e($filters['search']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Country</label>
                            <select class="form-select" name="country">
                                <option value="">All Countries</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?php echo e($c['country']); ?>"
                                        <?php echo ($filters['country'] === $c['country']) ? 'selected' : ''; ?>>
                                        <?php echo e($c['country']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Degree Level</label>
                            <select class="form-select" name="degree_level">
                                <option value="">All Levels</option>
                                <?php foreach (['Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate'] as $lvl): ?>
                                    <option value="<?php echo $lvl; ?>" <?php echo ($filters['degree_level'] === $lvl) ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Field of Study</label>
                            <select class="form-select" name="field_of_study">
                                <option value="">All Fields</option>
                                <?php foreach ($fields as $f): ?>
                                    <option value="<?php echo e($f['field_of_study']); ?>"
                                        <?php echo ($filters['field_of_study'] === $f['field_of_study']) ? 'selected' : ''; ?>>
                                        <?php echo e($f['field_of_study']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Funding Type</label>
                            <select class="form-select" name="funding_coverage">
                                <option value="">All Types</option>
                                <?php foreach (['Fully Funded', 'Partially Funded', 'Tuition Only', 'Accommodation Only'] as $fc): ?>
                                    <option value="<?php echo $fc; ?>" <?php echo ($filters['funding_coverage'] === $fc) ? 'selected' : ''; ?>><?php echo $fc; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">Apply Filters</button>
                        <a href="scholarships.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9">
            <?php if (empty($scholarships)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <p>No scholarships found matching your filters.</p>
                    <a href="scholarships.php" class="btn btn-primary">Reset Filters</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($scholarships as $s): $days = daysLeft($s['application_deadline']); ?>
                        <div class="col-12">
                            <div class="card scholarship-card shadow-sm">
                                <div class="row g-0">
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary"><?php echo e($s['scholarship_name']); ?></h5>
                                            <p class="card-text text-muted mb-2"><strong><?php echo e($s['university_name']); ?></strong></p>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary"><?php echo e($s['country']); ?></span>
                                                <span class="badge bg-info text-dark"><?php echo e($s['degree_level']); ?></span>
                                                <span class="badge bg-success"><?php echo e($s['funding_coverage']); ?></span>
                                                <?php if ($s['status'] === 'Upcoming'): ?><span class="badge bg-warning text-dark">Upcoming</span><?php endif; ?>
                                            </div>
                                            <p class="card-text"><?php echo e(mb_strimwidth($s['description'] ?? '', 0, 150, '...')); ?></p>
                                            <small class="<?php echo $days < 14 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                                <i class="fas fa-calendar"></i> Deadline: <?php echo date('M d, Y', strtotime($s['application_deadline'])); ?>
                                                (<?php echo $days >= 0 ? $days . ' days left' : 'closed'; ?>)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 bg-light d-flex flex-column justify-content-center align-items-center p-3">
                                        <h6 class="text-muted mb-2">Funding</h6>
                                        <p class="h6 text-primary mb-3 text-center"><?php echo e($s['scholarship_amount'] ?: $s['funding_coverage']); ?></p>
                                        <a href="scholarship-details.php?id=<?php echo (int)$s['scholarship_id']; ?>" class="btn btn-primary btn-sm mb-2 w-100">View Details</a>
                                        <?php if (isLoggedIn() && !isAdmin()): ?>
                                            <?php $is_saved = isScholarshipSaved($_SESSION['user_id'], $s['scholarship_id']); ?>
                                            <a href="save-scholarship.php?id=<?php echo (int)$s['scholarship_id']; ?>&redirect=scholarships"
                                               class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="fas fa-heart" style="color: <?php echo $is_saved ? '#dc3545' : '#adb5bd'; ?>"></i>
                                                <?php echo $is_saved ? 'Saved' : 'Save'; ?>
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

<?php include '../includes/footer.php'; ?>
