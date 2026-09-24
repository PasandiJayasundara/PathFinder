<?php
/**
 * PathFinder - Story Details Page
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = get_db();
$storyId = (int)($_GET['id'] ?? 0);

if (!$storyId) {
    redirect('stories.php');
}

// Fetch story details
$stmt = $pdo->prepare("
    SELECT cs.*, 
           gp.id as mentor_id, gp.current_job_title, gp.company, gp.is_mentor_available, gp.mentor_badge, 
           gp.mentor_headline, gp.bio as mentor_bio, gp.graduation_year, gp.years_experience, gp.linkedin_url,
           u.id as author_user_id, u.full_name as author_name, u.avatar_url as author_avatar, u.email as author_email,
           uni.name as uni_name, uni.short_name as uni_short_name,
           deg.name as degree_name,
           ind.name as industry_name
    FROM career_stories cs
    JOIN graduate_profiles gp ON cs.graduate_id = gp.id
    JOIN users u ON gp.user_id = u.id
    LEFT JOIN universities uni ON gp.university_id = uni.id
    LEFT JOIN degrees deg ON gp.degree_id = deg.id
    LEFT JOIN industries ind ON cs.industry_id = ind.id
    WHERE cs.id = ? AND (cs.status = 'approved' OR ? = 'admin' OR ? = u.id)
    LIMIT 1
");

$currentRole = $_SESSION['user_role'] ?? '';
$currentUserId = $_SESSION['user_id'] ?? 0;
$stmt->execute([$storyId, $currentRole, $currentUserId]);
$story = $stmt->fetch();

if (!$story) {
    set_flash('error', 'Career story not found or pending review.');
    redirect('stories.php');
}

// Record page view with IP deduplication
record_story_view($pdo, $storyId);

// Fetch timeline entries
$tlStmt = $pdo->prepare("SELECT * FROM career_timeline WHERE story_id = ? ORDER BY display_order ASC, id ASC");
$tlStmt->execute([$storyId]);
$timeline = $tlStmt->fetchAll();

// Fetch tags
$tagStmt = $pdo->prepare("SELECT tag_name FROM story_tags WHERE story_id = ?");
$tagStmt->execute([$storyId]);
$tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch related stories (same industry or university)
$relStmt = $pdo->prepare("
    SELECT cs.id, cs.title, cs.excerpt, cs.created_at, cs.current_job, cs.company,
           u.full_name as author_name, u.avatar_url as author_avatar,
           gp.mentor_badge, gp.graduation_year, uni.short_name as uni_short_name
    FROM career_stories cs
    JOIN graduate_profiles gp ON cs.graduate_id = gp.id
    JOIN users u ON gp.user_id = u.id
    LEFT JOIN universities uni ON gp.university_id = uni.id
    WHERE cs.id != ? AND cs.status = 'approved' AND (cs.industry_id = ? OR gp.university_id = ?)
    LIMIT 3
");
$relStmt->execute([$storyId, $story['industry_id'], $story['university_id']]);
$relatedStories = $relStmt->fetchAll();

$pageTitle = $story['title'];
$activeNav = 'stories';
require_once __DIR__ . '/includes/header.php';

$bookmarked = $currentUser ? is_bookmarked($pdo, $currentUser['id'], $story['id']) : false;
?>

<div style="background-color: #FAF8F5; padding: 48px 0 90px;">
  <div class="container">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <i class="fa-solid fa-angle-right" style="font-size: 0.75rem;"></i>
      <a href="<?= BASE_URL ?>/stories.php">Career Stories</a>
      <i class="fa-solid fa-angle-right" style="font-size: 0.75rem;"></i>
      <span style="color: var(--text-dark);"><?= e(truncate($story['title'], 40)) ?></span>
    </div>

    <!-- Main Content Layout (Story Body + Sticky Mentor Sidebar) -->
    <div style="display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; align-items: start;">
      
      <!-- Left Column: Full Career Narrative -->
      <article style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 40px; box-shadow: var(--shadow-sm);">
        
        <!-- Story Top Badges & Meta -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span class="story-status-pill <?= $story['mentor_badge'] === 'CV Teardown' ? 'badge-strategy' : 'badge-open-chat' ?>">
              <?= e($story['mentor_badge'] ?: 'Open to Chat') ?>
            </span>
            <span style="font-size: 0.82rem; color: var(--primary-teal); font-weight: 600; background: var(--primary-teal-light); padding: 3px 10px; border-radius: var(--radius-pill);">
              <?= e($story['industry_name']) ?>
            </span>
          </div>

          <div style="display: flex; align-items: center; gap: 16px; font-size: 0.82rem; color: var(--text-muted);">
            <span><i class="fa-regular fa-eye"></i> <?= number_format($story['views_count']) ?> views</span>
            <span><i class="fa-regular fa-calendar"></i> <?= date('M j, Y', strtotime($story['created_at'])) ?></span>
            <button 
              type="button" 
              class="bookmark-btn <?= $bookmarked ? 'bookmarked' : '' ?>" 
              data-story-id="<?= $story['id'] ?>" 
              title="Bookmark story"
              style="font-size: 1.25rem;"
            >
              <i class="<?= $bookmarked ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
            </button>
          </div>
        </div>

        <!-- Story Title -->
        <h1 style="font-size: 2.2rem; font-weight: 800; line-height: 1.25; margin-bottom: 20px; color: var(--text-dark);">
          <?= e($story['title']) ?>
        </h1>

        <!-- Story Excerpt Callout -->
        <div style="background: var(--bg-subtle); border-left: 4px solid var(--primary-teal); padding: 18px 22px; border-radius: 0 var(--radius-sm) var(--radius-sm) 0; font-size: 1.05rem; font-style: italic; color: var(--text-body); line-height: 1.6; margin-bottom: 36px;">
          &ldquo;<?= e($story['excerpt']) ?>&rdquo;
        </div>

        <!-- Career Timeline Component (from Requirements) -->
        <?php if (!empty($timeline)): ?>
          <section style="margin-bottom: 44px; padding-bottom: 32px; border-bottom: 1px solid var(--border-light);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px;">
              <div style="width: 32px; height: 32px; background: var(--primary-teal-light); color: var(--primary-teal); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-timeline"></i>
              </div>
              <h2 style="font-size: 1.35rem; font-weight: 700;">Career Timeline &amp; Milestones</h2>
            </div>

            <div style="position: relative; padding-left: 32px; border-left: 2px solid #E2E8F0; margin-left: 12px; display: flex; flex-direction: column; gap: 24px;">
              <?php foreach ($timeline as $item): ?>
                <div style="position: relative;">
                  <div style="position: absolute; left: -41px; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #FFFFFF; border: 4px solid var(--primary-teal); box-shadow: var(--shadow-sm);"></div>
                  <div style="font-size: 0.8rem; font-weight: 700; color: var(--primary-teal); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
                    <?= e($item['year']) ?>
                  </div>
                  <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 4px; color: var(--text-dark);">
                    <?= e($item['title']) ?>
                  </h4>
                  <?php if (!empty($item['description'])): ?>
                    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.55;">
                      <?= e($item['description']) ?>
                    </p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <!-- Full Story Narrative -->
        <section style="margin-bottom: 36px;">
          <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 16px;">The Journey in Detail</h2>
          <div style="font-size: 1rem; color: var(--text-body); line-height: 1.75; white-space: pre-line;">
            <?= e($story['summary']) ?>
          </div>
        </section>

        <!-- Challenges Faced -->
        <?php if (!empty($story['challenges'])): ?>
          <section style="margin-bottom: 36px; background: #FFFBF5; border: 1px solid #FED7AA; border-radius: var(--radius-md); padding: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: #C2410C;">
              <i class="fa-solid fa-mountain" style="font-size: 1.1rem;"></i>
              <h3 style="font-size: 1.15rem; font-weight: 700; color: #9A3412;">Real Challenges &amp; Stumbling Blocks</h3>
            </div>
            <p style="font-size: 0.95rem; color: #7C2D12; line-height: 1.65; white-space: pre-line;">
              <?= e($story['challenges']) ?>
            </p>
          </section>
        <?php endif; ?>

        <!-- Interview Experience -->
        <?php if (!empty($story['interview_experience'])): ?>
          <section style="margin-bottom: 36px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-md); padding: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: var(--primary-teal);">
              <i class="fa-solid fa-comments-dollar" style="font-size: 1.1rem;"></i>
              <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-dark);">Hiring &amp; Interview Breakdown</h3>
            </div>
            <p style="font-size: 0.95rem; color: var(--text-body); line-height: 1.65; white-space: pre-line;">
              <?= e($story['interview_experience']) ?>
            </p>
          </section>
        <?php endif; ?>

        <!-- Important Skills -->
        <?php if (!empty($story['important_skills'])): ?>
          <section style="margin-bottom: 36px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 12px;">Crucial Skills to Master</h3>
            <p style="font-size: 0.95rem; color: var(--text-body); line-height: 1.65;">
              <?= e($story['important_skills']) ?>
            </p>
          </section>
        <?php endif; ?>

        <!-- Advice for Students -->
        <?php if (!empty($story['student_advice'])): ?>
          <section style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--radius-md); padding: 24px; margin-bottom: 36px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: #15803D;">
              <i class="fa-solid fa-lightbulb" style="font-size: 1.1rem;"></i>
              <h3 style="font-size: 1.15rem; font-weight: 700; color: #166534;">Advice for Undergraduates</h3>
            </div>
            <p style="font-size: 0.95rem; color: #14532D; line-height: 1.65; white-space: pre-line;">
              <?= e($story['student_advice']) ?>
            </p>
          </section>
        <?php endif; ?>

        <!-- Tags Row -->
        <?php if (!empty($tags)): ?>
          <div style="display: flex; flex-wrap: wrap; gap: 8px; padding-top: 24px; border-top: 1px solid var(--border-light);">
            <?php foreach ($tags as $tag): ?>
              <a href="<?= BASE_URL ?>/stories.php?q=<?= urlencode($tag) ?>" class="story-tag-item" style="font-size: 0.85rem; padding: 5px 12px;">
                #<?= e($tag) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </article>

      <!-- Right Column: Author / Mentor Profile Card -->
      <aside style="position: sticky; top: 100px; display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Mentor Info Card -->
        <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
          <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
            <img src="<?= e(get_avatar_url($story['author_avatar'], $story['author_name'])) ?>" alt="<?= e($story['author_name']) ?>" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover;">
            <div>
              <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-dark);"><?= e($story['author_name']) ?></h3>
              <div style="font-size: 0.88rem; font-weight: 600; color: var(--primary-teal);">
                <?= e($story['current_job']) ?>
              </div>
              <div style="font-size: 0.82rem; color: var(--text-muted);">
                @ <?= e($story['company']) ?>
              </div>
            </div>
          </div>

          <!-- Academic & Experience Details -->
          <div style="display: flex; flex-direction: column; gap: 12px; padding: 16px 0; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); margin-bottom: 20px; font-size: 0.88rem;">
            <div style="display: flex; align-items: center; gap: 10px; color: var(--text-body);">
              <i class="fa-solid fa-graduation-cap" style="width: 18px; color: var(--text-muted);"></i>
              <span><?= e($story['uni_name'] ?: 'Sri Lankan University') ?></span>
            </div>
            <?php if (!empty($story['degree_name'])): ?>
              <div style="display: flex; align-items: center; gap: 10px; color: var(--text-body);">
                <i class="fa-solid fa-certificate" style="width: 18px; color: var(--text-muted);"></i>
                <span><?= e($story['degree_name']) ?> (Class of '<?= e($story['graduation_year']) ?>)</span>
              </div>
            <?php endif; ?>
            <div style="display: flex; align-items: center; gap: 10px; color: var(--text-body);">
              <i class="fa-solid fa-briefcase" style="width: 18px; color: var(--text-muted);"></i>
              <span>First Role: <?= e($story['first_job'] ?: 'Intern') ?></span>
            </div>
            <?php if (!empty($story['linkedin_url'])): ?>
              <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-brands fa-linkedin" style="width: 18px; color: #0A66C2;"></i>
                <a href="<?= e($story['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: #0A66C2; font-weight: 500;">
                  LinkedIn Profile <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>
                </a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Mentorship Availability CTA -->
          <?php if ($story['is_mentor_available']): ?>
            <div style="background: var(--bg-subtle); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 20px;">
              <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--badge-green-text); margin-bottom: 6px;">
                <i class="fa-solid fa-circle" style="font-size: 0.55rem; color: var(--badge-green-dot);"></i> Open for Mentorship Requests
              </div>
              <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.45;">
                Ask about CV reviews, coding interview prep, or career transition advice.
              </p>
            </div>

            <?php if (is_logged_in()): ?>
              <?php if ($_SESSION['user_id'] != $story['author_user_id']): ?>
                <button type="button" class="btn btn-teal" style="width: 100%;" data-modal-target="mentorshipModal">
                  <i class="fa-regular fa-comment-dots"></i> Request Mentorship
                </button>
              <?php else: ?>
                <a href="<?= BASE_URL ?>/graduate/story-edit.php?id=<?= $story['id'] ?>" class="btn btn-outline" style="width: 100%;">
                  <i class="fa-solid fa-pen"></i> Edit Your Story
                </a>
              <?php endif; ?>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-teal" style="width: 100%;">
                Sign in to Request Mentorship
              </a>
            <?php endif; ?>
          <?php else: ?>
            <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 10px;">
              Currently not accepting mentorship requests.
            </div>
          <?php endif; ?>

          <div style="margin-top: 16px; text-align: center;">
            <a href="<?= BASE_URL ?>/mentor.php?id=<?= $story['mentor_id'] ?>" style="font-size: 0.85rem; color: var(--primary-teal); font-weight: 600;">
              View Full Mentor Profile &rarr;
            </a>
          </div>
        </div>

      </aside>

    </div>

    <!-- Related Career Stories Grid -->
    <?php if (!empty($relatedStories)): ?>
      <div style="margin-top: 80px; padding-top: 48px; border-top: 1px solid var(--border-color);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
          <div>
            <span class="section-tag">CONTINUE EXPLORING</span>
            <h2 style="font-size: 1.75rem; font-weight: 800;">Related Graduate Stories</h2>
          </div>
          <a href="<?= BASE_URL ?>/stories.php?industry=<?= $story['industry_id'] ?>" class="view-all-link">
            Explore More in <?= e($story['industry_name']) ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>

        <div class="stories-grid">
          <?php foreach ($relatedStories as $rel): ?>
            <div class="story-card">
              <div class="story-card-badges">
                <span class="story-status-pill badge-open-chat">
                  <?= e($rel['mentor_badge'] ?: 'Open to Chat') ?>
                </span>
                <span class="story-uni-meta">
                  <?= e($rel['uni_short_name'] ?? 'University') ?> '<?= substr((string)$rel['graduation_year'], -2) ?>
                </span>
              </div>

              <div class="story-author-row">
                <img src="<?= e(get_avatar_url($rel['author_avatar'], $rel['author_name'])) ?>" alt="<?= e($rel['author_name']) ?>" class="story-author-avatar">
                <div>
                  <div class="story-author-name"><?= e($rel['author_name']) ?></div>
                  <div class="story-author-title"><?= e($rel['current_job']) ?> @ <?= e($rel['company']) ?></div>
                </div>
              </div>

              <h3 class="story-headline">
                <a href="<?= BASE_URL ?>/story.php?id=<?= $rel['id'] ?>">
                  &ldquo;<?= e(truncate($rel['title'], 68)) ?>&rdquo;
                </a>
              </h3>

              <p class="story-excerpt">
                <?= e(truncate($rel['excerpt'], 130)) ?>
              </p>

              <div class="story-card-footer">
                <a href="<?= BASE_URL ?>/story.php?id=<?= $rel['id'] ?>" class="story-read-link">
                  Read Story &rarr;
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- ========================================================================
     REQUEST MENTORSHIP MODAL
     ======================================================================== -->
<?php if (is_logged_in() && $story['is_mentor_available']): ?>
  <div class="modal-overlay" id="mentorshipModal">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Request Mentorship from <?= e($story['author_name']) ?></h3>
        <button type="button" class="modal-close-btn">&times;</button>
      </div>

      <form action="<?= BASE_URL ?>/actions/mentorship/request.php" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="mentor_id" value="<?= $story['mentor_id'] ?>">
        <input type="hidden" name="redirect_url" value="<?= e($_SERVER['REQUEST_URI']) ?>">

        <div class="form-group">
          <label class="form-label" for="topic">Mentorship Topic / Goal *</label>
          <input 
            type="text" 
            name="topic" 
            id="topic" 
            class="form-control" 
            placeholder="e.g. CV Review for Software Engineering Internship" 
            required
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="preferred_communication">Preferred Communication Method *</label>
          <select name="preferred_communication" id="preferred_communication" class="form-control" required>
            <option value="Google Meet">Google Meet (Recommended for 20-30 min call)</option>
            <option value="WhatsApp">WhatsApp Message / Audio</option>
            <option value="Email">Email Thread</option>
            <option value="Zoom">Zoom</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="message">Your Message to <?= e($story['author_name']) ?> *</label>
          <textarea 
            name="message" 
            id="message" 
            class="form-control form-control-textarea" 
            rows="4" 
            placeholder="Introduce yourself, your university and year, what specific questions you have, and links to your GitHub or LinkedIn..."
            required
          ></textarea>
          <div class="form-help">Be concise, respectful of their time, and specify what you need guidance on.</div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
          <button type="button" class="btn btn-outline btn-sm" data-modal-close>Cancel</button>
          <button type="submit" class="btn btn-teal btn-sm">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
