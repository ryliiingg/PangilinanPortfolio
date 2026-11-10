<?php
require __DIR__ . '/bootstrap.php';

if (!admin_exists()) redirect('setup.php');
if (current_admin()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  if (too_many_attempts()) {
    $error = 'Too many tries. Please wait 15 minutes and try again.';
  } else {
    $q = db()->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $q->execute([trim($_POST['username'] ?? '')]);
    $admin = $q->fetch();
    if ($admin && password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash'])) {
      clear_attempts();
      log_in((int) $admin['id']);
      redirect('index.php');
    }
    record_failed_attempt();
    $error = 'That username or password isn\'t right.';
  }
}

admin_header('Log in');
?>
      <div class="card narrow">
        <img class="card-bow" src="../images/assets/Strawberry_ribbon.png" alt="" />
        <h1>Welcome back ♡</h1>
<?php if ($error): ?>
        <p class="flash flash--error"><?= h($error) ?></p>
<?php endif; ?>
        <form method="post" class="form">
          <?= csrf_field() ?>
          <label>Username <input type="text" name="username" required autocomplete="username" autofocus /></label>
          <label>Password <input type="password" name="password" required autocomplete="current-password" /></label>
          <button type="submit" class="btn">Log in</button>
        </form>
      </div>
<?php admin_footer(); ?>
