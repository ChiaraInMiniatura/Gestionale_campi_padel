<?php

require_once __DIR__ . '/../config/config.php';

class Db
{

    // unica istanza condivisa (pattern Singleton)
    private static ?PDO $instance = null;

    // costruttore privato (cosi' nessuno puo' "fare new Db")
    private function __construct() {}

    public static function connect(): PDO
    {

        if (self::$instance === null) {

            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [

                // errori lanciano eccezioni invece di fallire silenziosamente
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {

                die('Connessione fallita: ' . $e->getMessage());

            }

        }

        return self::$instance;

    }
}

?>
