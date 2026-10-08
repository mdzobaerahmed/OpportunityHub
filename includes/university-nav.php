<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="container mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-body py-2">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <?php
        $links = [
          'dashboard.php'         => ['fa-tachometer-alt', 'Dashboard'],
          'my-scholarships.php'   => ['fa-graduation-cap', 'Manage Scholarships'],
          'post-scholarship.php'  => ['fa-plus-circle', 'Add New Scholarship'],
          'applications.php'      => ['fa-file-signature', 'Applications'],
          'profile.php'           => ['fa-university', 'University Profile'],
        ];
        foreach ($links as $file => $meta):
            $active = ($cur === $file) ? 'btn-primary shadow-sm' : 'btn-outline-primary';
        ?>
          <a href="<?php echo BASE_URL; ?>/pages/university/<?php echo $file; ?>" class="btn btn-sm <?php echo $active; ?>">
            <i class="fas <?php echo $meta[0]; ?> me-1"></i> <?php echo $meta[1]; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
