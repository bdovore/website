<?php

$GLOBALS['CEcountQuery'] = 0;
$GLOBALS['CEtabQuery'] = array();
$GLOBALS['CEtabQueryOcc'] = array();

/**
* affichage bilan des requetes executees dans la page
*
* @return true
*/

function Ce_bilanQuery()
{
    echo '
    <style>
    div.cephenix1 {color:#EEEEEE ; width:700 ; margin:2px ; padding:5px ; font-family:Verdana ; font-size:12px ; font-weight:bold ; background-color:#880000 ; text-align:center}
    div.cephenix2 {color:#000000 ; width:700 ; margin:2px ; padding:2px ; font-family:Verdana ; font-size:11px ; border: 1px solid #880000 ; background-color:#EEEEEE ; text-align:left}
    div.cephenix3 {color:#FFFFFF ; width:700 ; margin:2px ; padding:2px ; font-family:Verdana ; font-size:12px ; font-weight:bold ; border: 1px solid #880000 ; background-color:#DD3C00 ; text-align:left}
    pre.cephenix {color:#000000 ; font-family:Verdana ; font-size:10px ; text-align:left}
    </style>
    <div class=cephenix1>
    '.$GLOBALS['CEcountQuery'].' requetes executees pour cette page
     <br />'.count($GLOBALS['CEtabQueryOcc']).' requetes uniques executees pour cette page
    </div>
    ';

    foreach ($GLOBALS['CEtabQuery'] as $idQ=>$a_query)
    {
        echo '<div class=cephenix2><pre class=cephenix>'.$a_query['query'].'</pre>
                <i style="background-color:#DDDDDD">'.$a_query['numrows'].' lignes impactees
                <br />executee en '.$a_query['exectime'].' secondes</i>';
        if ($GLOBALS['CEtabQueryOcc'][$idQ] > 1)
        {
            echo '<div class=cephenix3>Requete executee '.$GLOBALS['CEtabQueryOcc'][$idQ].' fois</div>';
        }
        echo '</div>';
    }

    return true;
}


function getMicrotime()
{
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function execTime($msg=FALSE)
{
    global $tab_exec_time;
    $tab_exec_time[] = time();
    echo "<br />Flag Temp n°".count($tab_exec_time)."<br />";
    if ($msg) echo $msg."<br />";
}

function affExecTime()
{
    execTime();
    global $tab_exec_time;
    $count_exec_time = count($tab_exec_time);
    echo "<HR><U>Calcul des intervales de temps d'éxecution</U>";
    for ($i=1;$i<$count_exec_time;$i++)
    {
        echo "<br />Flag Temp n°".($i+1)." - Flag Temp n°".$i." = ".($tab_exec_time[$i] - $tab_exec_time[$i-1]);
    }
    echo "<HR>Temp total d'éxecution = ".($tab_exec_time[($count_exec_time-1)] - $tab_exec_time[0])."";

}