<?php


include (BDO_DIR."vendors/jpgraph-3.5.0b1/src/jpgraph.php");
include (BDO_DIR."vendors/jpgraph-3.5.0b1/src/jpgraph_bar.php");

// Emplacement des fonctions
function cv_date_req($date) {
	$mois = month_to_text((int)substr($date,4,2));
	$annee = substr($date,0,4);
	return $mois." ".$annee;
}

// Variable d�finissant l'utilisateur
if ($user != '') {
	$user_id = decodeUserId($user);
}
else{
	$user_id = $_SESSION["userConnect"]->user_id;
}

if (empty($user_id)) exit();

$query = "
SELECT
	DATE_FORMAT(IFNULL(u.date_achat,u.date_ajout), '%Y%m') as my_date,
	count(en.id_edition) as nbtome
FROM
	users_album u
	INNER JOIN bd_edition en ON en.id_edition = u.id_edition
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome
WHERE
	t.flg_type = 0
	AND u.flg_achat = 'N'
	AND u.user_id = ".$DB->escape($user_id)."
GROUP BY DATE_FORMAT(IFNULL(date_achat,date_ajout), '%Y%m')
ORDER BY DATE_FORMAT(IFNULL(date_achat,date_ajout), '%Y%m') DESC limit 0,12;";
$DB->query ($query);

// défini les variables
$nb_records = $DB->num_rows();
$other = 0;
$sum = 0;
$i=0;

// r�cup�re les 10 premiers �l�ments
while ($DB->next_record())
{
	$datay[$i] = $DB->f("nbtome");
	$lbl[$i] = cv_date_req($DB->f("my_date"));
	$i++;
}


// Size of graph
$width=600;
$height=250;

// Set the basic parameters of the graph
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");

$top = 30;
$bottom = 30;
$left = 150;
$right = 30;
$graph->Set90AndMargin($left,$right,$top,$bottom);

// Set some other color then the boring default
$graph->SetColor("beige");
$graph->SetMarginColor("wheat2");

// Setup labels
$graph->xaxis->SetTickLabels($lbl);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('right','center','right');

// Label align for Y-axis
$graph->yaxis->scale->SetGrace(5);
$graph->yaxis->HideLabels();
$graph->yaxis->HideTicks();

// Titles
$graph->title->Set('Achats annuels');

// Create a bar pot
$bplot = new BarPlot($datay);
$graph->Add($bplot);
// Setup color for gradient fill style
$bplot->SetFillGradient("brown","beige",GRAD_MIDVER);
$bplot->SetWidth(0.6);
$bplot->value->Show();
$bplot->value->SetColor('#000000');
$bplot->value->SetFormat('%d');


$graph->Stroke();
