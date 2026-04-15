<?php
// studyflow/stud/logout.php
require_once("config.php"); // Ensure config is loaded for BASE_URL
session_start();
$_SESSION = array();
session_destroy();

// Use the dynamic BASE_URL to redirect to the main landing page
header("location: " . BASE_URL . "/landing.php");
exit;