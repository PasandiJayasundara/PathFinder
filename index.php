<?php
/**
 * PathFinder - Homepage (Faithfully Reproducing Figma Reference Design)
 */

$pageTitle = "Real Stories. Real Careers. Your Next Step.";
$activeNav = 'home';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

// Fetch dynamic pathways/industries with story counts
$industries = get_all_industries($pdo);
// Top 6 for homepage grid
$topIndustries = array_slice($industries, 0, 6);

// Fetch featured career stories
$stmt = $pdo->prepare("
    SELECT cs.*, 
           gp.current_job_title, gp.company, gp.is_mentor_available, gp.mentor_badge, gp.graduation_year,
           u.full_name as author_name, u.avatar_url as author_avatar,
           uni.short_name as uni_short_name,
           ind.name as industry_name
    FROM career_stories cs
    JOIN graduate_profiles gp ON cs.graduate_id = gp.id
    JOIN users u ON gp.user_id = u.id
    LEFT JOIN universities uni ON gp.university_id = uni.id
    LEFT JOIN industries ind ON cs.industry_id = ind.id
    WHERE cs.status = 'approved'
    ORDER BY cs.is_featured DESC, cs.id ASC
    LIMIT 3
");
$stmt->execute();
$featuredStories = $stmt->fetchAll();

// Fetch tags for these stories
$storyIds = array_column($featuredStories, 'id');
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

<!-- ========================================================================
     HERO SECTION
     ======================================================================== -->
<section class="hero-section">
  <div class="container hero-grid">
    <!-- Left Column: Hero Copy & Search -->
    <div class="hero-content">
      <div class="hero-pill-badge">
        <span>🇱🇰</span> Sri Lanka's Career Community for Students &amp; Grads
      </div>

      <h1 class="hero-title">
        Real Stories. Real Careers.<br>
        <span class="text-teal">Your Next Step.</span>
      </h1>

      <p class="hero-subtitle">
        Discover how Sri Lankan graduates turned their university experiences into real careers — and connect directly with people who have already walked the path.
      </p>

      <!-- Search Bar -->
      <form action="<?= BASE_URL ?>/stories.php" method="GET" class="hero-search-box">
        <i class="fa-solid fa-magnifying-glass hero-search-icon"></i>
        <input 
          type="text" 
          name="q" 
          class="hero-search-input" 
          placeholder="Search by University, Degree, or Industry..."
          aria-label="Search career stories"
          required
        >
        <button type="submit" class="hero-search-btn">Find</button>
      </form>

      <!-- Trending Filter Pills -->
      <div class="hero-trending">
        <span class="trending-label">Trending:</span>
        <button type="button" class="tag-pill" data-search="UoM (Moratuwa)">UoM (Moratuwa)</button>
        <button type="button" class="tag-pill" data-search="Colombo UOC">Colombo UOC</button>
        <button type="button" class="tag-pill" data-search="SLIIT">SLIIT</button>
        <button type="button" class="tag-pill" data-search="Peradeniya">Peradeniya</button>
        <button type="button" class="tag-pill" data-search="Software Engineering">Software Eng</button>
        <button type="button" class="tag-pill" data-search="FinTech">FinTech</button>
      </div>

      <!-- Hero Actions -->
      <div class="hero-actions">
        <a href="<?= BASE_URL ?>/stories.php" class="btn btn-teal">
          Explore Career Journeys <i class="fa-solid fa-arrow-right"></i>
        </a>
        <a href="<?= is_logged_in() ? BASE_URL . '/graduate/story-create.php' : BASE_URL . '/register.php?role=graduate' ?>" class="btn btn-outline">
          <i class="fa-regular fa-pen-to-square"></i> Share Your Journey
        </a>
      </div>

      <!-- Social Proof Stats -->
      <div class="hero-social-proof">
        <div class="avatar-stack">
          <img src="<?= e(get_avatar_url(null, 'Janith Rathnayake')) ?>" alt="Student">
          <img src="<?= e(get_avatar_url(null, 'Nilukshi Fernando')) ?>" alt="Student">
          <img src="<?= e(get_avatar_url(null, 'Akeel Mansoor')) ?>" alt="Student">
          <span class="avatar-more">+4.5k</span>
        </div>
        <p>Joined by <strong>4,500+</strong> Sri Lankan undergraduates &amp; <strong>600+</strong> alumni mentors.</p>
      </div>
    </div>

    <!-- Right Column: Profile Preview Floating Cards -->
    <div class="hero-cards-col">
      <!-- Card 1: Kavindi Perera -->
      <div class="preview-card">
        <div class="preview-card-header">
          <div class="preview-card-user">
            <img src="<?= e(get_avatar_url(null, 'Kavindi Perera')) ?>" alt="Kavindi Perera" class="preview-card-avatar">
            <div class="preview-card-info">
              <h4>Kavindi Perera</h4>
              <div class="preview-card-role">Uni. of Moratuwa (BSc IT) &rarr; <span style="font-weight:600; color:var(--text-dark);">Senior UX @ Sysco LABS</span></div>
              <div class="preview-card-meta">
                <span>Tech &amp; Product</span> &bull; <span>3 yrs experience</span>
              </div>
            </div>
          </div>
          <span class="preview-badge-pill badge-open-chat">Open to Chat</span>
        </div>
        <div class="preview-card-quote">
          &ldquo;From campus hackathons to leading design sprints across global enterprise platforms. Happy to share how to build an industry-ready portfolio.&rdquo;
        </div>
      </div>

      <!-- Card 2: Dinuk Wijesinghe -->
      <div class="preview-card">
        <div class="preview-card-header">
          <div class="preview-card-user">
            <div class="preview-card-avatar-initials">DW</div>
            <div class="preview-card-info">
              <h4>Dinuk Wijesinghe</h4>
              <div class="preview-card-role">Colombo UOC &rarr; <span style="font-weight:600; color:var(--text-dark);">Strategy Associate @ MAS Holdings</span></div>
            </div>
          </div>
          <span class="preview-badge-pill badge-strategy">STRATEGY</span>
        </div>
        <div class="preview-card-quote">
          &ldquo;Transitioning from finance major to high-velocity apparel supply chain operations took 6 months of targeted unlearning.&rdquo;
        </div>
      </div>

      <!-- Card 3: Thilini Silva -->
      <div class="preview-card">
        <div class="preview-card-header">
          <div class="preview-card-user">
            <div class="preview-card-avatar-icon">
              <i class="fa-solid fa-cloud"></i>
            </div>
            <div class="preview-card-info">
              <h4>Thilini Silva</h4>
              <div class="preview-card-role">SLIIT (SE) &rarr; <span style="font-weight:600; color:var(--text-dark);">Cloud Architect @ Global FinTech</span></div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="preview-badge-pill badge-cloud">Cloud &amp; DevOps</span>
            <span style="font-size:0.8rem; font-weight:700; color:#D97706;">4.9 <i class="fa-solid fa-star"></i></span>
          </div>
        </div>
        <div class="preview-card-quote">
          &ldquo;Self-taught AWS during 3rd year internship after failing my first two code screenings. Here is what finally clicked.&rdquo;
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     HOW PATHFINDER WORKS (STEP-BY-STEP GUIDANCE)
     ======================================================================== -->
<section class="steps-section">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">STEP-BY-STEP GUIDANCE</span>
      <h2>How PathFinder Works</h2>
      <p>Bridging the gap between lecture halls and industry realities with complete peer transparency.</p>
    </div>

    <div class="steps-grid">
      <!-- Step 01 -->
      <div class="step-card">
        <div class="step-card-top">
          <span class="step-num-badge step-num-1">1</span>
          <i class="fa-solid fa-book-open step-card-icon"></i>
        </div>
        <h3>Read Authentic Journeys</h3>
        <p>Real, unfiltered accounts of job hunts, internship lessons, and career pivots by alumni from Moratuwa, Colombo, Peradeniya, SLIIT, and more.</p>
        <a href="<?= BASE_URL ?>/stories.php" class="step-card-link">Explore sample stories <i class="fa-solid fa-angle-right"></i></a>
      </div>

      <!-- Step 02 -->
      <div class="step-card">
        <div class="step-card-top">
          <span class="step-num-badge step-num-2">2</span>
          <i class="fa-solid fa-sliders step-card-icon"></i>
        </div>
        <h3>Filter by Your Reality</h3>
        <p>Narrow down stories by your exact degree, university, GPA tier, or target industry to see realistic timelines that match your starting line.</p>
        <a href="<?= BASE_URL ?>/stories.php" class="step-card-link">Try interactive filters <i class="fa-solid fa-angle-right"></i></a>
      </div>

      <!-- Step 03 -->
      <div class="step-card">
        <div class="step-card-top">
          <span class="step-num-badge step-num-3">3</span>
          <i class="fa-solid fa-headset step-card-icon"></i>
        </div>
        <h3>Request 1-on-1 Mentorship</h3>
        <p>Connect directly for 30-min guidance calls, CV teardowns, and realistic interview tips from grads already thriving inside top companies.</p>
        <a href="<?= BASE_URL ?>/mentors.php" class="step-card-link">Browse available mentors <i class="fa-solid fa-angle-right"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     POPULAR CAREER PATHWAYS (INDUSTRIES)
     ======================================================================== -->
<section class="pathways-section">
  <div class="container">
    <div class="section-title-row">
      <div class="section-header">
        <span class="section-tag">TRENDING HORIZONS</span>
        <h2>Explore Popular Pathways</h2>
        <p>Active career transition routes documented by Sri Lankan alumni.</p>
      </div>
      <a href="<?= BASE_URL ?>/industries.php" class="view-all-link">View all specialized industries <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="pathways-grid">
      <?php 
      $roadmapCounts = [
          1 => '140+ real graduate roadmaps',
          2 => '85+ real graduate roadmaps',
          3 => '62+ real graduate roadmaps',
          4 => '78+ real graduate roadmaps',
          5 => '54+ real graduate roadmaps',
          6 => '39+ real graduate roadmaps',
          7 => '45+ real graduate roadmaps',
          8 => '50+ real graduate roadmaps',
          9 => '65+ real graduate roadmaps',
          10 => '40+ real graduate roadmaps',
      ];
      foreach ($topIndustries as $index => $ind): 
        $iconClass = !empty($ind['icon_class']) ? $ind['icon_class'] : 'fa-solid fa-briefcase';
        $countText = $roadmapCounts[$ind['id']] ?? ($ind['story_count'] . ' verified stories');
        $colorIdx = ($index % 10) + 1;
      ?>
        <a href="<?= BASE_URL ?>/stories.php?industry=<?= $ind['id'] ?>" class="pathway-card">
          <div class="pathway-card-left">
            <div class="pathway-icon-box pathway-icon-<?= $colorIdx ?>">
              <i class="<?= e($iconClass) ?>"></i>
            </div>
            <div class="pathway-details">
              <h4><?= e($ind['name']) ?></h4>
              <p><?= e($countText) ?></p>
            </div>
          </div>
          <i class="fa-solid fa-angle-right pathway-chevron"></i>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========================================================================
     FEATURED GRADUATE STORIES
     ======================================================================== -->
<section class="stories-section">
  <div class="container">
    <div class="section-title-row">
      <div class="section-header">
        <span class="section-tag">UNFILTERED PERSPECTIVES</span>
        <h2>Featured Graduate Stories</h2>
        <p>Step inside the actual timelines, interview rounds, and pivotal decisions.</p>
      </div>
      <a href="<?= BASE_URL ?>/stories.php" class="view-all-link">Explore all 600+ stories <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="stories-grid">
      <?php foreach ($featuredStories as $story): 
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
              &ldquo;<?= e(truncate($story['title'], 68)) ?>&rdquo;
            </a>
          </h3>

          <p class="story-excerpt">
            <?= e(truncate($story['excerpt'], 140)) ?>
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
  </div>
</section>

<!-- ========================================================================
     WHY PATHFINDER SECTION
     ======================================================================== -->
<section class="why-section">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">THE PATHFINDER DIFFERENCE</span>
      <h2>Why PathFinder?</h2>
      <p>Designed specifically for the nuances, universities, and job realities of Sri Lanka.</p>
    </div>

    <div class="why-grid">
      <!-- Card 1 -->
      <div class="why-card">
        <div class="why-icon-box why-icon-1">
          <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <h3>Local Context First</h3>
        <p>Tailored directly to Sri Lankan corporate structures, local entry-level salary benchmarks, and real university degree-to-job mappings.</p>
      </div>

      <!-- Card 2 -->
      <div class="why-card">
        <div class="why-icon-box why-icon-2">
          <i class="fa-solid fa-eye"></i>
        </div>
        <h3>Zero Fluff &amp; Pure Reality</h3>
        <p>Honest reflections on hiring freezes, actual take-home compensation, rejection streaks, and self-taught skills that university missed.</p>
      </div>

      <!-- Card 3 -->
      <div class="why-card">
        <div class="why-icon-box why-icon-3">
          <i class="fa-solid fa-shield-check"></i>
        </div>
        <h3>Verified Alumni</h3>
        <p>Every mentor's graduation credentials and current workplace status are verified before they host 1-on-1 calls or resume teardowns.</p>
      </div>

      <!-- Card 4 -->
      <div class="why-card">
        <div class="why-icon-box why-icon-4">
          <i class="fa-solid fa-hand-holding-heart"></i>
        </div>
        <h3>Free for Undergrads</h3>
        <p>Core story browsing, filter engines, and peer mentorship requests remain 100% accessible to every Sri Lankan university student.</p>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     CTA BANNER SECTION
     ======================================================================== -->
<section class="cta-section">
  <div class="container">
    <div class="cta-banner">
      <div class="cta-pill-tag">
        <i class="fa-solid fa-circle-check" style="color: #6EE7B7; font-size: 0.75rem;"></i> Start for Free &bull; No credit card required
      </div>

      <h2 class="cta-title">Ready to Map Your Career with Confidence?</h2>
      <p class="cta-subtitle">Join 4,500+ Sri Lankan undergraduates and 600+ alumni sharing the roadmap today.</p>

      <div class="cta-actions">
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-white btn-lg">
          Get Started &mdash; It's Free
        </a>
        <a href="<?= BASE_URL ?>/stories.php" class="btn btn-outline-white btn-lg">
          Browse Stories First
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
