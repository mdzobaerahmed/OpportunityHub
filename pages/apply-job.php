<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin() || isEmployer() || isUniversity()) { header("Location: jobs.php"); exit; }

sweepExpiredPostings();

$id = (int) ($_GET['id'] ?? $_POST['posting_id'] ?? 0);
$p  = $id ? getPostingById($id) : null;

if (!$p || $p['org_status'] !== 'Approved') { header("Location: jobs.php"); exit; }

// Server-side guard: deadline is the source of truth, never trust the form.
if (isPostingExpired($p) || $p['posting_status'] !== 'Active') {
    header("Location: posting-details.php?id=" . $id . "&err=closed");
    exit;
}
if (hasUserAppliedToPosting($_SESSION['user_id'], $id)) {
    header("Location: posting-details.php?id=" . $id);
    exit;
}

$pageTitle = 'Apply - ' . $p['title'];
$error = '';
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $cover_note = trim($_POST['cover_note'] ?? '');
        if ($cover_note === '') {
            $error = 'Please write a short cover note.';
        } else {
            list($resume_path, $uploadError) = handleResumeUpload('resume');
            if ($uploadError) {
                $error = $uploadError;
            } elseif (applyToPosting($_SESSION['user_id'], $id, $cover_note, $resume_path)) {
                header("Location: my-job-applications.php?applied=1");
                exit;
            } else {
                $error = 'Could not submit your application. Please try again.';
            }
        }
    }
}

include '../includes/header.php';
?>
<div class="bg-white py-4 border-bottom">
  <div class="container">
    <h1 class="mb-0">Apply for this <?php echo e($p['posting_type']); ?></h1>
    <p class="text-muted mb-0"><?php echo e($p['title']); ?> at <?php echo e($p['org_name']); ?></p>
  </div>
</div>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="alert alert-light border mb-4">
            <strong>Applying as:</strong> <?php echo e($user['name']); ?> (<?php echo e($user['email']); ?>)<br>
            <small class="text-muted">Your profile details (university, degree, skills) will be shared with the employer.
            <a href="profile.php">Update profile</a></small>
          </div>

          <form method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <input type="hidden" name="posting_id" value="<?php echo $id; ?>">

            <div class="mb-3">
              <label class="form-label fw-bold">Cover Note *</label>
              <textarea class="form-control" name="cover_note" rows="6" required
                placeholder="Explain briefly why you are a good fit for this role..."><?php echo e($_POST['cover_note'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Upload CV / Resume (optional)</label>
              <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx">
              <small class="form-text text-muted">PDF, DOC or DOCX only. Maximum 2MB.</small>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Submit Application</button>
            <a href="posting-details.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">Cancel</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
