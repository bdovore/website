<?php





// check le num�ro de page � afficher
if ($first == "")
{
	$first=0;
}

if ($nb == "")
{
	$nb=4;
}

//D�termine le nombre de news � afficher

$DB->query ("SELECT COUNT(*) as nbtotal FROM news WHERE news_level>=".$DB->escape($_SESSION["UserLevel"]));
$DB->next_record();
$nbtotal = $DB->f ("nbtotal");

// Creation d'une nouvelle instance Fast Template
$t = new Template(BDO_DIR."public/templates");
// fichier � utiliser
$t->set_file(array(
"tpBody" => "rub_news.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"));

// on d�clare le block � utiliser
$t->set_block('tpBody','NewsBlock','NBlock');

$query = "SELECT * FROM news WHERE news_level>=" . $DB->escape($_SESSION["UserLevel"]) . " ORDER BY News_id DESC LIMIT ".$DB->escape($first).", ".$DB->escape($nb);
$DB->query ($query);
if ($DB->nf() != 0)
{
	while ($DB->next_record())
	{
		$titre = $DB->f ("news_titre");
		$auteur = $DB->f ("news_posteur");
		$newsdate = $DB->f ("news_date");
		$newscontent = $DB->f ("news_text");

		$t->set_var (array
		("TITRE" => $titre,
		"AUTEUR" => $auteur,
		"DATE" => $newsdate,
		"NEWSCONTENT"=> regextexte($newscontent),
		"ACTION"=> ""));

		$t->parse ("NBlock", "NewsBlock",true);
	}
}else
{
	$t->set_var (array
	("NEWSITEMS" => "aucune news � afficher"));
}

// assigne la barre le navigation NEWS
$t->set_var (array
("BARRENEWS" => GetNavigationBar($first,$nb,$nbtotal,"./shownews.php")));
$t->set_var("PAGETITLE","Les news de BDovore ");
// assigne la barre de login
$t->set_var (array
("LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
