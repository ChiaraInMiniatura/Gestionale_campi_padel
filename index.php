<?php include __DIR__ . '/ui/components/header.php'; ?>

<!-- HERO - immagine in background, testo e tasti sopra -->
<section class="hero-pubblica-bg">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-titolo">Pinea Padel</h1>
        <p class="hero-sottotitolo">Il tuo campo. La tua gente.</p>
        <div class="d-flex gap-3 flex-wrap">
            <a href="/gestionale_padel/prenota.php" class="btn btn-hero-primario">
                <i class="fa-solid fa-calendar-days me-2"></i>Prenota un campo
            </a>
            <a href="/gestionale_padel/lezioni.php" class="btn btn-hero-secondario">
                Scopri le lezioni
            </a>
        </div>
    </div>
</section>

<section class="sezione-info py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="info-card">
                    <i class="fa-solid fa-table-tennis-paddle-ball info-icona"></i>
                    <h5 class="mt-3 fw-bold">5 Campi</h5>
                    <p class="text-muted">Coperti e all'aperto disponibili tutto l'anno.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <i class="fa-solid fa-user-graduate info-icona"></i>
                    <h5 class="mt-3 fw-bold">Istruttori certificati</h5>
                    <p class="text-muted">Maestri FITP per tutti i livelli.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <i class="fa-solid fa-clock info-icona"></i>
                    <h5 class="mt-3 fw-bold">Orari flessibili</h5>
                    <p class="text-muted">Aperti dalle 8:00 alle 23:00, ogni giorno.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/ui/components/footer.php'; ?>
