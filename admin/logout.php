<?php
require_once __DIR__ . '/../config/init.php';

if (isset($_SESSION['admin_id'])) {
    session_regenerate_id(true);
}
session_unset();
session_destroy();
header('Location: login.php');
exit;
