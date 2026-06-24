<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$user = currentUser();

// Filtri da query string
$f_nome       = trim($_GET['nome'] ?? '');
$f_cognome    = trim($_GET['cognome'] ?? '');
$f_eta_min    = trim($_GET['eta_min'] ?? '');
$f_eta_max    = trim($_GET['eta_max'] ?? '');
$f_email      = trim($_GET['email'] ?? '');
$f_cellulare  = trim($_GET['cellulare'] ?? '');
$f_socio      = trim($_GET['socio'] ?? '');
$f_certificato = trim($_GET['certificato'] ?? '');
$f_livello    = trim($_GET['livello'] ?? '');
$f_posizione  = trim($_GET['posizione'] ?? '');
$f_mano       = trim($_GET['mano'] ?? '');
$f_tessera_fitp = trim($_GET['tessera_fitp'] ?? '');

$clienti = Cliente::cerca([
    'nome'        => $f_nome,
    'cognome'     => $f_cognome,
    'eta_min'     => $f_eta_min,
    'eta_max'     => $f_eta_max,
    'email'       => $f_email,
    'cellulare'   => $f_cellulare,
    'socio'       => $f_socio,
    'certificato' => $f_certificato,
    'livello'     => $f_livello,
    'posizione'   => $f_posizione,
    'mano'        => $f_mano,
    'tessera_fitp' => $f_tessera_fitp,
]);

include __DIR__ . '/../header_dashboard.php';
?>

