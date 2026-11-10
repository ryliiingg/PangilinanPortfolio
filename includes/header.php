<?php
// Shared top of every public page. Set before including:
//   $pageTitle  page title (optional)
//   $active     'home' | 'works' | 'contact'
$onHome = ($active ?? '') === 'home';
$home = $onHome ? '' : 'index.php';
$link = function (string $target, string $label) use ($active, $onHome, $home) {
  $href = $target === 'home' ? ($onHome ? '#home' : 'index.php') : $home . '#' . $target;
  $cls = ($active ?? '') === $target && !$onHome ? ' class="active"' : '';
  return '<a href="' . $href . '"' . $cls . '>' . $label . '</a>';
};
$isContact = ($active ?? '') === 'contact';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="images/favicon.png" />
    <title><?= isset($pageTitle) ? h($pageTitle) . ' – ' : '' ?>Andrea Pangilinan</title>
    <script>document.documentElement.className += ' js';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Gaegu:wght@400;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css?v=<?= asset_version() ?>" />
  </head>
  <body data-live-version="<?= h(content_version()) ?>">

    <a class="skip" href="#main">Skip to content</a>
    <div class="progress" id="progress"></div>

    <nav class="navbar">
      <a href="index.php" class="nav-logo-wrap" aria-label="Andrea Pangilinan, home">
        <img class="nav-bow" src="images/assets/Strawberry_ribbon.png" alt="" />
        <span class="nav-logo">Andrea <em>Pangilinan</em></span>
      </a>
      <ul class="nav-links">
        <li><?= $link('home', 'Home') ?></li>
        <li><?= $link('about', 'About') ?></li>
        <li><?= $link('works', 'Works') ?></li>
        <li><a href="contact.php" class="nav-cta<?= $isContact ? ' active' : '' ?>">Contact</a></li>
      </ul>
      <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="navDrawer">
        <span></span><span></span><span></span>
      </button>
    </nav>

    <div class="nav-drawer biscuit" id="navDrawer">
      <?= $link('home', 'Home') ?>
      <?= $link('about', 'About') ?>
      <?= $link('works', 'Works') ?>
      <a href="contact.php"<?= $isContact ? ' class="active"' : '' ?>>Contact</a>
    </div>
