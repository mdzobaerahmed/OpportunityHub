<?php
// Expects $s to be an associative array (from DB row or repopulated $_POST or defaults from $univ).
$g = function ($key) use ($s) {
    $val = $s[$key] ?? '';
    return e($val === null ? '' : (string) $val);
};
$degreeLevels = ['Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate'];
$fundingCoverages = ['Fully Funded', 'Partially Funded', 'Tuition Only', 'Accommodation Only'];
// Status options matching prompt requirements: Draft / Published / Closed
$statusOptions = [
    'Active' => 'Published',
    'Draft'  => 'Draft',
    'Closed' => 'Closed',
];
$currentStatus = $s['status'] ?? 'Active';
if ($currentStatus === 'Published') $currentStatus = 'Active';
?>
<div class="row">
    <!-- Scholarship Title -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Scholarship Title / Name *</label>
        <input type="text" class="form-control" name="scholarship_name" value="<?php echo $g('scholarship_name'); ?>" required placeholder="e.g. Vice-Chancellor's International Excellence Scholarship">
    </div>

    <!-- University Name -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">University Name *</label>
        <input type="text" class="form-control" name="university_name" value="<?php echo $g('university_name'); ?>" required placeholder="e.g. University of Oxford">
    </div>

    <!-- Scholarship Image / Logo -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Scholarship Image / Logo URL (Optional)</label>
        <input type="url" class="form-control" name="image_url" value="<?php echo $g('image_url'); ?>" placeholder="https://example.com/scholarship-banner.jpg">
        <small class="text-muted">Link to scholarship badge, institution crest, or feature graphic</small>
    </div>

    <!-- University Website -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">University Website</label>
        <input type="url" class="form-control" name="university_website" value="<?php echo $g('university_website'); ?>" placeholder="https://www.ox.ac.uk">
    </div>

    <!-- Country & City -->
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Country *</label>
        <input type="text" class="form-control" name="country" value="<?php echo $g('country'); ?>" required placeholder="e.g. United Kingdom">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">City</label>
        <input type="text" class="form-control" name="city" value="<?php echo $g('city'); ?>" placeholder="e.g. Oxford">
    </div>

    <!-- Study Level -->
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Study Level *</label>
        <select class="form-select" name="degree_level">
            <?php foreach ($degreeLevels as $lvl): ?>
                <option value="<?php echo $lvl; ?>" <?php echo (($s['degree_level'] ?? 'Master') === $lvl) ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Eligible Departments / Subjects -->
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Eligible Departments / Subjects *</label>
        <input type="text" class="form-control" name="field_of_study" value="<?php echo $g('field_of_study'); ?>" placeholder="e.g. Computer Science, Engineering, All Departments">
    </div>

    <!-- Scholarship Type & Coverage -->
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Scholarship Type</label>
        <input type="text" class="form-control" name="funding_type" value="<?php echo $g('funding_type'); ?>" placeholder="e.g. Merit-Based, Need-Based, Research Grant">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Funding Coverage</label>
        <select class="form-select" name="funding_coverage">
            <?php foreach ($fundingCoverages as $fc): ?>
                <option value="<?php echo $fc; ?>" <?php echo (($s['funding_coverage'] ?? 'Fully Funded') === $fc) ? 'selected' : ''; ?>><?php echo $fc; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Scholarship Amount -->
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Scholarship Amount</label>
        <input type="text" class="form-control" name="scholarship_amount" value="<?php echo $g('scholarship_amount'); ?>" placeholder="e.g. 100% Tuition Waiver + £1,200/mo stipend">
    </div>

    <!-- Number of available scholarships -->
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Number of Available Scholarships</label>
        <input type="number" min="1" class="form-control" name="total_spots" value="<?php echo $g('total_spots'); ?>" placeholder="e.g. 10">
    </div>

    <!-- Scholarship Description -->
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Scholarship Description *</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Provide background information, department overview, and key features of this scholarship..."><?php echo $g('description'); ?></textarea>
    </div>

    <!-- Eligibility Criteria -->
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Eligibility Criteria</label>
        <textarea class="form-control" name="eligibility_requirements" rows="2" placeholder="Academic qualifications, country/nationality restrictions, or minimum prerequisites..."><?php echo $g('eligibility_requirements'); ?></textarea>
    </div>

    <!-- Required Qualifications -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Required Qualifications</label>
        <input type="text" class="form-control" name="language_requirements" value="<?php echo $g('language_requirements'); ?>" placeholder="e.g. Bachelor Degree with First Class Honors, IELTS 7.0+">
    </div>

    <!-- Minimum GPA Requirement -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Minimum GPA Requirement (Optional)</label>
        <input type="number" step="0.01" min="0" max="4" class="form-control" name="gpa_requirement" value="<?php echo $g('gpa_requirement'); ?>" placeholder="e.g. 3.50">
    </div>

    <!-- Required Documents -->
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Required Documents</label>
        <textarea class="form-control" name="required_documents" rows="2" placeholder="e.g. Academic Transcripts, Statement of Purpose (SOP), 2 Letters of Recommendation, CV/Resume, Valid Passport"><?php echo $g('required_documents'); ?></textarea>
        <small class="text-muted">Separate items with commas or list them clearly for student applicants</small>
    </div>

    <!-- Application Start Date & Deadline -->
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Application Start Date</label>
        <input type="date" class="form-control" name="start_date" value="<?php echo $g('start_date'); ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Application Deadline *</label>
        <input type="date" class="form-control" name="application_deadline" value="<?php echo $g('application_deadline'); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Intake Month</label>
        <input type="text" class="form-control" name="intake_month" value="<?php echo $g('intake_month'); ?>" placeholder="e.g. September, January">
    </div>

    <!-- Application Instructions -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Application Instructions / Method</label>
        <input type="text" class="form-control" name="application_method" value="<?php echo $g('application_method') ?: 'Apply on OpportunityHub'; ?>" placeholder="Apply on OpportunityHub or via university portal">
    </div>

    <!-- Application Link -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Application Link (Optional)</label>
        <input type="url" class="form-control" name="application_link" value="<?php echo $g('application_link'); ?>" placeholder="https://ox.ac.uk/admissions/scholarships/apply">
    </div>

    <!-- Contact Information -->
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Contact Email</label>
        <input type="email" class="form-control" name="contact_email" value="<?php echo $g('contact_email'); ?>" placeholder="scholarships@university.edu">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Contact Phone</label>
        <input type="text" class="form-control" name="contact_phone" value="<?php echo $g('contact_phone'); ?>" placeholder="+44 1865 270000">
    </div>

    <!-- Status: Draft / Published / Closed -->
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Status</label>
        <select class="form-select" name="status">
            <option value="Active" <?php echo ($currentStatus === 'Active') ? 'selected' : ''; ?>>Published (Live)</option>
            <option value="Draft" <?php echo ($currentStatus === 'Draft') ? 'selected' : ''; ?>>Draft</option>
            <option value="Closed" <?php echo ($currentStatus === 'Closed') ? 'selected' : ''; ?>>Closed</option>
        </select>
    </div>
</div>