<div class="dashboard-wrapper">

    <!-- SIDEBAR -->
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
                <div class="profile-avatar">
                    <?= htmlspecialchars(strtoupper(substr($user['nome'], 0, 1))) ?>
                </div>
                <div class="ms-2 profile-info hide-on-collapse">
                    <div class="text-white small fw-bold"><?= htmlspecialchars(Cliente::capitalizza($user['nome'])) ?></div>
                    <div class="text-white-50" style="font-size: 11px;">Il mio profilo</div>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/pages/logout.php" class="btn btn-outline-light btn-sm w-100 hide-on-collapse">Esci</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dashboard-content">
        <h2 class="mb-3">Clienti</h2>
        <div class="mb-3">
            <a href="<?= BASE_URL ?>/pages/cliente_form.php" class="btn btn-primary">+ Nuovo cliente</a>
        </div>

        <form method="get" class="card card-body bg-light mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Nome</label>
                    <input type="text" name="nome" class="form-control form-control-sm" value="<?= htmlspecialchars($f_nome) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Cognome</label>
                    <input type="text" name="cognome" class="form-control form-control-sm" value="<?= htmlspecialchars($f_cognome) ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-1">Età min</label>
                    <input type="number" name="eta_min" class="form-control form-control-sm" value="<?= htmlspecialchars($f_eta_min) ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-1">Età max</label>
                    <input type="number" name="eta_max" class="form-control form-control-sm" value="<?= htmlspecialchars($f_eta_max) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Email</label>
                    <input type="text" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($f_email) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Cellulare</label>
                    <input type="text" name="cellulare" class="form-control form-control-sm" value="<?= htmlspecialchars($f_cellulare) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Tessera FITP</label>
                    <input type="text" name="tessera_fitp" class="form-control form-control-sm" value="<?= htmlspecialchars($f_tessera_fitp) ?>">
                </div>
            </div>
            <div class="row g-2 align-items-end mt-2">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Socio</label>
                    <select name="socio" class="form-select form-select-sm">
                        <option value="">Tutti</option>
                        <option value="si" <?= $f_socio === 'si' ? 'selected' : '' ?>>Sì</option>
                        <option value="no" <?= $f_socio === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Certificato</label>
                    <select name="certificato" class="form-select form-select-sm">
                        <option value="">Tutti</option>
                        <option value="si" <?= $f_certificato === 'si' ? 'selected' : '' ?>>Sì</option>
                        <option value="no" <?= $f_certificato === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Livello</label>
                    <select name="livello" class="form-select form-select-sm">
                        <option value="">Tutti</option>
                        <?php foreach (Cliente::LIVELLI as $l): ?>
                            <option value="<?= $l ?>" <?= $f_livello === $l ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Posizione</label>
                    <select name="posizione" class="form-select form-select-sm">
                        <option value="">Tutti</option>
                        <?php foreach (Cliente::POSIZIONI as $p): ?>
                            <option value="<?= $p ?>" <?= $f_posizione === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Mano</label>
                    <select name="mano" class="form-select form-select-sm">
                        <option value="">Tutti</option>
                        <?php foreach (Cliente::MANI as $m): ?>
                            <option value="<?= $m ?>" <?= $f_mano === $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filtra</button>
                </div>
                <div class="col-md-auto">
                    <a href="<?= BASE_URL ?>/pages/dashboard.php" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover bg-white align-middle" id="tabella-clienti">
                <thead>
                    <tr>
                        <th class="colonna-id">ID</th>
                        <th>Nome</th><th>Cognome</th><th>Livello</th>
                        <th class="colonna-extra">Età</th>
                        <th class="colonna-extra">Data di nascita</th>
                        <th class="colonna-extra">Email</th>
                        <th class="colonna-extra">Cellulare</th>
                        <th class="colonna-extra">Socio</th>
                        <th class="colonna-extra">Certificato</th>
                        <th class="colonna-extra">Scadenza certificato</th>
                        <th class="colonna-extra">Posizione</th>
                        <th class="colonna-extra">Mano</th>
                        <th class="colonna-extra">Tessera FITP</th>
                        <th class="colonna-extra">Scadenza tessera FITP</th>
                        <?php if (isAdmin()): ?><th>Azioni</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clienti)): ?>
                        <tr><td colspan="<?= isAdmin() ? 16 : 15 ?>" class="text-center text-muted py-3">Nessun cliente trovato con questi filtri.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($clienti as $c): ?>
                    <?php
                        $eta = Cliente::calcolaEta($c['data_nascita'] ?? null);
                        $certificatoScaduto = !empty($c['certificato_scadenza']) && $c['certificato_scadenza'] < date('Y-m-d');
                        $tesseraScaduta = !empty($c['tessera_fitp_scadenza']) && $c['tessera_fitp_scadenza'] < date('Y-m-d');
                        $dataNascitaFormattata = !empty($c['data_nascita']) ? date('d/m/Y', strtotime($c['data_nascita'])) : '-';
                        $scadenzaTesseraFormattata = !empty($c['tessera_fitp_scadenza']) ? date('d/m/Y', strtotime($c['tessera_fitp_scadenza'])) : '-';
                    ?>
                    <tr class="riga-cliccabile" onclick="window.location='<?= BASE_URL ?>/pages/cliente_form.php?id=<?= $c['id'] ?>'">
                        <td class="colonna-id text-muted"><?= (int) $c['id'] ?></td>
                        <td><?= htmlspecialchars(Cliente::capitalizza($c['nome'])) ?></td>
                        <td><?= htmlspecialchars(Cliente::capitalizza($c['cognome'])) ?></td>
                        <td><?= htmlspecialchars($c['livello'] ?? '-') ?></td>
                        <td class="colonna-extra"><?= $eta !== null ? $eta : '-' ?></td>
                        <td class="colonna-extra"><?= htmlspecialchars($dataNascitaFormattata) ?></td>
                        <td class="colonna-extra"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                        <td class="colonna-extra"><?= htmlspecialchars($c['cellulare'] ?? '-') ?></td>
                        <td class="colonna-extra"><span class="badge <?= $c['socio'] === 'si' ? 'badge-si' : 'badge-no' ?>"><?= $c['socio'] === 'si' ? 'Sì' : 'No' ?></span></td>
                        <td class="colonna-extra"><span class="badge <?= $c['certificato_medico'] === 'si' ? 'badge-si' : 'badge-no' ?>"><?= $c['certificato_medico'] === 'si' ? 'Sì' : 'No' ?></span></td>
                        <td class="colonna-extra <?= $certificatoScaduto ? 'certificato-scaduto' : '' ?>"><?= htmlspecialchars($c['certificato_scadenza'] ?? '-') ?></td>
                        <td class="colonna-extra"><?= htmlspecialchars($c['posizione'] ?? '-') ?></td>
                        <td class="colonna-extra"><?= htmlspecialchars($c['mano'] ?? '-') ?></td>
                        <td class="colonna-extra"><?= htmlspecialchars($c['tessera_fitp'] ?? '-') ?></td>
                        <td class="colonna-extra <?= $tesseraScaduta ? 'certificato-scaduto' : '' ?>"><?= htmlspecialchars($scadenzaTesseraFormattata) ?></td>
                        <?php if (isAdmin()): ?>
                        <td onclick="event.stopPropagation()">
                            <a href="toggle_ruolo.php?id=<?= $c['id'] ?>"
                               class="btn btn-sm <?= $c['ruolo'] === 'admin' ? 'btn-warning' : 'btn-outline-secondary' ?>"
                               title="<?= $c['ruolo'] === 'admin' ? 'Revoca admin' : 'Promuovi ad admin' ?>">
                                <i class="fa-solid fa-shield-halved"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" title="Reset password"
                                    data-bs-toggle="modal" data-bs-target="#modaleReset<?= $c['id'] ?>">
                                <i class="fa-solid fa-key"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Elimina"
                                    data-bs-toggle="modal" data-bs-target="#modaleElimina<?= $c['id'] ?>">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                            <!-- Modale reset password -->
                            <div class="modal fade" id="modaleReset<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reset password — <?= htmlspecialchars(Cliente::nomeCompleto($c)) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post" action="reset_password_admin.php">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <div class="modal-body">
                                                <label class="form-label">Nuova password <small class="text-muted">(minimo 6 caratteri)</small></label>
                                                <input type="password" name="nuova_password" class="form-control" required minlength="6">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                                <button type="submit" class="btn btn-primary">Salva password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modale elimina -->
                            <div class="modal fade" id="modaleElimina<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Elimina cliente</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Stai per eliminare definitivamente <strong><?= htmlspecialchars(Cliente::nomeCompleto($c)) ?></strong>.
                                            Questa azione non è reversibile.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                            <a href="cliente_delete.php?id=<?= $c['id'] ?>" class="btn btn-danger">Elimina definitivamente</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</div>

<?php include __DIR__ . '/../footer_dashboard.php'; ?>
