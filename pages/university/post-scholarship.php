<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();
$pageTitle = 'Add New Scholarship';
$error = '';

$user = currentUser();
$univ = getUniversityByUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $submitAction = $_POST['submit_action'] ?? 'publish';
        $status = $_POST['status'] ?? 'Active';
        if ($submitAction === 'draft') {
            $status = 'Draft';
        } elseif ($submitAction === 'publish' && $status === 'Draft') {
            $status = 'Active';
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
        } else {
            if (addScholarship($data, $_SESSION['user_id'])) {
                $msgParam = ($status === 'Draft') ? 'draft_saved=1' : 'created=1';
                header("Location: my-scholarships.php?" . $msgParam);
                exit;
            } else {
                $error = 'Failed to save scholarship. Please verify all inputs and try again.';
            }
        }
    }
}

// Default pre-population from university profile
$defaultFields = [
    'university_name'    => $univ['univ_name'] ?? $user['name'] ?? '',
    'university_website' => $univ['website'] ?? '',
    'country'            => $univ['country'] ?? '',
    'city'               => $univ['city'] ?? '',
    'contact_email'      => $univ['contact_email'] ?? $user['email'] ?? '',
    'contact_phone'      => $univ['contact_phone'] ?? '',
    'funding_coverage'   => 'Fully Funded',
    'degree_level'       => 'Master',
    'status'             => 'Active',
    'application_method' => 'Apply on OpportunityHub',
    'image_url'          => $univ['logo_url'] ?? '',
];

$s = !empty($_POST) ? array_merge($defaultFields, $_POST) : $defaultFields;

include '../../includes/header.php';
include '../../includes/university-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="mb-0"><i class="fas fa-plus-circle text-primary me-2"></i> Add New Scholarship</h1>
      <p class="text-muted mb-0">Provide complete information to publish or draft a new scholarship opportunity</p>
    </div>
    <a href="my-scholarships.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Cancel
    </a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger auto-dismiss"><?php echo e($error); ?></div><?php endif; ?>

  <form method="POST" class="card shadow-sm p-4 border-0">
    <?php echo csrfField(); ?>
    <?php include 'scholarship-form-fields.php'; ?>

    <hr class="my-4">

    <!-- Action Buttons matching prompt: Save as Draft, Publish Scholarship, Cancel -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="d-flex gap-2">
        <button type="submit" name="submit_action" value="publish" class="btn btn-primary btn-lg px-4 shadow-sm">
          <i class="fas fa-paper-plane me-1"></i> Publish Scholarship
        </button>
        <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary btn-lg px-4">
          <i class="fas fa-save me-1"></i> Save as Draft
        </button>
      </div>
      <a href="my-scholarships.php" class="btn btn-link text-muted text-decoration-none">
        <i class="fas fa-times me-1"></i> Cancel
      </a>
    </div>
  </form>
</div>
<?php include '../../includes/footer.php'; ?>
