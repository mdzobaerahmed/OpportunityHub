<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$org = getOrganizationByUser($_SESSION['user_id']);
if (!$org) { header("Location: organization.php"); exit; }

$id = (int) ($_GET['id'] ?? $_POST['posting_id'] ?? 0);

// Ownership check: an employer may only edit postings belonging to their own organization.
if (!$id || !postingBelongsToOrganization($id, $org['organization_id'])) {
    header("Location: my-postings.php?err=denied");
    exit;
}

$posting = getPostingById($id);
$categories = getAllCategories();
$pageTitle = 'Edit Posting';
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

        if ($data['title'] === '' || $data['application_deadline'] === '') {
            $error = 'Title and application deadline are required.';
        } elseif (updatePosting($id, $data)) {
            // Extending a deadline into the future revives an expired posting.
            if (strtotime($data['application_deadline']) >= strtotime(date('Y-m-d')) && $posting['posting_status'] === 'Expired') {
                setPostingStatus($id, organizationIsApproved($org) ? 'Active' : 'Pending');
            }
            header("Location: my-postings.php?updated=1");
            exit;
        } else {
            $error = 'Could not update the posting.';
        }
    }
}

include '../../includes/header.php';
include '../../includes/employer-nav.php';
?>
<div class="container py-4">
  <h1>Edit Posting <small class="text-muted fs-6">(<?php echo e($posting['posting_type']); ?>)</small></h1>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

  <form method="POST" class="card shadow-sm p-4">
    <?php echo csrfField(); ?>
    <input type="hidden" name="posting_id" value="<?php echo $id; ?>">
    <?php include 'posting-form-fields.php'; ?>
    <div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      <a href="my-postings.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include '../../includes/footer.php'; ?>
