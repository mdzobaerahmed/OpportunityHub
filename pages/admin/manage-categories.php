<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Manage Categories';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $name = trim($_POST['category_name'] ?? '');
        if ($name === '') {
            $error = 'Category name cannot be empty.';
        } elseif (addCategory($name)) {
            $success = 'Category added.';
        } else {
            $error = 'Could not add category (it may already exist).';
        }
    }
}

if (isset($_GET['delete'])) {
    deleteCategory((int)$_GET['delete']);
    header("Location: manage-categories.php?deleted=1");
    exit;
}

$categories = getAllCategories();

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Manage Categories <small class="text-muted fs-6">(<?php echo count($categories); ?>)</small></h1>

  <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success auto-dismiss"><?php echo e($success); ?></div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">Category deleted.</div><?php endif; ?>

  <div class="row">
    <div class="col-md-5 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-light"><strong>Add New Category</strong></div>
        <div class="card-body">
          <form method="POST">
            <?php echo csrfField(); ?>
            <div class="mb-3">
              <label class="form-label">Category Name</label>
              <input type="text" class="form-control" name="category_name" required placeholder="e.g. Cybersecurity">
            </div>
            <button class="btn btn-success w-100"><i class="fas fa-plus"></i> Add Category</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="table-responsive">
        <table class="table table-hover bg-white align-middle">
          <thead><tr><th>Category</th><th>Postings</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($categories as $c):
                $stmt = $conn->prepare("SELECT COUNT(*) c FROM postings WHERE category_id = ?");
                $stmt->bind_param("i", $c['category_id']); $stmt->execute();
                $count = $stmt->get_result()->fetch_assoc()['c'];
            ?>
              <tr>
                <td><?php echo e($c['category_name']); ?></td>
                <td><span class="badge bg-secondary"><?php echo $count; ?></span></td>
                <td><a href="manage-categories.php?delete=<?php echo (int)$c['category_id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this category? Postings using it will be left uncategorized.">Delete</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
              <tr><td colspan="3" class="text-center text-muted py-4">No categories yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
