<?php
// Expects $f (array of current values) and $categories
$g = function ($key) use ($f) { $v = $f[$key] ?? ''; return e($v === null ? '' : (string)$v); };
$sel = function ($key, $val) use ($f) { return (($f[$key] ?? '') === $val) ? 'selected' : ''; };
?>
<div class="row">
  <div class="col-md-8 mb-3">
    <label class="form-label">Title *</label>
    <input type="text" class="form-control" name="title" value="<?php echo $g('title'); ?>" required>
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Category</label>
    <select class="form-select" name="category_id">
      <option value="">-- Select --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?php echo (int)$c['category_id']; ?>" <?php echo ((int)($f['category_id'] ?? 0) === (int)$c['category_id']) ? 'selected' : ''; ?>>
          <?php echo e($c['category_name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12 mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"><?php echo $g('description'); ?></textarea>
  </div>
  <div class="col-12 mb-3">
    <label class="form-label">Responsibilities</label>
    <textarea class="form-control" name="responsibilities" rows="3"><?php echo $g('responsibilities'); ?></textarea>
  </div>
  <div class="col-12 mb-3">
    <label class="form-label">Requirements</label>
    <textarea class="form-control" name="requirements" rows="3"><?php echo $g('requirements'); ?></textarea>
  </div>
  <div class="col-12 mb-3">
    <label class="form-label">Qualifications</label>
    <textarea class="form-control" name="qualifications" rows="2"><?php echo $g('qualifications'); ?></textarea>
  </div>
  <div class="col-12 mb-3">
    <label class="form-label">Skills (comma separated)</label>
    <input type="text" class="form-control" name="skills" value="<?php echo $g('skills'); ?>" placeholder="e.g. PHP, MySQL, Bootstrap">
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">Location</label>
    <input type="text" class="form-control" name="location" value="<?php echo $g('location'); ?>" placeholder="e.g. Dhaka">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Work Type</label>
    <select class="form-select" name="work_type">
      <?php foreach (['On-site','Remote','Hybrid'] as $w): ?>
        <option value="<?php echo $w; ?>" <?php echo $sel('work_type', $w); ?>><?php echo $w; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Job Type</label>
    <select class="form-select" name="job_type">
      <?php foreach (['Full-time','Part-time','Contract','Temporary'] as $j): ?>
        <option value="<?php echo $j; ?>" <?php echo $sel('job_type', $j); ?>><?php echo $j; ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">Paid / Unpaid</label>
    <select class="form-select" name="paid_status">
      <?php foreach (['Paid','Unpaid'] as $p): ?>
        <option value="<?php echo $p; ?>" <?php echo $sel('paid_status', $p); ?>><?php echo $p; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 mb-3">
    <label class="form-label">Salary / Stipend Min</label>
    <input type="number" min="0" class="form-control" name="salary_min" value="<?php echo $g('salary_min'); ?>">
  </div>
  <div class="col-md-3 mb-3">
    <label class="form-label">Salary / Stipend Max</label>
    <input type="number" min="0" class="form-control" name="salary_max" value="<?php echo $g('salary_max'); ?>">
  </div>
  <div class="col-md-3 mb-3">
    <label class="form-label">Currency</label>
    <input type="text" class="form-control" name="salary_currency" value="<?php echo e($f['salary_currency'] ?? 'BDT'); ?>">
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">Duration (internships)</label>
    <input type="text" class="form-control" name="duration" value="<?php echo $g('duration'); ?>" placeholder="e.g. 3 months">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Vacancies</label>
    <input type="number" min="1" class="form-control" name="vacancies" value="<?php echo e($f['vacancies'] ?? 1); ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Application Deadline *</label>
    <input type="date" class="form-control" name="application_deadline" value="<?php echo $g('application_deadline'); ?>" required>
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Application Method</label>
    <input type="text" class="form-control" name="application_method" value="<?php echo e($f['application_method'] ?? 'Apply on OpportunityHub'); ?>">
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">External Application Link (optional)</label>
    <input type="url" class="form-control" name="application_link" value="<?php echo $g('application_link'); ?>">
  </div>
</div>
