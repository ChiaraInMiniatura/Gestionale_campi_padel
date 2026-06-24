<?php include __DIR__ . '/ui/components/header.php'; ?>

<section class="sezione-pubblica py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Le nostre lezioni</h2>
            <p class="text-muted">Programmi pensati per ogni livello, seguiti da istruttori certificati FITP.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-lezione h-100">
                    <img src="/gestionale_padel/img/lez1.jpg" class="card-img-top card-lezione-img" alt="Inizia a giocare">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">Inizia a Giocare</h5>
                        <p class="card-text text-muted flex-grow-1">4 lezioni individuali con istruttore certificato. Impara le basi, la postura e i colpi fondamentali.</p>
                        <p class="prezzo-lezione fw-bold mt-3 mb-0">€ 120,00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-lezione h-100">
                    <img src="/gestionale_padel/img/lez2.jpg" class="card-img-top card-lezione-img" alt="Alza il livello">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">Alza il Livello</h5>
                        <p class="card-text text-muted flex-grow-1">6 lezioni di gruppo (max 4 persone). Tecnica avanzata, tattica di gioco e situazioni di partita reale.</p>
                        <p class="prezzo-lezione fw-bold mt-3 mb-0">€ 180,00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-lezione h-100">
                    <img src="/gestionale_padel/img/lez3.jpg" class="card-img-top card-lezione-img" alt="Gioca da Pro">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">Gioca da Pro</h5>
                        <p class="card-text text-muted flex-grow-1">8 lezioni personalizzate con analisi video del tuo gioco. Per chi vuole competere e non ha tempo da perdere.</p>
                        <p class="prezzo-lezione fw-bold mt-3 mb-0">€ 320,00</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="/gestionale_padel/prenota.php" class="btn btn-hero-primario">
                <i class="fa-solid fa-calendar-days me-2"></i>Prenota ora
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/ui/components/footer.php'; ?>
