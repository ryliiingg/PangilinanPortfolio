<?php
require __DIR__ . '/includes/functions.php';

$counts = section_counts();
$featured = featured_works();
$active = 'home';
require __DIR__ . '/includes/header.php';
?>

    <main id="main">

      <!-- HOME -->
      <section id="home" class="hero">
        <div class="hero-plaque biscuit">
          <img class="hero-bow" src="images/assets/Strawberry_ribbon.png" alt="" />
          <p class="hero-hello">hello, welcome to my world ♡</p>
          <h1 class="hero-title">Creative &amp; <em>Dreamy</em></h1>
          <img class="hero-cream" src="images/assets/Whipped_creaml_line.png" alt="" />
          <p class="hero-role">
            I'm Andrea, a
            <span class="typed" id="typed" aria-hidden="true" data-words='["designer","illustrator","daydreamer","stationery hunter"]'>designer</span><span class="caret" aria-hidden="true"></span>
            <span class="sr-only">designer, illustrator and daydreamer</span>
          </p>
          <div class="hero-actions">
            <a href="#works" class="btn">See My Work</a>
            <a href="contact.php" class="btn">Say hi ♡</a>
          </div>
        </div>
        <a href="#about" class="scroll-hint">scroll for more ↓</a>
      </section>

      <div class="choco-line" aria-hidden="true"></div>

      <!-- ABOUT -->
      <section id="about" class="about-wrap">
        <div class="about">
          <div class="about-photo reveal">
            <figure class="polaroid polaroid--big">
              <img src="images/MyPic.jpg" alt="A picture of Andrea" />
              <figcaption>that's me!</figcaption>
            </figure>
            <div class="about-bubble" aria-hidden="true">
              <img src="images/assets/Blank_long_biscuit_commentbox.png" alt="" />
              <span>hello there!</span>
            </div>
            <img class="about-cake" src="images/assets/Folder_biscuit.png" alt="" />
          </div>

          <div class="about-text reveal">
            <p class="section-label">about me</p>
            <h2>Hi, I'm <em>Andrea</em></h2>

            <div class="about-card biscuit bow-top">
              <p>
                I'm a creative based in the Philippines with a love for all things creative and beautiful.
                I specialize in visual design and illustration.
              </p>
              <p>
                When I'm not designing, you'll find me looking for stationeries.
              </p>
            </div>

            <div class="about-extras">
              <div class="recipe biscuit biscuit--vanilla taped">
                <h3>Recipe for Andrea</h3>
                <ul>
                  <li><b>1 cup</b> of visual design</li>
                  <li><b>2 spoonfuls</b> of illustration</li>
                  <li><b>a sprinkle</b> of stationery love</li>
                  <li><b>a pinch</b> of daydreaming</li>
                </ul>
              </div>

              <div class="stats" data-live="stats">
<?php foreach (SECTIONS as $key => $s): ?>
                <div class="stat"><span class="stat-num" data-count="<?= $counts[$key] ?>">0</span><span class="stat-lbl"><?= h(strtolower($s['name'])) ?></span></div>
<?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="choco-line" aria-hidden="true"></div>

      <!-- WORKS -->
      <section id="works" class="works dots">
        <header class="section-head reveal">
          <p class="section-label">my works</p>
          <h2>Selected <em>Projects</em></h2>
          <img class="cream-line pipe" src="images/assets/Whipped_creaml_line.png" alt="" />
          <p class="note">click any piece to take a closer look</p>
        </header>

        <div class="works-grid" data-gallery data-live="featured">
<?php foreach ($featured as $w): ?>
          <?= work_card($w) ?>

<?php endforeach; ?>
        </div>

        <header class="shelf-head reveal">
          <p class="section-label">dig deeper</p>
          <h2>Pick a <em>slice</em></h2>
          <img class="cream-line pipe" src="images/assets/Whipped_creaml_line.png" alt="" />
        </header>

        <div class="shelf" data-live="shelf">
<?php foreach (SECTIONS as $key => $s): $peeks = array_slice(works_in($key), 0, 3); ?>
          <a href="<?= h($s['page']) ?>" class="folder reveal">
            <div class="folder-visual">
              <img class="folder-cake" src="images/assets/Folder_biscuit.png" alt="" />
<?php foreach ($peeks as $p): ?>
              <img class="folder-peek" src="<?= h($p['image']) ?>" alt="" loading="lazy" />
<?php endforeach; ?>
            </div>
            <span class="folder-count"><?= h(count_label($counts[$key], $s['unit'])) ?></span>
            <h3><?= h($s['name']) ?></h3>
            <p><?= h($s['blurb']) ?></p>
            <span class="folder-go">Open ♡</span>
          </a>

<?php endforeach; ?>
        </div>
      </section>

      <!-- CTA -->
      <section class="cta dots">
        <div class="cta-card reveal">
          <img class="cta-wing cta-wing--left" src="images/assets/Wing_white_left.png" alt="" />
          <img class="cta-wing cta-wing--right" src="images/assets/Wing_white_right.png" alt="" />
          <h2>Got an idea <em>brewing?</em></h2>
          <p>Commissions, collabs, or just a sweet hello. My inbox is always open!</p>
          <a href="contact.php" class="btn btn--choco">Let's talk ♡</a>
        </div>
      </section>

    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
