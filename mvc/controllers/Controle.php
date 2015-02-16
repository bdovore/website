<?php

/** 
 * @author laurent
 * 
 */
class Controle extends Bdo_Controller
{

    /**
     */
    public function index ()
    {
         if (User::minAccesslevel(1)) {
            ob_start();

            set_time_limit(360);

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
                    "url" => BDO_URL . "admin/editserie?serie_id=",
                    "colUrl" => "ID_SERIE",
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
                    "urlExplode" => "<a href='" . BDO_URL . "admin/editedition?edition_id={col}' target='_blank'>{col}</a>",
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
                    "urlExplode" => "<a href='" . BDO_URL . "admin/editedition?edition_id={col}' target='_blank'>{col}</a>",
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
                    "colUrl" => "ID_AUTEUR",
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
            ORDER BY `Enregistrements` DESC",
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
            ORDER BY `Enregistrements` DESC",
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
                    "url" => BDO_URL . "admin/editcollection?collec_id=",
                    "colUrl" => "ID_COLLECTION",
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
                    "url" => BDO_URL . "admin/editalbum?alb_id=",
                    "colUrl" => "ID_TOME",
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
                    "url" => BDO_URL . "admin/editserie?serie_id=",
                    "colUrl" => "ID_SERIE",
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
                    "url" => BDO_URL . "admin/editalbum?alb_id=",
                    "colUrl" => "ID_TOME",
                ),
                array(
                    "title" => "Editions dont la date de parution n'est pas renseignée (ou < 1800-01-01) (non marquées 'Introuvable')",
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
                    "url" => BDO_URL . "admin/editedition?edition_id=",
                    "colUrl" => "ID_EDITION"
            ),
array(    
    "title" => "EAN mal formés, selon le calcul de la clé ean",
    "query" => "SELECT SQL_CALC_FOUND_ROWS
		`ID_EDITION` , 
        concat(mid(ean,1,12),RIGHT(10 - MOD((MID(ean,2,1) + MID(ean,4,1)+MID(ean,6,1)+MID(ean,8,1)+MID(ean,10,1)+MID(ean,12,1))*3
        +MID(ean,1,1)+MID(ean,3,1)+MID(ean,5,1)+MID(ean,7,1)+MID(ean,9,1)+MID(ean,11,1),10),1)) as 'ean calculé', ean, isbn
        FROM `bd_edition` 
        WHERE concat(mid(ean,1,12),RIGHT(10 - MOD((MID(ean,2,1) + MID(ean,4,1)+MID(ean,6,1)+MID(ean,8,1)+MID(ean,10,1)+MID(ean,12,1))*3
            +MID(ean,1,1)+MID(ean,3,1)+MID(ean,5,1)+MID(ean,7,1)+MID(ean,9,1)+MID(ean,11,1),10),1)) <> ean and
            ean is not null",
        "url" => BDO_URL."admin/adminedition?edition_id=",
	"colUrl" => "ID_EDITION",
   ));


          
            echo '(Le resultat est limit&eacute; &agrave; 200 lignes)';

            echo '<form name="formregle" method="post">
                <div>
                Controle : 
                <select name="id_queryRegle">';
            foreach ($a_queryRegle as $id_queryRegle => $queryRegle) {
                $selected = ($id_queryRegle == $_POST['id_queryRegle']) ? 'SELECTED' : '';
                echo '<option value="' . $id_queryRegle . '" ' . $selected . ' >' . $queryRegle['title'] . '</option>';
            };
            echo '</select><br />
            <label for="viewQuery"><input type="checkbox" id="viewQuery" name="viewQuery" value="checked" ' . postVal('viewQuery') . '> voir la requete</label>
            <br /><input type="submit" name="execformvalue" value="Chercher">
            </div>
            </form>';


            if (issetNotEmpty(postVal('execformvalue'))  ) {
                $title = $a_queryRegle[postVal('id_queryRegle]')]["title"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["url"])) $url = $a_queryRegle[postVal('id_queryRegle')]["url"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["colUrl"]))
                    $colUrl = $a_queryRegle[postVal('id_queryRegle')]["colUrl"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["colExplode"]))
                    $colExplode = $a_queryRegle[postVal('id_queryRegle')]["colExplode"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["urlExplode"]))
                    $urlExplode = $a_queryRegle[postVal('id_queryRegle')]["urlExplode"];

                $query = $a_queryRegle[postVal('id_queryRegle')]["query"];
                $query .= " LIMIT 0,200";

                if (issetNotEmpty(postVal('viewQuery'))) {
                    echo_pre($query);
                }

                $resultat = Db_query($query);

                $nbr = Db_CountRow($resultat);

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
                    $cmpt++;
                }


                if ($nbr > 0) {
                    echo '<h3>' . $title . '</h3>';
                    echo $cmpt . ' lignes sur ' . $nbr;
                    tableOfFetchObj($a_obj, $a_onlyCol, false);
                } else {
                    echo 'Aucune ligne de resultat !';
                }
               
            }



            $this->view->set_var("PAGE_OB", ob_get_clean());

            $this->view->render();
        };
    }

   
}

