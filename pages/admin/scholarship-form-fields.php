<?php
// Expects $s to be an associative array (from DB row or repopulated $_POST). Missing keys default to ''.
$g = function ($key) use ($s) {
    $val = $s[$key] ?? '';
    return e($val === null ? '' : (string) $val);
};
$degreeLevels = ['Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate'];
$fundingCoverages = ['Fully Funded', 'Partially Funded', 'Tuition Only', 'Accommodation Only'];
$statuses = ['Active', 'Upcoming', 'Closed', 'Expired'];
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Scholarship Name *</label>
        <input type="text" class="form-control" name="scholarship_name" value="<?php echo $g('scholarship_name'); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">University Name *</label>
        <input type="text" class="form-control" name="university_name" value="<?php echo $g('university_name'); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">University Website</label>
        <input type="url" class="form-control" name="university_website" value="<?php echo $g('university_website'); ?>">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Country *</label>
        <input type="text" class="form-control" name="country" value="<?php echo $g('country'); ?>" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">City</label>
        <input type="text" class="form-control" name="city" value="<?php echo $g('city'); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Degree Level</label>
        <select class="form-select" name="degree_level">
            <?php foreach ($degreeLevels as $lvl): ?>
                <option value="<?php echo $lvl; ?>" <?php echo (($s['degree_level'] ?? '') === $lvl) ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Field of Study</label>
        <input type="text" class="form-control" name="field_of_study" value="<?php echo $g('field_of_study'); ?>">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Funding Type</label>
        <input type="text" class="form-control" name="funding_type" value="<?php echo $g('funding_type'); ?>" placeholder="e.g. Government Funded">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Funding Coverage</label>
        <select class="form-select" name="funding_coverage">
            <?php foreach ($fundingCoverages as $fc): ?>
                <option value="<?php echo $fc; ?>" <?php echo (($s['funding_coverage'] ?? '') === $fc) ? 'selected' : ''; ?>><?php echo $fc; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Scholarship Amount</label>
        <input type="text" class="form-control" name="scholarship_amount" value="<?php echo $g('scholarship_amount'); ?>" placeholder="e.g. Full tuition + $1000/month">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">GPA Requirement</label>
        <input type="number" step="0.01" min="0" max="4" class="form-control" name="gpa_requirement" value="<?php echo $g('gpa_requirement'); ?>">
    </div>

    <div class="col-12 mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3"><?php echo $g('description'); ?></textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Eligibility Requirements</label>
        <textarea class="form-control" name="eligibility_requirements" rows="2"><?php echo $g('eligibility_requirements'); ?></textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Required Documents</label>
        <textarea class="form-control" name="required_documents" rows="2"><?php echo $g('required_documents'); ?></textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Language Requirements</label>
        <input type="text" class="form-control" name="language_requirements" value="<?php echo $g('language_requirements'); ?>" placeholder="e.g. IELTS 6.5+">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Application Deadline *</label>
        <input type="date" class="form-control" name="application_deadline" value="<?php echo $g('application_deadline'); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Start Date</label>
        <input type="date" class="form-control" name="start_date" value="<?php echo $g('start_date'); ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Intake Month</label>
        <input type="text" class="form-control" name="intake_month" value="<?php echo $g('intake_month'); ?>" placeholder="e.g. September">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Total Spots</label>
        <input type="number" min="0" class="form-control" name="total_spots" value="<?php echo $g('total_spots'); ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <?php foreach ($statuses as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo (($s['status'] ?? 'Active') === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Application Link</label>
        <input type="url" class="form-control" name="application_link" value="<?php echo $g('application_link'); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Application Method</label>
        <input type="text" class="form-control" name="application_method" value="<?php echo $g('application_method'); ?>" placeholder="e.g. Online Portal">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Contact Email</label>
        <input type="email" class="form-control" name="contact_email" value="<?php echo $g('contact_email'); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Contact Phone</label>
        <input type="text" class="form-control" name="contact_phone" value="<?php echo $g('contact_phone'); ?>">
    </div>
</div>
