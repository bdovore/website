<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

if (isset($_GET['del']) && isset($_GET['id_tome']) && isset($_GET['id_edition']) ) {//suppression d'un album de la collection
	$id_tome = intval($_GET['id_tome']);
	$id_edition = intval($_GET['id_edition']);
	$delete = "delete from users_album where user_id =%d and id_edition=%d";
	$delete = sprintf($delete,$DB->escape($_SESSION["UserId"]),$DB->escape($id_edition));
	$DB->query($delete);
}
$t = new Template(BDO_DIR."public/templates");
$t->set_file(array(
"tpMenuColl" => "menu_coll.tpl",
"tpBody" => "userspace.home.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"));
$t->set_block('tpBody','LastBlock','LBlock');

// affichage du nombre de bd
$select = "
select
	count(ua.id_edition) nb,
	count(distinct t.id_tome) nb_tome,
	count(distinct t.id_serie) nbserie
from
	users_album ua
	INNER JOIN bd_edition en ON ua.id_edition = en.id_edition
	INNER JOIN bd_tome t ON t.id_tome = en.id_tome
where
	ua.user_id=".$DB->escape($_SESSION["UserId"])."
	and ua.flg_achat = 'N'
";
$DB->query($select);
$DB->next_record();

if ($DB->f("nb") != $DB->f("nb_tome"))
$t->set_var("NBUSEREDITION"," (".$DB->f("nb")." éditions différentes) ");
$t->set_var("NBUSERTOME",$DB->f("nb_tome"));
$t->set_var("NBSERIES",$DB->f("nbserie"));

//affichage du nb de bd notes
$selectnote = "SELECT count(*) nbnote FROM `users_comment` WHERE `note` IS NOT NULL AND user_id =".$DB->escape($_SESSION["UserId"]);
$DB->query($selectnote);
$DB->next_record();
$t->set_var("NBNOTES",$DB->f("nbnote"));

//affichage du nb de bd commentées
$selectcomment = "SELECT count(*) nbcomment FROM `users_comment` WHERE comment != '' AND user_id =".$DB->escape($_SESSION["UserId"]);
$DB->query($selectcomment);
$DB->next_record();
$t->set_var("NBCOMMENTS",$DB->f("nbcomment"));

// Selections des 5 albums les mieux notés
$query = "
select
	uc.note,
	uc.id_tome,
	t.titre,
	ua.id_edition
from
	users_album ua
	INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
	INNER JOIN bd_tome t ON en.id_tome=t.id_tome
	INNER JOIN bd_serie s ON t.id_serie=s.id_serie
	left outer join users_comment uc on uc.user_id = ua.user_id and	uc.id_tome = en.id_tome
where
	ua.user_id =".$DB->escape($_SESSION["UserId"])."
	and ua.flg_achat = 'N'
order by uc.note desc
limit 0,5";
$DB->query ($query);

// on déclare le block à utiliser
$t->set_block('tpBody','Top10Block','TBlock');

//Liste les news
while ($DB->next_record()){
	$t->set_var (array(
	"NOTE" => $DB->f("note"),
	"TOPTITRE" => stripslashes($DB->f("titre")),
	"URLTITRE"=> '#" onclick="window.open('."'useralbum.php?id_tome=".$DB->f("id_tome")."&id_edition=".$DB->f("id_edition")."','Album','width=580,height=530,scrollbars=1')"));
	$t->parse ("TBlock", "Top10Block",true);
}

// Selections des 5 Genres les plus représentés
$query = "
SELECT
	count(distinct(t.id_tome)) as nbtome,
	g.id_genre,
	g.libelle
from
	users_album ua
	INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
	INNER JOIN bd_tome t ON en.id_tome=t.id_tome
	INNER JOIN bd_serie s ON t.id_serie=s.id_serie
	INNER JOIN bd_genre g ON g.id_genre=s.id_genre
where
	ua.user_id =".$DB->escape($_SESSION["UserId"])."
	and ua.flg_achat = 'N'
group by g.id_genre
order by nbtome
desc limit 0,5";
$DB->query ($query);

// on déclare le block à utiliser
$t->set_block('tpBody','GenreBlock','GBlock');

//Liste les news
while ($DB->next_record()){
	$t->set_var (array
	("GENRE" => $DB->f("libelle"),
	"NBBYGENRE" => stripslashes($DB->f("nbtome"))));
	$t->parse ("GBlock", "GenreBlock",true);
}

// Selections des 5 dessinateurs les plus représentés
$query = "
SELECT
	count(t.id_tome) as nbtome,
	a.pseudo libelle
