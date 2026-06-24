<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pinea Padel</title>
    <link rel="stylesheet" href="/gestionale_padel/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/gestionale_padel/node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/gestionale_padel/assets/style.css">
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_utenteLoggato = isset($_SESSION['cliente_id']);
$_nomeUtente    = $_utenteLoggato ? ($_SESSION['cliente_nome'] ?? '') : '';
$_ruoloUtente   = $_utenteLoggato ? ($_SESSION['cliente_ruolo'] ?? '') : '';
?>

<nav class="navbar navbar-expand-lg navbar-pubblica">
    <div class="container">
        <a class="navbar-brand navbar-brand-pubblica" href="/gestionale_padel/index.php">
            <i class="fa-solid fa-table-tennis-paddle-ball"></i> Pinea Padel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link-pubblica <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'attivo' : '' ?>" href="/gestionale_padel/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-pubblica <?= basename($_SERVER['PHP_SELF']) === 'prenota.php' ? 'attivo' : '' ?>" href="/gestionale_padel/prenota.php">Prenota</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-pubblica <?= basename($_SERVER['PHP_SELF']) === 'lezioni.php' ? 'attivo' : '' ?>" href="/gestionale_padel/lezioni.php">Lezioni</a>
                </li>
                <?php if ($_utenteLoggato): ?>
                    <?php if ($_ruoloUtente === 'admin'): ?>
                        <li class="nav-item ms-lg-3">
                            <a class="btn-nav-registrati" href="/gestionale_padel/pages/dashboard.php">
                                <i class="fa-solid fa-gauge me-1"></i>Gestionale
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <span class="nav-link-pubblica">
                                <i class="fa-solid fa-circle-user me-1"></i><?= htmlspecialchars($_nomeUtente) ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn-nav-login" href="/gestionale_padel/pages/logout.php">Esci</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn-nav-registrati" href="/gestionale_padel/ui/page/register.php">Registrati</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn-nav-login" href="/gestionale_padel/ui/page/login.php">Accedi</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
