<?php
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../helpers/auth.php';

$errore  = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome             = trim($_POST['nome'] ?? '');
    $cognome          = trim($_POST['cognome'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $conferma         = $_POST['conferma_password'] ?? '';

    if ($nome === '' || $cognome === '' || $email === '') {
        $errore = 'Nome, cognome ed email sono obbligatori.';
    } elseif ($password === '') {
        $errore = 'Scegli una password.';
    } elseif (strlen($password) < 6) {
        $errore = 'La password deve avere almeno 6 caratteri.';
    } elseif ($password !== $conferma) {
        $errore = 'Le password non coincidono.';
    } else {
        $nuovoId = Cliente::registra($nome, $cognome, $email, $password);
        if ($nuovoId === null) {
            $errore = 'Esiste già un account con questa email.';
        } else {
            header('Location: login.php?registrato=1');
            exit;
        }
    }
}

include __DIR__ . '/../../ui/components/header.php';
?>

<section class="sezione-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="card card-auth p-4 p-md-5">

                    <h3 class="text-center mb-1">Crea il tuo account</h3>
                    <p class="text-center text-muted small mb-4">Solo nome, cognome ed email sono obbligatori.</p>

                    <?php if ($errore): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="nome" name="nome"
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="cognome" class="form-label">Cognome *</label>
                            <input type="text" class="form-control" id="cognome" name="cognome"
                                   value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   required minlength="6">
                        </div>
                        <div class="mb-4">
                            <label for="conferma_password" class="form-label">Conferma password *</label>
                            <input type="password" class="form-control" id="conferma_password"
                                   name="conferma_password" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-auth w-100">Registrati</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Hai già un account? <a href="login.php" class="link-auth">Accedi</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../ui/components/footer.php'; ?>
