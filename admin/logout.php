<?php
require_once __DIR__ . '/../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['admin_id'])) {
    session_regenerate_id(true);
}
session_unset();
session_destroy();
header('Location: login.php');
exit;
