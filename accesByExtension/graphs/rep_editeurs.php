<?php


include (BDO_DIR."vendors/jpgraph-3.5.0b1/src/jpgraph.php");
include (BDO_DIR."vendors/jpgraph-3.5.0b1/src/jpgraph_bar.php");

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
	er.nom, 
	count(en.id_edition) as nbtome 
FROM 
	users_album u 
	INNER JOIN bd_edition en ON en.id_edition = u.id_edition 
	INNER JOIN bd_editeur er ON er.id_editeur = en.id_editeur 
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome 
WHERE 
	t.flg_type = 0 
	AND u.flg_achat = 'N' 
	AND u.user_id =" . $user_id . "
GROUP BY er.nom 
ORDER BY nbtome DESC
";
$DB->query ($query);

// d�fini les variables
$nb_records = $DB->num_rows();
$other = 0;
$sum = 0;
$i=0;

// V�rifie le nombre de r�ponses renvoy�es
if ($nb_records <= 10)
{
	while ($DB->next_record())
	{
		$datay[$i] = $DB->f("nbtome");
		$lbl[$i] = $DB->f("nom");
		$i++;
	}
}else{
	// r�cup�re les 9 premiers �l�ments
	for ($compteur = 1;$compteur <= 9; $compteur++)
	{
		$DB->next_record();
		$datay[$i] = $DB->f("nbtome");
		$lbl[$i] = $DB->f("nom");
		$i++;
	}
	// Compl�te avec les derniers
	while ($DB->next_record())
	{
		$other += $DB->f("nbtome");
	}
	$datay[$i] = $other;
	$lbl[$i] = "Autre";
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
$graph->title->Set('Collection par Editeurs');

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
