<?php
// Forgot your admin password? Run this from a terminal (not a browser):
//
//   C:\xampp\php\php.exe tools\reset-password.php
//
// It asks for your username and a new password. Your works and messages are not touched.
require __DIR__ . '/../includes/db.php';

if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  exit('Run this from a terminal.');
}

$admins = db()->query('SELECT username FROM admins ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
if (!$admins) exit("There's no admin account yet. Open /admin/ in your browser to create one.\n");

echo 'Admin accounts: ' . implode(', ', $admins) . "\n";
$username = trim(readline('Username to reset: '));
if (!in_array($username, $admins, true)) exit("No admin called \"$username\".\n");

$password = readline('New password (at least 8 characters): ');
if (strlen($password) < 8) exit("Too short, nothing was changed.\n");

db()->prepare('UPDATE admins SET password_hash = ? WHERE username = ?')
    ->execute([password_hash($password, PASSWORD_DEFAULT), $username]);
db()->exec('DELETE FROM login_attempts');
echo "Done. Log in at /admin/ with your new password.\n";
