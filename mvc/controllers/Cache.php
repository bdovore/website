<?php

/**
 *
 * @author laurent
 *        
 */
class Cache extends Bdo_Controller
{

    /**
     */
    public function Index ()
    {
        $max_id_tome = 0;
        $max_id_edition = 0;
        $max_id_serie = 0;

        $resultat = Db_query("SELECT MAX(ID_TOME) as ID_TOME FROM bd_tome");
        while ($obj = Db_fetch_object($resultat)) {
            $max_id_tome = $obj->ID_TOME;
        }
        
        $resultat = Db_query("SELECT MAX(ID_EDITION) as ID_EDITION FROM bd_edition");
        while ($obj = Db_fetch_object($resultat)) {
            $max_id_edition = $obj->ID_EDITION;
        }
        
        $resultat = Db_query("SELECT MAX(ID_SERIE) as ID_SERIE FROM bd_serie");
        while ($obj = Db_fetch_object($resultat)) {
            $max_id_serie = $obj->ID_SERIE;
        }
        
        $this->view->set_var(array(
                'nbruser_ID_TOME' => $max_id_tome,
                'nbruser_ID_EDITION' => $max_id_edition,
                'nbruser_ID_SERIE' => $max_id_serie
        ));
        
        $this->view->render();
    }

    /**
     */
    public function Simil ()
    {
        $a_idGenreExclu = array(
                80,
                82,
                83
        ); // Publicité,Revue de prépublication,Périodique
        
        $continuer = getVal('continuer', '');
        $query = "
        SELECT 
          	bd_tome.ID_TOME,
          	bd_tome.ID_SERIE,
          	bd_serie.ID_GENRE,
            bd_tome.NBR_USER_ID, 
            bd_tome_simil.TSMP_TOME_SIMIL
        FROM bd_tome
            INNER JOIN bd_serie ON bd_tome.ID_SERIE = bd_serie.ID_SERIE
       	LEFT JOIN bd_tome_simil ON bd_tome.ID_TOME = bd_tome_simil.ID_TOME
        WHERE NOT(bd_tome.NBR_USER_ID=0)
                AND bd_serie.ID_GENRE NOT IN (" . implode(',', $a_idGenreExclu) . ")
                AND (bd_tome_simil.TSMP_TOME_SIMIL IS NULL
        OR  bd_tome_simil.TSMP_TOME_SIMIL <  " . (time() - 604800) . ") 
        ORDER BY bd_tome.ID_TOME DESC LIMIT 0,1 ";
        // echo_pre($query);
        $resultat = Db_query($query);
        
        $a_tome = array();
        
        while ($tome = Db_fetch_object($resultat)) {
            $this->loadModel("Tome_simil");
            $a_tome[$tome->ID_TOME] = json_encode($this->Tome_simil->load($tome));
            $id_tome = $tome->ID_TOME;
        }
        
        $this->view->set_var(array(
                'continuer' => $continuer,
                'id_tome' => $id_tome,
                'a_tome' => $a_tome
        ));
        
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    /*
     * UPDATE bd_tome LEFT JOIN ( SELECT
     * bd_edition.ID_TOME,COUNT(DISTINCT(users_album.user_id)) as nbr FROM
     * bd_edition INNER JOIN users_album ON users_album.ID_EDITION =
     * bd_edition.ID_EDITION WHERE bd_edition.ID_TOME between 60000 AND 70000
     * GROUP BY bd_edition.ID_TOME) e ON e.ID_TOME=bd_tome.ID_TOME SET
     * bd_tome.NBR_USER_ID = IFNULL(e.nbr,0) WHERE bd_tome.ID_TOME between 60000
     * AND 70000
     */
    public function Nbuserbytome ()
    {
        $nbruser_ID_TOME = getValInteger('nbruser_ID_TOME', 0) + 0;
        $continuer = getVal('continuer', '');
        
        $nbruser_ID_TOME_next = $nbruser_ID_TOME - 500;
        
        if ($nbruser_ID_TOME_next < 0) {
            $nbruser_ID_TOME_next = 0;
            $continuer = 'non';
        }
        
        $requete = "UPDATE bd_edition_stat LEFT JOIN (
        SELECT bd_edition.ID_TOME,COUNT(DISTINCT(users_album.user_id)) as nbr FROM bd_edition
               INNER JOIN users_album ON users_album.ID_EDITION = bd_edition.ID_EDITION
               WHERE users_album.flg_achat = 'N' 
               AND bd_edition.ID_TOME BETWEEN " . $nbruser_ID_TOME_next . " AND " . ($nbruser_ID_TOME - 1) . "
               GROUP BY bd_edition.ID_TOME) e ON e.ID_TOME=bd_edition_stat.ID_TOME
       SET bd_edition_stat.NBR_USER_ID_TOME = IFNULL(e.nbr,0)
       WHERE bd_edition_stat.ID_TOME BETWEEN " . $nbruser_ID_TOME_next . " AND " . ($nbruser_ID_TOME - 1) . "
       ";
        $resultat = Db_query($requete);
        
        $this->view->set_var(array(
                'continuer' => $continuer,
                'nbuser' => Db_affected_rows(),
                'nbruser_ID_TOME' => $nbruser_ID_TOME_next
        ));
        
        if (isset($_GET['noRender'])) {
            $boucle = getValInteger('boucle', 1);
            
            // retour sur cron
            if ($continuer != 'non') {
                header("location:" . BDO_URL . "cache/nbuserbytome?noRender&boucle=" . ($boucle + 1) . "&nbruser_ID_TOME=" . $nbruser_ID_TOME_next);
            }
            echo "\nDernier ID_TOME => " . $nbruser_ID_TOME_next;
            echo "\nBoucles => " . ($boucle + 1);
            echo "\nFin boucles => " . date('d/m/Y H:i:s');
            
            Bdo_Cfg::quit();
        }
        else {
            $this->view->layout = "ajax";
            $this->view->render();
        }
    }

