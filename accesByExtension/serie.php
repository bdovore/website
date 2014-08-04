<?php



include (BDO_DIR."inc/queryfunction.php");

if (isset($_SESSION["UserId"])){

	$DB->query("select rowserie from users where user_id=".$_SESSION["UserId"]);
	$DB->next_record();
	$limPage = $DB->f("rowserie");
}
if (notIssetOrEmpty($limPage)) {
	$limPage = 5;
}

// **************** nombre d'album à afficher par page : 5
// **********************************************
// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");
// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpBody" => "series.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));
if ($id_serie)
{
	$query_serie = "
	SELECT 
		s.nom, 
		g.libelle,
		(IFNULL(avg(CASE t.moyenne WHEN 0 then NULL else t.moyenne end),avg(s.note)) + avg(s.note))/
		(CASE sum(s.nb_note) when 0 then 1 when null then 1 else 2 end)   moy, 
		CASE s.flg_fini WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' when 3 then 'Interrompue/Abandonnée' ELSE '?' end ser_fini, 
		count(t.id_tome) nbalb, 
		max(t.num_tome) nbtom, 
		s.histoire
	FROM 
		bd_serie s 
		INNER JOIN bd_tome t ON s.id_serie = t.id_serie
		INNER JOIN bd_genre g ON s.id_genre = g.id_genre
	WHERE 
		s.id_serie =".$DB->escape($id_serie)."
	";
	$DB->query($query_serie);
	if ($DB->nf() != 0)
	{ // Si série non vide
		$DB->next_record();
		if ($DB->f("moy") > 0 ) {
			$note = "<img src='".BDO_URL."images/imgnote.php?note=".$DB->f("moy")."' title='".round($DB->f("moy"),2)."' />";
		}
		// Partie pour les correcteurs
		$url_edit_serie = "<a href='".BDO_URL."admin/adminseries.php?serie_id=".$id_serie."' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
		if (minAccessLevel(1,false)) $t->set_var("EDITSERIE",$url_edit_serie);

		$description1 = nl2br(stripslashes($DB->f("histoire")));

		$t->set_var(array(
		"SERTITLE"=> htmlspecialchars($DB->f("nom"),ENT_QUOTES),
		"SERGENRE" => $DB->f("libelle"),
		"SERFIN" => $DB->f("ser_fini"),
		"NBTOME" => $DB->f("nbtom"),
		"NBALB" => $DB->f("nbalb"),
		"SERNOTE" => $note,
		"SERID" => $id_serie,
		"HISTOIRE" => nl2br(stripslashes($DB->f("histoire")))
		));
		$pagetitle = "Série BD : ".$DB->f("nom");
		$keyword = $DB->f("nom").",";
		$nb_tome = $DB->f("nbalb");
		if ( !$page ) {
			$page = 1;
		}
		$t->set_var("NUMPAGE",$page);
		$debut = (($page - 1)*$limPage);
		$t->set_block('tpBody','CommentBlock','CBlock');
		
		$select = "
		SELECT 
			u.username, 
			sc.comment, 
			DATE_FORMAT(sc.dte_post,'%d/%m/%Y %H:%i'),
			sc.note, 
			sc.dte_post
		FROM 
			serie_comment sc 
			INNER JOIN users u ON sc.user_id=u.user_id
		WHERE 
			sc.comment is not null
			and	sc.comment <> ''
			and sc.id_serie =".$DB->escape($id_serie)."
		order by sc.dte_post DESC 
		limit 0,3
		";
		
		$DB->query($select);
		if ($DB->num_rows() > 0) {
			while ($DB->next_record()) {
				$t->set_var(array(
				"USERNAME"=>$DB->f("username"),
				"CMTDATE"=>$DB->f("dte_post"),
				"NOTE"=>$DB->f("note"),
				"CMTTEXTE"=>nl2br(stripslashes($DB->f("comment")))
				));
				$t->parse("CBlock", "CommentBlock",true);
			}
		}

		// Bloc albums
		$t->set_block('tpBody','AlbBlock','ABlock');
		// Parcours les albums de la série
		$query_album = "
		SELECT 
			t.id_tome, 
			t.titre, 
			sc.pseudo p_scenar, 
			t.id_scenar, 
			de.pseudo p_dessin, 
			t.id_dessin,
			er.nom editeur, 
			er.url_site urlediteur, 
			s.nom serie, 
			s.id_serie, 
			t.moyenne,
			en.id_edition,
			en.isbn, 
			en.ean, 
			t.nb_vote,
			t.histoire, 
			en.dte_parution,
			en.img_couv, 
			g.libelle,
			CASE t.flg_int when 'O' then 'Intégrale' 
			else (CASE t.flg_type when 1 then 'Coffret' 
				else (CASE s.flg_fini when 2 then 'One Shot' else t.num_tome 
				end) 
			end) end ntome 
		FROM
			bd_tome t
			INNER JOIN bd_edition en ON t.id_edition=en.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur=t.id_scenar 
			INNER JOIN bd_auteur de ON de.id_auteur=t.id_dessin 
			INNER JOIN bd_collection c ON c.id_collection=en.id_collection 
			INNER JOIN bd_editeur er ON er.id_editeur=c.id_editeur 
			INNER JOIN bd_serie s ON t.id_serie=s.id_serie 
			INNER JOIN bd_genre g ON s.id_genre=g.id_genre 
		WHERE 
			t.id_serie = ".$DB->escape($id_serie)."
		order by t.flg_int DESC, t.flg_type, t.num_tome, t.titre 
		limit ".$DB->escape($debut).", ".$DB->escape($limPage);
		
		$DB->query($query_album);

		// Pour chaque album
		while ($DB->next_record()){

			$id_tome = $DB->f("id_tome");

			$scenar1 = explode(",",stripslashes($DB->f('p_scenar')));
			$nom_scenar1 = $scenar1[0];
			$dessin1 = explode(",",stripslashes($DB->f('p_dessin')));
			$nom_dessin1 = $dessin1[0];
			$copyright = "©".$nom_scenar1."/".$nom_dessin1."-".stripslashes($DB->f('editeur'));

			// Lien d'admin pour les correcteurs
			$url_edit_serie = "<a href='".BDO_URL."admin/adminalbums.php?alb_id=".$DB->f("id_tome")."' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0 /></a>";
			if (minAccessLevel(1,false)) $t->set_var("EDITALB",$url_edit_serie);

			// Notes de l'album
			if ($DB->f("nb_vote") > 0){
				$note = "<img src='".BDO_URL."images/imgnote.php?note=".$DB->f("moyenne")."' title='".$DB->f("moyenne")."' />"."<br /> (".$DB->f("nb_vote")." note". (($DB->f("nb_vote") > 1) ? "s" : "") .")";
			}else{
				$note = "";
			}

			// Lien amazon
			if ($DB->f("isbn")){
				$amazon = "http://www.amazon.fr/exec/obidos/ASIN/".$DB->f("isbn")."/bdovorecom-21/";
			}else{
				$amazon = "http://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=".htmlspecialchars(stripslashes($DB->f("serie")))."%20".htmlspecialchars(stripslashes($DB->f("titre")))."&mode=books-fr";
			}

			// Couverture
			if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')) {
				$couv = "default.png";
			}else {
				$couv = $DB->f("img_couv");
			}

			$editeur = ($DB->f("urlediteur") == "") ? $DB->f("editeur") : "<a href=\"".$DB->f("urlediteur")."\" target=\"_blank\">".$DB->f("editeur")."</a>";
			$scenar = '<a href="browser.php?rb_browse=aut&lev_id='.$DB->f("id_scenar")."&let=".
			htmlspecialchars($DB->f("p_scenar")).'">'.$DB->f("p_scenar")."</a>";
			$dessin = '<a href="browser.php?rb_browse=aut&lev_id='.$DB->f("id_dessin")."&let=".
			htmlspecialchars($DB->f("p_dessin")).'"'.">".$DB->f("p_dessin")."</a>";

			$t->set_var(array(
			"ALBNOTE" => $note,
			"COUVALBUM" => $couv,
			"ALBTITLE" => stripslashes($DB->f("titre")),
			"NUMTOME" => $DB->f("ntome"),
			"ALBGENRE" => $DB->f("libelle"),
			"NSCENAR" => htmlspecialchars($DB->f("p_scenar"),ENT_QUOTES),
			"ALBSTORY" => nl2br(stripslashes($DB->f("histoire"))),
			"NDESSIN" => htmlspecialchars($DB->f("p_dessin"),ENT_QUOTES),
			"ALBSCENAR" => $scenar,
			"ALBDESSIN" => $dessin,
			"ALBEDITEUR" => $editeur,
			"DTEPARUTION" => dateParution($DB->f("dte_parution")),
			"TOMEID" => $id_tome,
			"ALBURL" => getAlbumUrl($id_tome),
			"EDITIONID" => $DB->f('id_edition'),
			"COPYRIGHT" => $copyright,
			"URLAMAZON" => $amazon,
			"ISBN" => $DB->f("isbn"),
			"EAN" => $DB->f("ean"),
			"URLEDIT"=> BDO_URL."membres/prop_correct.php?alb_id=".$id_tome
			));

			$t->parse ("ABlock", "AlbBlock",true);
			$keyword.=stripslashes($DB->f("titre")).",";
		}

		// Pagination
		$totalPage = ceil($nb_tome/ $limPage) + 1;
		$nav = "";
		for ($i=1;$i < $totalPage;$i++) {
			$nav.= "<a href='".$_SERVER["PHP_SELF"]."?page=$i&id_serie=$id_serie'>";
			if ($i == $page) {
				$nav.="<b>$i</b></a> ";
			}else {
				$nav.= "$i</a> ";
			}
		}
		$t->set_var("NAVPAGE",$nav);
	}
}

// assigne la barre de login
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => $pagetitle,
"DESCRIPTION" => htmlspecialchars($copyright." - ".$description1),
"PAGEKEYWORD" => $keyword
));
$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
