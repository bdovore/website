<?php


require_once (BDO_DIR."vendor/jpgraph-4.4.2/src/jpgraph.php");
require_once  (BDO_DIR."vendor/jpgraph-4.4.2/src/jpgraph_bar.php");

minAccessLevel(2);

// Variables g�n�rales
$nb = 20;

$first = isset($_GET["fisrt"]) ? $_GET["fisrt"] : 0 ;
$info = isset($_GET["info"]) ? $_GET["info"] : 0 ;

// Tableau label
$short_month = array("Jan","Fev","Mar","Avr","Mai","Jui","Jui","Aou","Sep","Oct","Nov","Dec");


// R�cup�re les valeurs par d�faut
$query = "SELECT val_alb, val_cof, val_int, val_cof_type FROM users WHERE user_id =" . $DB->escape( $_SESSION["userConnect"]->user_id);
$DB->query ($query);
$DB->next_record();
$defval[0] = $DB->f("val_alb");
$defval[1] = $DB->f("val_int");
$defval[2] = $DB->f("val_cof");
$defcoffret = $DB->f("val_cof_type");

// initialise mini et maxi
$min_date = 9999;
$max_date = 0;

// R�cup�re la collection
$query = "
SELECT
    DATE_FORMAT(IFNULL(u.date_achat, u.date_ajout),'%m') as date_mois,
    t.id_tome,
    t.titre,
    t.num_tome,
    t.flg_type,
    t.flg_int,
    t.prix_bdnet,
    u.cote,
    u.flg_cadeau
FROM
    users_album u
    INNER JOIN bd_edition en ON en.id_edition = u.id_edition
    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
WHERE
    u.flg_achat = 'N'
    AND u.user_id = " . $DB->escape( $_SESSION["userConnect"]->user_id) . "
AND DATE_FORMAT(IFNULL(u.date_achat, u.date_ajout),'%Y') ='".$DB->escape($annee)."'";

$DB->query ($query);
$tot_prix = array( 0 => 0,
                               1 => 0,
                                2 => 0 );
 $tot_count = array(0 => 0,
                               1 => 0,
                                2 => 0 );
 $depense = array();
 $nbalbums = array();

while ($DB->next_record())
{
    if ($DB->f("flg_int") == 'O')
    $type = 1;
    else
    $type = 0;
    if ($DB->f("flg_type") == 1) $type = 2;

    // V�rifie si l'album est cot� par l'utilisateur
    if (($DB->f("cote") != '') & (($DB->f("flg_type") == 0) | ($defcoffret == 1)))
    {
        $tot_prix[$type] += $DB->f("cote");
        $tot_count[$type]++;
        $prix_retenu = $DB->f("cote");
    }
    // Verifie si l'album est not� par bdovore
    elseif (($DB->f("prix_bdnet") != '') & ($DB->f("flg_type") == 0 | ($defcoffret == 1)))
    {
        $tot_prix[$type] += $DB->f("prix_bdnet");
        $tot_count[$type]++;
        $prix_retenu = $DB->f("prix_bdnet");
    }
    // Non valoris�
    elseif (($DB->f("flg_type") == 0) | ($defcoffret == 1))
    {
        if ($defval[$type] == '')
        {
            $tot_count[$type]++;
            $prix_retenu = 0;
        }else{
            $tot_prix[$type] += $defval[$type];
            $tot_count[$type]++;
            $prix_retenu = $defval[$type];
        }
    }
    // Coffret valoris� album par album
    elseif (($DB->f("flg_type") == 1) & ($defcoffret == 0))
    {
        $tot_prix[$type] += $defval[2];
        $tot_count[$type]++;
        $prix_retenu = $defval[2];
    }

    // stocke les mini et les maxi
    $mois = intval($DB->f("date_mois"));

    // Pr�pare le graph
    $depense[$mois] = isset($depense[$mois]) ? $depense[$mois] + $prix_retenu : $prix_retenu;
    // set year
    if (!isset($cadeau[$mois])) {
        $cadeau[$mois] = 0;
    }
     if (!isset($nbalbums[$mois])) {
        $nbalbums[$mois] = 0;
    }  
    // Compte si cadeau
    if ($DB->f("flg_cadeau") == 'O')
    $cadeau[$mois]++;
    else
    $nbalbums[$mois]++;
}


// Rempli les valeurs du graph
$i=0;
for ($compteur = 1;$compteur <= 12; $compteur++)
{
    if ($info == 0)
    {
        $datay[$i] = $depense[$compteur];
    }else{
       $datay[$i] = isset($nbalbums[$compteur]) ? $nbalbums[$compteur] : 0 ;
        $datay2[$i] = isset($cadeau[$compteur]) ? $cadeau[$compteur] : 0 ;
    }
    $lbl[$i] = $short_month[$compteur-1];
    //echo $lbl[$i].":".$datay[$i]." - ".$datay2[$i]."<br>";
    $i++;
}

// Size of graph
$width=550;
$height=200;

// Set the basic parameters of the graph
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");

$top = 35;
$bottom = 30;
$left = 35;
$right = 10;
$graph->SetMargin($left,$right,$top,$bottom);

// Set some other color then the boring default
$graph->SetColor("mintcream");
$graph->SetMarginColor("#FAF0F0");
$graph->SetFrameBevel(0,false);

// Setup labels
$graph->xaxis->SetTickLabels($lbl);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('center','top','right');

// Label align for Y-axis
$graph->yaxis->scale->SetGrace(10);

// Titles
//$graph->title->SetFont(FF_VERDANA, FS_NORMAL,10);
$graph->title->Set('Répartition Mensuelle');

// Legend
$graph->legend->Pos(0.010,0.010);

if ($info == 0)
{
    // Create new bar plots
    $bplot = new BarPlot($datay);
    // Create the grouped bar plot
    $graph->Add($bplot);

    // Setup color for gradient fill style - albums
    $bplot->SetFillGradient("brown","beige",GRAD_MIDVER);
    $bplot->SetWidth(.5);
    $bplot->value->Show();
    $bplot->value->SetColor('#000000');
    $bplot->value->SetFormat('%d');


}else{
    // Create two new bar plots
    $bplot = new BarPlot($datay);
    // Create new bar plots
    $bplot2 = new BarPlot($datay2);
    // Create the grouped bar plot
    $gbplot = new GroupBarPlot(array($bplot,$bplot2));
    // add group to graph
    $graph->Add($gbplot);
    // Setup color for gradient fill style - albums
    $bplot->SetFillGradient("brown","beige",GRAD_MIDVER);
    $bplot->SetWidth(.5);
    $bplot->value->Show();
    $bplot->value->SetColor('#000000');
    $bplot->SetValuePos('top');
    $bplot->value->SetFormat('%d');
    $bplot->SetLegend("Achats");

    // Setup color for gradient fill style - albums
    $bplot2->SetFillGradient("lightskyblue4","beige",GRAD_MIDVER);
    $bplot2->SetWidth(.5);
    $bplot2->value->Show();
    $bplot2->value->SetColor('#000000');
    $bplot2->value->SetFormat('%d');
    $bplot2->SetLegend("Cadeau");

}

$graph->Stroke();