    public function Nbuserbyedition ()
    {
        $nbruser_ID_EDITION = getVal('nbruser_ID_EDITION', '') + 0;
        $continuer = getVal('continuer', '');
        
        $nbruser_ID_EDITION_next = $nbruser_ID_EDITION - 1000;
        
        if ($nbruser_ID_EDITION_next < 0) {
            $nbruser_ID_EDITION_next = 0;
            $continuer = 'non';
        }
        
        $requete = "
UPDATE bd_edition_stat LEFT JOIN
(SELECT ID_EDITION,COUNT(USER_ID)as nbr FROM users_album
WHERE users_album.flg_achat = 'N' 
AND users_album.ID_EDITION BETWEEN " . $nbruser_ID_EDITION_next . " AND " . ($nbruser_ID_EDITION - 1) . "
GROUP BY ID_EDITION) nbruser
ON nbruser.ID_EDITION=bd_edition_stat.ID_EDITION
SET bd_edition_stat.`NBR_USER_ID_EDITION`=IFNULL(nbruser.nbr,0)
WHERE bd_edition_stat.ID_EDITION BETWEEN " . $nbruser_ID_EDITION_next . " AND " . ($nbruser_ID_EDITION - 1) . ";
";
        $resultat = Db_query($requete);
        
        $this->view->set_var(array(
                'continuer' => $continuer,
                'nbuser' => Db_affected_rows(),
                'nbruser_ID_EDITION' => $nbruser_ID_EDITION_next
        ));
        
        
        
        if (isset($_GET['noRender'])) {
            $boucle = getValInteger('boucle', 1);
        
            // retour sur cron
            if ($continuer != 'non') {
                header("location:" . BDO_URL . "cache/nbuserbyedition?noRender&boucle=" . ($boucle + 1) . "&nbruser_ID_EDITION=" . $nbruser_ID_EDITION_next);
            }
            echo "\nDernier ID_EDITION => " . $nbruser_ID_EDITION_next;
            echo "\nBoucles => " . ($boucle + 1);
            echo "\nFin boucles => " . date('d/m/Y H:i:s');
        
            Bdo_Cfg::quit();
        }
        else {
        $this->view->layout = "ajax";
        $this->view->render();
        }
    }

    public function Nbuserbyserie ()
    {
        $nbruser_ID_SERIE = getValInteger('nbruser_ID_SERIE', 0) + 0;
        $continuer = getVal('continuer', '');
        
        $nbruser_ID_SERIE_next = $nbruser_ID_SERIE - 100;
        
        if ($nbruser_ID_SERIE_next < 0) {
            $nbruser_ID_SERIE_next = 0;
            $continuer = 'non';
        }
        
        $requete = "UPDATE bd_edition_stat LEFT JOIN (
        SELECT bd_tome.ID_SERIE,COUNT(DISTINCT(users_album.user_id)) as nbr
        FROM bd_tome
               INNER JOIN bd_edition ON bd_edition.ID_TOME = bd_tome.ID_TOME
               INNER JOIN users_album ON users_album.ID_EDITION = bd_edition.ID_EDITION
               WHERE users_album.flg_achat = 'N' 
               AND bd_tome.ID_SERIE BETWEEN " . $nbruser_ID_SERIE_next . " AND " . ($nbruser_ID_SERIE - 1) . "
               GROUP BY bd_tome.ID_SERIE) nbruser ON nbruser.ID_SERIE=bd_edition_stat.ID_SERIE
       SET bd_edition_stat.NBR_USER_ID_SERIE = IFNULL(nbruser.nbr,0)
       WHERE bd_edition_stat.ID_SERIE BETWEEN " . $nbruser_ID_SERIE_next . " AND " . ($nbruser_ID_SERIE - 1) . "
       ";
        $resultat = Db_query($requete);
        
        $this->view->set_var(array(
                'continuer' => $continuer,
                'nbuser' => Db_affected_rows(),
                'nbruser_ID_SERIE' => $nbruser_ID_SERIE_next
        ));
        
        if (isset($_GET['noRender'])) {
            $boucle = getValInteger('boucle', 1);
        
            // retour sur cron
            if ($continuer != 'non') {
                header("location:" . BDO_URL . "cache/nbuserbyserie?noRender&boucle=" . ($boucle + 1) . "&nbruser_ID_SERIE=" . $nbruser_ID_SERIE_next);
            }
            echo "\nDernier ID_SERIE => " . $nbruser_ID_SERIE_next;
            echo "\nBoucles => " . ($boucle + 1);
            echo "\nFin boucles => " . date('d/m/Y H:i:s');
        
            Bdo_Cfg::quit();
        }
        else {
        $this->view->layout = "ajax";
        $this->view->render();
        }
    }
}

