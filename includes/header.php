<?php
// Expects BASE_URL to already be defined (from config/database.php)
$currentUserData = isLoggedIn() ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - OpportunityHub' : 'OpportunityHub - Jobs, Internships & Global Scholarships'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>/pages/index.php">
            <i class="fas fa-graduation-cap"></i> OpportunityHub
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/jobs.php">Jobs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/internships.php">Internships</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/scholarships.php">Scholarships</a>
                </li>

                <?php if (isLoggedIn() && isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/admin/dashboard.php">Admin Panel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/logout.php">Logout</a>
                    </li>

                <?php elseif (isLoggedIn() && isEmployer()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/employer/dashboard.php">Employer Panel</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-building"></i> <?php echo e($currentUserData['name'] ?? 'Account'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/employer/dashboard.php">Employer Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/employer/organization.php">Organization Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/employer/my-postings.php">My Postings</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/employer/applications.php">Applications Received</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/logout.php">Logout</a></li>
                        </ul>
                    </li>

                <?php elseif (isLoggedIn() && isUniversity()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/university/dashboard.php">University Panel</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-university"></i> <?php echo e($currentUserData['name'] ?? 'Account'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/university/dashboard.php"><i class="fas fa-tachometer-alt me-2 text-primary"></i> University Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/university/my-scholarships.php"><i class="fas fa-graduation-cap me-2 text-primary"></i> Manage Scholarships</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/university/post-scholarship.php"><i class="fas fa-plus-circle me-2 text-success"></i> Add New Scholarship</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/university/applications.php"><i class="fas fa-file-signature me-2 text-info"></i> Student Applications</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/university/profile.php"><i class="fas fa-university me-2 text-secondary"></i> University Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/logout.php">Logout</a></li>
                        </ul>
                    </li>

                <?php elseif (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo e($currentUserData['name'] ?? 'Account'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/my-job-applications.php">My Job Applications</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/saved-opportunities.php">Saved Jobs/Internships</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/saved-scholarships.php">Saved Scholarships</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/applications.php">Scholarship Applications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/logout.php">Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-lg-2" href="<?php echo BASE_URL; ?>/pages/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
