<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$user = currentUser();

$data_selezionata = $_GET['data'] ?? date('Y-m-d');
$ts = strtotime($data_selezionata);
if ($ts === false) { $data_selezionata = date('Y-m-d'); $ts = time(); }

$giorno_precedente = date('Y-m-d', strtotime('-1 day', $ts));
$giorno_successivo = date('Y-m-d', strtotime('+1 day', $ts));

$campi = Campo::findAll();
$prenotazioni = Prenotazione::delGiorno($data_selezionata);

$fasce_orarie = [];
for ($h = 8; $h < 24; $h++) {
    foreach (['00', '30'] as $m) {
        $fasce_orarie[] = sprintf('%02d:%s', $h, $m);
    }
}

$pixel_per_minuto = 1.6;
$altezza_griglia = Prenotazione::minutiTotaliGiornata() * $pixel_per_minuto;

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
        <h2 class="mb-3">Calendario campi</h2>

        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <a href="?data=<?= $giorno_precedente ?>" class="btn btn-outline-primary btn-sm">&laquo; Giorno prec.</a>
            <form method="get" class="d-inline">
                <input type="date" name="data" class="form-control form-control-sm d-inline-block" style="width:auto;"
                       value="<?= htmlspecialchars($data_selezionata) ?>" onchange="this.form.submit()">
            </form>
            <a href="?data=<?= $giorno_successivo ?>" class="btn btn-outline-primary btn-sm">Giorno succ. &raquo;</a>
            <strong class="ms-2"><?= date('d/m/Y', $ts) ?></strong>
        </div>

        <a href="prenotazione_form.php?data=<?= $data_selezionata ?>" class="btn btn-primary mb-3">+ Nuova prenotazione</a>

        <div class="mb-4" style="overflow-x: auto;">
            <div class="griglia-calendario" style="min-width: 700px;">
                <div class="griglia-intestazione colonna-ora">Ora</div>
                <?php foreach ($campi as $campo): ?>
                    <div class="griglia-intestazione"><?= htmlspecialchars($campo['nome']) ?></div>
                <?php endforeach; ?>

                <div class="colonna-ore" style="height: <?= $altezza_griglia ?>px;">
                    <?php foreach ($fasce_orarie as $fascia): ?>
                        <?php
                            [$ora, $min] = explode(':', $fascia);
                            $minutiDaInizio = (((int) $ora) * 60 + (int) $min) - 8 * 60;
                            $top = $minutiDaInizio * $pixel_per_minuto;
                        ?>
                        <span class="etichetta-ora" style="top: <?= $top ?>px;"><?= $fascia ?></span>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($campi as $campo): ?>
                    <div class="colonna-campo" style="height: <?= $altezza_griglia ?>px;">
                        <?php
                            $campoId = (int) $campo['id'];
                            foreach ($fasce_orarie as $fascia):
                                [$ora, $min] = explode(':', $fascia);
                                $minutiDaInizio = (((int) $ora) * 60 + (int) $min) - 8 * 60;
                                $top = $minutiDaInizio * $pixel_per_minuto;
                        ?>
                            <a class="cella-vuota-click"
                               style="top: <?= $top ?>px; height: <?= 30 * $pixel_per_minuto ?>px;"
                               href="prenotazione_form.php?data=<?= $data_selezionata ?>&campo_id=<?= $campoId ?>&ora_inizio=<?= $fascia ?>"
                               title="Nuova prenotazione alle <?= $fascia ?>"></a>
                        <?php endforeach; ?>

                        <?php if (!empty($prenotazioni[$campoId])): ?>
                            <?php foreach ($prenotazioni[$campoId] as $p): ?>
                                <a class="blocco-prenotazione <?= empty($p['clienti']) ? 'blocco-vuoto' : '' ?>"
                                   style="top: <?= Prenotazione::posizioneTop($p, $pixel_per_minuto) ?>px; height: <?= max(Prenotazione::altezzaBlocco($p, $pixel_per_minuto), 20) ?>px;"
                                   href="prenotazione_form.php?id=<?= $p['id'] ?>"
                                   title="<?= htmlspecialchars(Prenotazione::orarioFormattato($p) . ' - ' . (Prenotazione::nomiClientiFormattati($p) ?: 'Nessun cliente') . (!empty($p['note']) ? ' - ' . $p['note'] : '')) ?>">
                                    <span class="blocco-orario"><?= Prenotazione::orarioFormattato($p) ?></span>
                                    <?= htmlspecialchars(Prenotazione::nomiClientiFormattati($p) ?: 'Blocco/manutenzione') ?>
                                    <?php if (!empty($p['note'])): ?>
                                        <span class="blocco-note">📝 <?= htmlspecialchars($p['note']) ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <h3 class="h5 mb-3">Elenco prenotazioni del giorno</h3>
        <div class="table-responsive">
            <table class="table table-hover bg-white align-middle">
                <thead>
                    <tr>
                        <th>Campo</th><th>Orario</th><th>Clienti</th>
                        <th class="colonna-extra">Note</th>
                        <th class="colonna-extra">Inserita da</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $vuoto = true; ?>
                    <?php foreach ($campi as $campo): ?>
                        <?php
                            $campoId = (int) $campo['id'];
                            $numeroCampo = preg_replace('/[^0-9]/', '', $campo['nome']);
                        ?>
                        <?php if (!empty($prenotazioni[$campoId])): $vuoto = false; ?>
                            <?php foreach ($prenotazioni[$campoId] as $p): ?>
                            <tr>
                                <td>
                                    <span class="solo-desktop"><?= htmlspecialchars($campo['nome']) ?></span>
                                    <span class="solo-mobile"><?= htmlspecialchars($numeroCampo) ?></span>
                                </td>
                                <td><?= Prenotazione::orarioFormattato($p) ?></td>
                                <td>
                                    <?php if (empty($p['clienti'])): ?>
                                        <em class="text-muted">Nessun cliente (blocco/manutenzione)</em>
                                    <?php else: ?>
                                        <?= htmlspecialchars(Prenotazione::nomiClientiFormattati($p)) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="colonna-extra"><?= htmlspecialchars($p['note'] ?? '-') ?></td>
                                <td class="colonna-extra"><?= htmlspecialchars(Cliente::capitalizza($p['dip_nome']) . ' ' . Cliente::capitalizza($p['dip_cognome'])) ?></td>
                                <td>
                                    <a href="prenotazione_form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Modifica</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modaleElimina<?= $p['id'] ?>">Elimina</button>
                                    <div class="modal fade" id="modaleElimina<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confermi l'eliminazione?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Stai per eliminare la prenotazione delle
                                                    <strong><?= Prenotazione::orarioFormattato($p) ?></strong>
                                                    sul campo <strong><?= htmlspecialchars($campo['nome']) ?></strong>.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                                    <a href="prenotazione_delete.php?id=<?= $p['id'] ?>" class="btn btn-danger">Elimina definitivamente</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($vuoto): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">Nessuna prenotazione per questo giorno.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../footer_dashboard.php'; ?>
