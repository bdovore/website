<?php

/** 
 * @author laurent
 * 
 */
class Controle extends Bdo_Controller_Admin
{

    /**
     */
    public function __construct ()
    {
        parent::__construct();
    }

    public function fk_bdovore ()
    {
        
        /**
         * tableau des relations cles etrangeres
         *
         * to = table origine (reference)
         * td = table destination
         * co = colonne origine (reference)
         * cd = colonne destination
         */
        return array(
                array(
                        'td' => 'bd_collection',
                        'to' => 'bd_editeur',
                        'cd' => 'ID_EDITEUR',
                        'co' => 'ID_EDITEUR'
                ),
                array(
                        'td' => 'bd_edition',
                        'to' => 'bd_editeur',
                        'cd' => 'ID_EDITEUR',
                        'co' => 'ID_EDITEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_editeur',
                        'cd' => 'ID_EDITEUR',
                        'co' => 'ID_EDITEUR'
                ),
                
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_SCENAR',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_DESSIN',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_COLOR',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_SCENAR_ALT',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_DESSIN_ALT',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_COLOR_ALT',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_SCENAR',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_DESSIN',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_COLOR',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_SCENAR_ALT',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_DESSIN_ALT',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_auteur',
                        'cd' => 'ID_COLOR_ALT',
                        'co' => 'ID_AUTEUR'
                ),
                array(
                        'td' => 'users_list_aut',
                        'to' => 'bd_auteur',
                        'cd' => 'id_auteur',
                        'co' => 'ID_AUTEUR'
                ),
                
                array(
                        'td' => 'bd_edition',
                        'to' => 'bd_collection',
                        'cd' => 'ID_COLLECTION',
                        'co' => 'ID_COLLECTION'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_collection',
                        'cd' => 'ID_COLLECTION',
                        'co' => 'ID_COLLECTION'
                ),
                
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_edition',
                        'cd' => 'ID_EDITION',
                        'co' => 'ID_EDITION'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_edition',
                        'cd' => 'ID_EDITION',
                        'co' => 'ID_EDITION'
                ),
                
                array(
                        'td' => 'users_album',
                        'to' => 'bd_edition',
                        'cd' => 'id_edition',
                        'co' => 'ID_EDITION'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_genre',
                        'cd' => 'ID_GENRE',
                        'co' => 'ID_GENRE'
                ),
                array(
                        'td' => 'bd_serie',
                        'to' => 'bd_genre',
                        'cd' => 'ID_GENRE',
                        'co' => 'ID_GENRE'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_genre',
                        'cd' => 'ID_GENRE',
                        'co' => 'ID_GENRE'
                ),
                
                array(
                        'td' => 'users_exclusions',
                        'to' => 'bd_serie',
                        'cd' => 'id_serie',
                        'co' => 'ID_SERIE'
                ),
                array(
                        'td' => 'bd_tome',
                        'to' => 'bd_serie',
                        'cd' => 'ID_SERIE',
                        'co' => 'ID_SERIE'
                ),
                array(
                        'td' => 'serie_comment',
                        'to' => 'bd_serie',
                        'cd' => 'id_serie',
                        'co' => 'ID_SERIE'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_serie',
                        'cd' => 'ID_SERIE',
                        'co' => 'ID_SERIE'
                ),
                
                array(
                        'td' => 'bd_edition',
                        'to' => 'users',
                        'cd' => 'VALIDATOR',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'bd_edition',
                        'to' => 'users',
                        'cd' => 'USER_ID',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_list_aut',
                        'to' => 'users',
                        'cd' => 'user_id',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_exclusions',
                        'to' => 'users',
                        'cd' => 'user_id',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_list_carre',
                        'to' => 'users',
                        'cd' => 'user_id',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_comment',
                        'to' => 'users',
                        'cd' => 'USER_ID',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_album',
                        'to' => 'users',
                        'cd' => 'user_id',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'users',
                        'cd' => 'USER_ID',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'users',
                        'cd' => 'VALIDATOR',
                        'co' => 'user_id'
                ),
                array(
                        'td' => 'serie_comment',
                        'to' => 'users',
                        'cd' => 'user_id',
                        'co' => 'user_id'
                ),
                
                array(
                        'td' => 'bd_edition',
                        'to' => 'bd_tome',
                        'cd' => 'ID_TOME',
                        'co' => 'ID_TOME'
                ),
                
