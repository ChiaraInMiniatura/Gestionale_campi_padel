<?php

require_once __DIR__ . '/Db.php';

/**
 * Rappresenta una prenotazione su un campo, in un giorno e orario specifici.
 * Contiene la logica di validazione orario, controllo sovrapposizione,
 * e gestione dei clienti associati (relazione 0-4 clienti per prenotazione).
 */
class Prenotazione
{
    public const ORARIO_APERTURA = '08:00';
    public const ORARIO_CHIUSURA = '24:00';
    public const MAX_CLIENTI = 4;

    // cerca PER ID e restituisce un array associativo, o null se non esiste
    public static function findById(int $id): ?array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'SELECT * FROM prenotazioni WHERE id = :id'

        );
        // EXECUTE
        $stmt->execute([':id' => $id]);
        // FETCH
        $prenotazione = $stmt->fetch();

        if (!$prenotazione) {
            return null;
        }

        $prenotazione['clienti'] = self::clientiAssociati($id);
        return $prenotazione;
    }

    /**
     * Ritorna tutte le prenotazioni di un giorno specifico, raggruppate
     * per campo_id, con il dipendente che le ha create e i clienti associati
     * già caricati. Struttura: [campo_id => [prenotazione, prenotazione, ...]]
     */
    public static function delGiorno(string $data): array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'SELECT p.*, c.nome AS dip_nome, c.cognome AS dip_cognome
             FROM prenotazioni p
             JOIN clienti c ON c.id = p.creato_da
             WHERE p.data = :data
             ORDER BY p.ora_inizio'

        );
        // EXECUTE
        $stmt->execute([':data' => $data]);
        // FETCH
        $righe = $stmt->fetchAll();

        $perCampo = [];
        foreach ($righe as $riga) {
            $riga['clienti'] = self::clientiAssociati((int) $riga['id']);
            $perCampo[(int) $riga['campo_id']][] = $riga;
        }

        // RETURN
        return $perCampo;
    }

    // ritorna i clienti attualmente associati a una prenotazione (tabella ponte)
    public static function clientiAssociati(int $prenotazioneId): array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'SELECT c.* FROM prenotazioni_clienti pc
             JOIN clienti c ON c.id = pc.cliente_id
             WHERE pc.prenotazione_id = :prenotazione_id'

        );
        // EXECUTE
        $stmt->execute([':prenotazione_id' => $prenotazioneId]);
        // RETURN
        return $stmt->fetchAll();
    }

    // ritorna solo gli id dei clienti associati (per pre-selezionare le checkbox)
    public static function idClientiAssociati(int $prenotazioneId): array
    {
        $clienti = self::clientiAssociati($prenotazioneId);
        return array_map(fn($c) => (int) $c['id'], $clienti);
    }

    /**
     * Valida orario e numero massimo di clienti.
     * Ritorna un messaggio di errore, oppure stringa vuota se tutto ok.
     * NON controlla la sovrapposizione: per quella usa esisteSovrapposizione().
     */
    public static function valida(array $dati, array $idClientiSelezionati): string
    {
        if (!$dati['campo_id'] || $dati['data'] === '' || $dati['ora_inizio'] === '' || $dati['ora_fine'] === '') {
            return 'Campo, data, ora inizio e ora fine sono obbligatori.';
        }
        if ($dati['ora_inizio'] < self::ORARIO_APERTURA || $dati['ora_fine'] > self::ORARIO_CHIUSURA || $dati['ora_fine'] <= $dati['ora_inizio']) {
            return 'Orario non valido. Il centro è aperto dalle 08:00 alle 24:00 e l\'ora fine deve essere dopo l\'ora inizio.';
        }
        if (count($idClientiSelezionati) > self::MAX_CLIENTI) {
            return 'Non puoi associare più di ' . self::MAX_CLIENTI . ' clienti a una prenotazione.';
        }
        return '';
    }

    /**
     * Controlla se esiste già una prenotazione che si sovrappone
     * (stesso campo, stessa data, range orario che si interseca).
     * $idDaEscludere serve quando si modifica una prenotazione esistente,
     * per non farla risultare in conflitto con se stessa.
     */
    public static function esisteSovrapposizione(int $campoId, string $data, string $oraInizio, string $oraFine, ?int $idDaEscludere = null): bool
    {
        // CONNETTERCI
        $pdo = Db::connect();

        $sql = 'SELECT id FROM prenotazioni WHERE campo_id = :campo_id AND data = :data
                AND ora_inizio < :ora_fine AND ora_fine > :ora_inizio';
        $params = [
            ':campo_id' => $campoId,
            ':data' => $data,
            ':ora_fine' => $oraFine,
            ':ora_inizio' => $oraInizio,
        ];

        if ($idDaEscludere) {
            $sql .= ' AND id != :id_escludi';
            $params[':id_escludi'] = $idDaEscludere;
        }

        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare($sql);
        // EXECUTE
        $stmt->execute($params);
        // RETURN
        return $stmt->fetch() !== false;
    }

    // crea una nuova prenotazione e associa i clienti selezionati, ritorna l'id
    public static function create(array $dati, array $idClientiSelezionati): int
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'INSERT INTO prenotazioni (campo_id, data, ora_inizio, ora_fine, creato_da, note)
             VALUES (:campo_id, :data, :ora_inizio, :ora_fine, :creato_da, :note)'

        );
        // EXECUTE
        $stmt->execute([
            ':campo_id' => $dati['campo_id'],
            ':data' => $dati['data'],
            ':ora_inizio' => $dati['ora_inizio'],
            ':ora_fine' => $dati['ora_fine'],
            ':creato_da' => $dati['creato_da'],
            ':note' => $dati['note'],
        ]);

        $prenotazioneId = (int) $pdo->lastInsertId();
        self::sincronizzaClienti($prenotazioneId, $idClientiSelezionati);

        return $prenotazioneId;
    }

    // aggiorna una prenotazione esistente e risincronizza i clienti associati
    public static function update(int $id, array $dati, array $idClientiSelezionati): bool
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'UPDATE prenotazioni SET campo_id = :campo_id, data = :data,
             ora_inizio = :ora_inizio, ora_fine = :ora_fine, note = :note
             WHERE id = :id'

        );
        // EXECUTE
        $risultato = $stmt->execute([
            ':campo_id' => $dati['campo_id'],
            ':data' => $dati['data'],
            ':ora_inizio' => $dati['ora_inizio'],
            ':ora_fine' => $dati['ora_fine'],
            ':note' => $dati['note'],
            ':id' => $id,
        ]);

        self::sincronizzaClienti($id, $idClientiSelezionati);

        return $risultato;
    }

    /**
     * Rimuove tutte le associazioni cliente esistenti per questa prenotazione
     * e inserisce quelle nuove. Più semplice e sicuro che fare un diff.
     */
    private static function sincronizzaClienti(int $prenotazioneId, array $idClientiSelezionati): void
    {
        $pdo = Db::connect();

        $del = $pdo->prepare('DELETE FROM prenotazioni_clienti WHERE prenotazione_id = :prenotazione_id');
        $del->execute([':prenotazione_id' => $prenotazioneId]);

        $ins = $pdo->prepare('INSERT INTO prenotazioni_clienti (prenotazione_id, cliente_id) VALUES (:prenotazione_id, :cliente_id)');
        foreach ($idClientiSelezionati as $clienteId) {
            $ins->execute([':prenotazione_id' => $prenotazioneId, ':cliente_id' => $clienteId]);
        }
    }

    // elimina una prenotazione (le righe ponte si cancellano da sole grazie a ON DELETE CASCADE)
    public static function delete(int $id): bool
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'DELETE FROM prenotazioni WHERE id = :id'

        );
        // EXECUTE + RETURN
        return $stmt->execute([':id' => $id]);
    }

    public static function orarioFormattato(array $prenotazione): string
    {
        return substr($prenotazione['ora_inizio'], 0, 5) . ' - ' . substr($prenotazione['ora_fine'], 0, 5);
    }

    public static function nomiClientiFormattati(array $prenotazione): string
    {
        if (empty($prenotazione['clienti'])) {
            return '';
        }
        return implode(', ', array_map(fn($c) => Cliente::nomeCompleto($c), $prenotazione['clienti']));
    }

    // minuti trascorsi dall'apertura (08:00) per un orario dato, usato per la griglia visiva
    private static function minutiDaApertura(string $orario): int
    {
        [$ore, $minuti] = explode(':', substr($orario, 0, 5));
        $minutiOrario = ((int) $ore) * 60 + (int) $minuti;
        [$oreApertura, $minutiApertura] = explode(':', self::ORARIO_APERTURA);
        $minutiAperturaTotali = ((int) $oreApertura) * 60 + (int) $minutiApertura;
        return $minutiOrario - $minutiAperturaTotali;
    }

    // posizione verticale (in pixel) dell'inizio del blocco nella griglia visiva
    public static function posizioneTop(array $prenotazione, float $pixelPerMinuto): float
    {
        return self::minutiDaApertura($prenotazione['ora_inizio']) * $pixelPerMinuto;
    }

    // altezza (in pixel) del blocco, proporzionale alla durata reale
    public static function altezzaBlocco(array $prenotazione, float $pixelPerMinuto): float
    {
        $minutiInizio = self::minutiDaApertura($prenotazione['ora_inizio']);
        $minutiFine = self::minutiDaApertura($prenotazione['ora_fine']);
        return ($minutiFine - $minutiInizio) * $pixelPerMinuto;
    }

    // minuti totali tra apertura e chiusura, usato per calcolare l'altezza totale della griglia
    public static function minutiTotaliGiornata(): int
    {
        [$oreA, $minA] = explode(':', self::ORARIO_APERTURA);
        [$oreC, $minC] = explode(':', self::ORARIO_CHIUSURA);
        return (((int) $oreC) * 60 + (int) $minC) - (((int) $oreA) * 60 + (int) $minA);
    }
}

?>
