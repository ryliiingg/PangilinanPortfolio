<?php
require_once __DIR__ . '/db.php';

// Escape text for HTML.
function h(?string $s): string
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function works_in(string $section): array
{
  $q = db()->prepare('SELECT * FROM works WHERE section = ? ORDER BY sort, id');
  $q->execute([$section]);
  return $q->fetchAll();
}

function featured_works(): array
{
  return db()->query('SELECT * FROM works WHERE featured = 1 ORDER BY home_sort, id')->fetchAll();
}

// ['illustrations' => 18, 'multimedia' => 3, ...]
function section_counts(): array
{
  $counts = array_fill_keys(array_keys(SECTIONS), 0);
  foreach (db()->query('SELECT section, COUNT(*) AS n FROM works GROUP BY section') as $row) {
    if (isset($counts[$row['section']])) $counts[$row['section']] = (int) $row['n'];
  }
  return $counts;
}

// "1 piece", "18 pieces"
function count_label(int $n, array $unit): string
{
  return $n . ' ' . ($n === 1 ? $unit[0] : $unit[1]);
}

// One polaroid card, the same markup the gallery script and styles expect.
function work_card(array $w, bool $withCategory = false): string
{
  $classes = 'work-card';
  if ($w['section'] === 'certificates') $classes .= ' cert-card';
  if ($w['contain']) $classes .= ' work-card--contain';
  $cat = $withCategory && $w['category'] !== '' ? ' data-cat="' . h($w['category']) . '"' : '';

  $html  = '<article class="' . $classes . '"' . $cat . '>';
  $html .= '<div class="work-media"><img src="' . h($w['image']) . '" alt="' . h($w['title']) . '" class="work-img-placeholder" loading="lazy" /><span class="work-zoom">peek</span></div>';
  $html .= '<div class="work-info">';
  if ($w['tag'] !== '') $html .= '<span class="work-tag">' . h($w['tag']) . '</span>';
  $html .= '<h3>' . h($w['title']) . '</h3>';
  if ($w['description'] !== '') $html .= '<p>' . h($w['description']) . '</p>';
  $html .= '</div></article>';
  return $html;
}

// Stylesheet/script version, so browsers pick up changes without a hard refresh.
function asset_version(): string
{
  return '43';
}

// A number that goes up every time a work is added, changed, moved or removed.
// Open pages compare it (via api/version.php) to know when to pull in fresh content.
function content_version(): string
{
  $v = db()->query("SELECT value FROM meta WHERE key = 'content_version'")->fetchColumn();
  return $v === false ? '1' : (string) $v;
}

function bump_content(): string
{
  $next = (string) ((int) content_version() + 1);
  db()->prepare("INSERT INTO meta (key, value) VALUES ('content_version', ?)
                 ON CONFLICT(key) DO UPDATE SET value = excluded.value")->execute([$next]);
  return $next;
}
