<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="container mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-body py-2">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <?php
        $links = [
          'dashboard.php'               => ['fa-tachometer-alt', 'Dashboard'],
          'manage-users.php'            => ['fa-users', 'Users'],
          'manage-organizations.php'    => ['fa-building', 'Organizations'],
          'manage-postings.php'         => ['fa-briefcase', 'Jobs & Internships'],
          'manage-job-applications.php' => ['fa-file-signature', 'Applications'],
          'manage-categories.php'       => ['fa-tags', 'Categories'],
          'expired-postings.php'        => ['fa-hourglass-end', 'Expired Posts'],
          'manage-scholarships.php'     => ['fa-graduation-cap', 'Scholarships'],
        ];
        foreach ($links as $file => $meta):
            $active = ($cur === $file) ? 'btn-primary shadow-sm' : 'btn-outline-primary';
        ?>
          <a href="<?php echo BASE_URL; ?>/pages/admin/<?php echo $file; ?>" class="btn btn-sm <?php echo $active; ?>">
            <i class="fas <?php echo $meta[0]; ?> me-1"></i> <?php echo $meta[1]; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
