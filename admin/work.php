<?php
// Add a new work (work.php?section=...) or edit one (work.php?id=...).
require __DIR__ . '/bootstrap.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$work = null;
if ($id) {
  $q = db()->prepare('SELECT * FROM works WHERE id = ?');
  $q->execute([$id]);
  $work = $q->fetch();
  if (!$work) { flash('That work no longer exists.', 'error'); redirect('index.php'); }
}

$defaults = ['section' => isset(SECTIONS[$_GET['section'] ?? '']) ? $_GET['section'] : 'illustrations',
             'category' => '', 'tag' => '', 'title' => '', 'description' => '', 'image' => '', 'contain' => 0, 'featured' => 0];
$form = $work ?: $defaults;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $form = [
    'section'     => (string) ($_POST['section'] ?? ''),
    'category'    => (string) ($_POST['category'] ?? ''),
    'tag'         => trim($_POST['tag'] ?? ''),
    'title'       => trim($_POST['title'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'image'       => $work['image'] ?? '',
    'contain'     => isset($_POST['contain']) ? 1 : 0,
    'featured'    => isset($_POST['featured']) ? 1 : 0,
  ];
  $filters = SECTIONS[$form['section']]['filters'] ?? null;

  if ($filters === null) $error = 'Please choose where this work goes.';
  elseif ($form['title'] === '' || mb_strlen($form['title']) > 120) $error = 'Please give it a title (up to 120 characters).';
  elseif (mb_strlen($form['tag']) > 40) $error = 'The small label is too long (up to 40 characters).';
  elseif (mb_strlen($form['description']) > 500) $error = 'The description is too long (up to 500 characters).';
  elseif ($filters && !isset($filters[$form['category']])) $error = 'Please choose a type.';

  if (!$filters) $form['category'] = '';
  // no label typed? use the type's name, e.g. "original character"
  if (!$error && $form['tag'] === '' && $form['category'] !== '') $form['tag'] = $filters[$form['category']][1];

  $newImage = null;
  if (!$error && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try { $newImage = save_upload($_FILES['image']); } catch (RuntimeException $e) { $error = $e->getMessage(); }
  }
  if (!$error && !$newImage && $form['image'] === '') $error = 'Please choose an image.';

  if (!$error) {
    $oldImage = $form['image'];
    if ($newImage) $form['image'] = $newImage;
    $db = db();

    if ($work) {
      // moving to another gallery puts it at the end of that gallery
      $sort = $work['sort'];
      if ($work['section'] !== $form['section']) {
        $q = $db->prepare('SELECT COALESCE(MAX(sort), 0) + 1 FROM works WHERE section = ?');
        $q->execute([$form['section']]);
        $sort = (int) $q->fetchColumn();
      }
      $homeSort = $work['home_sort'];
      if ($form['featured'] && !$work['featured']) $homeSort = (int) $db->query('SELECT COALESCE(MAX(home_sort), 0) + 1 FROM works')->fetchColumn();
      $db->prepare('UPDATE works SET section = ?, category = ?, tag = ?, title = ?, description = ?, image = ?, contain = ?, featured = ?, home_sort = ?, sort = ? WHERE id = ?')
         ->execute([$form['section'], $form['category'], $form['tag'], $form['title'], $form['description'], $form['image'], $form['contain'], $form['featured'], $homeSort, $sort, $work['id']]);
      if ($newImage) delete_upload($oldImage);
      bump_content();
      flash('“' . $form['title'] . '” was saved.');
    } else {
      $q = $db->prepare('SELECT COALESCE(MAX(sort), 0) + 1 FROM works WHERE section = ?');
      $q->execute([$form['section']]);
      $sort = (int) $q->fetchColumn();
      $homeSort = $form['featured'] ? (int) $db->query('SELECT COALESCE(MAX(home_sort), 0) + 1 FROM works')->fetchColumn() : 0;
      $db->prepare('INSERT INTO works (section, category, tag, title, description, image, contain, featured, home_sort, sort) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
         ->execute([$form['section'], $form['category'], $form['tag'], $form['title'], $form['description'], $form['image'], $form['contain'], $form['featured'], $homeSort, $sort]);
      bump_content();
      flash('“' . $form['title'] . '” was added to ' . SECTIONS[$form['section']]['name'] . '.');
    }
    redirect('index.php?section=' . urlencode($form['section']));
  }
  // a new upload that didn't get saved shouldn't linger
  if ($error && $newImage) delete_upload($newImage);
}

admin_header($work ? 'Edit work' : 'Add a work', 'works');
?>
      <p><a href="index.php?section=<?= h($form['section']) ?>">← Back to your works</a></p>
      <div class="card">
        <h1><?= $work ? 'Edit “' . h($work['title']) . '”' : 'Add a work' ?></h1>
<?php if ($error): ?>
        <p class="flash flash--error"><?= h($error) ?></p>
<?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="form form--work">
          <?= csrf_field() ?>
          <div class="form-cols">
            <div>
              <label>Where does it go?
                <select name="section" id="section">
<?php foreach (SECTIONS as $key => $s): ?>
                  <option value="<?= h($key) ?>"<?= $form['section'] === $key ? ' selected' : '' ?>><?= h($s['name']) ?></option>
<?php endforeach; ?>
                </select>
              </label>

<?php foreach (SECTIONS as $key => $s): if (!$s['filters']) continue; ?>
              <label class="category-field" data-for="<?= h($key) ?>">Type (for the filter buttons)
                <select name="category">
                  <option value="">Choose…</option>
<?php foreach ($s['filters'] as $cat => [$label]): ?>
                  <option value="<?= h($cat) ?>"<?= $form['category'] === $cat ? ' selected' : '' ?>><?= h($label) ?></option>
<?php endforeach; ?>
                </select>
              </label>
<?php endforeach; ?>

              <label>Title <input type="text" name="title" maxlength="120" required value="<?= h($form['title']) ?>" /></label>
              <label>Small label <span class="muted">(the pink tag above the title, e.g. “poster” or “Cisco · 2025”; leave empty to use the type)</span>
                <input type="text" name="tag" maxlength="40" value="<?= h($form['tag']) ?>" />
              </label>
              <label>Description <span class="muted">(optional)</span>
                <textarea name="description" rows="3" maxlength="500"><?= h($form['description']) ?></textarea>
              </label>
              <label class="check"><input type="checkbox" name="featured"<?= $form['featured'] ? ' checked' : '' ?> /> Show on my homepage (Selected Projects)</label>
              <label class="check"><input type="checkbox" name="contain"<?= $form['contain'] ? ' checked' : '' ?> /> Show the whole image without cropping (good for logos and certificates)</label>
            </div>

            <div>
              <span class="label">Image <?= $work ? '<span class="muted">(choose a new one only to replace it)</span>' : '' ?></span>
              <label class="drop">
                <img id="preview" src="<?= $form['image'] ? '../' . h($form['image']) : '' ?>" alt=""<?= $form['image'] ? '' : ' hidden' ?> />
                <span id="drop-text"<?= $form['image'] ? ' hidden' : '' ?>>Click to choose an image<br /><small>JPG, PNG, WEBP or GIF, up to 10 MB</small></span>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp,image/gif"<?= $work ? '' : ' required' ?> />
              </label>
            </div>
          </div>
          <button type="submit" class="btn"><?= $work ? 'Save changes' : 'Add work' ?></button>
        </form>
      </div>
<?php admin_footer(); ?>
