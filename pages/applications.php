<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isUniversity()) {
    header("Location: university/applications.php");
    exit;
}
if (isAdmin() || isEmployer()) {
    header("Location: dashboard.php");
    exit;
}
$pageTitle = 'My Applications';

$filter_status = $_GET['status'] ?? '';
$applications = getUserApplications($_SESSION['user_id']);

if ($filter_status) {
    $applications = array_filter($applications, function ($app) use ($filter_status) {
        return $app['application_status'] === $filter_status;
    });
}

$statuses = ['Applied', 'Under Review', 'Shortlisted', 'Accepted', 'Rejected'];

include '../includes/header.php';
?>

<div class="bg-white py-4 border-bottom">
    <div class="container">
        <h1 class="mb-0">My Applications</h1>
        <p class="text-muted mb-0">Track the status of every scholarship you've applied to</p>
    </div>
</div>

<div class="container py-5">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="btn-group flex-wrap" role="group">
                <a href="applications.php" class="btn btn-outline-primary <?php echo !$filter_status ? 'active' : ''; ?>">
                    All (<?php echo count(getUserApplications($_SESSION['user_id'])); ?>)
                </a>
                <?php foreach ($statuses as $status):
                    $count = count(array_filter(getUserApplications($_SESSION['user_id']), fn($a) => $a['application_status'] === $status));
                ?>
                    <a href="applications.php?status=<?php echo urlencode($status); ?>"
                       class="btn btn-outline-<?php echo statusBadgeColor($status); ?> <?php echo ($filter_status === $status) ? 'active' : ''; ?>">
                        <?php echo $status; ?> (<?php echo $count; ?>)
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (empty($applications)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>No applications found.</p>
            <a href="scholarships.php" class="btn btn-primary">Browse Scholarships</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover bg-white">
                <thead>
                    <tr>
                        <th>Scholarship</th>
                        <th>University</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Deadline</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): $days = daysLeft($app['application_deadline']); ?>
                        <tr>
                            <td><strong><?php echo e(mb_strimwidth($app['scholarship_name'], 0, 40, '...')); ?></strong></td>
                            <td><?php echo e($app['university_name']); ?></td>
                            <td><?php echo e($app['country']); ?></td>
                            <td><span class="badge bg-<?php echo statusBadgeColor($app['application_status']); ?>"><?php echo e($app['application_status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $days < 0 ? 'danger' : ($days < 7 ? 'warning text-dark' : 'success'); ?>">
                                    <?php echo $days < 0 ? 'Closed' : $days . ' days'; ?>
                                </span>
                            </td>
                            <td><a href="scholarship-details.php?id=<?php echo (int)$app['scholarship_id']; ?>" class="btn btn-sm btn-primary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
