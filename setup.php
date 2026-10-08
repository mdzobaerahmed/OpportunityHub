<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Creates the default admin account and sets passwords for the demo
 * employer accounts that were inserted by opportunityhub.sql.
 *
 * HOW TO USE:
 * 1. Import database/opportunityhub.sql into MySQL (via phpMyAdmin).
 * 2. Visit http://localhost/opportunityhub/setup.php once.
 * 3. Log in using the credentials shown below.
 * 4. Delete this file afterwards.
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$admin_email    = 'admin@opportunityhub.com';
$admin_password = 'admin123';
$admin_name     = 'Admin User';

$demo_employer_password = 'employer123';
$demo_employers = ['hr@bracbank.com', 'hr@brainstation23.com', 'hr@pathao.com'];

$demo_university_password = 'university123';
$demo_universities = ['admissions@oxford.ac.uk', 'scholarships@harvard.edu'];

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>OpportunityHub Setup</title>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='bg-light'><div class='container py-5'><div class='card shadow-sm'><div class='card-body'>";
echo "<h2 class='mb-4'>OpportunityHub &mdash; Setup</h2>";

// ---- Ensure University schema exists ----
ensureUniversitySchema();

// ---- Create or repair the documented admin login ----
// Resetting the hash here makes rerunning setup recover an existing account
// whose password was changed or whose row still has a placeholder hash.
$hash = password_hash($admin_password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type, profile_complete, status)
                        VALUES (?, ?, ?, 'admin', 1, 'active')
                        ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password),
                                                user_type = 'admin', profile_complete = 1, status = 'active'");
$stmt->bind_param("sss", $admin_name, $admin_email, $hash);
echo $stmt->execute()
    ? "<p class='text-success'>Admin login is ready. Use the credentials listed below.</p>"
    : "<p class='text-danger'>Failed to set up admin: " . htmlspecialchars($stmt->error) . "</p>";

// ---- Demo employer passwords ----
$updated = 0;
foreach ($demo_employers as $email) {
    $hash = password_hash($demo_employer_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'employer'");
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) $updated++;
}
echo "<p class='text-success'>Demo employer passwords set for {$updated} account(s).</p>";

// ---- Demo university passwords ----
$univ_updated = 0;
foreach ($demo_universities as $email) {
    $hash = password_hash($demo_university_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'university'");
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) $univ_updated++;
}
echo "<p class='text-success'>Demo university passwords set for {$univ_updated} account(s).</p>";

// ---- Expiry sweep so demo data is consistent immediately ----
sweepExpiredPostings();
echo "<p class='text-muted'>Expired-posting sweep completed.</p>";

?>
<hr>
<h5>Login Credentials</h5>
<table class="table table-bordered">
  <thead><tr><th>Role</th><th>Email</th><th>Password</th></tr></thead>
  <tbody>
    <tr><td><strong>Admin</strong></td><td><?php echo htmlspecialchars($admin_email); ?></td><td><?php echo htmlspecialchars($admin_password); ?></td></tr>
    <tr><td>Employer (approved org)</td><td>hr@bracbank.com</td><td><?php echo htmlspecialchars($demo_employer_password); ?></td></tr>
    <tr><td>Employer (approved org)</td><td>hr@brainstation23.com</td><td><?php echo htmlspecialchars($demo_employer_password); ?></td></tr>
    <tr><td>Employer (pending org)</td><td>hr@pathao.com</td><td><?php echo htmlspecialchars($demo_employer_password); ?></td></tr>
    <tr><td><strong>University</strong> (approved)</td><td>admissions@oxford.ac.uk</td><td><?php echo htmlspecialchars($demo_university_password); ?></td></tr>
    <tr><td><strong>University</strong> (approved)</td><td>scholarships@harvard.edu</td><td><?php echo htmlspecialchars($demo_university_password); ?></td></tr>
    <tr><td>Student</td><td colspan="2">Register your own account at <a href="pages/register.php">register.php</a></td></tr>
  </tbody>
</table>

<a href="pages/index.php" class="btn btn-primary">Go to Homepage</a>
<a href="pages/login.php" class="btn btn-outline-primary">Go to Login</a>

<div class="alert alert-warning mt-4 mb-0">
  <strong>Important:</strong> delete <code>setup.php</code> once you have logged in successfully.
</div>
</div></div></div></body></html>
