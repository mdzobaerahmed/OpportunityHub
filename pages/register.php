<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$pageTitle = 'Register';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'student';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($user_type, ['student', 'graduate', 'employer', 'university'], true)) {
        $error = 'Invalid account type.';
    } elseif (emailExists($email)) {
        $error = 'This email is already registered.';
    } else {
        if (registerUser($name, $email, $password, $user_type)) {
            $stmt = $conn->prepare("SELECT user_id, name, user_type FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];

                header("Location: dashboard.php");
                exit;
            }

            $error = 'Registration failed. Please try again.';

        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="container">
        <div class="auth-card">
            <h2 class="text-center mb-4">Create Your Account</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger auto-dismiss"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                    <small class="form-text text-muted">Minimum 6 characters</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Account Type</label>
                    <select class="form-select" name="user_type" required>
                        <option value="student" <?php echo (($_POST['user_type'] ?? '') === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="graduate" <?php echo (($_POST['user_type'] ?? '') === 'graduate') ? 'selected' : ''; ?>>Fresh Graduate</option>
                        <option value="employer" <?php echo (($_POST['user_type'] ?? '') === 'employer') ? 'selected' : ''; ?>>Employer / Organization</option>
                        <option value="university" <?php echo (($_POST['user_type'] ?? '') === 'university') ? 'selected' : ''; ?>>University / Academic Institution</option>
                    </select>

                    <small class="form-text text-muted">
                        Universities can publish scholarships &amp; collect student applications. Employers can post jobs &amp; internships.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
            </form>

            <p class="text-center mb-0">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>