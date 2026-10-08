<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Browse Internships';
$type = 'Internship';

$filters = [
    'search'       => trim($_GET['search'] ?? ''),
    'organization' => trim($_GET['organization'] ?? ''),
    'location'     => trim($_GET['location'] ?? ''),
    'work_type'    => trim($_GET['work_type'] ?? ''),
    'job_type'     => '',
    'paid_status'  => trim($_GET['paid_status'] ?? ''),
    'category_id'  => trim($_GET['category_id'] ?? ''),
];

$postings   = getPostings($type, $filters, true);
$categories = getAllCategories();
$locations  = getDistinctPostingLocations();

include '../includes/header.php';
include '../includes/posting-list.php';
include '../includes/footer.php';
