<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$pageTitle = $org ? 'Organization Dashboard' : 'Create Organization Dashboard';

$org = getOrganizationByUser($_SESSION['user_id']);
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $data = [
            'org_name'     => trim($_POST['org_name'] ?? ''),
            'industry'     => trim($_POST['industry'] ?? ''),
            'website'      => trim($_POST['website'] ?? ''),
            'location'     => trim($_POST['location'] ?? ''),
            'org_size'     => trim($_POST['org_size'] ?? ''),
            'founded_year' => $_POST['founded_year'] !== '' ? (int)$_POST['founded_year'] : null,
            'description'  => trim($_POST['description'] ?? ''),
        ];
        if ($data['org_name'] === '') {
            $error = 'Organization name is required.';
        } elseif ($org) {
            if (updateOrganization($org['organization_id'], $data)) {
                $success = 'Organization profile updated.';
                $org = getOrganizationByUser($_SESSION['user_id']);
            } else { $error = 'Could not update organization.'; }
        } else {
            if (createOrganization($_SESSION['user_id'], $data)) {
                $success = 'Organization created. It is pending admin approval before you can publish postings.';
                $org = getOrganizationByUser($_SESSION['user_id']);
            } else { $error = 'Could not create organization.'; }
        }
    }
}

$o = $org ?: [];
include '../../includes/header.php';
include '../../includes/employer-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="mb-0"><?php echo $org ? 'Organization Dashboard' : 'Create Organization Dashboard'; ?></h1>
      <p class="text-muted mb-0"><?php echo $org ? 'Manage your organization profile and company details' : 'Set up your organization profile to unlock job and internship postings'; ?></p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Employer Dashboard
    </a>
  </div>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>

  <?php if ($org): ?>
    <div class="alert alert-<?php echo $org['status'] === 'Approved' ? 'success' : ($org['status'] === 'Pending' ? 'warning' : 'danger'); ?>">
      Verification status: <strong><?php echo e($org['status']); ?></strong>
      <?php if ($org['status'] === 'Pending'): ?> &mdash; an administrator must approve your organization before your postings go live publicly.<?php endif; ?>
      <?php if ($org['status'] === 'Rejected'): ?> &mdash; your organization was rejected. Please update your details and contact the administrator.<?php endif; ?>
      <?php if ($org['status'] === 'Suspended'): ?> &mdash; your organization is suspended. Postings are hidden from students.<?php endif; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">Fill in your organization details below to create your organization dashboard and start posting opportunities.</div>
  <?php endif; ?>

  <form method="POST" class="card shadow-sm p-4">
    <?php echo csrfField(); ?>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Organization Name *</label>
        <input type="text" class="form-control" name="org_name" value="<?php echo e($o['org_name'] ?? ''); ?>" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Industry</label>
        <input type="text" class="form-control" name="industry" value="<?php echo e($o['industry'] ?? ''); ?>" placeholder="e.g. Software / IT">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Website</label>
        <input type="url" class="form-control" name="website" value="<?php echo e($o['website'] ?? ''); ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" name="location" value="<?php echo e($o['location'] ?? ''); ?>" placeholder="e.g. Dhaka">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Company Size</label>
        <input type="text" class="form-control" name="org_size" value="<?php echo e($o['org_size'] ?? ''); ?>" placeholder="e.g. 51-200">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Founded Year</label>
        <input type="number" min="1800" max="2100" class="form-control" name="founded_year" value="<?php echo e($o['founded_year'] ?? ''); ?>">
      </div>
      <div class="col-12 mb-3">
        <label class="form-label">About the Organization</label>
        <textarea class="form-control" name="description" rows="4"><?php echo e($o['description'] ?? ''); ?></textarea>
      </div>
    </div>
    <div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $org ? 'Save Changes' : 'Create Organization'; ?></button>
    </div>
  </form>
</div>
<?php include '../../includes/footer.php'; ?>
