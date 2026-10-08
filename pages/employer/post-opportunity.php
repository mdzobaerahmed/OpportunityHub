<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployer();
$pageTitle = 'Post Opportunity';

$org = getOrganizationByUser($_SESSION['user_id']);
if (!$org) { header("Location: organization.php"); exit; }

$categories = getAllCategories();
$error = '';
$f = ['posting_type' => $_GET['type'] ?? 'Job', 'vacancies' => 1, 'salary_currency' => 'BDT',
      'application_method' => 'Apply on OpportunityHub', 'work_type' => 'On-site',
      'job_type' => 'Full-time', 'paid_status' => 'Paid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
        $f = array_merge($f, $_POST);
    } else {
        $type = in_array($_POST['posting_type'] ?? '', ['Job', 'Internship'], true) ? $_POST['posting_type'] : 'Job';
        $data = [
            'posting_type'         => $type,
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
            // Approved organizations publish straight to Active; others queue for admin review.
            'posting_status'       => organizationIsApproved($org) ? 'Active' : 'Pending',
        ];
        $f = $data;

        if ($data['title'] === '' || $data['application_deadline'] === '') {
            $error = 'Title and application deadline are required.';
        } elseif (strtotime($data['application_deadline']) < strtotime(date('Y-m-d'))) {
            $error = 'The application deadline cannot be in the past.';
        } elseif (addPosting($org['organization_id'], $data)) {
            header("Location: my-postings.php?created=1");
            exit;
        } else {
            $error = 'Could not create the posting. Please check your inputs.';
        }
    }
}

include '../../includes/header.php';
include '../../includes/employer-nav.php';
?>
<div class="container py-4">
  <h1>Post a New Opportunity</h1>
  <?php if (!organizationIsApproved($org)): ?>
    <div class="alert alert-warning">Your organization is <strong><?php echo e($org['status']); ?></strong>. New postings will be saved as <strong>Pending</strong> and go live only after admin approval.</div>
  <?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

  <form method="POST" class="card shadow-sm p-4">
    <?php echo csrfField(); ?>
    <div class="mb-3">
      <label class="form-label fw-bold">Opportunity Type *</label>
      <select class="form-select" name="posting_type" required>
        <?php foreach (['Job', 'Internship'] as $t): ?>
          <option value="<?php echo $t; ?>" <?php echo (($f['posting_type'] ?? '') === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php include 'posting-form-fields.php'; ?>
    <div>
      <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Create Posting</button>
      <a href="my-postings.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include '../../includes/footer.php'; ?>
