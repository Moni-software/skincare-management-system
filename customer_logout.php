<?php

session_start();

/* Remove all customer session variables */
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);

/* Destroy the session */
session_destroy();

/* Prevent browser from showing cached logged-in pages */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* Redirect to login */
header("Location: login.php");
exit();

?>