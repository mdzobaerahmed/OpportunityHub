<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();
$pageTitle = 'Manage Organizations';

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'status' => trim($_GET['status'] ?? ''),
];
$orgs = getAllOrganizations($filters);

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Manage Organizations <small class="text-muted fs-6">(<?php echo count($orgs); ?>)</small></h1>

  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success auto-dismiss">Organization updated.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success auto-dismiss">Organization deleted.</div><?php endif; ?>

  <form method="GET" class="card shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label">Search</label>
        <input type="text" class="form-control" name="search" placeholder="Organization name or owner email" value="<?php echo e($filters['search']); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">All</option>
          <?php foreach (['Pending','Approved','Rejected','Suspended'] as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo ($filters['status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary flex-fill">Filter</button>
        <a href="manage-organizations.php" class="btn btn-outline-secondary flex-fill">Clear</a>
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead>
        <tr><th>Organization</th><th>Owner</th><th>Industry</th><th>Location</th><th>Postings</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($orgs as $o): ?>
          <tr>
            <td><strong><?php echo e($o['org_name']); ?></strong>
              <?php if ($o['website']): ?><br><small><a href="<?php echo e($o['website']); ?>" target="_blank" rel="noopener">website</a></small><?php endif; ?>
            </td>
            <td><?php echo e($o['owner_name']); ?><br><small class="text-muted"><?php echo e($o['owner_email']); ?></small></td>
            <td><?php echo e($o['industry'] ?: '—'); ?></td>
            <td><?php echo e($o['location'] ?: '—'); ?></td>
            <td><a href="manage-postings.php?search=<?php echo urlencode($o['org_name']); ?>" class="btn btn-sm btn-outline-info"><?php echo (int)$o['total_postings']; ?></a></td>
            <td><span class="badge bg-<?php echo orgStatusBadgeColor($o['status']); ?>"><?php echo e($o['status']); ?></span></td>
            <td>
              <?php $oid = (int)$o['organization_id']; ?>
              <?php if ($o['status'] !== 'Approved'): ?>
                <a href="organization-action.php?id=<?php echo $oid; ?>&action=approve" class="btn btn-sm btn-success">Approve</a>
              <?php endif; ?>
              <?php if ($o['status'] !== 'Rejected'): ?>
                <a href="organization-action.php?id=<?php echo $oid; ?>&action=reject" class="btn btn-sm btn-outline-danger" data-confirm="Reject this organization?">Reject</a>
              <?php endif; ?>
              <?php if ($o['status'] !== 'Suspended'): ?>
                <a href="organization-action.php?id=<?php echo $oid; ?>&action=suspend" class="btn btn-sm btn-outline-dark" data-confirm="Suspend this organization?">Suspend</a>
              <?php endif; ?>
              <a href="organization-action.php?id=<?php echo $oid; ?>&action=delete" class="btn btn-sm btn-outline-danger" data-confirm="Delete this organization and all its postings? This cannot be undone.">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($orgs)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No organizations found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
