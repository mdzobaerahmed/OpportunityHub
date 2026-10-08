<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin() || isEmployer() || isUniversity()) {
    header("Location: scholarships.php");
    exit;
}
$pageTitle = 'Saved Scholarships';

$saved = getSavedScholarships($_SESSION['user_id']);

include '../includes/header.php';
?>

<div class="bg-white py-4 border-bottom">
    <div class="container">
        <h1 class="mb-0">Saved Scholarships</h1>
        <p class="text-muted mb-0">Opportunities you've bookmarked to review later</p>
    </div>
</div>

<div class="container py-5">
    <?php if (empty($saved)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-heart fa-3x mb-3"></i>
            <p>You haven't saved any scholarships yet.</p>
            <a href="scholarships.php" class="btn btn-primary">Browse Scholarships</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($saved as $s): $days = daysLeft($s['application_deadline']); ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?php echo e($s['scholarship_name']); ?></h5>
                            <p class="text-muted mb-2"><?php echo e($s['university_name']); ?></p>
                            <span class="badge bg-secondary"><?php echo e($s['country']); ?></span>
                            <span class="badge bg-info text-dark"><?php echo e($s['degree_level']); ?></span>
                            <p class="small mt-2 <?php echo $days < 14 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                Deadline: <?php echo date('M d, Y', strtotime($s['application_deadline'])); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 d-flex gap-2">
                            <a href="scholarship-details.php?id=<?php echo (int)$s['scholarship_id']; ?>" class="btn btn-primary btn-sm flex-fill">View</a>
                            <a href="save-scholarship.php?id=<?php echo (int)$s['scholarship_id']; ?>&redirect=saved" class="btn btn-outline-danger btn-sm flex-fill" data-confirm="Remove this scholarship from saved?">Remove</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
