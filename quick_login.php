<?php
/**
 * PathFinder - Instant Quick Login Portal
 * Allows instant 1-click login as Student, Graduate/Mentor, or Administrator
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = get_db();

// Handle instant 1-click login request
$targetRole = $_GET['as'] ?? $_POST['as'] ?? '';

if (!empty($targetRole)) {
    $emailMap = [
        'student'   => 'student@pathfinder.lk',
        'graduate'  => 'graduate@pathfinder.lk',
        'admin'     => 'admin@pathfinder.lk',
        'kavindi'   => 'kavindi@pathfinder.lk',
        'janith'    => 'janith@pathfinder.lk',
        'nilukshi'  => 'nilukshi@pathfinder.lk',
        'akeel'     => 'akeel@pathfinder.lk',
        'dinuk'     => 'dinuk@pathfinder.lk',
        'thilini'   => 'thilini@pathfinder.lk'
    ];

    $email = $emailMap[$targetRole] ?? null;

    if ($email) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            login_user($user);
            set_flash('success', "Logged in successfully as {$user['full_name']} ({$user['role']})!");

            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } elseif ($user['role'] === 'graduate') {
                redirect('graduate/dashboard.php');
            } else {
                redirect('student/dashboard.php');
            }
        }
    }
}

$pageTitle = "Instant Login Portal – PathFinder";
$activeNav = '';
require_once __DIR__ . '/includes/header.php';
?>

<div style="background-color: #FAF8F5; padding: 60px 0 90px; min-height: 80vh;">
  <div class="container" style="max-width: 900px;">
    
    <!-- Title -->
    <div style="text-align: center; margin-bottom: 40px;">
      <div class="brand-logo-icon" style="margin: 0 auto 16px; width: 52px; height: 52px; font-size: 1.5rem;">
        <i class="fa-solid fa-compass"></i>
      </div>
      <h1 style="font-size: 2.4rem; font-weight: 800; letter-spacing: -0.025em; margin-bottom: 8px;">
        PathFinder Instant Login Portal
      </h1>
      <p style="color: var(--text-muted); font-size: 1.05rem;">
        Click any role below to instantly log in and access its dashboard with zero passwords required.
      </p>
    </div>

    <!-- 3 Main Role Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px;">
      
      <!-- Student Card -->
      <div style="background: #FFFFFF; border: 2px solid #E2E8F0; border-radius: var(--radius-lg); padding: 32px 24px; text-align: center; box-shadow: var(--shadow-card); transition: var(--transition); display: flex; flex-direction: column;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 16px;">
          <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 6px;">Student Role</h2>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary-teal); margin-bottom: 12px;">Malith Perera (SLIIT)</div>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 24px; flex: 1;">
          Browse authentic career stories, bookmark favorites, find mentors, and track 1-on-1 mentorship requests.
        </p>
        <a href="<?= BASE_URL ?>/quick_login.php?as=student" class="btn btn-teal" style="width: 100%;">
          Log In as Student <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

      <!-- Graduate / Mentor Card -->
      <div style="background: #FFFFFF; border: 2px solid #CCFBF1; border-radius: var(--radius-lg); padding: 32px 24px; text-align: center; box-shadow: var(--shadow-card); transition: var(--transition); display: flex; flex-direction: column;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-teal-light); color: var(--primary-teal); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 16px;">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 6px;">Graduate Mentor</h2>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary-teal); margin-bottom: 12px;">Lead Systems Engineer @ LSEG</div>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 24px; flex: 1;">
          Write stories with interactive timeline milestones, toggle mentorship availability, and accept student requests.
        </p>
        <a href="<?= BASE_URL ?>/quick_login.php?as=graduate" class="btn btn-teal" style="width: 100%;">
          Log In as Mentor <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

      <!-- Administrator Card -->
      <div style="background: #FFFFFF; border: 2px solid #FEF3C7; border-radius: var(--radius-lg); padding: 32px 24px; text-align: center; box-shadow: var(--shadow-card); transition: var(--transition); display: flex; flex-direction: column;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 16px;">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 6px;">Administrator</h2>
        <div style="font-size: 0.85rem; font-weight: 700; color: #D97706; margin-bottom: 12px;">Full Platform Authority</div>
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 24px; flex: 1;">
          Manage users, approve or reject stories, feature stories on homepage, and manage universities &amp; industries.
        </p>
        <a href="<?= BASE_URL ?>/quick_login.php?as=admin" class="btn btn-teal" style="width: 100%; background: #D97706; border-color: #D97706;">
          Log In as Admin <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

    </div>

    <!-- Specific Seeded Mentors Row -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
      <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 16px; color: var(--text-dark);">
        ✨ Log In as Specific Seeded Mentors:
      </h3>
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;">
        <a href="<?= BASE_URL ?>/quick_login.php?as=kavindi" class="btn btn-outline btn-sm" style="display: flex; flex-direction: column; padding: 12px; height: auto; text-align: center;">
          <strong>Kavindi Perera</strong>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Senior UX @ Sysco LABS</span>
        </a>

        <a href="<?= BASE_URL ?>/quick_login.php?as=janith" class="btn btn-outline btn-sm" style="display: flex; flex-direction: column; padding: 12px; height: auto; text-align: center;">
          <strong>Janith Rathnayake</strong>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Associate SE @ IFS</span>
        </a>

        <a href="<?= BASE_URL ?>/quick_login.php?as=nilukshi" class="btn btn-outline btn-sm" style="display: flex; flex-direction: column; padding: 12px; height: auto; text-align: center;">
          <strong>Nilukshi Fernando</strong>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Solutions Architect @ WSO2</span>
        </a>

        <a href="<?= BASE_URL ?>/quick_login.php?as=akeel" class="btn btn-outline btn-sm" style="display: flex; flex-direction: column; padding: 12px; height: auto; text-align: center;">
          <strong>Akeel Mansoor</strong>
          <span style="font-size: 0.75rem; color: var(--text-muted);">Product Manager @ PickMe</span>
        </a>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
