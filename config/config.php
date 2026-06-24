<?php

    // Credenziali del database (default di XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'gestionale_padel');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');

    // URL base del progetto — cambia 'gestionale_padel' con il nome
    // della cartella che hai messo in htdocs
    if (!defined('BASE_URL')) {
        define('BASE_URL', '/gestionale_padel');
    }

?>
