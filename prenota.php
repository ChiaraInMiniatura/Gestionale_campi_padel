<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ui/page/login.php');
    exit;
}
include __DIR__ . '/ui/components/header.php';
?>

<section class="sezione-pubblica py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Prenota un campo</h2>
            <p class="text-muted">Controlla la disponibilità e scegli il tuo slot. Per completare la prenotazione contattaci.</p>
        </div>

        <!-- Selettore data -->
        <div class="d-flex justify-content-center mb-4">
            <div class="input-group" style="max-width: 280px;">
                <span class="input-group-text bg-white">
                    <i class="fa-solid fa-calendar-days text-success"></i>
                </span>
                <input type="date" id="data-calendario" class="form-control"
                       value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <!-- Griglia disponibilità -->
        <div class="table-responsive calendario-pubblica-wrapper">
            <table class="table table-bordered table-calendario-pubblica text-center align-middle">
                <thead>
                    <tr>
                        <th class="fascia-col">Fascia oraria</th>
                        <th>Campo 1</th>
                        <th>Campo 2</th>
                        <th>Campo 3</th>
                        <th>Campo 4</th>
                        <th>Campo 5</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Calendario visivo statico (mockup).
                    // I dati reali verranno collegati in una fase successiva.
                    $fasce = ['08:00 – 09:30', '09:30 – 11:00', '11:00 – 12:30', '15:00 – 16:30', '16:30 – 18:00', '18:00 – 19:30', '19:30 – 21:00', '21:00 – 22:30'];
                    // Pattern di occupazione casuale ma deterministico per il mockup
                    $occupazioni = [
                        [0,1,0,0,1],
                        [1,0,0,1,0],
                        [0,0,1,0,0],
                        [0,1,0,1,1],
                        [1,0,1,0,0],
                        [0,0,0,1,0],
                        [1,1,0,0,1],
                        [0,1,1,0,0],
                    ];
                    foreach ($fasce as $i => $fascia):
                    ?>
                    <tr>
                        <td class="fascia-label-pubblica"><?= $fascia ?></td>
                        <?php for ($c = 0; $c < 5; $c++): ?>
                            <?php if ($occupazioni[$i][$c]): ?>
                                <td><span class="slot-occupato">Occupato</span></td>
                            <?php else: ?>
                                <td><span class="slot-libero">Libero</span></td>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="text-center text-muted small mt-2">
            <i class="fa-solid fa-circle-info me-1"></i>
            Vista di anteprima. Per prenotare contattaci su WhatsApp o per telefono.
        </p>

        <!-- Card contatto -->
        <div class="card card-contatto mx-auto mt-4 text-center p-4" style="max-width: 480px;">
            <h5 class="fw-bold mb-2">Pronto a prenotare?</h5>
            <p class="text-muted mb-3">Scrivici e confermiamo il tuo slot in pochi minuti.</p>
            <a href="https://wa.me/" target="_blank" class="btn btn-hero-primario">
                <i class="fa-brands fa-whatsapp me-2"></i>Contattaci su WhatsApp
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/ui/components/footer.php'; ?>
