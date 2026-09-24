<?php
/**
 * PathFinder - Configuration
 */

// Prevent multiple inclusions
if (!defined('PATHFINDER_APP')) {
    define('PATHFINDER_APP', true);
}

// Session configuration
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Application Constants
define('APP_NAME', 'PathFinder');
define('APP_TAGLINE', "Sri Lanka's Career Community for Students & Grads");
define('APP_VERSION', '1.0.0');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'pathfinder_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Directories
define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles');

// Detect Base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Calculate base URL relative to root
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$currentDir = str_replace('\\', '/', ROOT_DIR);
$relativePath = str_replace($docRoot, '', $currentDir);
$baseUrl = rtrim($protocol . $host . '/' . ltrim($relativePath, '/'), '/');

// If running at root, ensure baseUrl is formatted properly
if (empty($relativePath) || $relativePath === '/') {
    $baseUrl = rtrim($protocol . $host, '/');
}

define('BASE_URL', $baseUrl);

// Error reporting (clean display in production, informative in development)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Errors logged to PHP log rather than raw display
ini_set('log_errors', '1');
