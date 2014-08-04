<?php




$page = $_GET["page"];
if ($page == '')
{ $page =0;}

// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpBody" => "bdovore-tuto_".$page.".tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));

if ($page == 0)
{
	//affichage du nb de bd dans les collections
	// --------------------------------------
	// Album
	$select = "select count(*) nb from bd_tome";
	$DB->query($select);
	$DB->next_record();
	$t->set_var("NBALB",$DB->f("nb"));

	$select = "select count(*) nb from bd_serie";
	$DB->query($select);
	$DB->next_record();
	$t->set_var("NBSERIE",$DB->f("nb"));

	// AJOUT & CORRECTION
	$select = "
	select 
		sum(case when PROP_TYPE = 'AJOUT' then 1 else 0 end) nbajout,
		sum(case when PROP_TYPE = 'CORRECTION' then 1 else 0 end) nbcorrect
	from
		users_alb_prop
	where
		status = 0
	";
	$DB->query($select);
	$DB->next_record();
	$t->set_var(array(
	"NBAJOUT" => $DB->f("nbajout"),
	"NBCORRECT" => $DB->f("nbcorrect")
	));

	// EDITION
	$select = "
	select 
	count(*) as nbedition 
	from 
		users_album u 
		INNER JOIN bd_edition en ON en.id_edition = u.id_edition
	where 
	en.prop_status = 0 
	";
	$DB->query($select);
	$DB->next_record();
	$t->set_var(array(
	"NBEDITION" => $DB->f("nbedition")
	));

	// --------------------------------
	// user
	$select = "select (count(*)) nb, sum(nb_connect) visite from users";
	//where level <> 0";
	$DB->query($select);
	$DB->next_record();
	$t->set_var("NBUSER",$DB->f("nb"));
	$t->set_var("NBVISITE",$DB->f("visite"));

	$select = "select count(*) nb from users_album";
	$DB->query($select);
	$DB->next_record();
	$t->set_var("NBCOLLEC",$DB->f("nb"));

	//---------------------------------
	// Site

	$select = "
	select 
		sum(case when note is not null and note > 0 then 1 else 0 end) nbnote,
		sum(case when comment is not null and comment <> '' then 1 else 0 end) nbcomment 
	from
		users_comment
	";
	$DB->query($select);
	$DB->next_record();
	$t->set_var("NBNOTEALBUM",$DB->f("nbnote"));
	$t->set_var("NBCOMMENTALBUM",$DB->f("nbcomment"));

	$select = "
	select
		sum(case when note is not null and note > 0 then 1 else 0 end) nbnote,
		sum(case when comment is not null and comment <> '' then 1 else 0 end) nbcomment 
	from
		serie_comment
		";
	$DB->query($select);
	$DB->next_record();
	$t->set_var("NBNOTESERIE",$DB->f("nbnote"));
	$t->set_var("NBCOMMENTSERIE",$DB->f("nbcomment"));
}

$t->set_var("PAGETITLE","La FAQ de BDovore.com");
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var(
array(
"LOGINBARRE" => GetIdentificationBar()
)
);

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");

