<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$user = currentUser();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$prenotazione = $id ? Prenotazione::findById($id) : null;
$errore = '';

if ($id && !$prenotazione) { $id = null; }

$dati = $prenotazione ?? [
    'campo_id' => '', 'data' => $_GET['data'] ?? date('Y-m-d'),
    'ora_inizio' => '', 'ora_fine' => '', 'note' => '', 'clienti' => [],
];

if (!$id) {
    if (isset($_GET['campo_id'])) { $dati['campo_id'] = (int) $_GET['campo_id']; }
    if (isset($_GET['ora_inizio'])) { $dati['ora_inizio'] = $_GET['ora_inizio']; }
}

$campi = Campo::findAll();
$tuttiClienti = Cliente::findAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idClientiSelezionati = array_filter(array_map('intval', $_POST['clienti'] ?? []));
} else {
    $idClientiSelezionati = $id ? Prenotazione::idClientiAssociati($id) : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = [
        'campo_id'  => (int) ($_POST['campo_id'] ?? 0),
        'data'      => trim($_POST['data'] ?? ''),
        'ora_inizio' => trim($_POST['ora_inizio'] ?? ''),
        'ora_fine'  => trim($_POST['ora_fine'] ?? ''),
        'note'      => trim($_POST['note'] ?? '') ?: null,
    ];

    $errore = Prenotazione::valida($dati, $idClientiSelezionati);

    if ($errore === '') {
        if (!$id) { $dati['creato_da'] = currentUser()['id']; }

        $sovrapposizione = Prenotazione::esisteSovrapposizione(
            $dati['campo_id'], $dati['data'], $dati['ora_inizio'], $dati['ora_fine'], $id
        );

        if ($sovrapposizione) {
            $errore = 'Questo campo è già prenotato in tutto o in parte di questo orario. Scegli un altro orario o campo.';
        } else {
            if ($id) { Prenotazione::update($id, $dati, $idClientiSelezionati); }
            else { Prenotazione::create($dati, $idClientiSelezionati); }
            header('Location: ' . BASE_URL . '/pages/calendario.php?data=' . urlencode($dati['data']));
            exit;
        }
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
            <a href="<?= BASE_URL ?>/pages/dashboard.php" class="sidebar-link">
                <i class="fa-solid fa-users"></i>
                <span class="hide-on-collapse">Clienti</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/calendario.php" class="sidebar-link active">
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
        <h2 class="mb-3"><?= $id ? 'Modifica prenotazione' : 'Nuova prenotazione' ?></h2>
        <?php if ($errore): ?><div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div><?php endif; ?>

        <form method="post" class="card card-body bg-white" style="max-width: 600px;" id="form-prenotazione">
            <div class="mb-3">
                <label class="form-label">Campo *</label>
                <select name="campo_id" class="form-select" required>
                    <option value="">-- Seleziona --</option>
                    <?php foreach ($campi as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (int) $dati['campo_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Data *</label>
                <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($dati['data']) ?>" required>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Ora inizio *</label>
                    <input type="time" name="ora_inizio" class="form-control" value="<?= htmlspecialchars(substr($dati['ora_inizio'], 0, 5)) ?>" min="08:00" max="24:00" required>
                </div>
                <div class="col">
                    <label class="form-label">Ora fine *</label>
                    <input type="time" name="ora_fine" class="form-control" value="<?= htmlspecialchars(substr($dati['ora_fine'], 0, 5)) ?>" min="08:00" max="24:00" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Note</label>
                <input type="text" name="note" class="form-control" value="<?= htmlspecialchars($dati['note'] ?? '') ?>" placeholder="es. manutenzione, evento privato...">
            </div>

            <label class="form-label">Giocatori (fino a <?= Prenotazione::MAX_CLIENTI ?>, tutti opzionali)</label>
            <div id="riepilogo-selezionati" class="mb-2 small text-muted">Nessun giocatore selezionato.</div>
            <input type="text" id="ricerca-giocatore" class="form-control mb-2" placeholder="Cerca cliente per nome o cognome..." autocomplete="off">
            <div id="elenco-clienti" class="border rounded p-2 mb-3" style="max-height: 240px; overflow-y: auto; display: none;">
                <?php foreach ($tuttiClienti as $cl): ?>
                    <div class="form-check riga-cliente" data-ricerca="<?= htmlspecialchars(mb_strtolower(Cliente::nomeCompleto($cl))) ?>">
                        <input type="checkbox" name="clienti[]" value="<?= $cl['id'] ?>"
                            class="form-check-input checkbox-cliente"
                            id="cliente-<?= $cl['id'] ?>"
                            data-nome="<?= htmlspecialchars(Cliente::nomeCompleto($cl)) ?>"
                            <?= in_array((int) $cl['id'], $idClientiSelezionati, true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cliente-<?= $cl['id'] ?>">
                            <?= htmlspecialchars(Cliente::nomeCompleto($cl)) ?>
                            <?php if (!empty($cl['cellulare'])): ?>
                                <span class="text-muted small">— <?= htmlspecialchars($cl['cellulare']) ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <p id="nessun-risultato" class="text-muted small mb-0" style="display: none;">Nessun cliente trovato.</p>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Salva prenotazione</button>
                <a href="<?= BASE_URL ?>/pages/calendario.php" class="btn btn-secondary">Annulla</a>
                <?php if ($id): ?>
                    <button type="button" class="btn btn-danger ms-auto" data-bs-toggle="modal" data-bs-target="#modaleEliminaPrenotazione">Cancella prenotazione</button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($id): ?>
        <div class="modal fade" id="modaleEliminaPrenotazione" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confermi l'eliminazione?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Stai per eliminare definitivamente questa prenotazione. Questa azione non è reversibile.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <a href="prenotazione_delete.php?id=<?= $id ?>" class="btn btn-danger">Elimina definitivamente</a>
                </div>
            </div></div>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
const inputRicerca = document.getElementById('ricerca-giocatore');
const elencoClienti = document.getElementById('elenco-clienti');
const righeCliente = document.querySelectorAll('.riga-cliente');
const nessunRisultato = document.getElementById('nessun-risultato');
const checkboxes = document.querySelectorAll('.checkbox-cliente');
const riepilogo = document.getElementById('riepilogo-selezionati');
const maxClienti = <?= Prenotazione::MAX_CLIENTI ?>;

function filtraClienti() {
    const termine = inputRicerca.value.trim().toLowerCase();
    if (termine === '') { elencoClienti.style.display = 'none'; return; }
    elencoClienti.style.display = 'block';
    let trovati = false;
    righeCliente.forEach(riga => {
        const corrisponde = riga.dataset.ricerca.includes(termine);
        riga.style.display = corrisponde ? '' : 'none';
        if (corrisponde) trovati = true;
    });
    nessunRisultato.style.display = trovati ? 'none' : 'block';
}

function aggiornaRiepilogo() {
    const selezionate = Array.from(checkboxes).filter(cb => cb.checked);
    riepilogo.textContent = selezionate.length === 0
        ? 'Nessun giocatore selezionato.'
        : selezionate.length + ' / ' + maxClienti + ' selezionati: ' + selezionate.map(cb => cb.dataset.nome).join(', ');
    checkboxes.forEach(cb => { if (!cb.checked) cb.disabled = selezionate.length >= maxClienti; });
}

inputRicerca.addEventListener('input', filtraClienti);
checkboxes.forEach(cb => cb.addEventListener('change', aggiornaRiepilogo));
aggiornaRiepilogo();
</script>

<?php include __DIR__ . '/../footer_dashboard.php'; ?>
