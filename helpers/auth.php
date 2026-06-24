<?php

require_once __DIR__ . '/../config/config.php';

function requireLogin(): void
{
    if (session_status() === PHP_SESSION_NONE) {

        session_start();

    }

    if (!isset($_SESSION['cliente_id'])) {

        header('Location: ' . BASE_URL . '/ui/page/login.php');
        exit;

    }
}

function requireAdmin(): void
{
    requireLogin();

    if (($_SESSION['cliente_ruolo'] ?? '') !== 'admin') {

        http_response_code(403);
        echo 'Accesso non consentito: questa pagina è riservata agli admin.';
        exit;

    }
}

function currentUser(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {

        session_start();

    }

    if (!isset($_SESSION['cliente_id'])) {

        return null;

    }

    return [
        'id'    => $_SESSION['cliente_id'],
        'nome'  => $_SESSION['cliente_nome']  ?? '',
        'ruolo' => $_SESSION['cliente_ruolo'] ?? 'cliente',
    ];
}

// Alias per compatibilità con il codice esistente
function currentDipendente(): ?array
{
    return currentUser();
}

function isAdmin(): bool
{
    $user = currentUser();
    return $user !== null && $user['ruolo'] === 'admin';
}

function effettuaLogin(array $cliente): void
{
    if (session_status() === PHP_SESSION_NONE) {

        session_start();

    }

    $_SESSION['cliente_id']    = $cliente['id'];
    $_SESSION['cliente_nome']  = $cliente['nome'];
    $_SESSION['cliente_ruolo'] = $cliente['ruolo'];
}

function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {

        session_start();

    }

    $_SESSION = [];
    session_destroy();

    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

?>
