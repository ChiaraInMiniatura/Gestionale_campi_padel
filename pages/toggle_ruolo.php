<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $cliente = Cliente::findById($id);
    if ($cliente) {
        $nuovoRuolo = $cliente['ruolo'] === 'admin' ? 'cliente' : 'admin';
        Cliente::aggiornaRuolo($id, $nuovoRuolo);
    }
}

header('Location: ' . BASE_URL . '/pages/dashboard.php');
exit;
