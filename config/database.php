<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'opportunityhub');

define('BASE_URL', '/opportunityhub');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error .
        "<br>Make sure XAMPP's MySQL/Apache is running and you have imported database/opportunityhub.sql");
}

$conn->set_charset("utf8mb4");

$requiredTables = ['users', 'scholarships', 'organizations', 'categories', 'postings', 'job_applications', 'saved_postings'];
$existing = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array(MYSQLI_NUM)) {
    $existing[] = $row[0];
}
$missing = array_diff($requiredTables, $existing);

if (!empty($missing)) {
    echo "<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:720px;margin:60px auto;padding:28px;
          border:1px solid #f0c674;background:#fff9e6;border-radius:10px;line-height:1.6\">";
    echo "<h2 style='margin-top:0'>Database import is incomplete</h2>";
    echo "<p>These tables are missing from the <code>opportunityhub</code> database:</p>";
    echo "<p><strong>" . htmlspecialchars(implode(', ', $missing)) . "</strong></p>";
    echo "<p>This almost always means the SQL import stopped early &mdash; usually because the
          database already existed, so phpMyAdmin hit a &ldquo;table already exists&rdquo; error
          and aborted the rest of the file.</p>";
    echo "<h4>How to fix</h4>";
    echo "<p><strong>Option A &mdash; fresh install (recommended).</strong> In phpMyAdmin, import
          <code>database/opportunityhub.sql</code> again. It now drops the old tables first, so it
          always completes. This deletes any existing OpportunityHub data.</p>";
    echo "<p><strong>Option B &mdash; keep your existing data.</strong> In phpMyAdmin, select the
          <code>opportunityhub</code> database and import
          <code>database/jobs_module_upgrade.sql</code>, which only adds the new tables.</p>";
    echo "<p style='margin-bottom:0'>After importing, run
          <a href='" . BASE_URL . "/setup.php'>setup.php</a> once, then reload this page.</p>";
    echo "</div>";
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
