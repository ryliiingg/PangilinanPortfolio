<?php
require __DIR__ . '/bootstrap.php';
require_login();

$counts = section_counts();
$tab = isset(SECTIONS[$_GET['section'] ?? '']) ? $_GET['section'] : 'illustrations';
$works = works_in($tab);
$featuredCount = count(featured_works());

admin_header('Works', 'works');
?>
      <div class="page-head">
        <div>
          <h1>Your works</h1>
          <p class="muted" data-live="intro">Add, edit, reorder or remove the pieces on your site. <span class="star on">★</span> marks the ones shown on your homepage (<?= $featuredCount ?> now).</p>
        </div>
        <a class="btn" href="work.php?section=<?= h($tab) ?>">+ Add a work</a>
      </div>

      <nav class="tabs" data-live="tabs">
<?php foreach (SECTIONS as $key => $s): ?>
        <a href="?section=<?= h($key) ?>"<?= $key === $tab ? ' class="on"' : '' ?>><?= h($s['name']) ?> <span><?= $counts[$key] ?></span></a>
<?php endforeach; ?>
      </nav>

      <div data-live="works">
<?php if (!$works): ?>
      <p class="empty"><img src="../images/assets/Folder_biscuit.png" alt="" />No <?= h(strtolower(SECTIONS[$tab]['name'])) ?> yet. <a href="work.php?section=<?= h($tab) ?>">Add the first one</a>.</p>
<?php else: ?>
      <ul class="work-list">
<?php foreach ($works as $i => $w): ?>
        <li>
          <span class="thumb"><img src="../<?= h($w['image']) ?>" alt="" class="<?= $w['contain'] ? 'contain' : '' ?>" loading="lazy" /></span>
          <div class="info">
            <strong><?= h($w['title']) ?></strong>
            <span class="muted"><?= h($w['tag'] ?: '—') ?></span>
          </div>
          <div class="actions">
            <form method="post" action="action.php" data-ajax>
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $w['id'] ?>" />
              <button name="do" value="feature" class="icon star<?= $w['featured'] ? ' on' : '' ?>" title="<?= $w['featured'] ? 'Remove from homepage' : 'Show on homepage' ?>">★</button>
              <button name="do" value="up" class="icon" title="Move up"<?= $i === 0 ? ' disabled' : '' ?>>↑</button>
              <button name="do" value="down" class="icon" title="Move down"<?= $i === count($works) - 1 ? ' disabled' : '' ?>>↓</button>
            </form>
            <a class="btn btn--small" href="work.php?id=<?= (int) $w['id'] ?>">Edit</a>
            <form method="post" action="action.php" data-ajax data-confirm="Delete “<?= h($w['title']) ?>”? This can't be undone.">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $w['id'] ?>" />
              <button name="do" value="delete" class="btn btn--small btn--danger">Delete</button>
            </form>
          </div>
        </li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
      </div>
<?php admin_footer(); ?>
