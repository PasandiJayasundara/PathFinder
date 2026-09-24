<?php
/**
 * PathFinder - Authentication & Access Control
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if a user is currently logged in
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Get current authenticated user details from database
 */
function current_user(): ?array {
    static $user = null;

    if ($user !== null) {
        return $user;
    }

    if (!is_logged_in()) {
        return null;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        // User was disabled or deleted
        logout_user();
        return null;
    }

    // Attach role-specific profile ID
    if ($user['role'] === 'student') {
        $st = $pdo->prepare("SELECT id, university_id, degree_id, current_year, target_industry FROM student_profiles WHERE user_id = ?");
        $st->execute([$user['id']]);
        $user['profile'] = $st->fetch() ?: [];
    } elseif ($user['role'] === 'graduate') {
        $gr = $pdo->prepare("SELECT id, university_id, degree_id, graduation_year, current_job_title, company, industry_id, is_mentor_available, mentor_headline FROM graduate_profiles WHERE user_id = ?");
        $gr->execute([$user['id']]);
        $user['profile'] = $gr->fetch() ?: [];
    }

    return $user;
}

/**
 * Restrict page access to logged-in users only
 */
function require_login(string $redirectUrl = ''): void {
    if (!is_logged_in()) {
        set_flash('info', 'Please sign in to access this page.');
        $target = $redirectUrl ?: $_SERVER['REQUEST_URI'];
        redirect('login.php?redirect=' . urlencode($target));
    }
}

/**
 * Restrict page access to specific roles ('student', 'graduate', 'admin')
 */
function require_role($roles): void {
    require_login();

    $roles = is_array($roles) ? $roles : [$roles];
    $currentRole = $_SESSION['user_role'] ?? '';

    if (!in_array($currentRole, $roles, true)) {
        set_flash('error', 'You do not have permission to access that area.');
        // Redirect to appropriate role dashboard
        if ($currentRole === 'admin') {
            redirect('admin/dashboard.php');
        } elseif ($currentRole === 'graduate') {
            redirect('graduate/dashboard.php');
        } else {
            redirect('student/dashboard.php');
        }
    }
}

/**
 * Authenticate and log in user session
 */
function login_user(array $user): void {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['avatar_url'] = $user['avatar_url'];

    // Update last activity / updated_at
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
}

/**
 * Cleanly logout user session
 */
function logout_user(): void {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
