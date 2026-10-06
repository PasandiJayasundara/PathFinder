<?php
require_once 'includes/auth.php';

// Logout user
session_destroy();
header("Location: admin_login.php");
exit;
?>
