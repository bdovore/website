<?php


function unhtmlspecialchars( $string ){
	$string = str_replace ( '&amp;', '&', $string );
	$string = str_replace ( '&#039;', '\'', $string );
	$string = str_replace ( '&quot;', '"', $string );
	$string = str_replace ( '&lt;', '<', $string );
	$string = str_replace ( '&gt;', '>', $string );
	return $string;
}




// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpAccueil" => "accueil.tpl",
"tpNews" => "news.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBody" => "body.tpl"
));







// ajout des news
// on déclare le block à utiliser
$t->set_block('tpNews','NewsBlock','NBlock');

// Ajout de 2 autres news bdovore
$query = "SELECT * FROM news WHERE news_level>=5  ORDER BY News_id DESC LIMIT 0, 10";
$DB->query ($query);
if ($DB->nf() != 0){
	while ($DB->next_record()){
		$titre = $DB->f ("news_titre");
		$auteur = $DB->f ("news_posteur");
		$newsdate = $DB->f ("news_date");
		$newscontent = $DB->f ("news_text");
		$t->set_var (array(
		"TITRE" => $titre,
		"AUTEUR" => $auteur,
		"DATE" => $newsdate,
		//		"NEWSCONTENT"=> regextexte($newscontent),
		"NEWSCONTENT"=> $newscontent,
		"ACTION"=> ""
		));
		$t->parse ("NBlock", "NewsBlock",true);
	}
}
else {
	$t->set_var (array("NEWSITEMS" => "aucune news à afficher"));
}

// insertion des 5 derniers commentaires
// on déclare le block à utiliser
$t->set_block('tpAccueil','ComBlock','CMBlock');
$select = "
select 
	t.titre, 
	t.id_tome id,  
	max(c.dte_post) dte, 
	en.img_couv
from 
	bd_tome t INNER JOIN bd_edition en ON en.id_edition = t.id_edition, 
	users_comment c
where 
	t.id_tome = c.id_tome 
	and c.comment <> ''
group by titre, t.id_tome
order by dte desc limit 0,5
";
$DB->query($select);
$nb = 1;
while ($DB->next_record()) {
	$url = getAlbumUrl($DB->f("id"));
	if ($nb == 1) {
		$t->set_var(array(
		"URLALLASTCMT1" => $url,
		"TITLASTCMT1" => $DB->f("titre"),
		"IMGLASTCMT1" => BDO_URL_IMAGE."couv/".$DB->f("img_couv")
		));
	}
	else {
		$t->set_var(array(
		"CMTLAST" => stripslashes($DB->f("titre")),
		"URLLASTCMT" => $url,
		"NUMERO" => $nb
		));
		$t->parse("CMBlock","ComBlock",true);
	}
	$nb ++;
}

// insertion des 5 dernieres sorties
// on déclare le block à utiliser
$t->set_block('tpAccueil','LastSortieBlock','LSBlock');
$select ="select
			t.titre, 
			t.id_tome,
			en.img_couv , 
			en.dte_parution
		from 
			bd_tome t
			INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
		where 
			en.dte_parution <= CURDATE()
		order by en.dte_parution desc
		limit 0,5";
$DB->query($select);
$nb = 1;
while ($DB->next_record()) {
	$url = getAlbumUrl($DB->f("id_tome"));
	if ($nb == 1) {
		$t->set_var(array(
		"URLALLAST1" => $url,
		"TITLAST1" => $DB->f("titre"),
		"IMGLAST1" => BDO_URL_IMAGE."couv/".$DB->f("img_couv")
		));
	}
	else {
		$t->set_var(array(
		"URLLASTITEM" => $url,
		"TITLASTITEM" => $DB->f("titre"),
		"NUMERO" => $nb
		));
		$t->parse("LSBlock","LastSortieBlock",true);
	}
	$nb ++;
}

// insertion des 5 prochaines sorties
// on déclare le block à utiliser
$t->set_block('tpAccueil','FuturSortieBlock','FSBlock');
$select ="select
			t.titre, 
			t.id_tome,
			en.img_couv , 
			en.dte_parution
		from 
			bd_tome t
			INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
		where 
			en.dte_parution > CURDATE()
		order by en.dte_parution 
		limit 0,5";
$DB->query($select);
$nb = 1;
while ($DB->next_record()) {
	$url = getAlbumUrl($DB->f("id_tome"));
	if ($nb == 1) {
		$t->set_var(array(
		"URLALFS1" => $url,
		"TITFS1" => $DB->f("titre"),
		"IMGFS1" => BDO_URL_IMAGE."couv/".$DB->f("img_couv")
		));
	}
	else {
		$t->set_var(array(
		"URLFSITEM" => $url,
		"TITFSITEM" => $DB->f("titre"),
		"NUMERO" => $nb
		));
		$t->parse("FSBlock","FuturSortieBlock",true);
	}
	$nb ++;
}


$t->set_var("LOGINBARRE",GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("PAGETITLE","BDOVORE.com, gestion de collection de BD en ligne, actualité BD et forum BD");
$t->set_var("PAGEKEYWORD","bdvore");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->parse("LASTNEWS","tpNews");
$t->parse("BODY","tpAccueil");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBody");
