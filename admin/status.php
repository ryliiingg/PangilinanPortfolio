<?php
// Polled by admin pages: a fingerprint of the works and messages, so a page can pull in
// fresh content the moment anything changes (a new message, or an edit from another tab).
require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
if (!current_admin()) { http_response_code(401); echo json_encode(['ok' => false]); exit; }

$m = db()->query('SELECT COUNT(*), COALESCE(MAX(id), 0), COALESCE(SUM(is_read), 0) FROM messages')->fetch(PDO::FETCH_NUM);
echo json_encode(['ok' => true, 'sig' => content_version() . '|' . implode('-', $m)]);
