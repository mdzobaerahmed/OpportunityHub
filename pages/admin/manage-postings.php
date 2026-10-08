<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Manage Jobs & Internships';

$filters = [
    'search'         => trim($_GET['search'] ?? ''),
    'posting_type'   => trim($_GET['posting_type'] ?? ''),
    'posting_status' => trim($_GET['posting_status'] ?? ''),
];
$postings = getAllPostingsAdmin($filters);

// Simple pagination
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = count($postings);
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$pagePostings = array_slice($postings, ($page - 1) * $perPage, $perPage);

$qs = $_GET; unset($qs['page']);
$baseQs = http_build_query($qs);

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Manage Jobs &amp; Internships <small class="text-muted fs-6">(<?php echo $total; ?>)</small></h1>

  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Posting updated.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">Posting deleted.</div><?php endif; ?>

  <form method="GET" class="card shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Search</label>
        <input type="text" class="form-control" name="search" placeholder="Title or organization" value="<?php echo e($filters['search']); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Type</label>
        <select class="form-select" name="posting_type">
          <option value="">All</option>
          <?php foreach (['Job','Internship'] as $t): ?>
            <option value="<?php echo $t; ?>" <?php echo ($filters['posting_type'] === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="posting_status">
          <option value="">All</option>
          <?php foreach (['Pending','Active','Rejected','Closed','Expired'] as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo ($filters['posting_status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="manage-postings.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead>
        <tr><th>Title</th><th>Organization</th><th>Type</th><th>Deadline</th><th>Status</th><th>Views</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($pagePostings as $p): $days = daysLeft($p['application_deadline']); $pid = (int)$p['posting_id']; ?>
          <tr>
            <td><strong><?php echo e(mb_strimwidth($p['title'], 0, 35, '...')); ?></strong></td>
            <td><?php echo e($p['org_name']); ?><br><small class="text-muted">org: <?php echo e($p['org_status']); ?></small></td>
            <td><span class="badge bg-secondary"><?php echo e($p['posting_type']); ?></span></td>
            <td><?php echo date('M d, Y', strtotime($p['application_deadline'])); ?>
                <br><small class="<?php echo $days < 0 ? 'text-danger' : 'text-muted'; ?>"><?php echo $days < 0 ? 'expired' : $days . ' days'; ?></small></td>
            <td><span class="badge bg-<?php echo postingStatusBadgeColor($p['posting_status']); ?>"><?php echo e($p['posting_status']); ?></span></td>
            <td><?php echo (int)$p['views']; ?></td>
            <td>
              <a href="../posting-details.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-outline-secondary">View</a>
              <a href="edit-posting.php?id=<?php echo $pid; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <?php if ($p['posting_status'] === 'Pending'): ?>
                <a href="posting-action.php?id=<?php echo $pid; ?>&action=approve" class="btn btn-sm btn-success">Approve</a>
                <a href="posting-action.php?id=<?php echo $pid; ?>&action=reject" class="btn btn-sm btn-outline-danger" data-confirm="Reject this posting?">Reject</a>
              <?php elseif ($p['posting_status'] === 'Active'): ?>
                <a href="posting-action.php?id=<?php echo $pid; ?>&action=deactivate" class="btn btn-sm btn-outline-warning" data-confirm="Deactivate (close) this posting?">Deactivate</a>
              <?php elseif (in_array($p['posting_status'], ['Rejected','Closed'], true) && $days >= 0): ?>
                <a href="posting-action.php?id=<?php echo $pid; ?>&action=approve" class="btn btn-sm btn-outline-success">Activate</a>
              <?php endif; ?>
              <a href="posting-action.php?id=<?php echo $pid; ?>&action=delete" class="btn btn-sm btn-outline-danger" data-confirm="Delete this posting permanently?">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($pagePostings)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No postings found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav><ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
          <a class="page-link" href="?<?php echo $baseQs ? $baseQs . '&' : ''; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>
    </ul></nav>
  <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
