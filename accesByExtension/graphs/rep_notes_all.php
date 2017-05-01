<?php


require_once (BDO_DIR."vendor/jpgraph/jpgraph/lib/jpgraph/src/jpgraph.php");
require_once  (BDO_DIR."vendor/jpgraph/jpgraph/lib/jpgraph/src/jpgraph_bar.php");

// Notes Albums
$query = "
SELECT note, count(*) as nbnotes FROM users_comment
WHERE note IS NOT NULL
GROUP BY note
ORDER BY note
";
$DB->query ($query);
$DB->next_record();

// Notes Series
$DB2 = new DB_Sql;
$query = "
SELECT note, count(*) as nbnotes FROM serie_comment
WHERE note IS NOT NULL
GROUP BY note
ORDER BY note
";
$DB2->query ($query);
$DB2->next_record();

// Traite les donn�es
for ($i = 0; $i <= 10; $i++)
{
    if ($DB->f('note') == $i)
    {
        $datay[$i] = $DB->f('nbnotes');
        $lbl[$i] = $i;
        $DB->next_record();
    }else{
        $datay[$i] = 0;
        $lbl[$i] = $i;
    }
    if ($DB2->f('note') == $i)
    {
        $datay2[$i] = $DB2->f('nbnotes');
        $DB2->next_record();
    }else{
        $datay2[$i] = 0;
    }
}

// Size of graph
$width=600;
$height=350;

// Set the basic parameters of the graph
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");

$top = 30;
$bottom = 30;
$left = 50;
$right = 30;

// Set some other color then the boring default
$graph->SetColor("beige");
$graph->SetMarginColor("wheat2");

// Setup labels
$graph->xaxis->SetTickLabels($lbl);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('center','top','right');

// Label align for Y-axis
$graph->yaxis->scale->SetGrace(10);

// Titles
$graph->title->Set('R�partition des notes');

// Create two new bar plots
$bplot = new BarPlot($datay);
$b2plot = new BarPlot($datay2);
// Create the grouped bar plot
$abplot = new AccBarPlot(array($bplot,$b2plot));

$graph->Add($abplot);
// Setup color for gradient fill style - albums
$bplot->SetFillGradient("brown","beige",GRAD_MIDVER);
$bplot->SetWidth(1);
$bplot->value->Show();
$bplot->value->SetFormat('%d');
$bplot->value->SetColor('#000000');
$bplot->SetLegend("Albums");

// Setup color for gradient fill style - Serie
$b2plot->SetFillGradient("lightskyblue4","beige",GRAD_MIDVER);
$b2plot->SetWidth(1);
$b2plot->value->Show();
$b2plot->value->SetFormat('%d');
$b2plot->value->SetColor('#000000');
$b2plot->SetLegend("Séries");



$graph->Stroke();
