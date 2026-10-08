<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="container mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-body py-2">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <?php
        $links = [
          'dashboard.php'        => ['fa-tachometer-alt', 'Dashboard'],
          'organization.php'     => ['fa-building', 'Organization Profile'],
          'my-postings.php'      => ['fa-briefcase', 'My Postings'],
          'post-opportunity.php' => ['fa-plus-circle', 'Post New Opportunity'],
          'applications.php'     => ['fa-file-signature', 'Applications Received'],
        ];
        foreach ($links as $file => $meta):
            $active = ($cur === $file) ? 'btn-primary shadow-sm' : 'btn-outline-primary';
        ?>
          <a href="<?php echo BASE_URL; ?>/pages/employer/<?php echo $file; ?>" class="btn btn-sm <?php echo $active; ?>">
            <i class="fas <?php echo $meta[0]; ?> me-1"></i> <?php echo $meta[1]; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
