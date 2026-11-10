<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
check_csrf();

$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
redirect('login.php');
