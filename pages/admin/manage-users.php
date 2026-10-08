<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Manage Users';

$search = trim($_GET['search'] ?? '');
$typeFilter = trim($_GET['user_type'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT u.user_id, u.name, u.email, u.user_type, u.status, u.university, u.created_at,
               (SELECT COUNT(*) FROM job_applications ja WHERE ja.user_id = u.user_id) AS job_apps,
               (SELECT COUNT(*) FROM applications a WHERE a.user_id = u.user_id) AS sch_apps
        FROM users u WHERE 1=1";
$params = []; $types = "";

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= "ss";
}
if ($typeFilter !== '') {
    $sql .= " AND u.user_type = ?"; $params[] = $typeFilter; $types .= "s";
}
if ($statusFilter !== '') {
    $sql .= " AND u.status = ?"; $params[] = $statusFilter; $types .= "s";
}
$sql .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Manage Users <small class="text-muted fs-6">(<?php echo count($users); ?>)</small></h1>

  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">User updated.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">User deleted.</div><?php endif; ?>
  <?php if (isset($_GET['err'])): ?><div class="alert alert-danger">That action is not permitted.</div><?php endif; ?>

  <form method="GET" class="card shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Search</label>
        <input type="text" class="form-control" name="search" placeholder="Name or email" value="<?php echo e($search); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Account Type</label>
        <select class="form-select" name="user_type">
          <option value="">All</option>
          <?php foreach (['student','graduate','employer','university','admin'] as $t): ?>
            <option value="<?php echo $t; ?>" <?php echo ($typeFilter === $t) ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">All</option>
          <?php foreach (['active','suspended'] as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo ($statusFilter === $s) ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="manage-users.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Type</th><th>Applications</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): $uid = (int)$u['user_id']; ?>
          <tr>
            <td><strong><?php echo e($u['name']); ?></strong><?php if ($u['university']): ?><br><small class="text-muted"><?php echo e($u['university']); ?></small><?php endif; ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td>
              <?php if ($u['user_type'] !== 'admin'): ?>
                <form method="POST" action="user-action.php" class="d-inline">
                  <?php echo csrfField(); ?>
                  <input type="hidden" name="id" value="<?php echo $uid; ?>">
                  <input type="hidden" name="action" value="role">
                  <select name="user_type" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                    <?php foreach (['student','graduate','employer','university'] as $t): ?>
                      <option value="<?php echo $t; ?>" <?php echo ($u['user_type'] === $t) ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              <?php else: ?>
                <span class="badge bg-danger">Admin</span>
              <?php endif; ?>
            </td>
            <td><small><?php echo (int)$u['job_apps']; ?> job / <?php echo (int)$u['sch_apps']; ?> scholarship</small></td>
            <td><span class="badge bg-<?php echo $u['status'] === 'active' ? 'success' : 'danger'; ?>"><?php echo e(ucfirst($u['status'])); ?></span></td>
            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
            <td>
              <?php if ($u['user_type'] !== 'admin'): ?>
                <a href="toggle-user-status.php?id=<?php echo $uid; ?>"
                   class="btn btn-sm btn-outline-<?php echo $u['status'] === 'active' ? 'danger' : 'success'; ?>"
                   data-confirm="<?php echo $u['status'] === 'active' ? 'Suspend this user?' : 'Reactivate this user?'; ?>">
                  <?php echo $u['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                </a>
                <a href="user-action.php?id=<?php echo $uid; ?>&action=delete" class="btn btn-sm btn-outline-danger"
                   data-confirm="Delete this user and all their applications? This cannot be undone.">Delete</a>
              <?php else: ?>
                <span class="text-muted small">&mdash;</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
