<?php
/**
 * PathFinder - Career Stories Catalog with Multi-facet Filtering
 */

$pageTitle = "Explore Authentic Career Stories";
$activeNav = 'stories';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

// Filters from query string
$q = trim($_GET['q'] ?? '');
$universityId = !empty($_GET['university']) ? (int)$_GET['university'] : null;
$industryId = !empty($_GET['industry']) ? (int)$_GET['industry'] : null;
$gradYear = !empty($_GET['year']) ? (int)$_GET['year'] : null;
$sortBy = $_GET['sort'] ?? 'newest';

// Base SQL query
$sql = "
    SELECT cs.*, 
           gp.current_job_title, gp.company, gp.is_mentor_available, gp.mentor_badge, gp.graduation_year,
           u.full_name as author_name, u.avatar_url as author_avatar,
           uni.short_name as uni_short_name, uni.name as uni_full_name,
           ind.name as industry_name
    FROM career_stories cs
    JOIN graduate_profiles gp ON cs.graduate_id = gp.id
    JOIN users u ON gp.user_id = u.id
    LEFT JOIN universities uni ON gp.university_id = uni.id
    LEFT JOIN industries ind ON cs.industry_id = ind.id
    WHERE cs.status = 'approved'
";

$params = [];

if (!empty($q)) {
    $sql .= " AND (cs.title LIKE ? OR cs.excerpt LIKE ? OR cs.summary LIKE ? OR cs.company LIKE ? OR cs.current_job LIKE ? OR uni.name LIKE ? OR ind.name LIKE ?)";
    $like = "%$q%";
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
}

if ($universityId) {
    $sql .= " AND gp.university_id = ?";
    $params[] = $universityId;
}

if ($industryId) {
    $sql .= " AND cs.industry_id = ?";
    $params[] = $industryId;
}

if ($gradYear) {
    $sql .= " AND gp.graduation_year = ?";
    $params[] = $gradYear;
}

