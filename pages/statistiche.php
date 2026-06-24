<?php
require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../helpers/auth.php';
requireAdmin();

$user = currentUser();

// Anno selezionato dalla select in cima alla pagina.
// Se non è ancora stato scelto nulla, usiamo l'anno corrente.
$anno = isset($_GET['anno']) ? (int) $_GET['anno'] : (int) date('Y');

// Lista degli anni disponibili per la select — dal corrente al 2024
$annoCorrente = (int) date('Y');
$anniDisponibili = [];
for ($a = $annoCorrente; $a >= 2024; $a--) {
    $anniDisponibili[] = $a;
}

// Mese selezionato per i grafici giorni e fasce orarie.
// Di default usiamo il mese corrente.
$mese = isset($_GET['mese']) ? (int) $_GET['mese'] : (int) date('n');

// Chiamiamo i metodi della classe Analytics passando i parametri
// che l'utente ha scelto (anno e mese).
// Ogni variabile contiene già l'array pronto per essere passato a JavaScript.

$prenotMensili      = Analytics::prenotazioniPerMese($anno);
$prenotPerGiorno    = Analytics::prenotazioniPerGiorno($anno, $mese);
$fasceOrarie        = Analytics::fasceOrarie($anno, $mese);
$livelli            = Analytics::distribuzioneLivelli();
$iscrizioni         = Analytics::iscrizioniPerMese($anno);

// Convertiamo tutto in JSON — il "ponte" tra PHP e JavaScript
$jsonPrenotMensili   = json_encode($prenotMensili);
$jsonPerGiorno       = json_encode($prenotPerGiorno);
$jsonFasceOrarie     = json_encode($fasceOrarie);
$jsonIscrizioni      = json_encode($iscrizioni);

// Per i livelli separiamo etichette e valori
// perché il grafico a ciambella ne ha bisogno separati
$jsonLivelliLabel    = json_encode(array_keys($livelli));
$jsonLivelliValori   = json_encode(array_values($livelli));

// Calcoliamo alcune card di riepilogo
$totalePrenotazioni  = array_sum($prenotMensili);
$totaleIscrizioni    = array_sum($iscrizioni);

