<?php
// Saves a contact-form message so it shows up in the admin inbox. Answers with JSON.
require __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function reply(int $status, array $body): void
{
  http_response_code($status);
  echo json_encode($body);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(405, ['ok' => false, 'error' => 'POST only']);

// bots fill the hidden "_honey" field; pretend it worked and save nothing
if (trim($_POST['_honey'] ?? '') !== '') reply(200, ['ok' => true]);

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || mb_strlen($name) > 100) reply(422, ['ok' => false, 'error' => 'Please add your name.']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 200) reply(422, ['ok' => false, 'error' => 'Please check your email address.']);
if (mb_strlen($subject) > 150) reply(422, ['ok' => false, 'error' => 'The subject is too long.']);
if ($message === '' || mb_strlen($message) > 5000) reply(422, ['ok' => false, 'error' => 'Please write a message (up to 5000 characters).']);

// at most 5 messages an hour from one visitor (stored as a hash, not the raw IP)
$ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . DB_FILE);
$recent = db()->prepare("SELECT COUNT(*) FROM messages WHERE ip_hash = ? AND created_at > datetime('now', '-1 hour')");
$recent->execute([$ipHash]);
if ((int) $recent->fetchColumn() >= 5) reply(429, ['ok' => false, 'error' => 'Too many messages, please try again later.']);

db()->prepare('INSERT INTO messages (name, email, subject, message, ip_hash) VALUES (?, ?, ?, ?, ?)')
    ->execute([$name, $email, $subject, $message, $ipHash]);

reply(200, ['ok' => true]);
