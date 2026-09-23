<?php
/**
 * PathFinder - Login Page
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $role = $_SESSION['user_role'] ?? 'student';
    redirect($role . '/dashboard.php');
}

$pageTitle = "Sign In to PathFinder";
$activeNav = '';
$redirectTarget = $_GET['redirect'] ?? '';
require_once __DIR__ . '/includes/header.php';
?>

<div style="background-color: #FAF8F5; padding: 60px 0 90px; min-height: 80vh; display: flex; align-items: center;">
  <div class="container" style="max-width: 480px;">
    
    <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 40px; box-shadow: var(--shadow-card);">
      
      <!-- Brand & Heading -->
      <div style="text-align: center; margin-bottom: 28px;">
        <div class="brand-logo-icon" style="margin: 0 auto 14px; width: 48px; height: 48px; font-size: 1.4rem;">
          <i class="fa-solid fa-compass"></i>
        </div>
        <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 6px;">Welcome Back</h1>
        <p style="color: var(--text-muted); font-size: 0.92rem;">Sign in to your PathFinder account to continue.</p>
      </div>

      <!-- Quick Demo Credentials Selector (Convenient for University Grading) -->
      <div style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px;">
        <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 8px; text-align: center;">
          ⚡ Quick Demo Login (One-Click Fill)
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
          <button type="button" class="btn btn-outline btn-sm" onclick="fillCredentials('student@pathfinder.lk', 'Password123!')" style="font-size: 0.76rem; padding: 6px 4px;">
            Student
          </button>
          <button type="button" class="btn btn-outline btn-sm" onclick="fillCredentials('graduate@pathfinder.lk', 'Password123!')" style="font-size: 0.76rem; padding: 6px 4px;">
            Graduate
          </button>
          <button type="button" class="btn btn-outline btn-sm" onclick="fillCredentials('admin@pathfinder.lk', 'Password123!')" style="font-size: 0.76rem; padding: 6px 4px;">
            Admin
          </button>
        </div>
      </div>

      <!-- Login Form -->
      <form action="<?= BASE_URL ?>/actions/auth/login.php" method="POST" id="loginForm">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">

        <div class="form-group">
          <label class="form-label" for="email">University or Personal Email *</label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control" 
            placeholder="e.g. yourname@gmail.com" 
            required
            autocomplete="email"
          >
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label class="form-label" for="password" style="margin-bottom: 0;">Password *</label>
            <span style="font-size: 0.78rem; color: var(--text-muted);">Default: Password123!</span>
          </div>
          <div class="password-toggle-wrapper">
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="Enter your password" 
              required
              autocomplete="current-password"
            >
            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-teal" style="width: 100%; padding: 13px; font-size: 1rem; margin-top: 10px;">
          Sign In <i class="fa-solid fa-arrow-right"></i>
        </button>
      </form>

      <!-- Footer Links -->
      <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-light); font-size: 0.9rem; color: var(--text-muted);">
        Don't have an account yet? 
        <a href="<?= BASE_URL ?>/register.php" style="color: var(--primary-teal); font-weight: 700;">
          Create an Account
        </a>
      </div>

    </div>

  </div>
</div>

<script>
function fillCredentials(email, password) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = password;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
