<?php
/**
 * PathFinder - Helper Functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Escape HTML output securely against XSS
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generate or get CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify CSRF token from POST request
 */
function verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

/**
 * Check if flash message exists
 */
function has_flash(): bool {
    return !empty($_SESSION['flash']);
}

/**
 * Get and clear flash message
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Safe HTTP redirect
 */
function redirect(string $path): void {
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header("Location: $path");
    } else {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
        header("Location: $url");
    }
    exit;
}

/**
 * Truncate string gracefully
 */
function truncate(?string $text, int $length = 140, string $append = '...'): string {
    if ($text === null) return '';
    $text = trim($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $append;
}

/**
 * Relative time formatter
 */
function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    if ($diff < 2592000) return floor($diff / 604800) . 'w ago';
    return date('M j, Y', $time);
}

/**
 * Check if story is bookmarked by a user
 */
function is_bookmarked(PDO $pdo, int $userId, int $storyId): bool {
    static $cache = [];
    $key = "{$userId}_{$storyId}";
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $stmt = $pdo->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND story_id = ?");
    $stmt->execute([$userId, $storyId]);
    $cache[$key] = (bool)$stmt->fetchColumn();
    return $cache[$key];
}

/**
 * Record view count with IP deduplication within 24h
 */
function record_story_view(PDO $pdo, int $storyId): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    // Check if viewed within last 12 hours
    $stmt = $pdo->prepare("
        SELECT id FROM story_views 
        WHERE story_id = ? AND ip_address = ? AND viewed_at > (NOW() - INTERVAL 12 HOUR)
        LIMIT 1
    ");
    $stmt->execute([$storyId, $ip]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare("INSERT INTO story_views (story_id, ip_address) VALUES (?, ?)");
        $ins->execute([$storyId, $ip]);

        $up = $pdo->prepare("UPDATE career_stories SET views_count = views_count + 1 WHERE id = ?");
        $up->execute([$storyId]);
    }
}

/**
 * Generate Avatar URL or SVG initials placeholder
 */
function get_avatar_url(?string $avatarPath, string $name = 'User'): string {
    if (!empty($avatarPath)) {
        // If it starts with http, return directly
        if (strpos($avatarPath, 'http://') === 0 || strpos($avatarPath, 'https://') === 0) {
            return $avatarPath;
        }
        // If it's a relative path to an existing file
        $fullPath = ROOT_DIR . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $avatarPath);
        if (file_exists($fullPath)) {
            return BASE_URL . '/' . ltrim($avatarPath, '/');
        }
    }

    // Generate clean SVG initials avatar
    $parts = explode(' ', trim($name));
    $initials = '';
    if (count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
    if (empty($initials)) $initials = 'PF';

    // Pick consistent color based on name hash
    $colors = [
        ['#0F766E', '#0A5852'], // teal
        ['#2563EB', '#1D4ED8'], // blue
        ['#D97706', '#B45309'], // amber
        ['#7C3AED', '#6D28D9'], // purple
        ['#059669', '#047857'], // emerald
        ['#DC2626', '#B91C1C'], // red
    ];
    $idx = abs(crc32($name)) % count($colors);
    $bg = $colors[$idx][0];

    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%s"/><text x="50" y="55" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="Inter, -apple-system, sans-serif" font-size="38" font-weight="700">%s</text></svg>',
        $bg,
        htmlspecialchars($initials)
    );

    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}

/**
 * Retrieve all industries with actual story counts from database
 */
function get_all_industries(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT i.*, COUNT(cs.id) as story_count 
        FROM industries i
        LEFT JOIN career_stories cs ON i.id = cs.industry_id AND cs.status = 'approved'
        GROUP BY i.id
        ORDER BY i.display_order ASC, i.name ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Retrieve all universities
 */
function get_all_universities(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM universities ORDER BY id ASC");
    return $stmt->fetchAll();
}

/**
 * Retrieve all degrees with university info
 */
function get_all_degrees(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT d.*, u.short_name as uni_short_name, u.name as uni_name 
        FROM degrees d
        JOIN universities u ON d.university_id = u.id
        ORDER BY u.name ASC, d.name ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Retrieve system-wide statistics
 */
function get_platform_stats(PDO $pdo): array {
    $userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $gradCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'graduate' AND status = 'active'")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active'")->fetchColumn();
    $storyCount = $pdo->query("SELECT COUNT(*) FROM career_stories WHERE status = 'approved'")->fetchColumn();
    $mentorCount = $pdo->query("SELECT COUNT(*) FROM graduate_profiles WHERE is_mentor_available = 1")->fetchColumn();
    $reqCount = $pdo->query("SELECT COUNT(*) FROM mentorship_requests")->fetchColumn();

    return [
        'users' => (int)$userCount,
        'graduates' => (int)$gradCount,
        'students' => (int)$studentCount,
        'stories' => (int)$storyCount,
        'mentors' => (int)$mentorCount,
        'requests' => (int)$reqCount,
        // Platform display counts matching reference
        'display_students' => '4,500+',
        'display_stories' => '600+',
    ];
}
