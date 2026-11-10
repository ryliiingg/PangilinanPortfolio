<?php
// Site settings. Everything else reads from here.

date_default_timezone_set('Asia/Manila');

// The database is one SQLite file in /data (blocked from the web by data/.htaccess).
define('DB_FILE', __DIR__ . '/../data/portfolio.sqlite');

// Uploaded artwork goes here (relative to the site root, as used in <img src>).
define('UPLOAD_DIR', 'images/uploads');
define('UPLOAD_MAX_BYTES', 10 * 1024 * 1024); // 10 MB

define('SITE_EMAIL', 'andreapangilinan1024@gmail.com');

// The three galleries. 'filters' are the filter buttons on that page (only Illustrations has them).
const SECTIONS = [
  'illustrations' => [
    'name'    => 'Illustrations',
    'page'    => 'illustrations.php',
    'unit'    => ['piece', 'pieces'],
    'blurb'   => 'Characters, fanart and commissions.',
    'heading' => 'My <em>Illustrations</em>',
    'sub'     => 'A collection of my hand-crafted illustrations and character art.',
    'note'    => '',
    'filters' => [
      'oc'           => ['Original Characters', 'original character'],
      'fanart'       => ['Fanart', 'fanart'],
      'illustration' => ['Illustrations', 'illustration'],
      'commission'   => ['Commissions', 'commission'],
      'poster'       => ['Posters', 'poster'],
      'logo'         => ['Pixel Logos', 'pixel logo'],
    ],
  ],
  'multimedia' => [
    'name'    => 'Multimedia',
    'page'    => 'multimedia.php',
    'unit'    => ['project', 'projects'],
    'blurb'   => 'Flyers, posters and billboard mockups.',
    'heading' => 'Multimedia <em>Projects</em>',
    'sub'     => 'Design, layout, and multimedia works.',
    'note'    => 'tap a mockup to see it up close ✿',
    'filters' => [],
  ],
  'certificates' => [
    'name'    => 'Certificates',
    'page'    => 'certificates.php',
    'unit'    => ['certificate', 'certificates'],
    'blurb'   => 'Certifications and little achievements.',
    'heading' => 'My <em>Certificates</em>',
    'sub'     => 'A showcase of my certifications, awards, and achievements.',
    'note'    => 'little wins, pinned to the wall ✿',
    'filters' => [],
  ],
];
