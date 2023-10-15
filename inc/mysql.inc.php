<?php

// Fonction connexion
function Db_connect ($tab_connect_var)
{
    $connexion = new mysqli($tab_connect_var['server'], $tab_connect_var['login'], $tab_connect_var['password'],$tab_connect_var['sid']);

    // Vérification de la connexion
    if (mysqli_connect_errno()) {

        printf("Échec de la connexion : %s\n", mysqli_connect_error());
        return false;
        //exit();
    }

    return $connexion;
}

// fonction déconnexion
function Db_close ($connexion=false)
{
    if ($connexion === false)
    {
        $connexion = Bdo_Cfg::getVar('connexion');
        Bdo_Cfg::setVar('connexion',null);
    }

    return $connexion->close();
}

// fonction autocommit
function Db_autocommit ($val, $connexion=false)
{
    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    return $connexion->autocommit($val);
}
// fonction commit
function Db_commit ( $connexion=false)
{
    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    return $connexion->commit();
}
// fonction rollback
function Db_rollback ( $connexion=false)
{
    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    return $connexion->rollback();
}

//execution d'une requête
function Db_queryPing ($requete,$connexion=false)
{
    if ($connexion === false)
    {
        $connexion = Bdo_Cfg::getVar('connexion');
        if (!$connexion or !$connexion->ping())
        {
        Bdo_Cfg::setVar('connexion',null);
            Bdo_Cfg::Db_connect();
        }
        $connexion = Bdo_Cfg::getVar('connexion');
    }
    else {
        exit('erreur : Db_queryPing');
    }
    return Db_query($requete);
}

//execution d'une requête
function Db_query ($requete,$connexion=false)
{
    if ($connexion === false)
    {
        $connexion = Bdo_Cfg::getVar('connexion');
    }

    if ($connexion != false)
    {

        if (DEBUG)
        {
            $t1 = microtime(true);
        }

        if (!($resultat = $connexion->query($requete)))
        {
//            if (CFG_RETURN_QUERY_ERROR)
//            {
//                Bdo_Cfg::log('Requete en erreur -> '.$requete);
//                Bdo_Cfg::log("Message d'erreur : %s\n".$connexion->error);
//            }
        }

        if (DEBUG)
        {
            $t2 = microtime(true);
            if ('SELECT' == strtoupper(substr(trim($requete),0,6)))
            {
                $numRow = $resultat->num_rows;
            }
            else
            {
                $numRow = $connexion->affected_rows;
            }

            Bdo_Debug::addQuery(array('query'=>$requete,'numrows'=>$numRow,'exectime'=>($t2-$t1)));

        }

        return $resultat;
    }
    else
    {
        return false;
    }
}


// libération des ressources utilisées par les résultats de requêtes
function Db_free_result($resultat)
{
    if (!empty($resultat)) $resultat->close();
}

function Db_insert_id ($connexion=false)
{
    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    return $connexion->insert_id;
}

function Db_affected_rows ($connexion=false)
{
    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    return $connexion->affected_rows;
}

// Recherche de la ligne suivante
function Db_fetch_object ($resultat)
{
    if ($resultat)
    return $resultat->fetch_object() ;
    else
    return false;
}

// Recherche du tableau suivant
function Db_fetch_array ($resultat,$type_tab=MYSQLI_ASSOC)
{
    if ($resultat)
    return $resultat->fetch_array($type_tab) ;
    else
    return false;
}

function Db_Escape_String ($chaine,$connexion=false)
{
    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    return $connexion->real_escape_string($chaine);
}


// select count * sur requete select
function Db_CountRow ($resul_or_query=false,$connexion=false)
{

    $nbr= 0;

//  if (!is_a($resul_or_query,'mysqli_result',false) php 5.3.9
    if (!($resul_or_query instanceof mysqli_result)
    and stristr($resul_or_query,"select"))
    {
        if ($connexion === false)
        $connexion = Bdo_Cfg::getVar('connexion');

        $debut_query=trim(substr(strtoupper($resul_or_query),0,strpos(strtoupper($resul_or_query),"FROM")));
        if (
        ($debut_query=="SELECT COUNT(*)") or
        ($debut_query=="SELECT COUNT(*) AS NBR") or
        ($debut_query=="SELECT *")
        )
        $query_count = "SELECT COUNT(1) AS NBR ".(stristr($resul_or_query,"FROM"));
        else
        $query_count = "SELECT COUNT(1) AS NBR FROM (".$resul_or_query.") e";
        
        $list_count = $connexion->query ($query_count);
        $obj_count = Db_fetch_object($list_count);
        $nbr = $obj_count ? $obj_count->NBR : 0 ;
        Db_free_result ($list_count);
    }
    else if($resul_or_query)
    {
        $nbr = isset($resul_or_query->num_rows) ? intval($resul_or_query->num_rows) : 0;
    }
    return $nbr;
}


//execution d'une requête
function Db_multi_query ($requete,$connexion=false)
{
    $a_resultat = array();

    if ($connexion === false)
    $connexion = Bdo_Cfg::getVar('connexion');

    if ($connexion != false)
    {

        /* Exécution d'une requête multiple */
        if (mysqli_multi_query($connexion, $requete))
        {
            $i = 1;
            do {

                $result = mysqli_store_result($connexion);
                $a_resultat[] = $result;

                $i++;
            } while (mysqli_next_result($connexion));

        }

        $error = mysqli_errno($connexion);
        if ($error !== 0)
        {
            echo_pre($requete);
            echo "<br />Message d'erreur requete ".$i." : ".mysqli_error($connexion)."\n";
        }


        return $a_resultat;
    }
    else
    {
        return false;
    }
}

function Db_fetch_all_obj ($resultat, $columnKey = '',$multiValByKey=false)
{
    $a_obj = array();
    while ($obj = Db_fetch_object($resultat)) {
        if (empty($columnKey)) $a_obj[] = $obj;
        else{
            if ($multiValByKey){
                $a_obj[$obj->$columnKey][] = $obj;
            }
            else {
                $a_obj[$obj->$columnKey]= $obj;
            }
        }
    }
    Db_free_result($resultat);
    return $a_obj;
}
