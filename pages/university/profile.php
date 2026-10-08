<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireUniversity();
$pageTitle = 'University Profile';

$user = currentUser();
$univ = getUniversityByUser($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $data = [
            'univ_name'        => trim($_POST['univ_name'] ?? ''),
            'logo_url'         => trim($_POST['logo_url'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'country'          => trim($_POST['country'] ?? ''),
            'city'             => trim($_POST['city'] ?? ''),
            'address'          => trim($_POST['address'] ?? ''),
            'website'          => trim($_POST['website'] ?? ''),
            'contact_email'    => trim($_POST['contact_email'] ?? ''),
            'contact_phone'    => trim($_POST['contact_phone'] ?? ''),
            'ranking'          => trim($_POST['ranking'] ?? ''),
            'established_year' => ($_POST['established_year'] ?? '') !== '' ? (int)$_POST['established_year'] : null,
            'social_linkedin'  => trim($_POST['social_linkedin'] ?? ''),
            'social_twitter'   => trim($_POST['social_twitter'] ?? ''),
            'social_facebook'  => trim($_POST['social_facebook'] ?? ''),
            'social_youtube'   => trim($_POST['social_youtube'] ?? ''),
        ];

        if ($data['univ_name'] === '') {
            $error = 'University name is required.';
        } elseif ($univ) {
            if (updateUniversity($univ['university_id'], $data)) {
                // Keep users.name in sync
                $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
                $stmt->bind_param("si", $data['univ_name'], $_SESSION['user_id']);
                $stmt->execute();
                $_SESSION['user_name'] = $data['univ_name'];

                $success = 'University profile updated successfully.';
                $univ = getUniversityByUser($_SESSION['user_id']);
            } else {
                $error = 'Failed to update university profile.';
            }
        } else {
            if (createUniversity($_SESSION['user_id'], $data)) {
                // Keep users.name in sync
                $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
                $stmt->bind_param("si", $data['univ_name'], $_SESSION['user_id']);
                $stmt->execute();
                $_SESSION['user_name'] = $data['univ_name'];

                $success = 'University profile created successfully.';
                $univ = getUniversityByUser($_SESSION['user_id']);
            } else {
                $error = 'Failed to create university profile.';
            }
        }
    }
}

$u = $univ ?: [
    'univ_name'        => $user['name'] ?? '',
    'contact_email'    => $user['email'] ?? '',
    'country'          => '',
    'city'             => '',
    'address'          => '',
    'website'          => '',
    'logo_url'         => '',
    'ranking'          => '',
    'established_year' => '',
    'description'      => '',
    'contact_phone'    => '',
    'social_linkedin'  => '',
    'social_twitter'   => '',
    'social_facebook'  => '',
    'social_youtube'   => '',
];

include '../../includes/header.php';
include '../../includes/university-nav.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="mb-0"><i class="fas fa-university text-primary me-2"></i> University Profile</h1>
      <p class="text-muted mb-0">Official institutional identity, campus address, contact details, and social links</p>
    </div>
    <div class="d-flex gap-2">
      <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
      </a>
      <a href="my-scholarships.php" class="btn btn-outline-primary">
        <i class="fas fa-graduation-cap me-1"></i> My Scholarships
      </a>
    </div>
  </div>

  <?php if ($error): ?><div class="alert alert-danger auto-dismiss"><?php echo e($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success auto-dismiss"><?php echo e($success); ?></div><?php endif; ?>

  <!-- 1. University Profile View Section (Requirement 5) -->
  <div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-3 border-bottom mb-4">
        <!-- University Logo & Identity -->
        <div class="d-flex align-items-center gap-3">
          <div class="bg-light border rounded p-1 text-center d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; min-width: 80px;">
            <?php if (!empty($u['logo_url'])): ?>
              <img src="<?php echo e($u['logo_url']); ?>" alt="<?php echo e($u['univ_name'] ?? 'University'); ?>" class="img-fluid rounded" style="max-height: 72px; max-width: 72px; object-fit: contain;">
            <?php else: ?>
              <i class="fas fa-university fa-3x text-primary"></i>
            <?php endif; ?>
          </div>
          <div>
            <h2 class="mb-1 fw-bold"><?php echo e($u['univ_name']); ?></h2>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <?php if (!empty($u['ranking'])): ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fas fa-trophy me-1"></i><?php echo e($u['ranking']); ?></span>
              <?php endif; ?>
              <?php if (!empty($u['established_year'])): ?>
                <span class="badge bg-secondary-subtle text-secondary border"><i class="fas fa-history me-1"></i>Est. <?php echo (int)$u['established_year']; ?></span>
              <?php endif; ?>
              <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified University</span>
            </div>
          </div>
        </div>

        <!-- Edit Profile Button matching requirement -->
        <button type="button" class="btn btn-primary px-3 shadow-sm" onclick="document.getElementById('editProfileSection').scrollIntoView({behavior: 'smooth'}); document.getElementById('univ_name_input').focus();">
          <i class="fas fa-edit me-1"></i> Edit Profile
        </button>
      </div>

      <!-- Institutional Details Grid -->
      <div class="row g-4">
        <!-- Description -->
        <div class="col-12">
          <h6 class="fw-bold text-muted text-uppercase small"><i class="fas fa-align-left me-1 text-primary"></i> About the University</h6>
          <p class="text-dark mb-0" style="white-space: pre-wrap; line-height: 1.6;">
            <?php echo e($u['description'] ?: 'No university description provided yet. Click "Edit Profile" below to add institutional overview and background.'); ?>
          </p>
        </div>

        <!-- Location & Campus Address -->
        <div class="col-md-6">
          <h6 class="fw-bold text-muted text-uppercase small"><i class="fas fa-map-marker-alt me-1 text-danger"></i> Location &amp; Campus Address</h6>
          <p class="mb-1">
            <?php 
              $loc = trim(($u['city'] ? $u['city'] . ', ' : '') . ($u['country'] ?? '')); 
              echo $loc !== '' ? e($loc) : '<span class="text-muted">Not specified</span>'; 
            ?>
          </p>
          <p class="mb-0">
            <strong>Physical Address:</strong>
            <?php echo e($u['address'] ?: 'Not specified'); ?>
          </p>
        </div>

        <!-- Official Contact Details & Website -->
        <div class="col-md-6">
          <h6 class="fw-bold text-muted text-uppercase small"><i class="fas fa-address-book me-1 text-info"></i> Contact &amp; Web Presence</h6>
          <p class="mb-1">
            <strong>Website:</strong>
            <?php if (!empty($u['website'])): ?>
              <a href="<?php echo e($u['website']); ?>" target="_blank" rel="noopener" class="text-primary text-decoration-none">
                <?php echo e($u['website']); ?> <i class="fas fa-external-link-alt small"></i>
              </a>
            <?php else: ?>
              <span class="text-muted">Not specified</span>
            <?php endif; ?>
          </p>
          <p class="mb-1">
            <strong>Official Email:</strong>
            <?php if (!empty($u['contact_email'])): ?>
              <a href="mailto:<?php echo e($u['contact_email']); ?>"><?php echo e($u['contact_email']); ?></a>
            <?php else: ?>
              <span class="text-muted">Not specified</span>
            <?php endif; ?>
          </p>
          <p class="mb-0">
            <strong>Phone Number:</strong>
            <?php echo e($u['contact_phone'] ?: 'Not specified'); ?>
          </p>
        </div>

        <!-- Social Media Links -->
        <div class="col-12">
          <h6 class="fw-bold text-muted text-uppercase small mb-2"><i class="fas fa-share-alt me-1 text-success"></i> Official Social Media Channels</h6>
          <div class="d-flex gap-2 flex-wrap">
            <?php if (!empty($u['social_linkedin'])): ?>
              <a href="<?php echo e($u['social_linkedin']); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                <i class="fab fa-linkedin me-1"></i> LinkedIn
              </a>
            <?php endif; ?>
            <?php if (!empty($u['social_twitter'])): ?>
              <a href="<?php echo e($u['social_twitter']); ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm">
                <i class="fab fa-twitter me-1"></i> Twitter / X
              </a>
            <?php endif; ?>
            <?php if (!empty($u['social_facebook'])): ?>
              <a href="<?php echo e($u['social_facebook']); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                <i class="fab fa-facebook me-1"></i> Facebook
              </a>
            <?php endif; ?>
            <?php if (!empty($u['social_youtube'])): ?>
              <a href="<?php echo e($u['social_youtube']); ?>" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm">
                <i class="fab fa-youtube me-1"></i> YouTube
              </a>
            <?php endif; ?>
            <?php if (empty($u['social_linkedin']) && empty($u['social_twitter']) && empty($u['social_facebook']) && empty($u['social_youtube'])): ?>
              <span class="text-muted small">No social media links configured yet. Add them in the edit form below.</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Edit Profile Form Section -->
  <div class="card shadow-sm border-0" id="editProfileSection">
    <div class="card-header bg-white py-3">
      <h5 class="mb-0 fw-bold"><i class="fas fa-edit text-primary me-2"></i> Edit University Profile</h5>
    </div>
    <div class="card-body p-4">
      <form method="POST">
        <?php echo csrfField(); ?>
        <div class="row">
          <!-- University Name -->
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">University Name *</label>
            <input type="text" class="form-control" name="univ_name" id="univ_name_input" value="<?php echo e($u['univ_name'] ?? ''); ?>" required placeholder="e.g. University of Oxford">
          </div>

          <!-- University Logo -->
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">University Logo URL</label>
            <input type="url" class="form-control" name="logo_url" value="<?php echo e($u['logo_url'] ?? ''); ?>" placeholder="https://example.com/logo.png">
            <small class="text-muted">Direct link to institution crest or official logo image</small>
          </div>

          <!-- University Description -->
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">University Description</label>
            <textarea class="form-control" name="description" rows="4" placeholder="Brief history, world rankings, research focus, campus environment, and academic mission..."><?php echo e($u['description'] ?? ''); ?></textarea>
          </div>

          <!-- Location (Country, City, Address) -->
          <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">Country</label>
            <input type="text" class="form-control" name="country" value="<?php echo e($u['country'] ?? ''); ?>" placeholder="e.g. United Kingdom">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">City</label>
            <input type="text" class="form-control" name="city" value="<?php echo e($u['city'] ?? ''); ?>" placeholder="e.g. Oxford">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">Established Year</label>
            <input type="number" class="form-control" name="established_year" value="<?php echo e($u['established_year'] ?? ''); ?>" placeholder="e.g. 1096">
          </div>

          <!-- Campus Address -->
          <div class="col-12 mb-3">
            <label class="form-label fw-bold">Physical Campus Address</label>
            <input type="text" class="form-control" name="address" value="<?php echo e($u['address'] ?? ''); ?>" placeholder="e.g. University Offices, Wellington Square, Oxford, OX1 2JD">
          </div>

          <!-- Website, Email, Phone, Ranking -->
          <div class="col-md-3 mb-3">
            <label class="form-label fw-bold">Official Website</label>
            <input type="url" class="form-control" name="website" value="<?php echo e($u['website'] ?? ''); ?>" placeholder="https://www.ox.ac.uk">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label fw-bold">Official Email</label>
            <input type="email" class="form-control" name="contact_email" value="<?php echo e($u['contact_email'] ?? ''); ?>" placeholder="admissions@ox.ac.uk">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label fw-bold">Phone Number</label>
            <input type="text" class="form-control" name="contact_phone" value="<?php echo e($u['contact_phone'] ?? ''); ?>" placeholder="+44 1865 270000">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label fw-bold">Global Ranking</label>
            <input type="text" class="form-control" name="ranking" value="<?php echo e($u['ranking'] ?? ''); ?>" placeholder="e.g. QS #3, THE #1">
          </div>

          <!-- Social Media Links -->
          <div class="col-12">
            <h6 class="fw-bold mt-2 mb-3 text-secondary"><i class="fas fa-share-alt me-1"></i> Official Social Media Channels</h6>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label small fw-semibold">LinkedIn Profile URL</label>
            <input type="url" class="form-control" name="social_linkedin" value="<?php echo e($u['social_linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/school/oxford">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label small fw-semibold">Twitter / X URL</label>
            <input type="url" class="form-control" name="social_twitter" value="<?php echo e($u['social_twitter'] ?? ''); ?>" placeholder="https://x.com/uniofoxford">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label small fw-semibold">Facebook URL</label>
            <input type="url" class="form-control" name="social_facebook" value="<?php echo e($u['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/the.university.of.oxford">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label small fw-semibold">YouTube URL</label>
            <input type="url" class="form-control" name="social_youtube" value="<?php echo e($u['social_youtube'] ?? ''); ?>" placeholder="https://youtube.com/oxford">
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-success btn-lg px-4 shadow-sm">
            <i class="fas fa-save me-1"></i> Save Profile Changes
          </button>
          <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
