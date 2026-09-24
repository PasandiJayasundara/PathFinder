<?php
/**
 * PathFinder - Registration Page (Student & Graduate Dual Mode)
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $role = $_SESSION['user_role'] ?? 'student';
    redirect($role . '/dashboard.php');
}

$pdo = get_db();
$universities = get_all_universities($pdo);
$degrees = get_all_degrees($pdo);
$industries = get_all_industries($pdo);

$initialRole = ($_GET['role'] ?? '') === 'graduate' ? 'graduate' : 'student';

$pageTitle = "Create an Account – PathFinder";
$activeNav = '';
require_once __DIR__ . '/includes/header.php';
?>

<div style="background-color: #FAF8F5; padding: 60px 0 90px;">
  <div class="container" style="max-width: 680px;">
    
    <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 40px; box-shadow: var(--shadow-card);">
      
      <!-- Brand & Title -->
      <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -0.025em; margin-bottom: 8px;">Join PathFinder LK</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
          Connect with authentic Sri Lankan graduate journeys and expand your career horizons.
        </p>
      </div>

      <!-- Role Selector Tabs -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: var(--bg-subtle); padding: 6px; border-radius: var(--radius-md); margin-bottom: 32px;">
        <button 
          type="button" 
          class="btn <?= $initialRole === 'student' ? 'btn-teal' : 'btn-outline' ?>" 
          id="tabBtnStudent" 
          onclick="switchRole('student')"
          style="border-radius: var(--radius-sm); border: none; font-size: 0.92rem;"
        >
          <i class="fa-solid fa-graduation-cap"></i> I am a Student
        </button>
        <button 
          type="button" 
          class="btn <?= $initialRole === 'graduate' ? 'btn-teal' : 'btn-outline' ?>" 
          id="tabBtnGraduate" 
          onclick="switchRole('graduate')"
          style="border-radius: var(--radius-sm); border: none; font-size: 0.92rem;"
        >
          <i class="fa-solid fa-user-tie"></i> I am a Graduate / Mentor
        </button>
      </div>

      <!-- Main Registration Form -->
      <form action="<?= BASE_URL ?>/actions/auth/register.php" method="POST" id="registerForm">
        <?= csrf_field() ?>
        <input type="hidden" name="role" id="selectedRole" value="<?= e($initialRole) ?>">

        <!-- Common Account Credentials -->
        <div style="margin-bottom: 28px;">
          <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light); color: var(--primary-teal-darker);">
            1. Account Information
          </h3>

          <div class="form-group">
            <label class="form-label" for="full_name">Full Name *</label>
            <input type="text" name="full_name" id="full_name" class="form-control" placeholder="e.g. Kavindi Perera" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="e.g. student@uom.lk or personal email" required>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label" for="password">Password *</label>
              <div class="password-toggle-wrapper">
                <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
                <button type="button" class="password-toggle-btn" aria-label="Toggle password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="confirm_password">Confirm Password *</label>
              <div class="password-toggle-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required minlength="8">
                <button type="button" class="password-toggle-btn" aria-label="Toggle password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Academic Information (Common) -->
        <div style="margin-bottom: 28px;">
          <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light); color: var(--primary-teal-darker);">
            2. Academic Background
          </h3>

          <div class="form-group">
            <label class="form-label" for="university_id">University *</label>
            <select name="university_id" id="university_id" class="form-control" required>
              <option value="">-- Select Sri Lankan University --</option>
              <?php foreach ($universities as $uni): ?>
                <option value="<?= $uni['id'] ?>"><?= e($uni['name']) ?> (<?= e($uni['short_name']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="degree_id">Degree / Study Program *</label>
            <select name="degree_id" id="degree_id" class="form-control" required>
              <option value="">-- Select Degree / Program --</option>
              <?php foreach ($degrees as $deg): ?>
                <option value="<?= $deg['id'] ?>" data-uni-id="<?= $deg['university_id'] ?>">
                  <?= e($deg['name']) ?> (<?= e($deg['uni_short_name']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Role-Specific Fields: Student Section -->
        <div id="studentFields" style="display: <?= $initialRole === 'student' ? 'block' : 'none' ?>; margin-bottom: 28px;">
          <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light); color: var(--primary-teal-darker);">
            3. Student Details
          </h3>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label" for="current_year">Current Academic Year *</label>
              <select name="current_year" id="current_year" class="form-control">
                <option value="1st Year">1st Year Undergraduate</option>
                <option value="2nd Year">2nd Year Undergraduate</option>
                <option value="3rd Year" selected>3rd Year (Internship Seeking)</option>
                <option value="4th Year">4th / Final Year</option>
                <option value="Recent Graduate">Recently Graduated</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="target_industry">Target Career Field</label>
              <select name="target_industry" id="target_industry" class="form-control">
                <option value="">-- Select Desired Industry --</option>
                <?php foreach ($industries as $ind): ?>
                  <option value="<?= e($ind['name']) ?>"><?= e($ind['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="student_bio">Short Bio / Career Aspirations</label>
            <textarea name="student_bio" id="student_bio" class="form-control" rows="3" placeholder="Tell us briefly about your career ambitions or questions you want to ask alumni..."></textarea>
          </div>
        </div>

        <!-- Role-Specific Fields: Graduate Section -->
        <div id="graduateFields" style="display: <?= $initialRole === 'graduate' ? 'block' : 'none' ?>; margin-bottom: 28px;">
          <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light); color: var(--primary-teal-darker);">
            3. Professional Career Details
          </h3>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label" for="current_job_title">Current Job Title *</label>
              <input type="text" name="current_job_title" id="current_job_title" class="form-control" placeholder="e.g. Associate Software Engineer">
            </div>

            <div class="form-group">
              <label class="form-label" for="company">Company / Organization *</label>
              <input type="text" name="company" id="company" class="form-control" placeholder="e.g. IFS, WSO2, MAS Holdings">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label" for="industry_id">Industry Sector *</label>
              <select name="industry_id" id="industry_id" class="form-control">
                <option value="">-- Select Industry --</option>
                <?php foreach ($industries as $ind): ?>
                  <option value="<?= $ind['id'] ?>"><?= e($ind['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="graduation_year">Graduation Year *</label>
              <select name="graduation_year" id="graduation_year" class="form-control">
                <?php for ($y = date('Y'); $y >= 2012; $y--): ?>
                  <option value="<?= $y ?>" <?= $y == 2022 ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="linkedin_url">LinkedIn Profile URL</label>
            <input type="url" name="linkedin_url" id="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/yourprofile">
          </div>

          <div class="form-group" style="background: var(--bg-subtle); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
              <input type="checkbox" name="is_mentor_available" value="1" checked style="width: 18px; height: 18px; accent-color: var(--primary-teal);">
              <span>Open to 1-on-1 Chat / Mentorship for Undergraduates</span>
            </label>
            <div class="form-help" style="margin-left: 28px;">
              Students can send you structured mentorship requests for 20-30 min calls or CV reviews.
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="mentor_headline">Mentor Headline / Catchphrase</label>
            <input type="text" name="mentor_headline" id="mentor_headline" class="form-control" placeholder="e.g. Happy to help undergraduates with Java backend interviews and CV reviews.">
          </div>
        </div>

        <button type="submit" class="btn btn-teal btn-lg" style="width: 100%;">
          Create Account &rarr;
        </button>
      </form>

      <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-light); font-size: 0.9rem; color: var(--text-muted);">
        Already have an account? 
        <a href="<?= BASE_URL ?>/login.php" style="color: var(--primary-teal); font-weight: 700;">
          Sign in here
        </a>
      </div>

    </div>

  </div>
</div>

<script>
function switchRole(role) {
  document.getElementById('selectedRole').value = role;
  const btnStudent = document.getElementById('tabBtnStudent');
  const btnGrad = document.getElementById('tabBtnGraduate');
  const studentFields = document.getElementById('studentFields');
  const gradFields = document.getElementById('graduateFields');

  if (role === 'graduate') {
    btnGrad.className = 'btn btn-teal';
    btnStudent.className = 'btn btn-outline';
    gradFields.style.display = 'block';
    studentFields.style.display = 'none';

    // Toggle required fields
    document.getElementById('current_job_title').setAttribute('required', 'required');
    document.getElementById('company').setAttribute('required', 'required');
    document.getElementById('industry_id').setAttribute('required', 'required');
  } else {
    btnStudent.className = 'btn btn-teal';
    btnGrad.className = 'btn btn-outline';
    studentFields.style.display = 'block';
    gradFields.style.display = 'none';

    document.getElementById('current_job_title').removeAttribute('required');
    document.getElementById('company').removeAttribute('required');
    document.getElementById('industry_id').removeAttribute('required');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
