<?php
/**
 * PathFinder - Mentor Directory
 */

$pageTitle = "Browse Verified Alumni Mentors";
$activeNav = 'mentors';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

$q = trim($_GET['q'] ?? '');
$universityId = !empty($_GET['university']) ? (int)$_GET['university'] : null;
$industryId = !empty($_GET['industry']) ? (int)$_GET['industry'] : null;

$sql = "
    SELECT gp.*, 
           u.full_name as mentor_name, u.avatar_url, u.email,
           uni.name as uni_name, uni.short_name as uni_short_name,
           deg.name as degree_name,
           ind.name as industry_name
    FROM graduate_profiles gp
    JOIN users u ON gp.user_id = u.id
    LEFT JOIN universities uni ON gp.university_id = uni.id
    LEFT JOIN degrees deg ON gp.degree_id = deg.id
    LEFT JOIN industries ind ON gp.industry_id = ind.id
    WHERE gp.is_mentor_available = 1 AND u.status = 'active'
";

$params = [];

if (!empty($q)) {
    $sql .= " AND (u.full_name LIKE ? OR gp.current_job_title LIKE ? OR gp.company LIKE ? OR uni.name LIKE ?)";
    $like = "%$q%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($universityId) {
    $sql .= " AND gp.university_id = ?";
    $params[] = $universityId;
}

if ($industryId) {
    $sql .= " AND gp.industry_id = ?";
    $params[] = $industryId;
}

$sql .= " ORDER BY gp.rating DESC, gp.years_experience DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mentors = $stmt->fetchAll();

