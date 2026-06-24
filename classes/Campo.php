<?php

require_once __DIR__ . '/Db.php';

/**
 * Rappresenta un campo da padel. Operazioni minime: i 5 campi sono
 * dati fissi inseriti dallo schema SQL, qui servono solo in lettura.
 */
class Campo
{
    // ritorna tutti i campi, ordinati per id
    public static function findAll(): array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->query(

            'SELECT * FROM campi ORDER BY id'

        );
        // RETURN
        return $stmt->fetchAll();
    }
}

?>
