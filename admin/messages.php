<?php
// Messages sent through the contact form.
require __DIR__ . '/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id = (int) ($_POST['id'] ?? 0);
  switch ($_POST['do'] ?? '') {
    case 'read':   db()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]); finish('messages.php');
    case 'unread': db()->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]); finish('messages.php');
    case 'delete': db()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]); finish('messages.php', 'Message deleted.');
  }
  finish('messages.php');
}

$messages = db()->query('SELECT * FROM messages ORDER BY created_at DESC, id DESC')->fetchAll();

admin_header('Messages', 'messages');
?>
      <div class="page-head">
        <div>
          <h1>Messages</h1>
          <p class="muted">Notes people sent through your contact page. They're also emailed to <?= h(SITE_EMAIL) ?>.</p>
        </div>
      </div>

      <div data-live="messages">
<?php if (!$messages): ?>
      <p class="empty"><img src="../images/assets/Folder_biscuit.png" alt="" />No messages yet. They'll show up here when someone writes to you ♡</p>
<?php endif; ?>
<?php foreach ($messages as $m): ?>
      <article class="message<?= $m['is_read'] ? '' : ' unread' ?>">
        <header>
          <div>
            <strong><?= h($m['name']) ?></strong>
            <a href="mailto:<?= h($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: 'your message')) ?>"><?= h($m['email']) ?></a>
          </div>
          <time class="muted"><?= h(date('M j, Y · g:i A', strtotime($m['created_at'] . ' UTC'))) ?></time>
        </header>
<?php if ($m['subject'] !== ''): ?>
        <h2><?= h($m['subject']) ?></h2>
<?php endif; ?>
        <p><?= nl2br(h($m['message'])) ?></p>
        <footer>
          <a class="btn btn--small" href="mailto:<?= h($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: 'your message')) ?>">Reply</a>
          <form method="post" data-ajax>
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
            <button name="do" value="<?= $m['is_read'] ? 'unread' : 'read' ?>" class="btn btn--small btn--ghost"><?= $m['is_read'] ? 'Mark as unread' : 'Mark as read' ?></button>
          </form>
          <form method="post" data-ajax data-confirm="Delete this message from <?= h($m['name']) ?>?">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
            <button name="do" value="delete" class="btn btn--small btn--danger">Delete</button>
          </form>
        </footer>
      </article>
<?php endforeach; ?>
      </div>
<?php admin_footer(); ?>
