<?php
/**
 * PathFinder - Public Mentor Profile
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = get_db();
$mentorId = (int)($_GET['id'] ?? 0);

if (!$mentorId) {
    redirect('mentors.php');
}

$stmt = $pdo->prepare("
    SELECT gp.*, 
           u.id as user_id, u.full_name as mentor_name, u.avatar_url, u.email,
           uni.name as uni_name, uni.short_name as uni_short_name,
           deg.name as degree_name,
           ind.name as industry_name
    FROM graduate_profiles gp
    JOIN users u ON gp.user_id = u.id
    LEFT JOIN universities uni ON gp.university_id = uni.id
    LEFT JOIN degrees deg ON gp.degree_id = deg.id
    LEFT JOIN industries ind ON gp.industry_id = ind.id
    WHERE gp.id = ? AND u.status = 'active'
    LIMIT 1
");
$stmt->execute([$mentorId]);
$mentor = $stmt->fetch();

if (!$mentor) {
    set_flash('error', 'Mentor profile not found.');
    redirect('mentors.php');
}

// Fetch skills
$sStmt = $pdo->prepare("
    SELECT s.name 
    FROM graduate_skills gs
    JOIN skills s ON gs.skill_id = s.id
    WHERE gs.graduate_id = ?
");
$sStmt->execute([$mentorId]);
$skills = $sStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch mentor's career stories
$stStmt = $pdo->prepare("
    SELECT * FROM career_stories 
    WHERE graduate_id = ? AND status = 'approved' 
    ORDER BY id DESC
");
$stStmt->execute([$mentorId]);
$stories = $stStmt->fetchAll();

$pageTitle = $mentor['mentor_name'] . ' – Mentor Profile';
$activeNav = 'mentors';
require_once __DIR__ . '/includes/header.php';
?>

<div style="background-color: #FAF8F5; padding: 48px 0 90px;">
  <div class="container">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/index.php">Home</a> &gt; 
      <a href="<?= BASE_URL ?>/mentors.php">Mentors</a> &gt; 
      <span style="color: var(--text-dark);"><?= e($mentor['mentor_name']) ?></span>
    </div>

    <div style="display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; align-items: start;">
      
      <!-- Left Column: Biography, Skills, & Stories -->
      <div>
        <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-sm); margin-bottom: 32px;">
          <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px;">
            <img src="<?= e(get_avatar_url($mentor['avatar_url'], $mentor['mentor_name'])) ?>" alt="<?= e($mentor['mentor_name']) ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
            <div>
              <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h1 style="font-size: 1.85rem; font-weight: 800;"><?= e($mentor['mentor_name']) ?></h1>
                <span class="preview-badge-pill badge-open-chat"><?= e($mentor['mentor_badge'] ?: 'Open to Chat') ?></span>
              </div>
              <div style="font-size: 1.05rem; font-weight: 600; color: var(--primary-teal);">
                <?= e($mentor['current_job_title']) ?> @ <?= e($mentor['company']) ?>
              </div>
              <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                <?= e($mentor['years_experience']) ?>+ years experience &bull; Rated <?= number_format($mentor['rating'], 1) ?> ★
              </div>
            </div>
          </div>

          <?php if (!empty($mentor['mentor_headline'])): ?>
            <div style="background: var(--bg-subtle); padding: 14px 18px; border-radius: var(--radius-sm); font-style: italic; font-size: 0.95rem; color: var(--text-body); margin-bottom: 24px;">
              &ldquo;<?= e($mentor['mentor_headline']) ?>&rdquo;
            </div>
          <?php endif; ?>

          <!-- Bio -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 10px;">About Me</h3>
            <p style="font-size: 0.95rem; color: var(--text-body); line-height: 1.7; white-space: pre-line;">
              <?= e($mentor['bio'] ?: 'Passionate alumni mentor dedicated to guiding Sri Lankan undergraduates through their early career decisions and technical challenges.') ?>
            </p>
          </div>

          <!-- Skills & Domains -->
          <?php if (!empty($skills)): ?>
            <div>
              <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 12px;">Core Skills &amp; Domain Expertise</h3>
              <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <?php foreach ($skills as $skill): ?>
                  <span class="story-tag-item" style="font-size: 0.85rem; padding: 5px 12px;"><?= e($skill) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Career Stories by this mentor -->
        <div>
          <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px;">Career Stories by <?= e($mentor['mentor_name']) ?></h2>
          <?php if (!empty($stories)): ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
              <?php foreach ($stories as $st): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm);">
                  <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 8px;">
                    <a href="<?= BASE_URL ?>/story.php?id=<?= $st['id'] ?>" style="color: var(--text-dark);">
                      &ldquo;<?= e($st['title']) ?>&rdquo;
                    </a>
                  </h3>
                  <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 14px;">
                    <?= e(truncate($st['excerpt'], 160)) ?>
                  </p>
                  <a href="<?= BASE_URL ?>/story.php?id=<?= $st['id'] ?>" class="story-read-link">
                    Read Full Story &rarr;
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: center; color: var(--text-muted);">
              No published stories yet.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right Column: Mentorship Request Panel -->
      <aside style="position: sticky; top: 100px;">
        <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
          <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 12px;">Request 1-on-1 Guidance</h3>
          <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px;">
            Submit a mentorship request to <?= e($mentor['mentor_name']) ?> for guidance on your CV, interview prep, or career steps.
          </p>

          <?php if ($mentor['is_mentor_available']): ?>
            <?php if (is_logged_in()): ?>
              <?php if ($_SESSION['user_id'] != $mentor['user_id']): ?>
                <form action="<?= BASE_URL ?>/actions/mentorship/request.php" method="POST">
                  <?= csrf_field() ?>
                  <input type="hidden" name="mentor_id" value="<?= $mentor['id'] ?>">
                  <input type="hidden" name="redirect_url" value="<?= e($_SERVER['REQUEST_URI']) ?>">

                  <div class="form-group">
                    <label class="form-label" for="topic">Topic / Goal *</label>
                    <input type="text" name="topic" id="topic" class="form-control" placeholder="e.g. CV Review / Internship Guidance" required>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="preferred_communication">Preferred Communication *</label>
                    <select name="preferred_communication" id="preferred_communication" class="form-control" required>
                      <option value="Google Meet">Google Meet</option>
                      <option value="WhatsApp">WhatsApp</option>
                      <option value="Email">Email</option>
                      <option value="Zoom">Zoom</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="message">Your Message *</label>
                    <textarea name="message" id="message" class="form-control form-control-textarea" rows="4" placeholder="Mention your university, year, and specific questions you need help with..." required></textarea>
                  </div>

                  <button type="submit" class="btn btn-teal" style="width: 100%; margin-top: 10px;">
                    Send Mentorship Request
                  </button>
                </form>
              <?php else: ?>
                <div style="background: var(--bg-subtle); padding: 14px; border-radius: var(--radius-sm); font-size: 0.88rem; text-align: center;">
                  This is your own mentor profile!
                  <div style="margin-top: 10px;">
                    <a href="<?= BASE_URL ?>/graduate/profile.php" class="btn btn-outline btn-sm">Edit Profile</a>
                  </div>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-teal" style="width: 100%;">
                Sign in to Request Mentorship
              </a>
            <?php endif; ?>
          <?php else: ?>
            <div style="text-align: center; color: var(--text-muted); font-size: 0.88rem; padding: 12px; background: var(--bg-subtle); border-radius: var(--radius-sm);">
              Mentor is currently taking a break from requests.
            </div>
          <?php endif; ?>

          <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-light); font-size: 0.82rem; color: var(--text-muted);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
              <i class="fa-solid fa-graduation-cap" style="color: var(--primary-teal);"></i>
              <span><?= e($mentor['uni_name']) ?></span>
            </div>
            <?php if (!empty($mentor['degree_name'])): ?>
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <i class="fa-solid fa-book" style="color: var(--primary-teal);"></i>
                <span><?= e($mentor['degree_name']) ?></span>
              </div>
            <?php endif; ?>
            <?php if (!empty($mentor['linkedin_url'])): ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-brands fa-linkedin" style="color: #0A66C2;"></i>
                <a href="<?= e($mentor['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: #0A66C2; font-weight: 500;">
                  LinkedIn Profile &rarr;
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </aside>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
