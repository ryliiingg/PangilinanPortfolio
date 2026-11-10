<?php
// Tells open pages whether the works changed since they loaded. Answers {"v": "<number>"}.
require __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['v' => content_version()]);