// Fascia oraria più richiesta — troviamo il valore massimo
// e prendiamo l'etichetta corrispondente
$etichetteFasce = ['8-10','10-12','12-14','14-16','16-18','18-20','20-22','22-24'];
$indiceMassimo  = array_search(max($fasceOrarie), $fasceOrarie);
$fasciaPiuRichiesta = $fasceOrarie[$indiceMassimo] > 0
    ? $etichetteFasce[$indiceMassimo]
    : 'Nessun dato';

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
            <a href="<?= BASE_URL ?>/pages/statistiche.php" class="sidebar-link active">
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
                    <div class="text-white small fw-bold">
                        <?= htmlspecialchars(Cliente::capitalizza($user['nome'])) ?>
                    </div>
                    <div class="text-white-50" style="font-size: 11px;">Il mio profilo</div>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/pages/logout.php" class="btn btn-outline-light btn-sm w-100 hide-on-collapse">Esci</a>
        </div>
    </aside>

    <main class="dashboard-content">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <h2 class="mb-0">Statistiche</h2>

            <!-- Select per scegliere l'anno -->
            <form method="get" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 text-muted small">Anno</label>
                <select name="anno" class="form-select form-select-sm" style="width: auto;"
                        onchange="this.form.submit()">
                    <?php foreach ($anniDisponibili as $a): ?>
                        <option value="<?= $a ?>" <?= $a === $anno ? 'selected' : '' ?>>
                            <?= $a ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- Manteniamo il mese selezionato quando cambia l'anno -->
                <input type="hidden" name="mese" value="<?= $mese ?>">
            </form>
        </div>

      <!-- Card di riepilogo -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-body bg-light h-100">
                    <div class="text-muted small mb-1">Prenotazioni totali <?= $anno ?></div>
                    <div class="fs-3 fw-bold"><?= $totalePrenotazioni ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body bg-light h-100">
                    <div class="text-muted small mb-1">Nuovi clienti <?= $anno ?></div>
                    <div class="fs-3 fw-bold"><?= $totaleIscrizioni ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body bg-light h-100">
                    <div class="text-muted small mb-1">Fascia più richiesta</div>
                    <div class="fs-3 fw-bold"><?= htmlspecialchars($fasciaPiuRichiesta) ?></div>
                    <div class="text-muted small">nel mese selezionato</div>
                </div>
            </div>
        </div>

        <!-- GRAFICO 1: prenotazioni per mese -->
        <div class="card card-body bg-white mb-4">
            <h5 class="fw-bold mb-1">Prenotazioni per mese</h5>
            <p class="text-muted small mb-3">Totale prenotazioni — <?= $anno ?></p>
            <div style="position: relative; height: 250px;">
                <canvas id="grafico-mesi"
                        role="img"
                        aria-label="Grafico prenotazioni mensili <?= $anno ?>">
                    Prenotazioni mensili anno <?= $anno ?>.
                </canvas>
            </div>
        </div>

        <!-- Selettore mese — controlla entrambi i grafici sotto -->
        <div class="card card-body bg-light mb-3">
            <p class="text-muted small mb-2">
                Seleziona un mese per vedere il dettaglio dei giorni e delle fasce orarie:
            </p>
            <div class="d-flex flex-wrap gap-2">
                <?php
                $nomiMesi = ['Gen','Feb','Mar','Apr','Mag','Giu',
                             'Lug','Ago','Set','Ott','Nov','Dic'];
                foreach ($nomiMesi as $indice => $nomeMese):
                    $numeroMese = $indice + 1; // l'indice parte da 0, i mesi da 1
                ?>
                    <a href="?anno=<?= $anno ?>&mese=<?= $numeroMese ?>"
                       class="btn btn-sm <?= $numeroMese === $mese ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        <?= $nomeMese ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grafici giorni e fasce affiancati -->
        <div class="row g-4 mb-4">

            <!-- GRAFICO 2: prenotazioni per giorno della settimana -->
            <div class="col-md-6">
                <div class="card card-body bg-white h-100">
                    <h5 class="fw-bold mb-1">Giorni più richiesti</h5>
                    <p class="text-muted small mb-3">
                        <?= $nomiMesi[$mese - 1] ?> <?= $anno ?>
                    </p>
                    <div style="position: relative; height: 220px;">
                        <canvas id="grafico-giorni"
                                role="img"
                                aria-label="Prenotazioni per giorno <?= $nomiMesi[$mese - 1] ?> <?= $anno ?>">
                            Prenotazioni per giorno della settimana.
                        </canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFICO 3: fasce orarie -->
            <div class="col-md-6">
                <div class="card card-body bg-white h-100">
                    <h5 class="fw-bold mb-1">Fasce orarie più richieste</h5>
                    <p class="text-muted small mb-3">
                        <?= $nomiMesi[$mese - 1] ?> <?= $anno ?>
                    </p>
                    <div style="position: relative; height: 220px;">
                        <canvas id="grafico-fasce"
                                role="img"
                                aria-label="Fasce orarie <?= $nomiMesi[$mese - 1] ?> <?= $anno ?>">
                            Distribuzione prenotazioni per fascia oraria.
                        </canvas>
                    </div>
                </div>
            </div>

        </div>

        <script src="<?= BASE_URL ?>/node_modules/chart.js/dist/chart.umd.js"></script>
<script>

// -------------------------------------------------------
// DATI — PHP scrive qui i valori dal database
// Quello che vedi sono stringhe JSON che JavaScript legge come array
// -------------------------------------------------------
const prenotMensili  = <?= $jsonPrenotMensili ?>;
const perGiorno      = <?= $jsonPerGiorno ?>;
const fasceOrarie    = <?= $jsonFasceOrarie ?>;
const livelliLabel   = <?= $jsonLivelliLabel ?>;
const livelliValori  = <?= $jsonLivelliValori ?>;
const iscrizioni     = <?= $jsonIscrizioni ?>;

// -------------------------------------------------------
// COLORI — palette verde coerente con il tema del sito
// -------------------------------------------------------
const verde1 = '#1b4332'; // verde scuro — valori alti
const verde2 = '#2d6a4f';
const verde3 = '#52b788';
const verde4 = '#95d5b2'; // verde chiaro — valori bassi
const grigio = '#adb5bd'; // per "Non definito" nel grafico livelli

// Funzione che assegna un colore più scuro alle barre con valore più alto
// e più chiaro a quelle con valore più basso — effetto visivo immediato
function coloriGradienti(dati) {
    const massimo = Math.max(...dati.filter(v => v > 0)) || 1;
    return dati.map(v => {
        if (v >= massimo)        return verde1;
        if (v >= massimo * 0.75) return verde2;
        if (v >= massimo * 0.45) return verde3;
        return verde4;
    });
}

