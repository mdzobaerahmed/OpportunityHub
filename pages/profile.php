<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isUniversity()) {
    header("Location: university/profile.php");
    exit;
}
$pageTitle = 'My Profile';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'university'        => trim($_POST['university'] ?? ''),
        'degree'            => trim($_POST['degree'] ?? ''),
        'field_of_study'    => trim($_POST['field_of_study'] ?? ''),
        'skills'            => trim($_POST['skills'] ?? ''),
        'experience_years'  => (int) ($_POST['experience_years'] ?? 0),
        'preferred_country' => trim($_POST['preferred_country'] ?? ''),
        'preferred_field'   => trim($_POST['preferred_field'] ?? ''),
        'phone'             => trim($_POST['phone'] ?? ''),
        'bio'               => trim($_POST['bio'] ?? ''),
    ];

    if (updateUserProfile($_SESSION['user_id'], $data)) {
        $success = 'Profile updated successfully.';
    } else {
        $error = 'Could not update profile. Please try again.';
    }
}

$user = currentUser();

include '../includes/header.php';
?>

<div class="bg-white py-4 border-bottom">
    <div class="container">
        <h1 class="mb-0">My Profile</h1>
        <p class="text-muted mb-0">Keep your academic background up to date</p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($success): ?><div class="alert alert-success auto-dismiss"><?php echo e($success); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger auto-dismiss"><?php echo e($error); ?></div><?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <strong>Account Information</strong>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> <?php echo e($user['name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo e($user['email']); ?></p>
                    <p class="mb-0"><strong>Account Type:</strong> <?php echo e(ucfirst($user['user_type'])); ?></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong>Academic & Career Profile</strong>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">University</label>
                                <input type="text" class="form-control" name="university" value="<?php echo e($user['university']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current / Highest Degree</label>
                                <input type="text" class="form-control" name="degree" value="<?php echo e($user['degree']); ?>" placeholder="e.g. BSc in CSE">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Field of Study</label>
                                <input type="text" class="form-control" name="field_of_study" value="<?php echo e($user['field_of_study']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Years of Experience</label>
                                <input type="number" min="0" class="form-control" name="experience_years" value="<?php echo (int) $user['experience_years']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preferred Country</label>
                                <input type="text" class="form-control" name="preferred_country" value="<?php echo e($user['preferred_country']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preferred Field</label>
                                <input type="text" class="form-control" name="preferred_field" value="<?php echo e($user['preferred_field']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo e($user['phone']); ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Skills (comma separated)</label>
                                <input type="text" class="form-control" name="skills" value="<?php echo e($user['skills']); ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Short Bio</label>
                                <textarea class="form-control" name="bio" rows="3"><?php echo e($user['bio']); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
