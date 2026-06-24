<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$user = currentUser();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$cliente = $id ? Cliente::findById($id) : null;
$errore = '';

if ($id && !$cliente) { $id = null; }

$dati = $cliente ?? [
    'nome' => '', 'cognome' => '', 'data_nascita' => '', 'email' => '',
    'cellulare' => '', 'socio' => 'no', 'posizione' => '', 'mano' => '',
    'livello' => '', 'certificato_medico' => 'no', 'certificato_scadenza' => '',
    'tessera_fitp' => '', 'tessera_fitp_scadenza' => '', 'ruolo' => 'cliente',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = [
        'nome' => trim($_POST['nome'] ?? ''),
        'cognome' => trim($_POST['cognome'] ?? ''),
        'data_nascita' => trim($_POST['data_nascita'] ?? '') ?: null,
        'email' => trim($_POST['email'] ?? '') ?: null,
        'cellulare' => trim($_POST['cellulare'] ?? '') ?: null,
        'socio' => $_POST['socio'] ?? 'no',
        'posizione' => trim($_POST['posizione'] ?? '') ?: null,
        'mano' => trim($_POST['mano'] ?? '') ?: null,
        'livello' => trim($_POST['livello'] ?? '') ?: null,
        'certificato_medico' => $_POST['certificato_medico'] ?? 'no',
        'certificato_scadenza' => trim($_POST['certificato_scadenza'] ?? '') ?: null,
        'tessera_fitp' => trim($_POST['tessera_fitp'] ?? '') ?: null,
        'tessera_fitp_scadenza' => trim($_POST['tessera_fitp_scadenza'] ?? '') ?: null,
        'ruolo' => isAdmin() ? ($_POST['ruolo'] ?? 'cliente') : ($cliente['ruolo'] ?? 'cliente'),
    ];

    $errore = Cliente::valida($dati);
    if ($errore === '') {
        if ($id) { Cliente::update($id, $dati); }
        else { Cliente::create($dati); }
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

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
            <a href="<?= BASE_URL ?>/pages/dashboard.php" class="sidebar-link active">
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
        <h2 class="mb-3"><?= $id ? 'Modifica cliente' : 'Nuovo cliente' ?></h2>
        <?php if ($errore): ?><div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div><?php endif; ?>

        <form method="post" class="card card-body bg-white" style="max-width: 600px;">
            <div class="mb-3">
                <label class="form-label">Nome *</label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dati['nome']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Cognome *</label>
                <input type="text" name="cognome" class="form-control" value="<?= htmlspecialchars($dati['cognome']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Data di nascita</label>
                <input type="date" name="data_nascita" class="form-control" value="<?= htmlspecialchars($dati['data_nascita'] ?? '') ?>">
                <?php $etaCalcolata = Cliente::calcolaEta($dati['data_nascita'] ?? null); ?>
                <?php if ($etaCalcolata !== null): ?><div class="form-text">Età calcolata: <?= $etaCalcolata ?> anni</div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($dati['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Cellulare</label>
                <input type="text" name="cellulare" class="form-control" value="<?= htmlspecialchars($dati['cellulare'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Socio</label>
                <select name="socio" class="form-select">
                    <option value="no" <?= $dati['socio'] === 'no' ? 'selected' : '' ?>>No</option>
                    <option value="si" <?= $dati['socio'] === 'si' ? 'selected' : '' ?>>Sì</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Posizione</label>
                <select name="posizione" class="form-select">
                    <option value="">-- Seleziona --</option>
                    <?php foreach (Cliente::POSIZIONI as $p): ?>
                        <option value="<?= $p ?>" <?= ($dati['posizione'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mano</label>
                <select name="mano" class="form-select">
                    <option value="">-- Seleziona --</option>
                    <?php foreach (Cliente::MANI as $m): ?>
                        <option value="<?= $m ?>" <?= ($dati['mano'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Livello</label>
                <select name="livello" class="form-select">
                    <option value="">-- Seleziona --</option>
                    <?php foreach (Cliente::LIVELLI as $l): ?>
                        <option value="<?= $l ?>" <?= ($dati['livello'] ?? '') === $l ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Certificato medico</label>
                <select name="certificato_medico" class="form-select">
                    <option value="no" <?= $dati['certificato_medico'] === 'no' ? 'selected' : '' ?>>No</option>
                    <option value="si" <?= $dati['certificato_medico'] === 'si' ? 'selected' : '' ?>>Sì</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Scadenza certificato</label>
                <input type="date" name="certificato_scadenza" class="form-control" value="<?= htmlspecialchars($dati['certificato_scadenza'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">N° tessera FITP</label>
                <input type="text" name="tessera_fitp" class="form-control" value="<?= htmlspecialchars($dati['tessera_fitp'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Scadenza tessera FITP</label>
                <input type="date" name="tessera_fitp_scadenza" class="form-control" value="<?= htmlspecialchars($dati['tessera_fitp_scadenza'] ?? '') ?>">
            </div>
            <?php if (isAdmin()): ?>
            <div class="mb-4">
                <label class="form-label">Ruolo</label>
                <select name="ruolo" class="form-select">
                    <option value="cliente" <?= ($dati['ruolo'] ?? 'cliente') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="admin"   <?= ($dati['ruolo'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <div class="form-text">Un admin può accedere al gestionale interno.</div>
            </div>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salva</button>
                <a href="<?= BASE_URL ?>/pages/dashboard.php" class="btn btn-secondary">Annulla</a>
                <?php if ($id): ?>
                    <button type="button" class="btn btn-danger ms-auto" data-bs-toggle="modal" data-bs-target="#modaleEliminaCliente">Elimina cliente</button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($id): ?>
        <div class="modal fade" id="modaleEliminaCliente" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confermi l'eliminazione?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Stai per eliminare definitivamente <strong><?= htmlspecialchars(Cliente::nomeCompleto($dati)) ?></strong>. Questa azione non è reversibile.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <a href="cliente_delete.php?id=<?= $id ?>" class="btn btn-danger">Elimina definitivamente</a>
                </div>
            </div></div>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../footer_dashboard.php'; ?>
