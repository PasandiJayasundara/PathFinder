<?php
/**
 * PathFinder - Logout Handler
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash('info', 'You have been signed out successfully.');
redirect('login.php');
