<?php
// Small actions from the works list: delete, move up/down, show on homepage.
require __DIR__ . '/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
check_csrf();

$q = db()->prepare('SELECT * FROM works WHERE id = ?');
$q->execute([(int) ($_POST['id'] ?? 0)]);
$work = $q->fetch();
if (!$work) finish('index.php', 'That work no longer exists.', 'error');

$back = 'index.php?section=' . urlencode($work['section']);

switch ($_POST['do'] ?? '') {
  case 'delete':
    db()->prepare('DELETE FROM works WHERE id = ?')->execute([$work['id']]);
    delete_upload($work['image']);
    $message = '“' . $work['title'] . '” was deleted.';
    break;

  case 'feature':
    if ($work['featured']) {
      db()->prepare('UPDATE works SET featured = 0 WHERE id = ?')->execute([$work['id']]);
      $message = '“' . $work['title'] . '” is no longer on your homepage.';
    } else {
      // new homepage picks go to the end of the Selected Projects row
      $next = (int) db()->query('SELECT COALESCE(MAX(home_sort), 0) + 1 FROM works')->fetchColumn();
      db()->prepare('UPDATE works SET featured = 1, home_sort = ? WHERE id = ?')->execute([$next, $work['id']]);
      $message = '“' . $work['title'] . '” now shows on your homepage.';
    }
    break;

  case 'up':
  case 'down':
    // swap places with the neighbouring work in the same gallery
    $list = works_in($work['section']);
    $ids = array_column($list, 'id');
    $pos = array_search($work['id'], $ids);
    $swap = $_POST['do'] === 'up' ? $pos - 1 : $pos + 1;
    if (isset($list[$swap])) {
      $ids[$pos] = $list[$swap]['id'];
      $ids[$swap] = $work['id'];
      $update = db()->prepare('UPDATE works SET sort = ? WHERE id = ?');
      db()->beginTransaction();
      foreach ($ids as $order => $id) $update->execute([$order + 1, $id]);
      db()->commit();
    }
    break;
}

// open pages of the website pick the change up within a few seconds
bump_content();
finish($back, $message ?? '');
