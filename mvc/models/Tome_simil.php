<?php

/**
 *
 * @author laurent
 *
 */
class Tome_simil
{

    public function load ($tome)
    {
        $a_idGenreExclu = array(80,82,83); // Publicité,Revue de prépublication,Périodique


        // recherche des editions du tome
            /*
        $requete = "SELECT bd_edition.ID_EDITION FROM bd_edition
                WHERE bd_edition.ID_TOME=" . $tome->ID_TOME;
        $resultat = Db_query($requete);
        $a_idEdition = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_idEdition[] = $obj->ID_EDITION;
        }
        */


            // recherche des editions de la serie
        $requete = "SELECT bd_edition.ID_EDITION FROM bd_edition
                INNER JOIN bd_tome USING(ID_TOME) WHERE ID_SERIE=" . $tome->ID_SERIE;
        $resultat = Db_query($requete);
        $a_idEdition = array();
        while ($obj = Db_fetch_object($resultat)) {
            $a_idEdition[] = $obj->ID_EDITION;
        }

        // recherche d'une ligne de edition stat
        $requete = "SELECT NBR_USER_ID_SERIE FROM bd_edition_stat WHERE ID_TOME = " . $tome->ID_TOME . " LIMIT 0,1";
        $resultat = Db_query($requete);
        if ($obj = Db_fetch_object($resultat)) {
            $tome->NBR_USER_ID_SERIE = $obj->NBR_USER_ID_SERIE;
        }

        $a_idTomeSimil = array();


        if ($a_idEdition AND !in_array($tome->ID_GENRE, $a_idGenreExclu)) {
            $requete = "
SELECT
    MIN(bd_edition_stat.ID_TOME) as ID_TOME,
    (count(DISTINCT(users_album.user_id)) / (".$tome->NBR_USER_ID_SERIE." + MAX(bd_edition_stat.NBR_USER_ID_SERIE) - count(DISTINCT(users_album.user_id)))) as score
FROM
users_album INNER JOIN (
    SELECT DISTINCT user_id
    FROM users_album
    WHERE id_edition IN (" . implode(',', $a_idEdition) . ")
    ) usersb USING (user_id)
INNER JOIN bd_edition_stat ON bd_edition_stat.ID_EDITION=users_album.ID_EDITION
INNER JOIN bd_tome on bd_edition_stat.id_tome = bd_tome.id_tome
WHERE NOT(bd_edition_stat.ID_SERIE = ".$tome->ID_SERIE.")
AND NOT(bd_edition_stat.NBR_USER_ID_SERIE = 0)
AND bd_edition_stat.ID_GENRE NOT IN (" . implode(',', $a_idGenreExclu) . ")
    AND bd_tome.flg_type = 0 and bd_tome.flg_int = 'N' 
GROUP BY bd_edition_stat.ID_SERIE
ORDER BY score DESC
LIMIT 0,5
";
            $resultat = Db_query($requete);
            if (0 == Db_CountRow($resultat)) {
                $requete = "
                SELECT MIN(bd_edition_stat.ID_TOME), 0 as score
                FROM bd_edition_stat
                WHERE bd_edition_stat.ID_GENRE = " . $tome->ID_GENRE . "
                AND NOT(bd_edition_stat.ID_SERIE = ".$tome->ID_SERIE.")
                AND NOT(bd_edition_stat.NBR_USER_ID_SERIE = 0)
                GROUP BY bd_edition_stat.ID_SERIE
                ORDER BY bd_edition_stat.NBR_USER_ID_SERIE DESC LIMIT 0,5";
                // les plus rependus dans le genre
                $resultat = Db_query($requete);
            }
                  //echo_pre($requete);


            while ($obj = Db_fetch_object($resultat)) {

                // mise a jour apres recherche ...meme vide
                Db_query(
                        "INSERT INTO bd_tome_simil (ID_TOME, ID_TOME_SIMIL, SCORE_TOME_SIMIL)
                    VALUES (" . $tome->ID_TOME . ",'" . $obj->ID_TOME . "','" . $obj->score . "')
                    ON DUPLICATE KEY UPDATE
                    SCORE_TOME_SIMIL='" . $obj->score . "',
                    TSMP_TOME_SIMIL=NOW()");

                $a_idTomeSimil[] = $obj->ID_TOME;
            }

            Db_query("DELETE FROM bd_tome_simil WHERE ID_TOME=" . $tome->ID_TOME . "
                    AND ID_TOME_SIMIL NOT IN
                    ( SELECT ID_TOME_SIMIL FROM bd_tome_simil WHERE ID_TOME=" . $tome->ID_TOME . "
                            ORDER BY score DESC LIMIT 0,5");
        }
        return $a_idTomeSimil;
    }
}
