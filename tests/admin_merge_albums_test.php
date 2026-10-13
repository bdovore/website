<?php

/**
 * Tests CLI du lot B (fusion d'albums pilote) sur base jetable :
 * preflight, journal admin_operation, verrou applicatif, etapes rejouables,
 * echec apres chaque etape, reprise et postconditions.
 *
 * Aucune donnee destructive n'est touchee si le nom de la base ne commence
 * pas par le prefixe de test autorise.
 */

$database = getenv('ADMIN_TEST_DATABASE');
if (!$database || strpos($database, 'admin_test') !== 0) {
    fwrite(STDERR, "ADMIN_TEST_DATABASE doit commencer par admin_test.\n");
    exit(2);
}

$host = getenv('ADMIN_TEST_HOST') ?: 'localhost';
$user = getenv('ADMIN_TEST_USER') ?: 'root';
$password = getenv('ADMIN_TEST_PASSWORD') === false ? 'root' : getenv('ADMIN_TEST_PASSWORD');
$socket = getenv('ADMIN_TEST_SOCKET') ?: ini_get('mysqli.default_socket');
$port = intval(getenv('ADMIN_TEST_PORT') ?: ini_get('mysqli.default_port') ?: 3306);
$server = new mysqli($host, $user, $password, '', $port, $socket);
if ($server->connect_errno) { fwrite(STDERR, $server->connect_error . "\n"); exit(2); }

$failures = 0;
$checks = 0;
function dbAssert ($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) { $failures++; fwrite(STDERR, "ECHEC: $message\n"); }
}

function countRows ($table, $where = '1=1')
{
    global $server;
    $row = $server->query("SELECT COUNT(*) total FROM `$table` WHERE $where")->fetch_assoc();
    return intval($row['total']);
}

function expectAdminException ($callable, $httpStatus, $errorCode, $message)
{
    try {
        $callable();
        dbAssert(false, $message . ' : attendait AdminOperationException');
        return null;
    }
    catch (AdminOperationException $e) {
        dbAssert($e->httpStatus() === $httpStatus, $message . ' : statut ' . $e->httpStatus() . ' != ' . $httpStatus);
        dbAssert($e->errorCode() === $errorCode, $message . ' : code ' . $e->errorCode() . ' != ' . $errorCode);
        return $e;
    }
}

