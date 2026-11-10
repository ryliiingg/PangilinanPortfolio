<?php
require __DIR__ . '/bootstrap.php';
$admin = require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $q = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
  $q->execute([$admin['id']]);
  $hash = (string) $q->fetchColumn();
  $new = (string) ($_POST['new'] ?? '');

  if (!password_verify((string) ($_POST['current'] ?? ''), $hash)) $error = 'Your current password isn\'t right.';
  elseif (strlen($new) < 8) $error = 'Please use a new password of at least 8 characters.';
  elseif ($new !== ($_POST['confirm'] ?? '')) $error = 'The two new passwords don\'t match.';
  else {
    db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
    session_regenerate_id(true);
    flash('Your password was changed.');
    redirect('account.php');
  }
}

admin_header('Account', 'account');
?>
      <div class="card narrow">
        <h1>Your account</h1>
        <p class="muted">You're logged in as <strong><?= h($admin['username']) ?></strong>.</p>
<?php if ($error): ?>
        <p class="flash flash--error"><?= h($error) ?></p>
<?php endif; ?>
        <form method="post" class="form">
          <?= csrf_field() ?>
          <h2>Change password</h2>
          <label>Current password <input type="password" name="current" required autocomplete="current-password" /></label>
          <label>New password <input type="password" name="new" required minlength="8" autocomplete="new-password" /></label>
          <label>Confirm new password <input type="password" name="confirm" required minlength="8" autocomplete="new-password" /></label>
          <button type="submit" class="btn">Change password</button>
        </form>
      </div>
<?php admin_footer(); ?>
