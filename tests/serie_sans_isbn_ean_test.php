<?php
/** Intégration sur base jetable : confirmation, propagation et valeurs par défaut. */
$database = getenv('ADMIN_TEST_DATABASE');
if (!$database || !preg_match('/^admin_test_[a-z0-9_]+$/', $database)) {
    fwrite(STDERR, "ADMIN_TEST_DATABASE doit commencer par admin_test_.\n"); exit(2);
}
$db = new mysqli(getenv('ADMIN_TEST_HOST') ?: 'localhost', getenv('ADMIN_TEST_USER') ?: 'root',
    getenv('ADMIN_TEST_PASSWORD') === false ? 'root' : getenv('ADMIN_TEST_PASSWORD'), '',
    intval(getenv('ADMIN_TEST_PORT') ?: 3306), getenv('ADMIN_TEST_SOCKET') ?: ini_get('mysqli.default_socket'));
$created = false;
$checks = 0;
function serieAssert($condition, $message) {
    global $checks; $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function GetMetaTag($delay, $message, $url) { return $message; }
function scalarQuery($sql) { global $db; return $db->query($sql)->fetch_row()[0]; }
class Bdo_Cfg {
    public static function getVar($name) { global $db; return $name === 'connexion' ? $db : null; }
    public static function setVar($name, $value) {}
}
class User { public static function minAccesslevel($level) { return true; } }
class Bdo_Controller {}
class SerieConfirmationRendered extends RuntimeException {}
class SerieTestView {
    public $vars = array();
    public $layout;
    public function set_var($values) { $this->vars = array_merge($this->vars, $values); }
    public function addPhtmlFile($file, $slot) { $this->vars['template'] = $file; }
    public function render() { throw new SerieConfirmationRendered(); }
}
class SerieTestTome {
    public function updateGenreForSerie($serie, $genre) {}
}
class SerieTestGroupeserie {
    public $links;
    public function deleteLiens($serie) { $this->links = array(); }
    public function addLiens($serie, $links) { $this->links = $links; }
}
define('DEBUG', false);
define('BDO_DIR', dirname(__DIR__));
define('BDO_URL', 'http://localhost/');
require_once BDO_DIR . '/inc/util.inc.php';
require_once BDO_DIR . '/inc/mysql.inc.php';
require_once BDO_DIR . '/library/Bdo/Db/Line.php';
require_once BDO_DIR . '/mvc/models/Serie.php';
require_once BDO_DIR . '/mvc/controllers/Admin.php';
class SerieTestModel extends Serie {
    public $updates = 0;
    public function __construct() {}
    public function set_dataPaste($data = array()) { $this->a_dataPaste = $data; }
    public function update() {
        global $db;
        $this->updates++;
        $name = $db->real_escape_string($this->a_dataPaste['NOM']);
        $this->ID_SERIE = $this->a_dataPaste['ID_SERIE'] ?? 4;
        if (!isset($this->a_dataPaste['ID_SERIE'])) $db->query("INSERT INTO bd_serie (ID_SERIE, NOM) VALUES (4, '$name')");
        else $db->query("UPDATE bd_serie SET NOM = '$name' WHERE ID_SERIE = " . intval($this->ID_SERIE));
    }
}
#[AllowDynamicProperties]
class SerieTestAdmin extends Admin {
    public function __construct($serie) {
        $this->Serie = $serie; $this->Tome = new SerieTestTome();
        $this->Groupeserie = new SerieTestGroupeserie(); $this->view = new SerieTestView();
    }
    public function loadModel($name) {}
}
try {
    // CREATE refuses an existing database; this test never erases unrelated fixtures.
    $db->query("CREATE DATABASE `$database` CHARACTER SET utf8"); $created = true;
    $db->select_db($database);
    $db->query("CREATE TABLE bd_serie (ID_SERIE INT PRIMARY KEY, NOM VARCHAR(100), FLAG_SANS_ISBN_EAN TINYINT UNSIGNED NOT NULL DEFAULT 0) ENGINE=MyISAM");
    $db->query("CREATE TABLE bd_tome (ID_TOME INT PRIMARY KEY, ID_SERIE INT) ENGINE=MyISAM");
    $db->query("CREATE TABLE bd_edition (ID_EDITION INT PRIMARY KEY, ID_TOME INT, PROP_STATUS INT, ISBN VARCHAR(20), EAN VARCHAR(20), FLAG_SANS_ISBN_EAN TINYINT NULL) ENGINE=MyISAM");
    $db->query("INSERT INTO bd_serie (ID_SERIE,NOM) VALUES (1,'Test'),(2,'Autre'),(3,'Vide')");
    $db->query("INSERT INTO bd_tome VALUES (10,1),(11,1),(20,2)");
    $db->query("INSERT INTO bd_edition VALUES (100,10,1,'123','456',NULL),(101,10,0,'','',NULL),(102,11,1,'','',0),(200,20,1,'','',NULL)");
    $serie = new SerieTestModel();
    serieAssert(!$serie->isSansIsbnEan(1), 'série ordinaire : défaut désactivé');
    serieAssert(!$serie->isAlbumSansIsbnEan(10), 'album ordinaire : défaut désactivé');
    $impact = $serie->getSansIsbnEanImpact(1);
    serieAssert($impact->NB_ALBUMS == 2 && $impact->NB_EDITIONS == 3, 'compte toutes les éditions et albums');
    $_SESSION = array(); $_SERVER['REQUEST_METHOD'] = 'POST'; $_GET = array('act'=>'update');
    $_POST = array('csrf_token'=>adminCsrfToken(), 'txtSerieId'=>'1', 'txtSerie'=>'Titre modifié "test"',
        'txtGenreId'=>'6', 'chkFini'=>'1', 'txtNbTome'=>'8', 'txtTri'=>'Tes', 'txtSerieHist'=>'Texte <p>conservé</p>',
        'idSerie'=>array('2','3'), 'FLAG_SANS_ISBN_EAN'=>'1');
    $admin = new SerieTestAdmin($serie);
    try { $admin->editSerie(); throw new RuntimeException('confirmation absente'); }
    catch (SerieConfirmationRendered $e) {}
    serieAssert($serie->updates === 0, 'aucune écriture avant confirmation');
    serieAssert(scalarQuery('SELECT FLAG_SANS_ISBN_EAN FROM bd_serie WHERE ID_SERIE=1') == 0, 'réglage inchangé avant confirmation');
    serieAssert(scalarQuery('SELECT COUNT(*) FROM bd_edition WHERE FLAG_SANS_ISBN_EAN=1') == 0, 'éditions inchangées avant confirmation');
    serieAssert($admin->view->vars['template'] === 'admin/confirm', 'utilise la confirmation admin commune');
    $fields = $admin->view->vars['CONFIRM_FIELDS'];
    serieAssert($fields['txtSerieHist'] === $_POST['txtSerieHist'] && $fields['txtSerie'] === $_POST['txtSerie'], 'texte et titre conservés');
    serieAssert($fields['idSerie[0]'] === 2 && $fields['idSerie[1]'] === 3, 'liens transmis sans perdre les tableaux');
    $_POST['confirm_sans_isbn_ean'] = 'ok';
    ob_start(); $admin->editSerie(); ob_end_clean();
    serieAssert($serie->updates === 1, 'fiche enregistrée après confirmation');
    serieAssert($admin->Groupeserie->links === array('2','3'), 'liens conservés après enregistrement');
    serieAssert(scalarQuery('SELECT COUNT(*) FROM bd_edition WHERE ID_TOME IN (10,11) AND FLAG_SANS_ISBN_EAN=1') == 3, 'toutes les éditions marquées, dont les éditions en attente');
    serieAssert(scalarQuery('SELECT FLAG_SANS_ISBN_EAN IS NULL FROM bd_edition WHERE ID_EDITION=200') == 1, 'autre série inchangée');
    serieAssert(scalarQuery('SELECT CONCAT(ISBN, ":", EAN) FROM bd_edition WHERE ID_EDITION=100') === '123:456', 'codes existants conservés');
    serieAssert($serie->isSansIsbnEan(1) && $serie->isAlbumSansIsbnEan(10), 'défaut transmis aux albums');
    $db->query('UPDATE bd_edition SET FLAG_SANS_ISBN_EAN=NULL WHERE ID_EDITION=101');
    unset($_POST['confirm_sans_isbn_ean']);
    ob_start(); $admin->editSerie(); ob_end_clean();
    serieAssert(scalarQuery('SELECT FLAG_SANS_ISBN_EAN IS NULL FROM bd_edition WHERE ID_EDITION=101') == 1, 'enregistrer une série déjà cochée préserve une exception manuelle');
    unset($_POST['FLAG_SANS_ISBN_EAN']);
    ob_start(); $admin->editSerie(); ob_end_clean();
    serieAssert(!$serie->isSansIsbnEan(1), 'décocher désactive le défaut');
    serieAssert(scalarQuery('SELECT FLAG_SANS_ISBN_EAN FROM bd_edition WHERE ID_EDITION=100') == 1, 'décocher ne retire pas les indicateurs existants');
    $serie->setSansIsbnEan(3,true,true);
    serieAssert($serie->isSansIsbnEan(3), 'série vide : réglage persistant');
    serieAssert(!$serie->isSansIsbnEan(999) && !$serie->isAlbumSansIsbnEan(999), 'identifiants inconnus sans défaut');
    serieAssert($serie->getSansIsbnEanImpact(999) === null, 'série inexistante détectée');
    try { $serie->setSansIsbnEan(0,true,true); throw new RuntimeException('ID nul accepté'); }
    catch (InvalidArgumentException $e) { $checks++; }
    $_GET = array('act'=>'append');
    $_POST = array('csrf_token'=>adminCsrfToken(), 'txtSerie'=>'Nouvelle série', 'txtGenreId'=>'6',
        'chkFini'=>'1', 'txtNbTome'=>'', 'txtTri'=>'Nou', 'txtSerieHist'=>'', 'FLAG_SANS_ISBN_EAN'=>'1');
    $newSerie = new SerieTestModel(); $newAdmin = new SerieTestAdmin($newSerie);
    try { $newAdmin->editSerie(); throw new RuntimeException('confirmation de création absente'); }
    catch (SerieConfirmationRendered $e) {}
    serieAssert($newSerie->updates === 0 && scalarQuery('SELECT COUNT(*) FROM bd_serie WHERE ID_SERIE=4') == 0, 'création différée jusqu’à confirmation');
    $_POST['confirm_sans_isbn_ean'] = 'ok';
    ob_start(); $newAdmin->editSerie(); ob_end_clean();
    serieAssert($newSerie->isSansIsbnEan(4), 'nouvelle série créée avec le défaut activé');
    echo "$checks contrôles, 0 échec(s).\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ECHEC : " . $e->getMessage() . "\n"); $failed = true;
} finally {
    if ($created) $db->query("DROP DATABASE `$database`");
    $db->close();
}
exit(empty($failed) ? 0 : 1);
