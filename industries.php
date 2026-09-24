<?php
/**
 * PathFinder - Career Pathways & Industries
 */

$pageTitle = "Explore Popular Career Pathways in Sri Lanka";
$activeNav = 'industries';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db();
$industries = get_all_industries($pdo);

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
?>

<div style="background-color: #FAF8F5; padding: 48px 0 90px;">
  <div class="container">
    <div style="margin-bottom: 40px; text-align: center; max-width: 680px; margin-left: auto; margin-right: auto;">
      <span class="section-tag">TRENDING CAREER HORIZONS</span>
      <h1 style="font-size: 2.5rem; letter-spacing: -0.025em; margin-bottom: 12px;">Explore Popular Career Pathways</h1>
      <p style="color: var(--text-muted); font-size: 1.05rem;">
        Discover how graduates transition from Sri Lankan universities into major high-growth sectors, local tech giants, and global multinationals.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
      <?php foreach ($industries as $index => $ind): 
        $iconClass = !empty($ind['icon_class']) ? $ind['icon_class'] : 'fa-solid fa-briefcase';
        $countText = $roadmapCounts[$ind['id']] ?? ($ind['story_count'] . ' verified stories');
        $colorIdx = ($index % 10) + 1;
      ?>
        <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-card); display: flex; flex-direction: column; transition: var(--transition);">
          <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
            <div class="pathway-icon-box pathway-icon-<?= $colorIdx ?>" style="width: 52px; height: 52px; border-radius: var(--radius-sm); font-size: 1.35rem;">
              <i class="<?= e($iconClass) ?>"></i>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; font-weight: 700;"><?= e($ind['name']) ?></h3>
              <span style="font-size: 0.8rem; font-weight: 600; color: var(--primary-teal);">
                <?= e($countText) ?>
              </span>
            </div>
          </div>

          <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px; flex: 1;">
            <?= e($ind['description']) ?>
          </p>

          <div style="display: flex; gap: 10px; margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border-light);">
            <a href="<?= BASE_URL ?>/stories.php?industry=<?= $ind['id'] ?>" class="btn btn-teal btn-sm" style="flex: 1;">
              Browse Stories &rarr;
            </a>
            <a href="<?= BASE_URL ?>/mentors.php?industry=<?= $ind['id'] ?>" class="btn btn-outline btn-sm">
              Mentors
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
