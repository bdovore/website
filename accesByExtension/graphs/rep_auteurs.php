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
t.id_tome,
t.id_dessin,
t.id_scenar,
s.pseudo as ps_scen,
d.pseudo as ps_dess
FROM
users_album u
INNER JOIN bd_edition en ON en.id_edition = u.id_edition
INNER JOIN bd_tome t ON t.id_tome = en.id_tome
INNER JOIN bd_auteur d ON t.id_dessin = d.id_auteur
INNER JOIN bd_auteur s ON t.id_scenar = s.id_auteur
WHERE
flg_achat = 'N' AND
u.user_id =".$DB->escape($user_id);
$DB->query ($query);

// d�fini les variables
$num_rows = $DB->num_rows();
$other = 0;
$sum = 0;
$i=0;

// Stocke le r�sultat dans un tableau
// empty array
$aut_fav = array();
$list_aut = array();

while ($DB->next_record())
{
	// Stocke l'auteur
	$list_aut[$DB->f('id_scenar')] = $DB->f('ps_scen');
	$list_aut[$DB->f('id_dessin')] = $DB->f('ps_dess');

	// stocke le scenar
	$aut_fav[$DB->f('id_scenar')]++;

	//traite le dessinateur
	if ($DB->f('id_scenar')== $DB->f('id_dessin'))
	{
		$aut_fav[$DB->f('id_scenar')] = $aut_fav[$DB->f('id_scenar')]+0.5;
	}
	else{
		$aut_fav[$DB->f('id_dessin')]++;
	}
}

arsort($aut_fav);
$compteur = 0;
while ((list ($cle, $val) = each ($aut_fav)) & $compteur <10)
{
	if ($cle >= 4)
	{
		$datay[$compteur] = $val;
		$lbl[$compteur] = $list_aut[$cle];
		$compteur++;
	}
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
$graph->title->Set('Auteurs préférés (Nb de points)');

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
