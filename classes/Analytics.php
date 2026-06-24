<?php

require_once __DIR__ . '/Db.php';

/**
 * Contiene tutte le query aggregate usate nella pagina statistiche.
 * Non gestisce CRUD (quello sta in Cliente.php e Prenotazione.php),
 * ma solo calcoli e raggruppamenti per i grafici.
 */
class Analytics
{

/**
 * Restituisce il numero di prenotazioni per ogni mese dell'anno indicato.
 * I mesi senza prenotazioni vengono restituiti con valore 0,
 * così JavaScript riceve sempre tutti e 12 i mesi.
 */
public static function prenotazioniPerMese(int $anno): array
{
    // CONNETTERCI
    $pdo = Db::connect();
    // PREPARARE LA QUERY AL DB
    $stmt = $pdo->prepare(

        'SELECT MONTH(data) AS mese, COUNT(*) AS totale
         FROM prenotazioni
         WHERE YEAR(data) = :anno
         GROUP BY MONTH(data)
         ORDER BY mese'

    );
    // EXECUTE
    $stmt->execute([':anno' => $anno]);
    // FETCH
    $righe = $stmt->fetchAll();

    // Inizializziamo tutti e 12 i mesi a zero
    $risultato = array_fill(1, 12, 0);

    // Inseriamo i valori reali dove esistono
    foreach ($righe as $riga) {
        $risultato[$riga['mese']] = (int) $riga['totale'];
    }

    // Restituiamo i valori senza le chiavi numeriche 1-12
    // perché JavaScript si aspetta [42, 0, 71...] non [1=>42, 2=>0, 3=>71...]
    return array_values($risultato);
}

/**
 * Restituisce il numero di prenotazioni per ogni giorno della settimana,
 * filtrato per anno e mese specifici.
 * L'ordine è sempre: Lun, Mar, Mer, Gio, Ven, Sab, Dom.
 */
public static function prenotazioniPerGiorno(int $anno, int $mese): array
{
    // CONNETTERCI
    $pdo = Db::connect();
    // PREPARARE LA QUERY AL DB
    $stmt = $pdo->prepare(

        'SELECT DAYOFWEEK(data) AS giorno, COUNT(*) AS totale
         FROM prenotazioni
         WHERE YEAR(data) = :anno
         AND MONTH(data) = :mese
         GROUP BY DAYOFWEEK(data)
         ORDER BY giorno'

    );
    // EXECUTE
    $stmt->execute([':anno' => $anno, ':mese' => $mese]);
    // FETCH
    $righe = $stmt->fetchAll();

    // MySQL: DAYOFWEEK restituisce 1=domenica, 2=lunedì... 7=sabato
    // Noi vogliamo: 0=lunedì, 1=martedì... 6=domenica (ordine europeo)
    // Inizializziamo i 7 giorni a zero
    $risultato = array_fill(0, 7, 0);

    foreach ($righe as $riga) {
        $giornoMysql = (int) $riga['giorno'];

        // Convertiamo dal sistema MySQL (1=dom...7=sab)
        // al sistema europeo (0=lun...6=dom)
        // Lunedì MySQL=2 → nostro=0
        // Martedì MySQL=3 → nostro=1
        // ...
        // Domenica MySQL=1 → nostro=6
        $giornoNostro = ($giornoMysql === 1) ? 6 : $giornoMysql - 2;

        $risultato[$giornoNostro] = (int) $riga['totale'];
    }

    return $risultato;
}

/**
 * Restituisce il numero di prenotazioni per ogni fascia oraria,
 * filtrato per anno e mese specifici.
 * Le fasce sono di 2 ore: 8-10, 10-12, 12-14, 14-16, 16-18, 18-20, 20-22, 22-24.
 */
public static function fasceOrarie(int $anno, int $mese): array
{
    // CONNETTERCI
    $pdo = Db::connect();
    // PREPARARE LA QUERY AL DB
    $stmt = $pdo->prepare(

        'SELECT HOUR(ora_inizio) AS ora, COUNT(*) AS totale
         FROM prenotazioni
         WHERE YEAR(data) = :anno
         AND MONTH(data) = :mese
         GROUP BY HOUR(ora_inizio)
         ORDER BY ora'

    );
    // EXECUTE
    $stmt->execute([':anno' => $anno, ':mese' => $mese]);
    // FETCH
    $righe = $stmt->fetchAll();

    // Definiamo le 8 fasce orarie che vogliamo mostrare
    // La chiave è l'ora di inizio, il valore inizia a zero
    $fasce = [8=>0, 10=>0, 12=>0, 14=>0, 16=>0, 18=>0, 20=>0, 22=>0];

    foreach ($righe as $riga) {
        $ora = (int) $riga['ora'];

        // Ogni prenotazione viene assegnata alla fascia di appartenenza.
        // Es: una prenotazione alle 9:30 appartiene alla fascia 8-10,
        // una alle 19:00 appartiene alla fascia 18-20.
        // floor() arrotonda sempre verso il basso, poi moltiplichiamo
        // per 2 per trovare l'inizio della fascia da 2 ore.
        $inizioFascia = floor($ora / 2) * 2;

        // Se la fascia esiste nel nostro array, aggiungiamo la prenotazione
        if (isset($fasce[$inizioFascia])) {
            $fasce[$inizioFascia] += (int) $riga['totale'];
        }
    }

    return array_values($fasce);
}

public static function distribuzioneLivelli(): array
{
    // CONNETTERCI
    $pdo = Db::connect();
    // PREPARARE LA QUERY AL DB
    $stmt = $pdo->prepare(

        'SELECT 
            COALESCE(livello, "Non definito") AS livello,
            COUNT(*) AS totale
         FROM clienti
         GROUP BY livello
         ORDER BY totale DESC'

    );
    // EXECUTE
    $stmt->execute();
    // FETCH
    $righe = $stmt->fetchAll();

    // Inizializziamo tutti i livelli FITP a zero
    $risultato = [];
    foreach (Cliente::LIVELLI as $livello) {
        $risultato[$livello] = 0;
    }

    // Aggiungiamo la voce per i clienti senza livello
    $risultato['Non definito'] = 0;

    // Inseriamo i valori reali
    foreach ($righe as $riga) {
        if (isset($risultato[$riga['livello']])) {
            $risultato[$riga['livello']] = (int) $riga['totale'];
        }
    }

    return $risultato;
}

/**
 * Restituisce il numero di nuovi clienti registrati per ogni mese
 * dell'anno indicato, basandosi sulla data di creazione dell'account.
 */
public static function iscrizioniPerMese(int $anno): array
{
    // CONNETTERCI
    $pdo = Db::connect();
    // PREPARARE LA QUERY AL DB
    $stmt = $pdo->prepare(

        'SELECT MONTH(creato_il) AS mese, COUNT(*) AS totale
         FROM clienti
         WHERE YEAR(creato_il) = :anno
         GROUP BY MONTH(creato_il)
         ORDER BY mese'

    );
    // EXECUTE
    $stmt->execute([':anno' => $anno]);
    // FETCH
    $righe = $stmt->fetchAll();

    // Inizializziamo tutti e 12 i mesi a zero
    $risultato = array_fill(1, 12, 0);

    // Inseriamo i valori reali dove esistono
    foreach ($righe as $riga) {
        $risultato[$riga['mese']] = (int) $riga['totale'];
    }

    return array_values($risultato);
}

    

}

?>