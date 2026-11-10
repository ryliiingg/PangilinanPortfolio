<?php
// Database connection. The tables are created (and filled with the current works) the first
// time the site runs, so there's nothing to set up by hand.

require_once __DIR__ . '/config.php';

function db(): PDO
{
  static $pdo = null;
  if ($pdo) return $pdo;

  $isNew = !file_exists(DB_FILE);
  if (!is_dir(dirname(DB_FILE))) mkdir(dirname(DB_FILE), 0775, true);

  $pdo = new PDO('sqlite:' . DB_FILE);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  $pdo->exec('PRAGMA foreign_keys = ON');

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS works (
      id          INTEGER PRIMARY KEY AUTOINCREMENT,
      section     TEXT    NOT NULL,
      category    TEXT    NOT NULL DEFAULT '',
      tag         TEXT    NOT NULL DEFAULT '',
      title       TEXT    NOT NULL,
      description TEXT    NOT NULL DEFAULT '',
      image       TEXT    NOT NULL,
      contain     INTEGER NOT NULL DEFAULT 0,
      featured    INTEGER NOT NULL DEFAULT 0,
      home_sort   INTEGER NOT NULL DEFAULT 0,
      sort        INTEGER NOT NULL DEFAULT 0,
      created_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS messages (
      id         INTEGER PRIMARY KEY AUTOINCREMENT,
      name       TEXT    NOT NULL,
      email      TEXT    NOT NULL,
      subject    TEXT    NOT NULL DEFAULT '',
      message    TEXT    NOT NULL,
      ip_hash    TEXT    NOT NULL DEFAULT '',
      is_read    INTEGER NOT NULL DEFAULT 0,
      created_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS admins (
      id            INTEGER PRIMARY KEY AUTOINCREMENT,
      username      TEXT    NOT NULL UNIQUE,
      password_hash TEXT    NOT NULL,
      created_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS login_attempts (
      ip_hash      TEXT NOT NULL,
      attempted_at INTEGER NOT NULL
    );
    CREATE TABLE IF NOT EXISTS meta (
      key   TEXT PRIMARY KEY,
      value TEXT NOT NULL
    );
  ");

  if ($isNew) seed_works($pdo);
  return $pdo;
}

// The works that were on the site before the admin existed.
function seed_works(PDO $pdo): void
{
  $oc = 'An Original Character Design.';
  $itl = 'An illustration for the PLV ITlympics poster making.';
  $works = [
    // section, category, tag, title, description, image, contain, featured (home order)
    ['illustrations', 'oc', 'original character', 'Myu', $oc, 'images/OC-1.jpg', 0, 0],
    ['illustrations', 'oc', 'original character', 'Sakumi', $oc, 'images/OC-4.png', 0, 0],
    ['illustrations', 'oc', 'original character', 'Cina', 'An Original Character designed with Blue Box artstyle.', 'images/OC-3.jpg', 0, 0],
    ['illustrations', 'oc', 'original character', 'Lydia', $oc, 'images/OC-2.jpg', 0, 0],
    ['illustrations', 'oc', 'original character', 'Nameless', $oc, 'images/OC-5.jpg', 0, 0],
    ['illustrations', 'fanart', 'fanart', 'Bamby', 'A Fanart of Bamby from PLAVE.', 'images/Fanart-1.jpg', 0, 0],
    ['illustrations', 'fanart', 'fanart', 'Sawako Kuronuma', 'A Fanart of Sawako Kuronuma from Kimi ni Todoke.', 'images/Fanart-3.jpg', 0, 0],
    ['illustrations', 'fanart', 'fanart', 'Usagi Tsukino', 'A Fanart of Usagi Tsukino from Sailor Moon.', 'images/Fanart-4.jpg', 0, 0],
    ['illustrations', 'commission', 'commission', 'Commissioned', 'A Commissioned Work.', 'images/Commissioned-1.jpg', 0, 0],
    ['illustrations', 'commission', 'commission', 'Commissioned', 'A Commissioned Work.', 'images/Commissioned-2.jpg', 0, 0],
    ['illustrations', 'logo', 'pixel logo', 'Pixel Logo', 'A Pixelized Logo.', 'images/Logo-2.png', 1, 0],
    ['illustrations', 'logo', 'pixel logo', 'Pixel Logo', 'A Pixelized Logo for my branding.', 'images/Logo-3.png', 1, 0],
    ['illustrations', 'illustration', 'illustration', 'My Blue', "An illustration for my partner ♡, inspired by Yung Kai's Blue.", 'images/Illustration-2.jpg', 0, 3],
    ['illustrations', 'illustration', 'illustration', 'Catimagination', 'An illustration for my assignment Visual Arts.', 'images/Illustration-3.jpg', 0, 0],
    ['illustrations', 'illustration', 'illustration', 'Dreaming Pastel', 'An illustration for my assignment in Visual Arts.', 'images/Illustration-4.jpg', 0, 0],
    ['illustrations', 'illustration', 'illustration', 'Fish in a Dream', 'An abstract illustration for my assignment Visual Arts.', 'images/Illustration-1.jpg', 0, 0],
    ['illustrations', 'poster', 'poster', 'ITLympics Poster 1', $itl, 'images/ITlympics-poster-1.jpg', 0, 1],
    ['illustrations', 'poster', 'poster', 'ITLympics Poster 2', $itl, 'images/Itlympics-poster-2.jpg', 0, 2],
    ['multimedia', '', 'flyer', 'Flyer Mockup', 'A visual design of a flyer mockup for MultiMedia.', 'images/Multimedia-flyer.jpg', 0, 0],
    ['multimedia', '', 'poster', 'Poster Mockup', 'A visual design of a Poster mockup for MultiMedia.', 'images/Multimedia-poster.jpg', 0, 0],
    ['multimedia', '', 'billboard', 'Billboard Mockup', 'A visual design of a billboard mockup for MultiMedia.', 'images/Multimedia-billboard.jpg', 0, 0],
    ['certificates', '', 'Cisco · 2025', 'Cisco Certificate', '', 'images/Cert-1.png', 1, 0],
    ['certificates', '', 'Cisco · 2025', 'Cisco Certificate', '', 'images/Cert-2.png', 1, 0],
    ['certificates', '', 'Cisco · 2025', 'Cisco Certificate', '', 'images/Cert-3.png', 1, 0],
    ['certificates', '', 'Cisco · 2025', 'Cisco Certificate', '', 'images/Cert-4.png', 1, 0],
    ['certificates', '', 'CodeSignal · 2026', 'CodeSignal Certificate', '', 'images/Cert-5.png', 1, 0],
  ];

  $insert = $pdo->prepare('INSERT INTO works (section, category, tag, title, description, image, contain, featured, home_sort, sort)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $sort = [];
  $pdo->beginTransaction();
  foreach ($works as [$section, $cat, $tag, $title, $desc, $img, $contain, $home]) {
    $sort[$section] = ($sort[$section] ?? 0) + 1;
    $insert->execute([$section, $cat, $tag, $title, $desc, $img, $contain, $home ? 1 : 0, $home, $sort[$section]]);
  }
  $pdo->commit();
}