                array(
                        'td' => 'users_exclusions',
                        'to' => 'bd_tome',
                        'cd' => 'id_tome',
                        'co' => 'ID_TOME'
                ),
                array(
                        'td' => 'users_comment',
                        'to' => 'bd_tome',
                        'cd' => 'ID_TOME',
                        'co' => 'ID_TOME'
                ),
                array(
                        'td' => 'users_list_carre',
                        'to' => 'bd_tome',
                        'cd' => 'id_tome',
                        'co' => 'ID_TOME'
                ),
                array(
                        'td' => 'users_alb_prop',
                        'to' => 'bd_tome',
                        'cd' => 'ID_TOME',
                        'co' => 'ID_TOME'
                )
        );
    }

    public function references ()
    {
        $a_liaison = $this->fk_bdovore();
        
        $a_column = array();
        $a_onlyCol = array();
        $a_liaison2 = array();
        foreach ($a_liaison as $liaison) {
            if (! in_array($liaison['co'], $a_onlyCol)) $a_onlyCol[] = $liaison['co'];
            if (! in_array($liaison['cd'], $a_onlyCol)) $a_onlyCol[] = $liaison['cd'];
            $a_column[$liaison['co']] = $liaison['to'];
            $a_liaison2[$liaison['to']][$liaison['co']][] = array(
                    'td' => $liaison['td'],
                    'cd' => $liaison['cd']
            );
        }
        $this->view->set_var('a_column', $a_column);
        
        /*
         *
         * array('td'=>'users_list_carre','to'=>'bd_tome','cd'=>'id_tome','co'=>'ID_TOME'),
         * array('td'=>'users_alb_prop','to'=>'bd_tome','cd'=>'ID_TOME','co'=>'ID_TOME'),
         */
        
        $a_onlyCol[] = 'ID_PROPOSAL';
        $a_onlyCol[] = 'ISBN';
        $a_onlyCol[] = 'EAN';
        $a_onlyCol[] = 'TITRE';
        /*
         * $query = "SELECT TABLE_NAME,COLUMN_NAME FROM db_column"; $resultat =
         * mysql_query($query); $a_table = array(); $a_column = array();
         * $a_tableByColumn = array(); while ($obj =
         * mysql_fetch_object($resultat)) { if (!in_array($obj->TABLE_NAME,
         * $a_table)) $a_table[] = $obj->TABLE_NAME; if
         * (!in_array($obj->COLUMN_NAME, $a_column)) $a_column[] =
         * $obj->COLUMN_NAME; $a_tableByColumn[$obj->COLUMN_NAME][] =
         * $obj->TABLE_NAME; }
         */
        
        $a_ref = array();
        if (isset($_POST['execformvalue']) and isset($_POST['valeur'])) {
            
            $co = $_POST['column_name'];
            $to = $a_column[$_POST['column_name']];
            
            // recherche table d'origine
            $query = "SELECT * FROM `" . Db_Escape_String($to) . "` 
                    WHERE `" . Db_Escape_String($co) . "`= '" . Db_Escape_String($_POST['valeur']) . "'";
            
            $resultat = Db_query($query);
            $a_obj = array();
            while ($obj = Db_fetch_object($resultat)) {
                $a_obj[] = $obj;
            }
            $nbr = Db_CountRow($resultat);
            $a_ref[$to][$co]->nbr = $nbr;
            $a_ref[$to][$co]->query = $query;
            $a_ref[$to][$co]->a_obj = $a_obj;
            
            // recherche table de reference
            foreach ($a_liaison2[$to][$co] as $a_d) {
                
                $query = "SELECT * FROM `" . $a_d['td'] . "` 
                        WHERE `" . $a_d['cd'] . "`= '" . Db_Escape_String($_POST['valeur']) . "'";
                $resultat = Db_query($query);
                $a_obj = array();
                while ($obj = Db_fetch_object($resultat)) {
                    $a_obj[] = $obj;
                }
                $nbr = Db_CountRow($resultat);
                $a_ref[$a_d['td']][$a_d['cd']]->nbr = $nbr;
                $a_ref[$a_d['td']][$a_d['cd']]->query = $query;
                $a_ref[$a_d['td']][$a_d['cd']]->a_obj = $a_obj;
            }
        }
        $this->view->set_var('a_ref', $a_ref);
        $this->view->set_var('a_onlyCol', $a_onlyCol);
        
        $this->view->render();
    }

    public function relations ()
    {
        $a_liaison = $this->fk_bdovore();
                
        if (! isset($_POST['tableOrigine'])) $_POST['tableOrigine'] = array();
        
        $a_to = array();
        foreach ($a_liaison as $a_lien) {
            if (! in_array($a_lien['to'], $a_to)) $a_to[] = $a_lien['to'];
        }
        
        $a_relation = array();
        $i = 0;
        if (isset($_POST['execformrelation'])) {
            
            foreach ($a_liaison as $a_lien) {
                if (in_array($a_lien['to'], $_POST['tableOrigine'])) {
                    
                    $to = $a_lien['to'];
                    $td = $a_lien['td'];
                    $co = $a_lien['co'];
                    $cd = $a_lien['cd'];
                    
                    $query = "SELECT distinct(`" . $td . "`.`" . $cd . "`) FROM `" . $td . "`
			WHERE `" . $td . "`.`" . $cd . "` IS NOT NULL
			AND NOT EXISTS (SELECT 1 FROM `" . $to . "` WHERE `" . $td . "`.`" . $cd . "` =`" . $to . "`.`" . $co . "`)";
                    
                    $result = Db_query($query);
                    $nbr = Db_CountRow($result);
                    
                    if (! isset($_POST['onlyDiff']) or (isset($_POST['onlyDiff']) and (0 < $nbr))) {
                        $a_obj = array();
                        
                        if (0 < $nbr) {
                            While ($obj = Db_fetch_object($result)) {
                                $a_obj[] = $obj;
                            }
                        }
                        
                        $a_relation[$i]->a_lien = $a_lien;
                        $a_relation[$i]->query = $query;
                        $a_relation[$i]->nbr = $nbr;
                        $a_relation[$i]->a_obj = $a_obj;
                    }
                    $i ++;
                }
            }
        }
        
        $this->view->set_var('a_relation', $a_relation);
        $this->view->set_var('a_to', $a_to);
        
        $this->view->render();
    }

    public function regles ()
    {
       
        $a_queryRegle = array(
                array(
                        "title" => "Nom de série ne contenant pas la valeur de la colonne TRI",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		`ID_SERIE`,
		`NOM`,
		`TRI`
	FROM
		`bd_serie`
	WHERE
		`NOM` NOT LIKE concat( '%', `TRI`, '%' )",
                        "url" => BDO_URL . "admin/adminseries.php?serie_id=",
                        "colUrl" => "ID_SERIE"
                ),
                array(
                        "title" => "EAN référencés plusieurs fois dans la table des éditions pour des albums différents (parution >31/12/2006 ou non-renseignée)",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		COUNT(DISTINCT(`ID_TOME`)) AS 'ID albums différents',
		`EAN` ,
		GROUP_CONCAT(distinct(`ID_EDITION`) SEPARATOR ';') as 'Liens vers les éditions (séparateur ;)'
	FROM
		`bd_edition`
	WHERE
		`EAN` IS NOT NULL
				AND TRIM(`EAN`)<>''
	AND (`DTE_PARUTION` > '2006-12-31' OR `DTE_PARUTION` IS NULL )
	GROUP BY `EAN`
	HAVING COUNT(DISTINCT(`ID_TOME`))>1
	ORDER BY 1 DESC",
                        
                        "colExplode" => 'Liens vers les éditions (séparateur ;)',
                        "urlExplode" => "<a href='" . BDO_URL . "admin/admineditions.php?edition_id={col}' target='_blank'>{col}</a>"
                ),
                array(
                        "title" => "ISBN référencés plusieurs fois dans la table des éditions pour des albums différents (parution >31/12/1973 ou non-renseignée)",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		COUNT(DISTINCT(`ID_TOME`)) AS 'ID albums différents',
		`ISBN` ,
		GROUP_CONCAT(distinct(`ID_EDITION`) SEPARATOR ';') as 'Liens vers les éditions (séparateur ;)'
	FROM
		`bd_edition`
	WHERE
		`ISBN` IS NOT NULL
		AND TRIM(`ISBN`)<>''
	AND (`DTE_PARUTION` > '1973-12-31' OR `DTE_PARUTION` IS NULL)
        
	GROUP BY `ISBN`
	HAVING COUNT(DISTINCT(`ID_TOME`))>1
	ORDER BY 1 DESC",
                        "colExplode" => 'Liens vers les éditions (séparateur ;)',
                        "urlExplode" => "<a href='" . BDO_URL . "admin/admineditions.php?edition_id={col}' target='_blank'>{col}</a>"
                ),
                array(
                        "title" => "Triplet PSEUDO, NOM, PRENOM référencés plusieurs fois dans la table des auteurs",
                        "query" => "
	SELECT
		bd_auteur.ID_AUTEUR,
		bd_auteur.`PSEUDO`,
		bd_auteur.`PRENOM`,
		bd_auteur.`NOM`
	FROM `bd_auteur`,
	(
		SELECT
		`PSEUDO`,
		`PRENOM`,
		`NOM`
		FROM `bd_auteur`
		GROUP BY `PSEUDO`,`PRENOM`,`NOM`
		HAVING count(*)>1
	) withDoublon
	WHERE
		bd_auteur.`PSEUDO`=withDoublon.`PSEUDO`
		AND bd_auteur.`PRENOM`=withDoublon.`PRENOM`
		AND bd_auteur.`NOM`=withDoublon.`NOM`",
                        "url" => BDO_URL . "admin/adminauteurs.php?auteur_id=",
                        "colUrl" => "ID_AUTEUR"
                ),
                array(
                        "title" => "PSEUDO référencés plusieurs fois dans la table des auteurs",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		COUNT(*) AS `Enregistrements`,
		`PSEUDO`
	FROM
		`bd_auteur`
	GROUP BY `PSEUDO`
	HAVING count(*)>1
	ORDER BY `Enregistrements` DESC"
                ),
                array(
                        "title" => "Couple NOM, PRENOM référencés plusieurs fois dans la table des auteurs",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		COUNT(*) AS `Enregistrements`,
		`NOM`,
		`PRENOM`,
		GROUP_CONCAT(`PSEUDO` SEPARATOR ' ; ') as 'Liste des pseudos (séparateur ;)'
	FROM
		`bd_auteur`
	WHERE
		`NOM` IS NOT NULL
		AND `PRENOM` IS NOT NULL
	GROUP BY `NOM`,`PRENOM`
	HAVING count(*)>1
	ORDER BY `Enregistrements` DESC"
                ),
                array(
                        "title" => "Couple NOM, ID_EDITEUR référencés plusieurs fois dans la table des collections",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		`bd_collection`.`ID_COLLECTION`,
		`bd_collection`.`NOM`,
		`bd_collection`.`ID_EDITEUR`
	FROM `bd_collection`,
	(
		SELECT
			`NOM`,
			`ID_EDITEUR`
		FROM
			`bd_collection`
		GROUP BY `NOM`,`ID_EDITEUR`
		HAVING count(*)>1
	) withDoublon
	WHERE
		`bd_collection`.`NOM`=withDoublon.`NOM`
		AND `bd_collection`.`ID_EDITEUR`=withDoublon.`ID_EDITEUR`",
                        "url" => BDO_URL . "admin/admincollections.php?collec_id=",
                        "colUrl" => "ID_COLLECTION"
                ),
                array(
                        "title" => "triplet date / collection / Tome présent dans la table des éditions",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		COUNT(*) AS `Enregistrements`,
		`ID_TOME`
	FROM
		`bd_edition`
	GROUP BY `ID_TOME`,`ID_COLLECTION`,`DTE_PARUTION`
	HAVING COUNT(*)>1
	ORDER BY `Enregistrements` DESC",
                        "url" => BDO_URL . "admin/adminalbums.php?alb_id=",
                        "colUrl" => "ID_TOME"
                ),
                array(
                        "title" => "Séries déclarées one-shot (FLG_FINI=2) avec plus de 1 tome",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		`bd_serie`.`ID_SERIE` ,
		count( bd_tome.ID_TOME ) as 'nbr de tomes'
	FROM
		`bd_serie`
		INNER JOIN `bd_tome` ON `bd_tome`.`ID_SERIE` = `bd_serie`.`ID_SERIE`
	WHERE
		`bd_serie`.`FLG_FINI` =2
	GROUP BY `bd_serie`.`ID_SERIE`
	HAVING count( `bd_tome`.`ID_TOME` ) >1
	ORDER BY 2 DESC",
                        "url" => BDO_URL . "admin/adminseries.php?serie_id=",
                        "colUrl" => "ID_SERIE"
                ),
                array(
                        "title" => "Albums de série one-shot (1 seul album) titre différent de celui de la série",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		`bd_serie`.`ID_SERIE` ,
		`bd_serie`.`NOM` AS 'Titre de la série',
		`bd_tome`.`ID_TOME` ,
		`bd_tome`.`TITRE` AS 'Titre de l''album'
	FROM
		`bd_serie`
		INNER JOIN `bd_tome` ON `bd_tome`.`ID_SERIE` = `bd_serie`.`ID_SERIE`
	WHERE
		`bd_serie`.`FLG_FINI` =2
		AND `bd_serie`.`NOM` <> `bd_tome`.`TITRE`
	GROUP BY `bd_serie`.`ID_SERIE`
	HAVING count(`bd_tome`.`ID_TOME`)=1",
                        "url" => BDO_URL . "admin/adminalbums.php?alb_id=",
                        "colUrl" => "ID_TOME"
                ),
                
                array(
                        "title" => "Éditions dont la date de parution n'est pas renseignée (ou < 1800-01-01) (non marquées 'Introuvable')",
                        "query" => "
	SELECT SQL_CALC_FOUND_ROWS
		`ID_EDITION` ,
		`DTE_PARUTION`
	FROM `bd_edition`
	WHERE
		(`DTE_PARUTION` IS NULL
		OR `DTE_PARUTION` < '1800-01-01')
		AND `FLAG_DTE_PARUTION` IS NULL
	ORDER BY `bd_edition`.`DTE_PARUTION` DESC",
                        "url" => BDO_URL . "admin/admineditions.php?edition_id=",
                        "colUrl" => "ID_EDITION"
                ),
                /*
                 array (
                         "title" => "Couvertures dans le répertoire images/couv absentes des éditions",
                         "query" => "
                         SELECT SQL_CALC_FOUND_ROWS couv.IMG_COUV FROM couv
                         WHERE NOT EXISTS (SELECT NULL FROM bd_edition WHERE couv.IMG_COUV = bd_edition.IMG_COUV)
                         ",
                         "url" => BDO_URL_IMAGE."couv/",
                         "colUrl" => "IMG_COUV",
                 ),
        
        array (
                "title" => "Couvertures dans les éditions absentes du répertoire images/couv",
                "query" => "
                SELECT SQL_CALC_FOUND_ROWS bd_edition.IMG_COUV,bd_edition.ID_TOME FROM bd_edition
                WHERE NOT EXISTS (SELECT NULL FROM couv WHERE couv.IMG_COUV = bd_edition.IMG_COUV)
                AND bd_edition.IMG_COUV IS NOT NULL
                ",
                "url" => BDO_URL."admin/adminalbums.php?alb_id=",
                "colUrl" => "ID_TOME",
        ),
        */
        );
        
        $a_regle = array();
        if (isset($_POST['execformvalue']) and isset($_POST['id_queryRegle'])) {
            
            $title = $a_queryRegle[$_POST['id_queryRegle']]["title"];
            if (isset($a_queryRegle[$_POST['id_queryRegle']]["url"])) $url = $a_queryRegle[$_POST['id_queryRegle']]["url"];
            if (isset($a_queryRegle[$_POST['id_queryRegle']]["colUrl"])) $colUrl = $a_queryRegle[$_POST['id_queryRegle']]["colUrl"];
            if (isset($a_queryRegle[$_POST['id_queryRegle']]["colExplode"])) $colExplode = $a_queryRegle[$_POST['id_queryRegle']]["colExplode"];
            if (isset($a_queryRegle[$_POST['id_queryRegle']]["urlExplode"])) $urlExplode = $a_queryRegle[$_POST['id_queryRegle']]["urlExplode"];
            
            $query = $a_queryRegle[$_POST['id_queryRegle']]["query"];
            $query .= " LIMIT 0,200";
            
            
            
            $resultat = Db_query($query);
            
            $resCount = Db_query('SELECT FOUND_ROWS() as nb');
            $rowCount = Db_fetch_array($resCount);
            $nbr = $rowCount['nb'];
            
            $a_obj = array();
            $cmpt = 0;
            while ($obj = Db_fetch_object($resultat)) {
                if (isset($colUrl)) {
                    $obj->voir = '<a href="' . $url . $obj->$colUrl . '" target="_blank">Voir</a>';
                }
                if (isset($colExplode)) {
                    $a_fieldData = explode(';', $obj->{$colExplode});
                    foreach ($a_fieldData as $key => $data) {
                        $a_fieldData[$key] = str_replace('{col}', $data, $urlExplode);
                    }
                    $obj->{$colExplode} = implode(' ; ', $a_fieldData);
                }
                $a_obj[] = $obj;
                $cmpt ++;
            }
            $a_regle[$_POST['id_queryRegle']]->query = $query;
            $a_regle[$_POST['id_queryRegle']]->nbr = $nbr;
            $a_regle[$_POST['id_queryRegle']]->cmpt = $cmpt;
            $a_regle[$_POST['id_queryRegle']]->a_obj = $a_obj;
        }
        
        $this->view->set_var('a_regle', $a_regle);
        $this->view->set_var('a_queryRegle', $a_queryRegle);
        
        $this->view->render();
    }
}

