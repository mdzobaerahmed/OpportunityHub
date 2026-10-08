<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Manage Scholarships';

$scholarships = $conn->query("SELECT * FROM scholarships ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

include '../../includes/header.php';
?>

<div class="bg-white py-4 border-bottom">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="mb-0">Manage Scholarships</h1>
            <p class="text-muted mb-0"><?php echo count($scholarships); ?> total</p>
        </div>
        <a href="add-scholarship.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Scholarship</a>
    </div>
</div>

<div class="container py-5">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success auto-dismiss">Scholarship deleted successfully.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover bg-white align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>University</th>
                    <th>Country</th>
                    <th>Degree</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scholarships as $s): ?>
                    <tr>
                        <td><?php echo e(mb_strimwidth($s['scholarship_name'], 0, 35, '...')); ?></td>
                        <td><?php echo e($s['university_name']); ?></td>
                        <td><?php echo e($s['country']); ?></td>
                        <td><?php echo e($s['degree_level']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($s['application_deadline'])); ?></td>
                        <td><span class="badge bg-<?php echo $s['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo e($s['status']); ?></span></td>
                        <td><?php echo (int) $s['views']; ?></td>
                        <td>
                            <a href="edit-scholarship.php?id=<?php echo (int)$s['scholarship_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="delete-scholarship.php?id=<?php echo (int)$s['scholarship_id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this scholarship? This cannot be undone.">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($scholarships)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No scholarships found. <a href="add-scholarship.php">Add one now</a>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
