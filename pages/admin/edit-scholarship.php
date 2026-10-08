<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireAdmin();

$id = (int) ($_GET['id'] ?? $_POST['scholarship_id'] ?? 0);
$existing = getScholarshipById($id);

if (!$existing) {
    header("Location: manage-scholarships.php");
    exit;
}

$pageTitle = 'Edit Scholarship';
$error = '';
$s = $existing;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        'gpa_requirement'           => $_POST['gpa_requirement'] !== '' ? (float) $_POST['gpa_requirement'] : null,
        'application_deadline'      => $_POST['application_deadline'] ?? null,
        'start_date'                => $_POST['start_date'] ?: null,
        'intake_month'              => trim($_POST['intake_month'] ?? ''),
        'status'                    => $_POST['status'] ?? 'Active',
        'application_link'          => trim($_POST['application_link'] ?? ''),
        'application_method'        => trim($_POST['application_method'] ?? ''),
        'total_spots'               => $_POST['total_spots'] !== '' ? (int) $_POST['total_spots'] : null,
        'contact_email'             => trim($_POST['contact_email'] ?? ''),
        'contact_phone'             => trim($_POST['contact_phone'] ?? ''),
    ];

    if (empty($data['scholarship_name']) || empty($data['university_name']) || empty($data['country']) || empty($data['application_deadline'])) {
        $error = 'Name, university, country and application deadline are required.';
        $s = $data;
        $s['scholarship_id'] = $id;
    } else {
        if (updateScholarship($id, $data)) {
            header("Location: manage-scholarships.php");
            exit;
        }
        $error = 'Failed to update scholarship.';
    }
}

include '../../includes/header.php';
?>

<div class="bg-white py-4 border-bottom">
    <div class="container">
        <h1 class="mb-0">Edit Scholarship</h1>
    </div>
</div>

<div class="container py-5">
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

    <form method="POST" class="card shadow-sm p-4">
        <input type="hidden" name="scholarship_id" value="<?php echo $id; ?>">
        <?php include 'scholarship-form-fields.php'; ?>
        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Save Changes</button>
        <a href="manage-scholarships.php" class="btn btn-outline-secondary mt-3">Cancel</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
