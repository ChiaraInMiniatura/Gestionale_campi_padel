<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$data_redirect = date('Y-m-d');

if ($id) {
    $prenotazione = Prenotazione::findById($id);
    if ($prenotazione) {
        $data_redirect = $prenotazione['data'];
        Prenotazione::delete($id);
    }
}
header('Location: calendario.php?data=' . urlencode($data_redirect));
exit;
