<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$user = currentUser();
$errore  = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordAttuale  = $_POST['password_attuale'] ?? '';
    $nuovaPassword    = $_POST['nuova_password'] ?? '';
    $confermaPassword = $_POST['conferma_password'] ?? '';

    if ($passwordAttuale === '' || $nuovaPassword === '' || $confermaPassword === '') {
        $errore = 'Tutti i campi sono obbligatori.';
    } elseif ($nuovaPassword !== $confermaPassword) {
        $errore = 'La nuova password e la conferma non coincidono.';
    } else {
        $errore = Cliente::cambiaPassword($user['id'], $passwordAttuale, $nuovaPassword);
        if ($errore === '') {
            $successo = 'Password aggiornata correttamente.';
        }
    }
}

$profilo = Cliente::findById($user['id']);

include __DIR__ . '/../header_dashboard.php';
?>

<div class="dashboard-wrapper">

    <aside class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Comprimi sidebar">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="sidebar-brand">
            <h4 class="logo-text fw-bold mb-0 hide-on-collapse">Gestionale Padel</h4>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>/pages/dashboard.php" class="sidebar-link">
                <i class="fa-solid fa-users"></i>
                <span class="hide-on-collapse">Clienti</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/calendario.php" class="sidebar-link">
                <i class="fa-solid fa-calendar-days"></i>
                <span class="hide-on-collapse">Calendario</span>
            </a>
            
            <a href="<?= BASE_URL ?>/pages/statistiche.php" class="sidebar-link">
                <i class="fa-solid fa-chart-bar"></i>
                <span class="hide-on-collapse">Statistiche</span>
            </a>
        </nav>
        <div class="profile-section">
            <a href="<?= BASE_URL ?>/pages/profilo.php" class="d-flex align-items-center mb-2 text-decoration-none">
                <div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($user['nome'], 0, 1))) ?></div>
                <div class="ms-2 profile-info hide-on-collapse">
                    <div class="text-white small fw-bold"><?= htmlspecialchars(Cliente::capitalizza($user['nome'])) ?></div>
                    <div class="text-white-50" style="font-size: 11px;">Il mio profilo</div>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/pages/logout.php" class="btn btn-outline-light btn-sm w-100 hide-on-collapse">Esci</a>
        </div>
    </aside>

    <main class="dashboard-content">
        <h2 class="mb-3">Il mio profilo</h2>

        <div class="card card-body bg-light mb-4" style="max-width: 500px;">
            <p class="mb-1"><strong>Nome:</strong> <?= htmlspecialchars(Cliente::capitalizza($profilo['nome']) . ' ' . Cliente::capitalizza($profilo['cognome'])) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($profilo['email'] ?? '-') ?></p>
            <p class="mb-0"><strong>Ruolo:</strong> <?= $profilo['ruolo'] === 'admin' ? 'Admin' : 'Cliente' ?></p>
            <p class="text-muted small mt-2 mb-0">
                Per modificare nome, cognome o email, contatta un admin.
                Questa pagina permette solo di cambiare la propria password.
            </p>
        </div>

        <h3 class="h5 mb-3">Cambia password</h3>
        <?php if ($errore): ?><div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div><?php endif; ?>
        <?php if ($successo): ?><div class="alert alert-success"><?= htmlspecialchars($successo) ?></div><?php endif; ?>

        <form method="post" class="card card-body bg-white" style="max-width: 500px;">
            <div class="mb-3">
                <label class="form-label">Password attuale *</label>
                <input type="password" name="password_attuale" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nuova password * (minimo 6 caratteri)</label>
                <input type="password" name="nuova_password" class="form-control" required minlength="6">
            </div>
            <div class="mb-4">
                <label class="form-label">Conferma nuova password *</label>
                <input type="password" name="conferma_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Aggiorna password</button>
        </form>
    </main>
</div>

<?php include __DIR__ . '/../footer_dashboard.php'; ?>
