<?php
// Shared by every admin page: session, login checks, form protection, uploads and the page layout.
require_once __DIR__ . '/../includes/functions.php';

// keep the admin out of search engines, out of other sites' frames, and out of the browser cache
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

session_name('ap_admin');
session_set_cookie_params([
  'httponly' => true,
  'samesite' => 'Lax',
  'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// ---------- login ----------

function admin_exists(): bool
{
  return (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
}

function current_admin(): ?array
{
  if (empty($_SESSION['admin_id'])) return null;
  $q = db()->prepare('SELECT id, username FROM admins WHERE id = ?');
  $q->execute([$_SESSION['admin_id']]);
  return $q->fetch() ?: null;
}

function log_in(int $adminId): void
{
  session_regenerate_id(true);
  $_SESSION['admin_id'] = $adminId;
}

// Every admin page except login/setup starts with this.
function require_login(): array
{
  if (!admin_exists()) redirect('setup.php');
  $admin = current_admin();
  if (!$admin) redirect('login.php');
  return $admin;
}

// The first admin account can only be created from this computer.
function is_local_request(): bool
{
  return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

function ip_hash(): string
{
  return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . DB_FILE);
}

// 5 wrong passwords within 15 minutes locks that visitor out for a while
function too_many_attempts(): bool
{
  $q = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ? AND attempted_at > ?');
  $q->execute([ip_hash(), time() - 15 * 60]);
  return (int) $q->fetchColumn() >= 5;
}

function record_failed_attempt(): void
{
  db()->prepare('INSERT INTO login_attempts (ip_hash, attempted_at) VALUES (?, ?)')->execute([ip_hash(), time()]);
  db()->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')->execute([time() - 24 * 3600]);
}

function clear_attempts(): void
{
  db()->prepare('DELETE FROM login_attempts WHERE ip_hash = ?')->execute([ip_hash()]);
}

// ---------- forms ----------

function csrf_token(): string
{
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}

function csrf_field(): string
{
  return '<input type="hidden" name="csrf" value="' . csrf_token() . '" />';
}

// Stops other websites from submitting forms to the admin on your behalf.
function check_csrf(): void
{
  if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(400);
    exit('That form expired. Please go back, refresh the page and try again.');
  }
}

function redirect(string $to): void
{
  header('Location: ' . $to);
  exit;
}

// Admin buttons send their forms from the page's script (header X-Requested-With: fetch) and get
// a short JSON answer, so the page never reloads. Without the script, forms still work normally.
function is_ajax(): bool
{
  return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

function finish(string $redirect, string $message = '', string $type = 'ok'): void
{
  if (is_ajax()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $type === 'ok', 'message' => $message]);
    exit;
  }
  if ($message !== '') flash($message, $type);
  redirect($redirect);
}

function flash(?string $message = null, string $type = 'ok'): ?array
{
  if ($message !== null) { $_SESSION['flash'] = [$message, $type]; return null; }
  $f = $_SESSION['flash'] ?? null;
  unset($_SESSION['flash']);
  return $f;
}

// ---------- uploads ----------

// Saves an uploaded image into images/uploads with a random name and returns its path
// (e.g. "images/uploads/3f9c2a1b7d4e6f80.jpg"). Throws a friendly message if something's wrong.
function save_upload(array $file): string
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $errors = [
      UPLOAD_ERR_INI_SIZE => 'That image is too big.',
      UPLOAD_ERR_FORM_SIZE => 'That image is too big.',
      UPLOAD_ERR_PARTIAL => 'The upload was interrupted, please try again.',
      UPLOAD_ERR_NO_FILE => 'Please choose an image.',
    ];
    throw new RuntimeException($errors[$file['error']] ?? 'The upload failed, please try again.');
  }
  if ($file['size'] > UPLOAD_MAX_BYTES) throw new RuntimeException('That image is over 10 MB.');

  // trust the file's actual contents, not its name
  $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
  if (!isset($types[$mime]) || !getimagesize($file['tmp_name'])) {
    throw new RuntimeException('Please upload a JPG, PNG, WEBP or GIF image.');
  }

  $dir = __DIR__ . '/../' . UPLOAD_DIR;
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
  if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Could not save the image.');
  return UPLOAD_DIR . '/' . $name;
}

// Only ever deletes files the admin uploaded, never the original site images.
function delete_upload(string $path): void
{
  if (strpos($path, UPLOAD_DIR . '/') !== 0 || strpos($path, '..') !== false) return;
  $full = __DIR__ . '/../' . $path;
  if (is_file($full)) unlink($full);
}

// ---------- layout ----------

function unread_count(): int
{
  return (int) db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
}

function admin_header(string $title, string $nav = ''): void
{
  $admin = current_admin();
  $unread = $admin ? unread_count() : 0;
  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" type="image/png" href="../images/favicon.png" />
    <title><?= h($title) ?> – Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="admin.css?v=<?= asset_version() ?>" />
  </head>
  <body>
<?php if ($admin): ?>
    <header class="topbar">
      <a class="brand" href="index.php"><img src="../images/assets/Strawberry_ribbon.png" alt="" /> Admin</a>
      <nav>
        <a href="index.php"<?= $nav === 'works' ? ' class="on"' : '' ?>>Works</a>
        <a href="messages.php"<?= $nav === 'messages' ? ' class="on"' : '' ?>>Messages <span class="badge" data-live="badge"><?= $unread ?: '' ?></span></a>
        <a href="account.php"<?= $nav === 'account' ? ' class="on"' : '' ?>>Account</a>
        <a href="../index.php" target="_blank" rel="noopener">View site ↗</a>
        <form method="post" action="logout.php"><?= csrf_field() ?><button type="submit" class="link">Log out</button></form>
      </nav>
    </header>
<?php endif; ?>
    <main class="page">
<?php if ($f = flash()): ?>
      <p class="flash flash--<?= h($f[1]) ?>"><?= h($f[0]) ?></p>
<?php endif;
}

function admin_footer(): void
{
  ?>
    </main>
    <script src="admin.js?v=<?= asset_version() ?>"></script>
  </body>
</html>
<?php
}