// Opzioni comuni a tutti i grafici a barre
// Le scriviamo una volta sola per non ripeterci
const opzioniBarre = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { autoSkip: false }
        },
        y: {
            grid: { color: 'rgba(0,0,0,0.05)' },
            beginAtZero: true
        }
    }
};

// -------------------------------------------------------
// GRAFICO 1 — Prenotazioni per mese
// -------------------------------------------------------
new Chart(document.getElementById('grafico-mesi'), {
    type: 'bar',
    data: {
        labels: ['Gen','Feb','Mar','Apr','Mag','Giu',
                 'Lug','Ago','Set','Ott','Nov','Dic'],
        datasets: [{
            data: prenotMensili,
            backgroundColor: coloriGradienti(prenotMensili),
            borderRadius: 4
        }]
    },
    options: opzioniBarre
});

// -------------------------------------------------------
// GRAFICO 2 — Prenotazioni per giorno della settimana
// -------------------------------------------------------
new Chart(document.getElementById('grafico-giorni'), {
    type: 'bar',
    data: {
        labels: ['Lun','Mar','Mer','Gio','Ven','Sab','Dom'],
        datasets: [{
            data: perGiorno,
            backgroundColor: coloriGradienti(perGiorno),
            borderRadius: 4
        }]
    },
    options: opzioniBarre
});

// -------------------------------------------------------
// GRAFICO 3 — Fasce orarie
// -------------------------------------------------------
new Chart(document.getElementById('grafico-fasce'), {
    type: 'bar',
    data: {
        labels: ['8-10','10-12','12-14','14-16','16-18','18-20','20-22','22-24'],
        datasets: [{
            data: fasceOrarie,
            backgroundColor: coloriGradienti(fasceOrarie),
            borderRadius: 4
        }]
    },
    options: {
        ...opzioniBarre,
        scales: {
            ...opzioniBarre.scales,
            x: {
                ...opzioniBarre.scales.x,
                ticks: { autoSkip: false, maxRotation: 45 }
            }
        }
    }
});

// -------------------------------------------------------
// GRAFICO 4 — Distribuzione livelli FITP (ciambella)
// -------------------------------------------------------

// Costruiamo i colori: verde per i livelli FITP, grigio per "Non definito"
const coloriLivelli = livelliLabel.map(label =>
    label === 'Non definito' ? grigio : null
);

// Per i livelli FITP usiamo una scala di verdi dal chiaro allo scuro
const livelliSoloFitp = livelliLabel.filter(l => l !== 'Non definito');
const scalaVerdi = [
    '#c7f9cc','#b7e4c7','#95d5b2','#74c69d',
    '#52b788','#40916c','#2d6a4f','#1b4332',
    '#081c15','#0d2818','#143320','#1e4d2b'
];
livelliLabel.forEach((label, i) => {
    if (label !== 'Non definito') {
        const indiceFiltp = livelliSoloFitp.indexOf(label);
        coloriLivelli[i] = scalaVerdi[indiceFiltp];
    }
});

new Chart(document.getElementById('grafico-livelli'), {
    type: 'doughnut',
    data: {
        labels: livelliLabel,
        datasets: [{
            data: livelliValori,
            backgroundColor: coloriLivelli,
            borderWidth: 1,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const totale = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const perc = totale > 0
                            ? Math.round(ctx.raw / totale * 100)
                            : 0;
                        return ' ' + ctx.label + ': ' + ctx.raw + ' (' + perc + '%)';
                    }
                }
            }
        }
    }
});

// -------------------------------------------------------
// GRAFICO 5 — Nuove iscrizioni (linea)
// -------------------------------------------------------
new Chart(document.getElementById('grafico-iscrizioni'), {
    type: 'line',
    data: {
        labels: ['Gen','Feb','Mar','Apr','Mag','Giu',
                 'Lug','Ago','Set','Ott','Nov','Dic'],
        datasets: [{
            data: iscrizioni,
            borderColor: verde1,
            backgroundColor: 'rgba(27,67,50,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: verde1,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { display: false },
                ticks: { autoSkip: false }
            },
            y: {
                grid: { color: 'rgba(0,0,0,0.05)' },
                beginAtZero: true
            }
        }
    }
});

</script>

<?php include __DIR__ . '/../footer_dashboard.php'; ?>