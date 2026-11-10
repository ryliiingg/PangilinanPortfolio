<?php
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact';
$active = 'contact';
$isSent = isset($_GET['sent']);
require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="dots">

      <header class="page-hero dots">
        <div class="reveal">
          <p class="crumbs"><a href="index.php">home</a> / contact</p>
          <div class="page-plaque biscuit bow-top">
            <h1 class="page-title">Let's <em>Connect</em></h1>
            <img class="cream-line pipe" src="images/assets/Whipped_creaml_line.png" alt="" />
          </div>
          <p class="page-sub">Have a project in mind? I'd love to hear from you.</p>
        </div>
      </header>

      <section class="contact-page">
        <div class="contact-wrap">

          <!-- CONTACT FORM (first on mobile, left on desktop) -->
          <div class="contact-form-box biscuit bow-top reveal <?php echo $isSent ? 'sent' : ''; ?>">
            <p class="form-label">write me a little note ✎</p>

            <!-- Sent through FormSubmit (formsubmit.co), which forwards the message to the inbox below -->
            <form class="contact-form" action="https://formsubmit.co/andreapangilinan1024@gmail.com" method="POST" data-to="andreapangilinan1024@gmail.com">
              <input type="hidden" name="_subject" value="New message from your portfolio ♡" />
              <input type="hidden" name="_template" value="table" />
              <input type="hidden" name="_captcha" value="false" />
              <input type="hidden" name="_next" value="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8'); ?>/contact.php?sent=true" />
              <input type="text" name="_honey" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true" />

              <div class="form-group">
                <label class="form-lbl" for="name">Your Name</label>
                <input class="form-input" type="text" id="name" name="name" placeholder="Andrea ♡" required />
              </div>

              <div class="form-group">
                <label class="form-lbl" for="email">Your Email</label>
                <input class="form-input" type="email" id="email" name="email" placeholder="hello@email.com" required />
              </div>

              <div class="form-group">
                <label class="form-lbl" for="subject">Subject</label>
                <input class="form-input" type="text" id="subject" name="subject" placeholder="Commission, collab, or just saying hi!" />
              </div>

              <div class="form-group">
                <label class="form-lbl" for="message">Message</label>
                <textarea class="form-textarea" id="message" name="message" rows="5" placeholder="Write your sweet message here..." required></textarea>
                <span class="char-count" id="charCount" aria-live="polite"></span>
              </div>

              <button class="btn btn--choco form-btn" type="submit">Send Message ♡</button>
              <p class="form-alt">or email me directly at <a href="mailto:andreapangilinan1024@gmail.com">andreapangilinan1024@gmail.com</a></p>

            </form>

            <div class="form-sent" role="status">
              <img src="images/assets/Wings_white.png" alt="" />
              <h3>Message sent!</h3>
              <p>Thank you for writing. I'll get back to you soon ♡</p>
              <button class="btn" type="button" data-write-again>Write another</button>
            </div>
          </div>

          <!-- CONTACT INFO (second on mobile, right on desktop) -->
          <div class="contact-info reveal">
            <p class="section-label">reach me at</p>
            <h2>Say <em>Hello</em></h2>
            <p class="contact-info-text">
              Whether it's a commission, collaboration, or just a sweet message,
              my inbox is always open!
            </p>

            <div class="contact-details">
              <div class="contact-detail-item">
                <span class="detail-icon" aria-hidden="true">✉</span>
                <a class="txt" href="mailto:andreapangilinan1024@gmail.com">andreapangilinan1024@gmail.com</a>
                <button class="copy-btn" type="button" data-copy="andreapangilinan1024@gmail.com" aria-label="Copy email address">copy</button>
              </div>
              <a class="contact-detail-item" href="https://github.com/ryliiingg" target="_blank" rel="noopener">
                <span class="detail-icon" aria-hidden="true"><img src="images/assets/icon-github.png" alt="" /></span>
                <span class="txt">ryliiingg on GitHub</span>
              </a>
              <a class="contact-detail-item" href="https://www.instagram.com/applsyrp" target="_blank" rel="noopener">
                <span class="detail-icon" aria-hidden="true"><img src="images/assets/icon-github-logo.png" alt="" /></span>
                <span class="txt">@applsyrp on Instagram</span>
              </a>
              <a class="contact-detail-item" href="https://www.facebook.com/profile.php?id=61592928724310" target="_blank" rel="noopener">
                <span class="detail-icon" aria-hidden="true"><img src="images/assets/icon-facebook.png" alt="" /></span>
                <span class="txt">Facebook</span>
              </a>
            </div>

            <div class="contact-treats" aria-hidden="true">
              <div class="treat-biscuit">
                <img src="images/assets/Blank_comment_biscuitbox.png" alt="" />
                <span>my inbox is<br />always open ♡</span>
              </div>
              <img class="treat-cake" src="images/assets/Folder_biscuit.png" alt="" />
            </div>
          </div>

        </div>
      </section>

    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