// Fetch skills for these mentors
$mentorIds = array_column($mentors, 'id');
$skillsByMentor = [];
if (!empty($mentorIds)) {
    $inClause = implode(',', array_fill(0, count($mentorIds), '?'));
    $sStmt = $pdo->prepare("
        SELECT gs.graduate_id, s.name 
        FROM graduate_skills gs
        JOIN skills s ON gs.skill_id = s.id
        WHERE gs.graduate_id IN ($inClause)
    ");
    $sStmt->execute($mentorIds);
    while ($r = $sStmt->fetch()) {
        $skillsByMentor[$r['graduate_id']][] = $r['name'];
    }
}

$universities = get_all_universities($pdo);
$industries = get_all_industries($pdo);
?>

<div style="background-color: #FAF8F5; padding: 48px 0 90px;">
  <div class="container">
    <!-- Header -->
    <div style="margin-bottom: 36px;">
      <span class="section-tag">1-ON-1 GUIDANCE</span>
      <h1 style="font-size: 2.4rem; letter-spacing: -0.025em; margin-bottom: 8px;">Alumni Mentor Directory</h1>
      <p style="color: var(--text-muted); font-size: 1.05rem;">
        Connect directly with verified Sri Lankan graduates working at top local and global companies for CV reviews and career advice.
      </p>
    </div>

    <!-- Filters -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
      <form action="<?= BASE_URL ?>/mentors.php" method="GET" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr auto; gap: 16px; align-items: end;">
        <div>
          <label class="form-label" style="font-size: 0.82rem;">Search Mentor, Company or Role</label>
          <input type="text" name="q" class="form-control" placeholder="e.g. Sysco LABS, IFS, UX Designer..." value="<?= e($q) ?>">
        </div>

        <div>
          <label class="form-label" style="font-size: 0.82rem;">University</label>
          <select name="university" class="form-control">
            <option value="">All Universities</option>
            <?php foreach ($universities as $uni): ?>
              <option value="<?= $uni['id'] ?>" <?= $universityId === (int)$uni['id'] ? 'selected' : '' ?>><?= e($uni['short_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="form-label" style="font-size: 0.82rem;">Industry</label>
          <select name="industry" class="form-control">
            <option value="">All Industries</option>
            <?php foreach ($industries as $ind): ?>
              <option value="<?= $ind['id'] ?>" <?= $industryId === (int)$ind['id'] ? 'selected' : '' ?>><?= e($ind['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display: flex; gap: 8px;">
          <button type="submit" class="btn btn-teal" style="padding: 11px 20px;">
            <i class="fa-solid fa-filter"></i> Filter
          </button>
          <?php if (!empty($q) || $universityId || $industryId): ?>
            <a href="<?= BASE_URL ?>/mentors.php" class="btn btn-outline" style="padding: 11px 16px;">
              <i class="fa-solid fa-rotate-left"></i>
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Mentors Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
      <?php foreach ($mentors as $mentor): 
        $skills = $skillsByMentor[$mentor['id']] ?? [];
        $badgeClass = ($mentor['mentor_badge'] === 'STRATEGY' || $mentor['mentor_badge'] === 'CV Teardown') ? 'badge-strategy' : 'badge-open-chat';
      ?>
        <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-card); display: flex; flex-direction: column; transition: var(--transition);">
          
          <!-- Top Row -->
          <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
            <img src="<?= e(get_avatar_url($mentor['avatar_url'], $mentor['mentor_name'])) ?>" alt="<?= e($mentor['mentor_name']) ?>" style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover;">
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
              <span class="preview-badge-pill <?= $badgeClass ?>">
                <?= e($mentor['mentor_badge'] ?: 'Open to Chat') ?>
              </span>
              <span style="font-size: 0.8rem; font-weight: 700; color: #D97706;">
                <?= number_format($mentor['rating'], 1) ?> <i class="fa-solid fa-star"></i>
              </span>
            </div>
          </div>

          <!-- Info -->
          <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 4px;"><?= e($mentor['mentor_name']) ?></h3>
          <div style="font-size: 0.9rem; font-weight: 600; color: var(--primary-teal); margin-bottom: 4px;">
            <?= e($mentor['current_job_title']) ?>
          </div>
          <div style="font-size: 0.85rem; color: var(--text-dark); margin-bottom: 10px;">
            @ <?= e($mentor['company']) ?> &bull; <span style="color: var(--text-muted);"><?= e($mentor['years_experience']) ?> yrs exp</span>
          </div>

          <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-graduation-cap"></i>
            <span><?= e($mentor['uni_short_name'] ?: 'University') ?> &bull; Class of '<?= e($mentor['graduation_year']) ?></span>
          </div>

          <?php if (!empty($mentor['mentor_headline'])): ?>
            <p style="font-size: 0.86rem; color: var(--text-body); line-height: 1.5; font-style: italic; background: var(--bg-subtle); padding: 10px 12px; border-radius: var(--radius-sm); margin-bottom: 16px; flex: 1;">
              &ldquo;<?= e(truncate($mentor['mentor_headline'], 110)) ?>&rdquo;
            </p>
          <?php endif; ?>

          <!-- Skills -->
          <?php if (!empty($skills)): ?>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px;">
              <?php foreach ($skills as $skill): ?>
                <span class="story-tag-item" style="font-size: 0.72rem;"><?= e($skill) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Actions -->
          <div style="display: flex; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border-light);">
            <a href="<?= BASE_URL ?>/mentor.php?id=<?= $mentor['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1;">
              View Profile
            </a>
            <?php if (is_logged_in()): ?>
              <button 
                type="button" 
                class="btn btn-teal btn-sm" 
                style="flex: 1;"
                onclick="openMentorModal(<?= $mentor['id'] ?>, '<?= e(addslashes($mentor['mentor_name'])) ?>')"
              >
                Request
              </button>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-teal btn-sm" style="flex: 1;">
                Request
              </a>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Modal for Requesting Mentorship -->
<?php if (is_logged_in()): ?>
  <div class="modal-overlay" id="mentorshipDirectoryModal">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title" id="mentorModalTitle">Request Mentorship</h3>
        <button type="button" class="modal-close-btn" onclick="closeMentorModal()">&times;</button>
      </div>

      <form action="<?= BASE_URL ?>/actions/mentorship/request.php" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="mentor_id" id="modalMentorId" value="">
        <input type="hidden" name="redirect_url" value="<?= e($_SERVER['REQUEST_URI']) ?>">

        <div class="form-group">
          <label class="form-label" for="dir_topic">Mentorship Topic / Goal *</label>
          <input type="text" name="topic" id="dir_topic" class="form-control" placeholder="e.g. Guidance for upcoming Associate SE interview" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="dir_comm">Preferred Communication *</label>
          <select name="preferred_communication" id="dir_comm" class="form-control" required>
            <option value="Google Meet">Google Meet (Recommended for 20-30 min call)</option>
            <option value="WhatsApp">WhatsApp Message / Call</option>
            <option value="Email">Email Thread</option>
            <option value="Zoom">Zoom</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="dir_msg">Message / Specific Questions *</label>
          <textarea name="message" id="dir_msg" class="form-control form-control-textarea" rows="4" placeholder="Briefly describe what you're working on and what you'd like feedback on..." required></textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
          <button type="button" class="btn btn-outline btn-sm" onclick="closeMentorModal()">Cancel</button>
          <button type="submit" class="btn btn-teal btn-sm">Submit Request</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openMentorModal(mentorId, mentorName) {
      document.getElementById('modalMentorId').value = mentorId;
      document.getElementById('mentorModalTitle').textContent = `Request Mentorship from ${mentorName}`;
      document.getElementById('mentorshipDirectoryModal').classList.add('show');
    }
    function closeMentorModal() {
      document.getElementById('mentorshipDirectoryModal').classList.remove('show');
    }
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