// Sorting
switch ($sortBy) {
    case 'popular':
        $sql .= " ORDER BY cs.views_count DESC, cs.id DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY cs.id ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY cs.is_featured DESC, cs.id DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stories = $stmt->fetchAll();

// Fetch all filter options
$universities = get_all_universities($pdo);
$industries = get_all_industries($pdo);

// Fetch tags
$storyIds = array_column($stories, 'id');
$tagsByStory = [];
if (!empty($storyIds)) {
    $inClause = implode(',', array_fill(0, count($storyIds), '?'));
    $tagStmt = $pdo->prepare("SELECT story_id, tag_name FROM story_tags WHERE story_id IN ($inClause)");
    $tagStmt->execute($storyIds);
    while ($row = $tagStmt->fetch()) {
        $tagsByStory[$row['story_id']][] = $row['tag_name'];
    }
}
?>

<div style="background-color: #FAF8F5; padding: 48px 0 80px;">
  <div class="container">
    <!-- Page Header -->
    <div style="margin-bottom: 36px;">
      <span class="section-tag">AUTHENTIC ALUMNI EXPERIENCES</span>
      <h1 style="font-size: 2.4rem; letter-spacing: -0.025em; margin-bottom: 8px;">Explore Career Stories</h1>
      <p style="color: var(--text-muted); font-size: 1.05rem;">
        Filter unfiltered accounts of job searches, technical interview rounds, and career trajectories from Sri Lankan graduates.
      </p>
    </div>

    <!-- Filter Control Card -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 40px;" id="filters">
      <form action="<?= BASE_URL ?>/stories.php" method="GET" id="storiesFilterForm">
        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 0.8fr 0.8fr auto; gap: 14px; align-items: end;">
          <!-- Keyword -->
          <div>
            <label class="form-label" style="font-size: 0.82rem;">Keyword / Company</label>
            <input 
              type="text" 
              name="q" 
              class="form-control" 
              placeholder="e.g. LeetCode, IFS, WSO2..." 
              value="<?= e($q) ?>"
            >
          </div>

          <!-- University -->
          <div>
            <label class="form-label" style="font-size: 0.82rem;">University</label>
            <select name="university" class="form-control">
              <option value="">All Universities</option>
              <?php foreach ($universities as $uni): ?>
                <option value="<?= $uni['id'] ?>" <?= $universityId === (int)$uni['id'] ? 'selected' : '' ?>>
                  <?= e($uni['short_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Industry -->
          <div>
            <label class="form-label" style="font-size: 0.82rem;">Industry</label>
            <select name="industry" class="form-control">
              <option value="">All Industries</option>
              <?php foreach ($industries as $ind): ?>
                <option value="<?= $ind['id'] ?>" <?= $industryId === (int)$ind['id'] ? 'selected' : '' ?>>
                  <?= e($ind['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Year -->
          <div>
            <label class="form-label" style="font-size: 0.82rem;">Grad Year</label>
            <select name="year" class="form-control">
              <option value="">Any Year</option>
              <?php for ($y = 2024; $y >= 2018; $y--): ?>
                <option value="<?= $y ?>" <?= $gradYear === $y ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <!-- Sort -->
          <div>
            <label class="form-label" style="font-size: 0.82rem;">Sort By</label>
            <select name="sort" class="form-control">
              <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest</option>
              <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Most Viewed</option>
              <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest</option>
            </select>
          </div>

          <!-- Actions -->
          <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-teal" style="padding: 11px 20px;">
              <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($q) || $universityId || $industryId || $gradYear || $sortBy !== 'newest'): ?>
              <a href="<?= BASE_URL ?>/stories.php" class="btn btn-outline" style="padding: 11px 16px;" title="Reset filters">
                <i class="fa-solid fa-rotate-left"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <!-- Results Meta -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
      <p style="color: var(--text-muted); font-size: 0.95rem;">
        Showing <strong><?= count($stories) ?></strong> verified graduate <?= count($stories) === 1 ? 'story' : 'stories' ?>
      </p>
    </div>

    <!-- Stories Grid -->
    <?php if (!empty($stories)): ?>
      <div class="stories-grid">
        <?php foreach ($stories as $story): 
          $badgeClass = ($story['mentor_badge'] === 'CV Teardown') ? 'badge-strategy' : 'badge-open-chat';
          $badgeText = $story['mentor_badge'] ?: 'Open to Chat';
          $tags = $tagsByStory[$story['id']] ?? [];
          $bookmarked = $currentUser ? is_bookmarked($pdo, $currentUser['id'], $story['id']) : false;
        ?>
          <div class="story-card">
            <div class="story-card-badges">
              <span class="story-status-pill <?= $badgeClass ?>">
                <?= e($badgeText) ?>
              </span>
              <span class="story-uni-meta">
                <?= e($story['uni_short_name'] ?? 'University') ?> '<?= substr((string)$story['graduation_year'], -2) ?>
              </span>
            </div>

            <div class="story-author-row">
              <img src="<?= e(get_avatar_url($story['author_avatar'], $story['author_name'])) ?>" alt="<?= e($story['author_name']) ?>" class="story-author-avatar">
              <div>
                <div class="story-author-name"><?= e($story['author_name']) ?></div>
                <div class="story-author-title"><?= e($story['current_job']) ?> @ <?= e($story['company']) ?></div>
              </div>
            </div>

            <h3 class="story-headline">
              <a href="<?= BASE_URL ?>/story.php?id=<?= $story['id'] ?>">
                &ldquo;<?= e(truncate($story['title'], 72)) ?>&rdquo;
              </a>
            </h3>

            <p class="story-excerpt">
              <?= e(truncate($story['excerpt'], 145)) ?>
            </p>

            <div class="story-tags-row">
              <?php foreach ($tags as $tag): ?>
                <span class="story-tag-item">#<?= e($tag) ?></span>
              <?php endforeach; ?>
            </div>

            <div class="story-card-footer">
              <a href="<?= BASE_URL ?>/story.php?id=<?= $story['id'] ?>" class="story-read-link">
                Read Story &amp; Mentorship <i class="fa-solid fa-arrow-right"></i>
              </a>
              <button 
                type="button" 
                class="bookmark-btn <?= $bookmarked ? 'bookmarked' : '' ?>" 
                data-story-id="<?= $story['id'] ?>" 
                aria-label="Bookmark story"
              >
                <i class="<?= $bookmarked ? 'fa-solid' : 'fa-regular' ?> fa-bookmark"></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div style="text-align: center; padding: 64px 20px; background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
        <i class="fa-regular fa-folder-open" style="font-size: 3rem; color: var(--text-subtle); margin-bottom: 16px;"></i>
        <h3 style="font-size: 1.35rem; margin-bottom: 8px;">No career stories match your filters</h3>
        <p style="color: var(--text-muted); margin-bottom: 24px;">Try loosening your search terms or clearing selected universities and industries.</p>
        <a href="<?= BASE_URL ?>/stories.php" class="btn btn-teal btn-sm">Clear All Filters</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
