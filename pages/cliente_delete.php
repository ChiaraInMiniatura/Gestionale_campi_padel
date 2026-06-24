<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
if ($id) {
    Cliente::delete($id);
}
header('Location: dashboard.php');
exit;
