<?php

require_once __DIR__ . '/Db.php';

/**
 * Gestisce tutti gli utenti del sistema (clienti e admin).
 * Il login avviene tramite email + password.
 * I campi sportivi (livello, tessera, ecc.) sono opzionali e
 * vengono completati dall'admin dopo la registrazione.
 */
class Cliente
{
    public const LIVELLI = [
        'Principiante 0', 'Principiante 1', 'Principiante 2', 'Principiante 3',
        'Intermedio 3.5', 'Intermedio 4', 'Intermedio 5', 'Intermedio 6',
        'Avanzato 6.5', 'Avanzato 7', 'Avanzato 8', 'Avanzato 9',
    ];
    public const POSIZIONI = ['Dx', 'Sx', 'Entrambe'];
    public const MANI      = ['Dx', 'Sx'];
    public const RUOLI     = ['cliente', 'admin'];

    // -------------------------------------------------------
    // FIND
    // -------------------------------------------------------

    public static function findById(int $id): ?array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'SELECT * FROM clienti WHERE id = :id'

        );
        // EXECUTE
        $stmt->execute([':id' => $id]);
        // FETCH + RETURN
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    // cerca per email — include la password per la verifica al login
    public static function findByEmail(string $email): ?array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'SELECT * FROM clienti WHERE email = :email'

        );
        // EXECUTE
        $stmt->execute([':email' => $email]);
        // FETCH + RETURN
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    /**
     * Verifica le credenziali di login.
     * Ritorna l'array del cliente se email e password sono corrette,
     * altrimenti null.
     */
    public static function verificaCredenziali(string $email, string $password): ?array
    {
        $cliente = self::findByEmail($email);

        if ($cliente && $cliente['password'] && password_verify($password, $cliente['password'])) {
            return $cliente;
        }

        return null;
    }

    // ritorna tutti i clienti, admin prima poi per cognome
    public static function findAll(): array
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->query(

            'SELECT * FROM clienti ORDER BY cognome, nome'

        );
        // RETURN
        return $stmt->fetchAll();
    }

    /**
     * Cerca clienti applicando i filtri passati come array associativo.
     */
    public static function cerca(array $filtri, string $ordina = 'creato_il'): array
    {
        // CONNETTERCI
        $pdo = Db::connect();

        $sql    = 'SELECT * FROM clienti WHERE 1=1';
        $params = [];

        if (!empty($filtri['nome'])) {
            $sql .= ' AND nome LIKE :nome';
            $params[':nome'] = '%' . $filtri['nome'] . '%';
        }
        if (!empty($filtri['cognome'])) {
            $sql .= ' AND cognome LIKE :cognome';
            $params[':cognome'] = '%' . $filtri['cognome'] . '%';
        }
        if (!empty($filtri['eta_min'])) {
            $sql .= ' AND data_nascita <= :data_nascita_max';
            $params[':data_nascita_max'] = self::dataLimitePerEta((int) $filtri['eta_min']);
        }
        if (!empty($filtri['eta_max'])) {
            $sql .= ' AND data_nascita >= :data_nascita_min';
            $params[':data_nascita_min'] = self::dataLimitePerEta((int) $filtri['eta_max'] + 1);
        }
        if (!empty($filtri['email'])) {
            $sql .= ' AND email LIKE :email';
            $params[':email'] = '%' . $filtri['email'] . '%';
        }
        if (!empty($filtri['cellulare'])) {
            $sql .= ' AND cellulare LIKE :cellulare';
            $params[':cellulare'] = '%' . $filtri['cellulare'] . '%';
        }
        if (!empty($filtri['socio'])) {
            $sql .= ' AND socio = :socio';
            $params[':socio'] = $filtri['socio'];
        }
        if (!empty($filtri['certificato'])) {
            $sql .= ' AND certificato_medico = :certificato';
            $params[':certificato'] = $filtri['certificato'];
        }
        if (!empty($filtri['livello'])) {
            $sql .= ' AND livello = :livello';
            $params[':livello'] = $filtri['livello'];
        }
        if (!empty($filtri['posizione'])) {
            $sql .= ' AND posizione = :posizione';
            $params[':posizione'] = $filtri['posizione'];
        }
        if (!empty($filtri['mano'])) {
            $sql .= ' AND mano = :mano';
            $params[':mano'] = $filtri['mano'];
        }
        if (!empty($filtri['tessera_fitp'])) {
            $sql .= ' AND tessera_fitp LIKE :tessera_fitp';
            $params[':tessera_fitp'] = '%' . $filtri['tessera_fitp'] . '%';
        }

        $colonneOrdinabili = ['creato_il', 'cognome, nome'];
        if (!in_array($ordina, $colonneOrdinabili, true)) {
            $ordina = 'creato_il';
        }
        $sql .= ' ORDER BY ' . $ordina;

        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare($sql);
        // EXECUTE + RETURN
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // COUNT
    // -------------------------------------------------------

    public static function contaAdmin(): int
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->query(

            "SELECT COUNT(*) AS totale FROM clienti WHERE ruolo = 'admin'"

        );
        // RETURN
        return (int) $stmt->fetch()['totale'];
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------

    /**
     * Registrazione pubblica: solo nome, cognome, email e password.
     * Ruolo default: 'cliente'. Ritorna null se l'email è già in uso.
     */
    public static function registra(string $nome, string $cognome, string $email, string $password): ?int
    {
        if (self::findByEmail($email)) {
            return null; // email già registrata
        }

        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'INSERT INTO clienti (nome, cognome, email, password, ruolo)
             VALUES (:nome, :cognome, :email, :password, :ruolo)'

        );
        // EXECUTE
        $stmt->execute([
            ':nome'     => $nome,
            ':cognome'  => $cognome,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':ruolo'    => 'cliente',
        ]);

        // RETURN
        return (int) $pdo->lastInsertId();
    }

    /**
     * Inserimento manuale dall'admin (scheda completa, niente password).
     * Il cliente non potrà fare login finché un admin non gli imposta
     * una password tramite resetPassword().
     */
    public static function create(array $dati): int
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'INSERT INTO clienti (nome, cognome, data_nascita, email, cellulare, socio,
             posizione, mano, livello, certificato_medico, certificato_scadenza,
             tessera_fitp, tessera_fitp_scadenza, ruolo)
             VALUES (:nome, :cognome, :data_nascita, :email, :cellulare, :socio,
             :posizione, :mano, :livello, :certificato_medico, :certificato_scadenza,
             :tessera_fitp, :tessera_fitp_scadenza, :ruolo)'

        );
        // EXECUTE
        $stmt->execute([
            ':nome'                   => $dati['nome'],
            ':cognome'                => $dati['cognome'],
            ':data_nascita'           => $dati['data_nascita'],
            ':email'                  => $dati['email'],
            ':cellulare'              => $dati['cellulare'],
            ':socio'                  => $dati['socio'],
            ':posizione'              => $dati['posizione'],
            ':mano'                   => $dati['mano'],
            ':livello'                => $dati['livello'],
            ':certificato_medico'     => $dati['certificato_medico'],
            ':certificato_scadenza'   => $dati['certificato_scadenza'],
            ':tessera_fitp'           => $dati['tessera_fitp'],
            ':tessera_fitp_scadenza'  => $dati['tessera_fitp_scadenza'],
            ':ruolo'                  => $dati['ruolo'] ?? 'cliente',
        ]);

        // RETURN
        return (int) $pdo->lastInsertId();
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    public static function update(int $id, array $dati): bool
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'UPDATE clienti SET nome = :nome, cognome = :cognome,
             data_nascita = :data_nascita, email = :email, cellulare = :cellulare,
             socio = :socio, posizione = :posizione, mano = :mano, livello = :livello,
             certificato_medico = :certificato_medico,
             certificato_scadenza = :certificato_scadenza,
             tessera_fitp = :tessera_fitp,
             tessera_fitp_scadenza = :tessera_fitp_scadenza,
             ruolo = :ruolo
             WHERE id = :id'

        );
        // EXECUTE + RETURN
        return $stmt->execute([
            ':nome'                  => $dati['nome'],
            ':cognome'               => $dati['cognome'],
            ':data_nascita'          => $dati['data_nascita'],
            ':email'                 => $dati['email'],
            ':cellulare'             => $dati['cellulare'],
            ':socio'                 => $dati['socio'],
            ':posizione'             => $dati['posizione'],
            ':mano'                  => $dati['mano'],
            ':livello'               => $dati['livello'],
            ':certificato_medico'    => $dati['certificato_medico'],
            ':certificato_scadenza'  => $dati['certificato_scadenza'],
            ':tessera_fitp'          => $dati['tessera_fitp'],
            ':tessera_fitp_scadenza' => $dati['tessera_fitp_scadenza'],
            ':ruolo'                 => $dati['ruolo'] ?? 'cliente',
            ':id'                    => $id,
        ]);
    }

    /**
     * Aggiorna solo il ruolo di un cliente. Se si tenta di togliere il
     * ruolo admin all'ultimo admin rimasto, l'operazione viene bloccata.
     * Ritorna stringa vuota se ok, messaggio di errore altrimenti.
     */
    public static function aggiornaRuolo(int $id, string $ruolo): string
    {
        if (!in_array($ruolo, self::RUOLI, true)) {
            return 'Ruolo non valido.';
        }

        $target = self::findById($id);
        if (!$target) {
            return 'Cliente non trovato.';
        }

        if ($target['ruolo'] === 'admin' && $ruolo !== 'admin' && self::contaAdmin() <= 1) {
            return 'Non puoi togliere il ruolo admin a questo account: è l\'ultimo admin rimasto.';
        }

        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'UPDATE clienti SET ruolo = :ruolo WHERE id = :id'

        );
        // EXECUTE
        $stmt->execute([':ruolo' => $ruolo, ':id' => $id]);
        return '';
    }

    /**
     * Cambia la propria password (il cliente conosce quella attuale).
     * Ritorna stringa vuota se ok, messaggio di errore altrimenti.
     */
    public static function cambiaPassword(int $id, string $passwordAttuale, string $nuovaPassword): string
    {
        $cliente = self::findById($id);
        if (!$cliente) {
            return 'Cliente non trovato.';
        }

        if (!$cliente['password'] || !password_verify($passwordAttuale, $cliente['password'])) {
            return 'La password attuale inserita non è corretta.';
        }

        if (strlen($nuovaPassword) < 6) {
            return 'La nuova password deve avere almeno 6 caratteri.';
        }

        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'UPDATE clienti SET password = :password WHERE id = :id'

        );
        // EXECUTE + RETURN
        return $stmt->execute([
            ':password' => password_hash($nuovaPassword, PASSWORD_DEFAULT),
            ':id'       => $id,
        ]) ? '' : 'Errore durante il salvataggio.';
    }

    /**
     * Reset password da parte dell'admin (non richiede la vecchia password).
     * Ritorna stringa vuota se ok, messaggio di errore altrimenti.
     */
    public static function resetPassword(int $id, string $nuovaPassword): string
    {
        if (strlen($nuovaPassword) < 6) {
            return 'La nuova password deve avere almeno 6 caratteri.';
        }

        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'UPDATE clienti SET password = :password WHERE id = :id'

        );
        // EXECUTE + RETURN
        return $stmt->execute([
            ':password' => password_hash($nuovaPassword, PASSWORD_DEFAULT),
            ':id'       => $id,
        ]) ? '' : 'Errore durante il salvataggio.';
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    /**
     * Elimina un cliente, con protezioni:
     * non si può eliminare se stessi, non si può eliminare l'ultimo admin.
     */
    public static function eliminaConProtezioni(int $id, int $idUtenteCorrente): string
    {
        if ($id === $idUtenteCorrente) {
            return 'Non puoi eliminare il tuo stesso account mentre sei collegato.';
        }

        $target = self::findById($id);
        if (!$target) {
            return 'Cliente non trovato.';
        }

        if ($target['ruolo'] === 'admin' && self::contaAdmin() <= 1) {
            return 'Non puoi eliminare questo account: è l\'ultimo admin rimasto.';
        }

        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'DELETE FROM clienti WHERE id = :id'

        );
        // EXECUTE + RETURN
        $stmt->execute([':id' => $id]);
        return '';
    }

    public static function delete(int $id): bool
    {
        // CONNETTERCI
        $pdo = Db::connect();
        // PREPARARE LA QUERY AL DB
        $stmt = $pdo->prepare(

            'DELETE FROM clienti WHERE id = :id'

        );
        // EXECUTE + RETURN
        return $stmt->execute([':id' => $id]);
    }

    // -------------------------------------------------------
    // VALIDAZIONE
    // -------------------------------------------------------

    public static function valida(array $dati): string
    {
        if (trim($dati['nome'] ?? '') === '' || trim($dati['cognome'] ?? '') === '') {
            return 'Nome e cognome sono obbligatori.';
        }
        return '';
    }

    // -------------------------------------------------------
    // UTILITÀ
    // -------------------------------------------------------

    public static function calcolaEta(?string $dataNascita): ?int
    {
        if (!$dataNascita) {
            return null;
        }
        $nascita = new DateTime($dataNascita);
        $oggi    = new DateTime();
        return $oggi->diff($nascita)->y;
    }

    private static function dataLimitePerEta(int $eta): string
    {
        return date('Y-m-d', strtotime("-$eta years"));
    }

    public static function nomeCompleto(array $cliente): string
    {
        return self::capitalizza($cliente['nome']) . ' ' . self::capitalizza($cliente['cognome']);
    }

    public static function capitalizza(string $testo): string
    {
        return mb_convert_case(mb_strtolower($testo), MB_CASE_TITLE, 'UTF-8');
    }
}

?>
