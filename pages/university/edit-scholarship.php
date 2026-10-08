<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();

$id = (int) ($_GET['id'] ?? $_POST['scholarship_id'] ?? 0);
$existing = getScholarshipById($id);

// Ensure the scholarship belongs to this university
if (!$existing || !scholarshipBelongsToUniversity($id, $_SESSION['user_id'])) {
    header("Location: my-scholarships.php?err=denied");
    exit;
}

$pageTitle = 'Edit Scholarship';
$error = '';
$s = $existing;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $submitAction = $_POST['submit_action'] ?? 'save';
        $status = $_POST['status'] ?? $existing['status'];
        if ($submitAction === 'draft') {
            $status = 'Draft';
        } elseif ($submitAction === 'publish') {
            $status = 'Active';
        } elseif ($submitAction === 'close') {
            $status = 'Closed';
        }

        $data = [
            'scholarship_name'          => trim($_POST['scholarship_name'] ?? ''),
            'university_name'           => trim($_POST['university_name'] ?? ''),
            'university_website'        => trim($_POST['university_website'] ?? ''),
            'country'                   => trim($_POST['country'] ?? ''),
            'city'                      => trim($_POST['city'] ?? ''),
            'degree_level'              => $_POST['degree_level'] ?? 'Master',
            'field_of_study'            => trim($_POST['field_of_study'] ?? ''),
            'funding_type'              => trim($_POST['funding_type'] ?? ''),
            'funding_coverage'          => $_POST['funding_coverage'] ?? 'Partially Funded',
            'scholarship_amount'        => trim($_POST['scholarship_amount'] ?? ''),
            'description'               => trim($_POST['description'] ?? ''),
            'eligibility_requirements'  => trim($_POST['eligibility_requirements'] ?? ''),
            'required_documents'        => trim($_POST['required_documents'] ?? ''),
            'language_requirements'     => trim($_POST['language_requirements'] ?? ''),
            'gpa_requirement'           => ($_POST['gpa_requirement'] ?? '') !== '' ? (float) $_POST['gpa_requirement'] : null,
            'application_deadline'      => $_POST['application_deadline'] ?? null,
            'start_date'                => ($_POST['start_date'] ?? '') ?: null,
            'intake_month'              => trim($_POST['intake_month'] ?? ''),
            'status'                    => $status,
            'application_link'          => trim($_POST['application_link'] ?? ''),
            'application_method'        => trim($_POST['application_method'] ?? 'Apply on OpportunityHub'),
            'total_spots'               => ($_POST['total_spots'] ?? '') !== '' ? (int) $_POST['total_spots'] : null,
            'contact_email'             => trim($_POST['contact_email'] ?? ''),
            'contact_phone'             => trim($_POST['contact_phone'] ?? ''),
            'image_url'                 => trim($_POST['image_url'] ?? ''),
        ];

        if (empty($data['scholarship_name']) || empty($data['university_name']) || empty($data['country']) || empty($data['application_deadline'])) {
            $error = 'Scholarship title, university name, country, and application deadline are required.';
            $s = array_merge($existing, $data);
            $s['scholarship_id'] = $id;
        } else {
            if (updateScholarship($id, $data)) {
                header("Location: my-scholarships.php?updated=1");
                exit;
            } else {
                $error = 'Failed to update scholarship.';
                $s = array_merge($existing, $data);
                $s['scholarship_id'] = $id;
            }
        }
    }
}

include '../../includes/header.php';
include '../../includes/university-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="mb-0"><i class="fas fa-edit text-primary me-2"></i> Edit Scholarship</h1>
      <p class="text-muted mb-0">Update deadlines, eligibility, scholarship amount, required documents, or publishing status</p>
    </div>
    <div class="d-flex gap-2">
      <a href="applications.php?scholarship_id=<?php echo $id; ?>" class="btn btn-outline-info">
        <i class="fas fa-users me-1"></i> View Applications
      </a>
      <a href="my-scholarships.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to My Scholarships
      </a>
    </div>
  </div>

  <?php if ($error): ?><div class="alert alert-danger auto-dismiss"><?php echo e($error); ?></div><?php endif; ?>

  <form method="POST" class="card shadow-sm p-4 border-0">
    <?php echo csrfField(); ?>
    <input type="hidden" name="scholarship_id" value="<?php echo $id; ?>">
    <?php include 'scholarship-form-fields.php'; ?>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="d-flex gap-2 flex-wrap">
        <button type="submit" name="submit_action" value="save" class="btn btn-primary btn-lg px-4 shadow-sm">
          <i class="fas fa-save me-1"></i> Save Changes
        </button>

        <?php if ($existing['status'] === 'Draft'): ?>
          <button type="submit" name="submit_action" value="publish" class="btn btn-success btn-lg px-3">
            <i class="fas fa-globe me-1"></i> Publish Scholarship
          </button>
        <?php else: ?>
          <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary btn-lg px-3">
            <i class="fas fa-eye-slash me-1"></i> Unpublish to Draft
          </button>
        <?php endif; ?>

        <?php if ($existing['status'] !== 'Closed'): ?>
          <button type="submit" name="submit_action" value="close" class="btn btn-outline-danger btn-lg px-3">
            <i class="fas fa-ban me-1"></i> Close Scholarship
          </button>
        <?php endif; ?>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <a href="my-scholarships.php" class="btn btn-link text-muted text-decoration-none">
          Cancel
        </a>
        <a href="../scholarship-details.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-outline-info">
          <i class="fas fa-external-link-alt me-1"></i> Public Page
        </a>
      </div>
    </div>
  </form>
</div>
<?php include '../../includes/footer.php'; ?>
