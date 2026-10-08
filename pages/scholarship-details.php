<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$scholarship = $id ? getScholarshipById($id) : null;

if (!$scholarship) {
    header("Location: scholarships.php");
    exit;
}

incrementScholarshipViews($id);
$pageTitle = $scholarship['scholarship_name'];

$is_saved = false;
$application = null;
$viewerIsOwner = isLoggedIn() && isUniversity() && $scholarship && (int)($scholarship['created_by'] ?? 0) === (int)$_SESSION['user_id'];
if (isLoggedIn() && !isAdmin() && !isEmployer() && !isUniversity()) {
    $is_saved = isScholarshipSaved($_SESSION['user_id'], $id);
    $application = getApplicationForScholarship($_SESSION['user_id'], $id);
}

$days = daysLeft($scholarship['application_deadline']);

include '../includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="scholarships.php">Scholarships</a></li>
            <li class="breadcrumb-item active"><?php echo e($scholarship['scholarship_name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="text-primary"><?php echo e($scholarship['scholarship_name']); ?></h2>
                    <h5 class="text-muted mb-3"><?php echo e($scholarship['university_name']); ?><?php echo $scholarship['city'] ? ', ' . e($scholarship['city']) : ''; ?></h5>

                    <div class="mb-3">
                        <span class="badge bg-secondary"><?php echo e($scholarship['country']); ?></span>
                        <span class="badge bg-info text-dark"><?php echo e($scholarship['degree_level']); ?></span>
                        <span class="badge bg-success"><?php echo e($scholarship['funding_coverage']); ?></span>
                        <span class="badge bg-<?php echo $scholarship['status'] === 'Active' ? 'primary' : 'warning text-dark'; ?>"><?php echo e($scholarship['status']); ?></span>
                    </div>

                    <h5>Description</h5>
                    <p><?php echo nl2br(e($scholarship['description'])); ?></p>

                    <?php if ($scholarship['eligibility_requirements']): ?>
                        <h5>Eligibility Requirements</h5>
                        <p><?php echo nl2br(e($scholarship['eligibility_requirements'])); ?></p>
                    <?php endif; ?>

                    <?php if ($scholarship['required_documents']): ?>
                        <h5>Required Documents</h5>
                        <p><?php echo nl2br(e($scholarship['required_documents'])); ?></p>
                    <?php endif; ?>

                    <div class="row mt-4">
                        <?php if ($scholarship['language_requirements']): ?>
                            <div class="col-md-6 mb-2"><strong>Language:</strong> <?php echo e($scholarship['language_requirements']); ?></div>
                        <?php endif; ?>
                        <?php if ($scholarship['gpa_requirement']): ?>
                            <div class="col-md-6 mb-2"><strong>Minimum GPA:</strong> <?php echo e($scholarship['gpa_requirement']); ?></div>
                        <?php endif; ?>
                        <?php if ($scholarship['intake_month']): ?>
                            <div class="col-md-6 mb-2"><strong>Intake:</strong> <?php echo e($scholarship['intake_month']); ?></div>
                        <?php endif; ?>
                        <?php if ($scholarship['total_spots']): ?>
                            <div class="col-md-6 mb-2"><strong>Total Spots:</strong> <?php echo e($scholarship['total_spots']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h6 class="text-muted">Application Deadline</h6>
                    <h4 class="<?php echo $days < 14 ? 'text-danger' : 'text-primary'; ?>">
                        <?php echo date('M d, Y', strtotime($scholarship['application_deadline'])); ?>
                    </h4>
                    <p class="mb-3 <?php echo $days < 14 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                        <?php echo $days >= 0 ? $days . ' days remaining' : 'Application closed'; ?>
                    </p>

                    <?php if ($scholarship['application_link']): ?>
                        <a href="<?php echo e($scholarship['application_link']); ?>" target="_blank" rel="noopener" class="btn btn-warning w-100 mb-2 fw-bold">
                            <i class="fas fa-external-link-alt"></i> Official Application Page
                        </a>
                    <?php endif; ?>

                    <?php if ($viewerIsOwner): ?>
                        <div class="alert alert-info py-2 mb-2 text-start">
                            <i class="fas fa-university me-1"></i> You published this scholarship.
                        </div>
                        <a href="<?php echo BASE_URL; ?>/pages/university/edit-scholarship.php?id=<?php echo $id; ?>"
                           class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-pencil-alt me-1"></i> Edit Scholarship
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/university/applications.php?scholarship_id=<?php echo $id; ?>"
                           class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-users me-1"></i> View Applications
                        </a>
                    <?php elseif (isLoggedIn() && isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/admin/edit-scholarship.php?id=<?php echo $id; ?>"
                           class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-pencil-alt me-1"></i> Edit (Admin)
                        </a>
                    <?php elseif (isLoggedIn() && !isEmployer() && !isUniversity()): ?>
                        <a href="save-scholarship.php?id=<?php echo $id; ?>&redirect=details"
                           class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-heart" style="color: <?php echo $is_saved ? '#dc3545' : '#adb5bd'; ?>"></i>
                            <?php echo $is_saved ? 'Remove from Saved' : 'Save Scholarship'; ?>
                        </a>

                        <?php if ($application): ?>
                            <div class="alert alert-<?php echo statusBadgeColor($application['application_status']); ?> py-2 mb-2">
                                Application status: <strong><?php echo e($application['application_status']); ?></strong>
                            </div>
                            <form method="POST" action="apply.php">
                                <input type="hidden" name="scholarship_id" value="<?php echo $id; ?>">
                                <select name="status" class="form-select form-select-sm mb-2">
                                    <?php foreach (['Applied', 'Under Review', 'Shortlisted', 'Accepted', 'Rejected'] as $st): ?>
                                        <option value="<?php echo $st; ?>" <?php echo $application['application_status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary w-100">Update Status</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="apply.php">
                                <input type="hidden" name="scholarship_id" value="<?php echo $id; ?>">
                                <input type="hidden" name="status" value="Applied">
                                <div class="mb-2 text-start">
                                    <label class="form-label small text-muted mb-1">Application Statement / Notes (Optional):</label>
                                    <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Brief note about your background or qualifications..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane me-1"></i> Submit Application</button>
                            </form>
                        <?php endif; ?>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php" class="btn btn-outline-primary w-100">Login to Apply / Track</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($scholarship['contact_email'] || $scholarship['contact_phone']): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Contact</h6>
                    <?php if ($scholarship['contact_email']): ?><p class="mb-1"><i class="fas fa-envelope"></i> <?php echo e($scholarship['contact_email']); ?></p><?php endif; ?>
                    <?php if ($scholarship['contact_phone']): ?><p class="mb-0"><i class="fas fa-phone"></i> <?php echo e($scholarship['contact_phone']); ?></p><?php endif; ?>
                    <?php if ($scholarship['university_website']): ?>
                        <p class="mt-2 mb-0"><a href="<?php echo e($scholarship['university_website']); ?>" target="_blank" rel="noopener">University Website <i class="fas fa-external-link-alt"></i></a></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