try {
    $server->query("DROP DATABASE IF EXISTS `$database`");
    if (!$server->query("CREATE DATABASE `$database` CHARACTER SET utf8 COLLATE utf8_unicode_ci")) throw new RuntimeException($server->error);
    $server->select_db($database);

    $history = "
        CREATE TABLE bd_tome (ID_TOME INT UNSIGNED NOT NULL PRIMARY KEY, ID_EDITION INT UNSIGNED NULL, TITRE VARCHAR(150) NULL, NUM_TOME INT NULL, ID_SERIE INT UNSIGNED NOT NULL DEFAULT 0) ENGINE=MyISAM;
        CREATE TABLE bd_edition (ID_EDITION INT UNSIGNED NOT NULL PRIMARY KEY, ID_TOME INT UNSIGNED NOT NULL, IMG_COUV VARCHAR(100) NULL) ENGINE=MyISAM;
        CREATE TABLE users (user_id INT UNSIGNED NOT NULL PRIMARY KEY, username VARCHAR(25) NOT NULL DEFAULT '') ENGINE=MyISAM;
        CREATE TABLE users_album (user_id INT UNSIGNED NOT NULL, id_edition INT UNSIGNED NOT NULL, PRIMARY KEY (user_id, id_edition)) ENGINE=MyISAM;
        CREATE TABLE users_comment (USER_ID INT UNSIGNED NOT NULL, ID_TOME INT UNSIGNED NOT NULL, NOTE TINYINT UNSIGNED NULL, COMMENT MEDIUMTEXT NULL, DTE_POST DATETIME NOT NULL, PRIMARY KEY (USER_ID, ID_TOME)) ENGINE=MyISAM;
        CREATE TABLE users_list_carre (user_id INT UNSIGNED NOT NULL, id_tome INT UNSIGNED NOT NULL, rang TINYINT UNSIGNED NULL, PRIMARY KEY (user_id, id_tome)) ENGINE=MyISAM;
        CREATE TABLE users_exclusions (user_id INT UNSIGNED NOT NULL, id_tome INT UNSIGNED NOT NULL, id_serie INT UNSIGNED NOT NULL, PRIMARY KEY (user_id, id_tome, id_serie)) ENGINE=MyISAM;
    ";
    if (!$server->multi_query($history)) throw new RuntimeException($server->error);
    do { if ($result = $server->store_result()) $result->free(); } while ($server->more_results() && $server->next_result());
    if ($server->errno) throw new RuntimeException($server->error);
    $migration = file_get_contents(dirname(__DIR__) . '/sql/2026-09-21-create-admin-operation.sql');
    if (!$server->multi_query($migration)) throw new RuntimeException($server->error);
    do { if ($result = $server->store_result()) $result->free(); } while ($server->more_results() && $server->next_result());
    if ($server->errno) throw new RuntimeException($server->error);

    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    if (!defined('BDO_DIR')) define('BDO_DIR', dirname(__DIR__) . DS);
    if (!defined('DEBUG')) define('DEBUG', false);
    class Bdo_Cfg {
        private static $values = array();
        public static function getVar ($key) { return isset(self::$values[$key]) ? self::$values[$key] : null; }
        public static function setVar ($key, $value) { self::$values[$key] = $value; }
        public static function schema () { return self::getVar('schema'); }
    }
    class AdminTestSchema {
        public $schema;
        public $dbColumn = array();
        public $dbConstraint = array();
        public function __construct ($schema) { $this->schema = $schema; }
        public function is_table ($table) { return false; }
    }
    class User { public static function minAccesslevel ($level = 5) { return isset($_SESSION['userConnect']->level) && $_SESSION['userConnect']->level <= $level; } }
    Bdo_Cfg::setVar('connexion', $server);
    Bdo_Cfg::setVar('schema', new AdminTestSchema($database));
    $_SESSION = array();
    require_once dirname(__DIR__) . '/inc/mysql.inc.php';
    require_once dirname(__DIR__) . '/inc/util.inc.php';
    require_once dirname(__DIR__) . '/mvc/models/AdminMergeAlbumsService.php';

    /**
     * Jeu de donnees de reference : album source 10 (editions 11,12), album
     * cible 20 (edition 21, edition par defaut valide), album tiers 30 sans
     * edition par defaut valide. Le membre 4 possede des lignes sur les deux
     * albums (doublons a dedupliquer).
     */
    function resetFixtures ()
    {
        global $server;
        foreach (array('bd_tome', 'bd_edition', 'users', 'users_album', 'users_comment', 'users_list_carre', 'users_exclusions', 'admin_operation') as $table) {
            $server->query("DELETE FROM `$table`");
        }
        $server->query("INSERT INTO users (user_id, username) VALUES (1,'un'),(2,'deux'),(3,'trois'),(4,'quatre'),(5,'cinq'),(7,'admin')");
        $server->query("INSERT INTO bd_tome (ID_TOME, ID_EDITION, TITRE, NUM_TOME, ID_SERIE) VALUES (10, 11, 'Album source', 1, 100), (20, 21, 'Album cible', 1, 100), (30, NULL, 'Album tiers', 1, 100)");
        $server->query("INSERT INTO bd_edition (ID_EDITION, ID_TOME, IMG_COUV) VALUES (11, 10, 'CV-000010-000011.jpg'), (12, 10, 'CV-000010-000012.jpg'), (21, 20, 'CV-000020-000021.jpg'), (31, 30, NULL)");
        $server->query("INSERT INTO users_album (user_id, id_edition) VALUES (1, 11), (2, 12), (3, 21)");
        $server->query("INSERT INTO users_comment (USER_ID, ID_TOME, NOTE, COMMENT, DTE_POST) VALUES (1, 10, 5, 'seul sur source', '2020-01-01 00:00:00'), (4, 10, 3, 'doublon', '2020-01-02 00:00:00'), (3, 20, 4, 'deja cible', '2020-01-03 00:00:00'), (4, 20, 4, 'conserve', '2020-01-04 00:00:00')");
        $server->query("INSERT INTO users_list_carre (user_id, id_tome, rang) VALUES (1, 10, 1), (4, 10, 2), (4, 20, 3)");
        $server->query("INSERT INTO users_exclusions (user_id, id_tome, id_serie) VALUES (1, 10, 100), (4, 10, 100), (4, 20, 100)");
        AdminMergeAlbumsService::$hooksBeforeStep = array();
    }

    $service = new AdminMergeAlbumsService();

    // ---------------- validation metier (specification 13.2)

    resetFixtures();
    expectAdminException(function () use ($service) { $service->execute(10, 10, 7); }, 422, 'SAME_ALBUM', 'source egale cible');
    expectAdminException(function () use ($service) { $service->execute(0, 20, 7); }, 422, 'UNDEFINED_ALBUM', 'source nulle');
    expectAdminException(function () use ($service) { $service->execute(10, 0, 7); }, 422, 'UNDEFINED_ALBUM', 'cible nulle');
    expectAdminException(function () use ($service) { $service->execute(999, 20, 7); }, 404, 'SOURCE_NOT_FOUND', 'source inexistante');
    expectAdminException(function () use ($service) { $service->execute(10, 999, 7); }, 404, 'TARGET_NOT_FOUND', 'cible inexistante');
    dbAssert(countRows('admin_operation') === 0, 'refus de validation : aucune operation journalisee');
    dbAssert(countRows('users_comment', 'ID_TOME = 10') === 2, 'refus de validation : aucune ligne modifiee');

    resetFixtures();
    expectAdminException(function () use ($service) { $service->execute(10, 30, 7); }, 422, 'TARGET_DEFAULT_EDITION', 'cible sans edition par defaut');
    $server->query("UPDATE bd_tome SET ID_EDITION = 11 WHERE ID_TOME = 30");
    expectAdminException(function () use ($service) { $service->execute(10, 30, 7); }, 422, 'TARGET_DEFAULT_EDITION', 'cible avec edition par defaut etrangere');
    dbAssert(countRows('bd_tome', 'ID_TOME = 10') === 1, 'cible invalide : source non supprimee');

    // ---------------- preflight : impacts et doublons annonces (9.1)

    resetFixtures();
    $preflight = $service->preflight(10, 20);
    dbAssert($preflight['impacts']['editions'] === 2, 'preflight : 2 editions a deplacer');
    dbAssert($preflight['impacts']['comments'] === 2, 'preflight : 2 commentaires');
    dbAssert($preflight['impacts']['selections'] === 2, 'preflight : 2 selections');
    dbAssert($preflight['impacts']['exclusions'] === 2, 'preflight : 2 exclusions');
    dbAssert($preflight['impacts']['collection_refs'] === 2, 'preflight : 2 references de collections');
    dbAssert($preflight['conflicts']['comments'] === 1, 'preflight : 1 doublon de commentaire prevu');
    dbAssert($preflight['conflicts']['selections'] === 1, 'preflight : 1 doublon de selection prevu');
    dbAssert($preflight['conflicts']['exclusions'] === 1, 'preflight : 1 doublon d exclusion prevu');
    dbAssert($preflight['default_edition'] === 21, 'preflight : edition par defaut de la cible');
    dbAssert($preflight['idempotency_key'] === 'MERGE_ALBUM:10:20', 'preflight : cle d idempotence stable');

    // ---------------- succes complet (13.3)

    resetFixtures();
    $op = $service->execute(10, 20, 7);
    dbAssert($op->STATUS === 'VERIFIED', 'succes : operation VERIFIED');
    dbAssert(countRows('admin_operation') === 1, 'succes : une seule operation journalisee');
    dbAssert(countRows('bd_tome', 'ID_TOME = 10') === 0, 'succes : album source supprime');
    dbAssert(countRows('bd_edition', 'ID_TOME = 10') === 0, 'succes : aucune edition rattachee a la source');
    dbAssert(countRows('bd_edition', 'ID_TOME = 20') === 3, 'succes : editions rattachees a la cible');
    dbAssert(countRows('users_comment', 'ID_TOME = 10') === 0, 'succes : aucun commentaire rattache a la source');
    dbAssert(countRows('users_comment', "ID_TOME = 20 AND USER_ID = 4") === 1, 'succes : le doublon de commentaire est deduplique');
    $keptComment = $server->query("SELECT COMMENT FROM users_comment WHERE ID_TOME = 20 AND USER_ID = 4")->fetch_assoc();
    dbAssert($keptComment['COMMENT'] === 'conserve', 'succes : le commentaire de l album conserve n est pas ecrase');
    dbAssert(countRows('users_comment', 'ID_TOME = 20') === 3, 'succes : commentaires deplaces sans doublon');
    dbAssert(countRows('users_list_carre', 'id_tome = 20') === 2 && countRows('users_list_carre', 'user_id = 4') === 1, 'succes : selections dedupliquees');
    dbAssert(countRows('users_exclusions', 'id_tome = 20') === 2 && countRows('users_exclusions', 'user_id = 4') === 1, 'succes : exclusions dedupliquees');
    dbAssert(countRows('users_album') === 3, 'succes : references de collections intactes');
    $defaultEdition = $server->query("SELECT t.ID_EDITION, e.ID_TOME AS EDITION_TOME FROM bd_tome t LEFT JOIN bd_edition e ON e.ID_EDITION = t.ID_EDITION WHERE t.ID_TOME = 20")->fetch_assoc();
    dbAssert(intval($defaultEdition['ID_EDITION']) === 21 && intval($defaultEdition['EDITION_TOME']) === 20, 'succes : edition par defaut de la cible valide');
    $progression = json_decode($op->PROGRESSION, true);
    dbAssert($progression['steps']['MOVE_COMMENTS']['moved'] === 1, 'succes : compteur de commentaires deplaces');
    dbAssert($progression['steps']['MOVE_COMMENTS']['deduplicated'] === 1, 'succes : compteur de commentaires dedupliques');
    dbAssert($progression['steps']['MOVE_EDITIONS']['moved'] === 2, 'succes : compteur d editions deplacees');
    dbAssert(!empty($op->FINISHED_DTE), 'succes : date de fin renseignee');
    $snapshot = json_decode($op->SNAPSHOT, true);
    dbAssert($snapshot['impacts']['collection_refs'] === 2 && count($snapshot['source_editions']) === 2, 'succes : snapshot complet');

    // ---------------- double soumission : la meme operation, pas une seconde (13.1)

    $again = $service->execute(10, 20, 7);
    dbAssert(intval($again->ID_ADMIN_OPERATION) === intval($op->ID_ADMIN_OPERATION), 'double soumission : meme operation retournee');
    dbAssert(countRows('admin_operation') === 1, 'double soumission : aucune operation supplementaire');

    // ---------------- echec apres chaque etape, puis reprise (13.3)

    $steps = array('MOVE_COMMENTS', 'MOVE_SELECTIONS', 'MOVE_EXCLUSIONS', 'MOVE_EDITIONS',
                   'ENSURE_DEFAULT_EDITION', 'CHECK_POSTCONDITIONS', 'DELETE_SOURCE', 'VERIFY_FINAL');
    foreach ($steps as $index => $completedStep) {
        resetFixtures();
        if ('VERIFY_FINAL' === $completedStep) continue; // pas d etape ulterieure : le succes est teste ailleurs
        $failingStep = $steps[$index + 1];
        AdminMergeAlbumsService::$hooksBeforeStep[$failingStep][] = function ($op) {
            throw new AdminOperationException('Panne simulee apres l etape precedente.', 500, 'SIMULATED_FAILURE', intval($op->ID_ADMIN_OPERATION));
        };
        $failedException = expectAdminException(function () use ($service) { $service->execute(10, 20, 7); }, 500, 'SIMULATED_FAILURE', 'panne apres ' . $completedStep);
        $failedOp = $service->getOperation($failedException->operationId());
        dbAssert($failedOp->STATUS === 'FAILED', 'panne apres ' . $completedStep . ' : operation FAILED');
        dbAssert($failedOp->CURRENT_STEP === $completedStep, 'panne apres ' . $completedStep . ' : etape courante = ' . $completedStep);
        dbAssert('DELETE_SOURCE' !== $completedStep || countRows('bd_tome', 'ID_TOME = 10') === 0, 'panne : source supprimee seulement apres DELETE_SOURCE');
        dbAssert('DELETE_SOURCE' === $completedStep || countRows('bd_tome', 'ID_TOME = 10') === 1, 'panne apres ' . $completedStep . ' : source non supprimee');
        dbAssert($index < 3 || countRows('bd_edition', 'ID_TOME = 10') === 0, 'panne : editions deplacees seulement apres MOVE_EDITIONS');
        dbAssert($index >= 3 || countRows('bd_edition', 'ID_TOME = 10') === 2, 'panne apres ' . $completedStep . ' : editions encore en place');

        // reprise : aucune etape deja appliquee n est rejouee, aucun doublon, compteurs stables
        AdminMergeAlbumsService::$hooksBeforeStep = array();
        $resumed = $service->resume($failedOp->ID_ADMIN_OPERATION, 7);
        dbAssert($resumed->STATUS === 'VERIFIED', 'panne apres ' . $completedStep . ' : reprise jusqu a VERIFIED');
        dbAssert(countRows('users_comment', 'ID_TOME = 20') === 3, 'panne apres ' . $completedStep . ' : reprise sans doublon de commentaires');
        dbAssert(countRows('users_comment', 'ID_TOME = 20 AND USER_ID = 4') === 1, 'panne apres ' . $completedStep . ' : reprise sans doublon pour le membre en conflit');
        dbAssert(countRows('users_list_carre', 'id_tome = 20') === 2, 'panne apres ' . $completedStep . ' : reprise sans doublon de selections');
        dbAssert(countRows('users_exclusions', 'id_tome = 20') === 2, 'panne apres ' . $completedStep . ' : reprise sans doublon d exclusions');
        dbAssert(countRows('users_album') === 3, 'panne apres ' . $completedStep . ' : collections intactes apres reprise');
        dbAssert(countRows('bd_tome', 'ID_TOME = 10') === 0, 'panne apres ' . $completedStep . ' : source supprimee apres reprise');
        $resumedProgression = json_decode($resumed->PROGRESSION, true);
        dbAssert(!empty($resumedProgression['resumes']), 'panne apres ' . $completedStep . ' : reprise journalisee');
        dbAssert($resumedProgression['steps']['MOVE_COMMENTS']['moved'] === 1 && $resumedProgression['steps']['MOVE_COMMENTS']['deduplicated'] === 1,
                 'panne apres ' . $completedStep . ' : compteurs stables apres reprise');
        dbAssert(intval($resumed->ID_ADMIN_OPERATION) === intval($failedOp->ID_ADMIN_OPERATION), 'panne apres ' . $completedStep . ' : reprise sur la meme operation');
    }

    // ---------------- postcondition invalide : source non supprimee (13.3)

    resetFixtures();
    AdminMergeAlbumsService::$hooksBeforeStep['DELETE_SOURCE'][] = function ($op) {
        global $server;
        $server->query("INSERT INTO users_comment (USER_ID, ID_TOME, NOTE, COMMENT, DTE_POST) VALUES (5, 10, 3, 'incoherence apparue', '2020-01-05 00:00:00')");
    };
    $postException = expectAdminException(function () use ($service) { $service->execute(10, 20, 7); }, 422, 'POSTCONDITION_BEFORE_DELETE', 'postcondition invalide');
    $failedOp = $service->getOperation($postException->operationId());
    dbAssert($failedOp->STATUS === 'FAILED', 'postcondition invalide : operation FAILED');
    dbAssert(countRows('bd_tome', 'ID_TOME = 10') === 1, 'postcondition invalide : album source non supprime');
    dbAssert($failedOp->ERROR_CODE === 'POSTCONDITION_BEFORE_DELETE', 'postcondition invalide : code d erreur journalise');
    // l'incoherence relevee est celle signalee, pas une autre
    AdminMergeAlbumsService::$hooksBeforeStep = array();
    $server->query("DELETE FROM users_comment WHERE ID_TOME = 10 AND USER_ID = 5");
    $resumed = $service->resume($failedOp->ID_ADMIN_OPERATION, 7);
    dbAssert($resumed->STATUS === 'VERIFIED', 'postcondition invalide : reprise apres correction');

    // ---------------- verrou applicatif (13.3)

    resetFixtures();
    $other = new mysqli($host, $user, $password, $database, $port, $socket);
    $otherLock = $other->query("SELECT GET_LOCK('bdovore_admin_catalogue', 0) AS locked")->fetch_assoc();
    dbAssert(intval($otherLock['locked']) === 1, 'verrou : acquis par une autre connexion');
    dbAssert(!$service->acquireLock(), 'verrou : indisponible pour la seconde execution (409 cote controleur)');
    $other->query("SELECT RELEASE_LOCK('bdovore_admin_catalogue')");
    dbAssert($service->acquireLock(), 'verrou : disponible apres liberation');
    dbAssert($service->releaseLock(), 'verrou : libere dans le bloc finally');
    $other->close();

    // ---------------- operation active incompatible sur les memes albums (9.1)

    resetFixtures();
    $server->query("INSERT INTO admin_operation (OP_TYPE, SOURCE_ENTITY, SOURCE_ID, TARGET_ENTITY, TARGET_ID, IDEMPOTENCY_KEY, STATUS, ID_USER, CREATED_DTE, STARTED_DTE, UPDATED_DTE)
                    VALUES ('MERGE_ALBUM', 'album', 10, 'album', 999, 'MERGE_ALBUM:10:999', 'PREPARED', 7, NOW(), NOW(), NOW())");
    $knownOpId = intval($server->insert_id);
    expectAdminException(function () use ($service) { $service->execute(10, 20, 7); }, 409, 'ACTIVE_OPERATION', 'operation active incompatible');
    expectAdminException(function () use ($service) { $service->execute(20, 10, 7); }, 409, 'ACTIVE_OPERATION', 'operation active incompatible (albums vises)');
    dbAssert(countRows('bd_tome', 'ID_TOME = 10') === 1, 'operation active : aucune mutation');

    // ---------------- garde de reprise

    $knownOp = $service->getOperation($knownOpId);
    dbAssert($knownOp !== null, 'garde de reprise : operation connue');
    expectAdminException(function () use ($service, $knownOp) { $service->resume(intval($knownOp->ID_ADMIN_OPERATION), 7); }, 409, 'RESUME_NOT_ALLOWED', 'reprise refusee hors echec');
    expectAdminException(function () use ($service) { $service->resume(999999, 7); }, 404, 'OPERATION_NOT_FOUND', 'reprise d operation inconnue');

    // ---------------- non-regression : rejeu apres succes deja verifie

    resetFixtures();
    $verified = $service->execute(10, 20, 7);
    $replay = $service->execute(10, 20, 7);
    dbAssert(intval($replay->ID_ADMIN_OPERATION) === intval($verified->ID_ADMIN_OPERATION) && $replay->STATUS === 'VERIFIED', 'rejeu apres succes : meme operation VERIFIED');
    dbAssert(countRows('admin_operation') === 1, 'rejeu apres succes : aucune nouvelle operation');

    echo $checks . " contrôles, " . $failures . " échec(s).\n";
    exit($failures ? 1 : 0);
}
catch (Exception $e) {
    fwrite(STDERR, "ERREUR: " . $e->getMessage() . "\n");
    exit(3);
}
