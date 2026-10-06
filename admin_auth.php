<?php
// Admin Authentication Functions

function is_admin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function require_admin() {
    if (!is_admin()) {
        header("Location: admin_login.php");
        exit;
    }
}

function check_admin_permission($permission) {
    global $mysqli;
    $user_id = $_SESSION['user_id'];
    
    $result = $mysqli->query("
        SELECT * FROM admin_users WHERE user_id = $user_id
    ");
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $admin = $result->fetch_assoc();
    $permissions = json_decode($admin['permissions'] ?? '[]', true);
    
    return in_array($permission, $permissions);
}

function log_admin_action($action, $target_type, $target_id, $details = '') {
    global $mysqli;
    $admin_id = $_SESSION['user_id'];
    $details = sanitize($details);
    
    $mysqli->query("
        INSERT INTO admin_logs (admin_id, action, target_type, target_id, details)
        VALUES ($admin_id, '$action', '$target_type', $target_id, '$details')
    ");
}

?>
