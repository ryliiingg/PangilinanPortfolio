<?php
// First visit only: create the admin account. Works only from this computer, and only while
// no admin exists yet, so nobody else can claim the admin once the site is online.
require __DIR__ . '/bootstrap.php';

if (admin_exists()) redirect('login.php');

$error = '';
if (!is_local_request()) {
  http_response_code(403);
  $error = 'The admin account can only be created on the computer the site runs on.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $username = trim($_POST['username'] ?? '');
  $password = (string) ($_POST['password'] ?? '');
  $confirm  = (string) ($_POST['confirm'] ?? '');

  if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) $error = 'Usernames are 3–30 letters, numbers, dots, dashes or underscores.';
  elseif (strlen($password) < 8) $error = 'Please use a password of at least 8 characters.';
  elseif ($password !== $confirm) $error = 'The two passwords don\'t match.';
  else {
    db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
        ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    log_in((int) db()->lastInsertId());
    flash('Your admin account is ready. Welcome! ♡');
    redirect('index.php');
  }
}

admin_header('Set up');
?>
      <div class="card narrow">
        <img class="card-bow" src="../images/assets/Strawberry_ribbon.png" alt="" />
        <h1>Set up your admin</h1>
        <p class="muted">Choose the username and password you'll use to manage your portfolio. You only do this once.</p>
<?php if ($error): ?>
        <p class="flash flash--error"><?= h($error) ?></p>
<?php endif; ?>
<?php if (is_local_request()): ?>
        <form method="post" class="form">
          <?= csrf_field() ?>
          <label>Username <input type="text" name="username" required autocomplete="username" value="<?= h($_POST['username'] ?? '') ?>" /></label>
          <label>Password <input type="password" name="password" required minlength="8" autocomplete="new-password" /></label>
          <label>Confirm password <input type="password" name="confirm" required minlength="8" autocomplete="new-password" /></label>
          <button type="submit" class="btn">Create my account</button>
        </form>
<?php endif; ?>
      </div>
<?php admin_footer(); ?>
