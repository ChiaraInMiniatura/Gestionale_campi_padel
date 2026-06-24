<?php

/**
 * Autoloader semplice: quando il codice usa una classe non ancora
 * caricata (es. "new Cliente()"), PHP chiama automaticamente questa
 * funzione, che include il file corrispondente da classes/.
 *
 * Vantaggio: le pagine non devono scrivere require_once per ogni
 * singola classe, basta includere questo unico file.
 */
spl_autoload_register(function (string $nomeClasse) {
    $percorso = __DIR__ . '/' . $nomeClasse . '.php';
    if (file_exists($percorso)) {
        require_once $percorso;
    }
});