from
	users_album ua
	INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
	INNER JOIN bd_tome t ON en.id_tome=t.id_tome
	INNER JOIN bd_auteur a ON a.id_auteur=t.id_dessin
where
	ua.user_id =".$DB->escape($_SESSION["UserId"])."
	and ua.flg_achat = 'N'
group by a.id_auteur
order by nbtome desc limit 0,5
";
$DB->query ($query);

// on déclare le block à utiliser
$t->set_block('tpBody','DessinBlock','DBlock');

//Liste les news
while ($DB->next_record()){
	$t->set_var (array
	("TOPDESSIN" => stripslashes($DB->f("libelle")),
	"NBBYDESSIN" => $DB->f("nbtome")));
	$t->parse ("DBlock", "DessinBlock",true);
}

///* Je ne capte rien au code Tomesque. Je le remplace donc par une query classique mais je le garde pour mes petits enfants
// je remet le code tomesque incompréhensible ;-)
$Album = new QueryAlbum;
$Album->setUserMode($_SESSION["UserId"]);
$Album->setOrder("u.date_ajout DESC");
$DB->query($Album->getQuery(2,0,5));


if ($DB->nf() != 0 ) {
	while ($DB->next_record()) {
		$urldelete = $_SERVER["PHP_SELF"]."?del=1&id_tome=".$DB->f("id_tome")."&id_edition=".$DB->f("id_edition");
		if ($DB->f("flg_int") == 'O') {
			$lib_serie = stripslashes($DB->f("serie"))." - Intégrale";
		}else {
			if ($DB->f("flg_fini") == 2 ) {
				$lib_serie = stripslashes($DB->f("serie")). " (One shot)";
			}else{
				if ($DB->f("flg_type") == 1) {
					$lib_serie = stripslashes($DB->f("serie"))." - Coffret";
				}else {
					if ($DB->f("num_tome") == 0 || !$DB->f("num_tome")) {
						$lib_serie = stripslashes($DB->f("serie"))." - HS";
					}else {
						$lib_serie = stripslashes($DB->f("serie"))." n°".$DB->f("num_tome");
					}
				}
			}
		}
		$t->set_var (array(
		//"URLALBUM" => '"#"'.' onclick="window.open('."'useralbum.php?id_tome=".$DB->f("id_tome")."&id_edition=".$DB->f("id_edition")."','Album','width=580,height=530,scrollbars=1')".'"',
	"URLALBUM" => getAlbumUrl($DB->f("id_tome")),//"#"'.' onclick="window.open('."'membres/album.php?id_tome=".$DB->f("id_tome")."','Album','width=500,height=400,scrollbars=1')".'"',
		"TITRE" => stripslashes($DB->f("titre")),
		"SERIE" => $lib_serie,
		"EDITEUR" => $DB->f("editeur"),
		"DTEPARUTION" => dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution")),
		"URLSERIE"=> "usersearch.php?str_search=".$DB->f("serie")."&cb_serie=1",
		"URLDELETE" => $urldelete,
		"IDALBUM"=> $DB->f("id_tome")
		));
		// Assigne Auteur et Scenariste si necessaire
		if ($DB->f("id_scenar") == $DB->f("id_dessin")) {
			$t->set_var (array
			("SCEN" => $DB->f("p_scenar"),
			"URLSCEN"=> "usersearch.php?str_search=".$DB->f("p_scenar")."&cb_aut=1",
			"SEP" => "",
			"DESS" => "",
			"URLDESS" => ""
			));
		}else {
			$t->set_var (array(
			"SCEN" => $DB->f("p_scenar"),
			"URLSCEN" => "usersearch.php?str_search=".$DB->f("p_scenar")."&cb_aut=1",
			"SEP" => "/",
			"DESS" => $DB->f("p_dessin"),
			"URLDESS" => "usersearch.php?str_search=".$DB->f("p_dessin")."&cb_aut=1"
			));
		}
		$t->parse ("LBlock", "LastBlock",true);
		//fin while
	}
}

// les achats
$select = "select count(*) nb from users_album where flg_achat = 'O' and user_id =".$DB->escape($_SESSION["UserId"]);
$DB->query($select);
$DB->next_record();
$t->set_var("NBACHAT",$DB->f("nb"));

// les prets
$select = "select count(*) nb from users_album where flg_pret = 'O' and user_id =".$DB->escape($_SESSION["UserId"]);
$DB->query($select);
$DB->next_record();
$t->set_var("NBPRET",$DB->f("nb"));
$t->set_var ("LOGINBARRE", GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("PAGETITLE","BDOVORE.com : mon Garde-manger");

$t->parse("MENUCOLL","tpMenuColl");
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
