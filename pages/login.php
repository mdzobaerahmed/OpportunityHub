<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

function loginRedirectTarget() {
    if (isAdmin())      return "admin/dashboard.php";
    if (isEmployer())   return "employer/dashboard.php";
    if (isUniversity()) return "university/dashboard.php";
    return "dashboard.php";
}

if (isLoggedIn()) {
    header("Location: " . loginRedirectTarget());
    exit;
}

$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $result = loginUser($email, $password);
        if ($result === true) {
            header("Location: " . loginRedirectTarget());
            exit;
        } elseif ($result === 'suspended') {
            $error = 'Your account has been suspended. Please contact the administrator.';
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="container">
        <div class="auth-card">
            <h2 class="text-center mb-4">Welcome Back</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger auto-dismiss"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
            </form>

            <p class="text-center mb-0">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
