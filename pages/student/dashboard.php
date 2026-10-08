<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireLogin();
header("Location: " . BASE_URL . "/pages/dashboard.php");
exit;
