<?php
// One gallery page. Set $sectionKey ('illustrations', 'multimedia' or 'certificates') before including.
require_once __DIR__ . '/functions.php';

$s = SECTIONS[$sectionKey];
$works = works_in($sectionKey);
$pageTitle = $s['name'];
$active = 'works';
require __DIR__ . '/header.php';
?>

    <main id="main">

      <header class="page-hero dots">
        <div class="reveal">
          <p class="crumbs"><a href="index.php">home</a> / <a href="index.php#works">works</a> / <?= h(strtolower($s['name'])) ?></p>
          <div class="page-plaque biscuit bow-top">
            <h1 class="page-title"><?= $s['heading'] /* trusted HTML from config */ ?></h1>
            <img class="cream-line pipe" src="images/assets/Whipped_creaml_line.png" alt="" />
          </div>
          <p class="page-sub"><?= h($s['sub']) ?></p>
        </div>
      </header>

      <div class="choco-line" aria-hidden="true"></div>

      <section class="gallery-section">
<?php if ($s['filters']): ?>
        <div class="filter-bar" data-filters role="group" aria-label="Filter <?= h(strtolower($s['name'])) ?>">
          <button class="chip active" type="button" data-filter="all" aria-pressed="true">All <span class="n"></span></button>
<?php foreach ($s['filters'] as $key => [$label]): ?>
          <button class="chip" type="button" data-filter="<?= h($key) ?>" aria-pressed="false"><?= h($label) ?> <span class="n"></span></button>
<?php endforeach; ?>
        </div>

        <div class="filter-meta">
          <span aria-live="polite">showing <span id="shownCount"><?= count($works) ?></span> <?= h($s['unit'][1]) ?> ✿</span>
          <button class="surprise" type="button" data-surprise>surprise me!</button>
        </div>
<?php elseif ($s['note']): ?>
        <p class="note center" style="margin: -10px 0 44px;"><?= h($s['note']) ?></p>
<?php endif; ?>

        <div class="works-grid" data-gallery data-live="gallery">
<?php foreach ($works as $w): ?>
          <?= work_card($w, (bool) $s['filters']) ?>

<?php endforeach; ?>
<?php if (!$works): ?>
          <p class="note center">nothing here yet, check back soon ✿</p>
<?php endif; ?>
        </div>
      </section>

      <div class="back-link-wrap">
        <a href="index.php#works" class="btn">&larr; Back to Works</a>
      </div>

    </main>

<?php require __DIR__ . '/footer.php'; ?>
