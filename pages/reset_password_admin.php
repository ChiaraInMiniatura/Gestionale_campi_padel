<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = (int) ($_POST['id'] ?? 0);
    $nuovaPassword = trim($_POST['nuova_password'] ?? '');

    if ($id > 0 && $nuovaPassword !== '') {
        Cliente::resetPassword($id, $nuovaPassword);
    }
}

header('Location: ' . BASE_URL . '/pages/dashboard.php');
exit;
