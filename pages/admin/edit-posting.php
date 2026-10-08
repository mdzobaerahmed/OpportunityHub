<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id = (int) ($_GET['id'] ?? $_POST['posting_id'] ?? 0);
$posting = $id ? getPostingById($id) : null;
if (!$posting) { header("Location: manage-postings.php"); exit; }

$categories = getAllCategories();
$pageTitle = 'Edit Posting (Admin)';
$error = '';
$f = $posting;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $data = [
            'title'                => trim($_POST['title'] ?? ''),
            'category_id'          => $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null,
            'description'          => trim($_POST['description'] ?? ''),
            'responsibilities'     => trim($_POST['responsibilities'] ?? ''),
            'requirements'         => trim($_POST['requirements'] ?? ''),
            'qualifications'       => trim($_POST['qualifications'] ?? ''),
            'skills'               => trim($_POST['skills'] ?? ''),
            'location'             => trim($_POST['location'] ?? ''),
            'work_type'            => in_array($_POST['work_type'] ?? '', ['Remote','On-site','Hybrid'], true) ? $_POST['work_type'] : 'On-site',
            'job_type'             => in_array($_POST['job_type'] ?? '', ['Full-time','Part-time','Contract','Temporary'], true) ? $_POST['job_type'] : 'Full-time',
            'paid_status'          => in_array($_POST['paid_status'] ?? '', ['Paid','Unpaid'], true) ? $_POST['paid_status'] : 'Paid',
            'salary_min'           => $_POST['salary_min'] !== '' ? (int)$_POST['salary_min'] : null,
            'salary_max'           => $_POST['salary_max'] !== '' ? (int)$_POST['salary_max'] : null,
            'salary_currency'      => trim($_POST['salary_currency'] ?? 'BDT'),
            'duration'             => trim($_POST['duration'] ?? ''),
            'vacancies'            => max(1, (int)($_POST['vacancies'] ?? 1)),
            'application_deadline' => $_POST['application_deadline'] ?? '',
            'application_method'   => trim($_POST['application_method'] ?? ''),
            'application_link'     => trim($_POST['application_link'] ?? ''),
        ];
        $f = array_merge($posting, $data);
        $newStatus = $_POST['posting_status'] ?? $posting['posting_status'];

        if ($data['title'] === '' || $data['application_deadline'] === '') {
            $error = 'Title and application deadline are required.';
        } elseif (updatePosting($id, $data)) {
            setPostingStatus($id, $newStatus);
            header("Location: manage-postings.php?updated=1");
            exit;
        } else {
            $error = 'Could not update the posting.';
        }
    }
}

include '../../includes/header.php';
include '../../includes/admin-nav.php';
?>
<div class="container py-4">
  <h1>Edit Posting <small class="text-muted fs-6">(<?php echo e($posting['posting_type']); ?> &mdash; <?php echo e($posting['org_name']); ?>)</small></h1>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

  <form method="POST" class="card shadow-sm p-4">
    <?php echo csrfField(); ?>
    <input type="hidden" name="posting_id" value="<?php echo $id; ?>">
    <div class="mb-3">
      <label class="form-label fw-bold">Posting Status</label>
      <select class="form-select" name="posting_status">
        <?php foreach (['Pending','Active','Rejected','Closed','Expired'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo ($posting['posting_status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
        <?php endforeach; ?>
      </select>
      <small class="text-muted">Note: a posting whose deadline has passed is automatically set back to Expired.</small>
    </div>
    <?php include '../employer/posting-form-fields.php'; ?>
    <div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      <a href="manage-postings.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include '../../includes/footer.php'; ?>
