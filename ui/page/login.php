<?php
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../helpers/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// già loggato: admin -> gestionale, cliente -> homepage (area futura)
if (isset($_SESSION['cliente_id'])) {
    if ($_SESSION['cliente_ruolo'] === 'admin') {
        header('Location: ../../pages/dashboard.php');
    } else {
        header('Location: ../../index.php');
    }
    exit;
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errore = 'Inserisci email e password.';
    } else {
        $cliente = Cliente::verificaCredenziali($email, $password);
        if ($cliente) {
            effettuaLogin($cliente);
            if ($cliente['ruolo'] === 'admin') {
                header('Location: ../../pages/dashboard.php');
            } else {
                header('Location: ../../index.php');
            }
            exit;
        } else {
            $errore = 'Email o password non corrette.';
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

                    <h3 class="text-center mb-4">Accedi</h3>

                    <?php if ($errore): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
                    <?php endif; ?>

                    <?php if (isset($_GET['registrato'])): ?>
                        <div class="alert alert-success">Account creato! Accedi con le tue credenziali.</div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-auth w-100">Accedi</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Non hai un account? <a href="register.php" class="link-auth">Registrati</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../ui/components/footer.php'; ?>
