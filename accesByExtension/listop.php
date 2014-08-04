<?php



$querystring= "";

if (minAccessLevel(1,false)) {
	$editAlbum = "<a href='admin/adminalbums.php?alb_id=%d' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
	$editSerie = "<a href='admin/adminseries.php?serie_id=%d' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}

// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpAccueil" => "listtop.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBody" => "body.tpl"));

if (! $rb_mode) {
	$rb_mode = "top_vote";
}

// selection des genres

$query = "select id_genre, libelle from bd_genre order by libelle";
$option = "";
$DB->query($query);
$DB->next_record();
$option.= '<option value="'.$DB->f(id_genre).'"';
if ($sel_genre == $DB->f("id_genre") ) {
	$option.= " selected";
}
$option.=">".htmlspecialchars($DB->f("libelle"))."</option>";
$first = $DB->f("id_genre");
while ($DB->next_record()) {
	$option.= '<option value="'.$DB->f(id_genre).'"';
	if ($sel_genre == $DB->f("id_genre") ) {
		$option.= " selected";
	}
	$option.=">".htmlspecialchars($DB->f("libelle"))."</option>";
}

$t->set_var("OPTIONS",$option);

if (!$sel_genre) {
	$sel_genre = $first;
}
if ($rb_mode == "top_vote") {
	$t->set_var(array("TOPCHECKED"=>"checked", "REPCHECKED"=>""));
}
else {
	$t->set_var(array("TOPCHECKED"=>"", "REPCHECKED"=>"checked"));
}

// construction de la ra requete en fonction du mode et de l'affichage
if ($rb_list == "serie" ) {
	$albcheck = "";
	$sercheck = "checked";
	if ($rb_mode == "top_rep") {
		// les plus répandus
		$select = "
		select
			s.id_serie, 
			s.nom, 
			CASE s.flg_fini WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' ELSE '?' end ser_fini,
			count(distinct ua.user_id) nbuser, 																										   
			avg(CASE t.moyenne WHEN 0 then NULL else t.moyenne end  ) moy, 
			min(en.img_couv) img_couv													   
		from 
			users_album ua
			INNER JOIN bd_edition en ON en.id_edition = ua.id_edition 
			INNER JOIN bd_tome t ON en.id_tome = t.id_tome
			INNER JOIN bd_serie s ON s.id_serie = t.id_serie
		where 
			s.id_genre=".$DB->escape($sel_genre)." 
		group by s.id_serie, s.flg_fini
		order by nbuser DESC
			";
	}
	elseif ($rb_mode == "top_vote") {
		// top des votes
		$select = "
		select
			s.id_serie, 
			s.nom, 
			CASE s.flg_fini WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' ELSE '?' end ser_fini,
			count(distinct ua.user_id) nbuser, 
			avg(t.moyenne ) moy, 
			min(en.img_couv) img_couv
		from 
			users_album ua
			INNER JOIN bd_edition en ON en.id_edition = ua.id_edition 
			INNER JOIN bd_tome t ON en.id_tome = t.id_tome
			INNER JOIN bd_serie s ON s.id_serie = t.id_serie
		where 
			s.id_genre=".$DB->escape($sel_genre)." 
			and t.moyenne > 0
		group by s.id_serie, s.flg_fini
		order by moy DESC";
	}
	else {
		$select = "
		select 
			s.id_serie, 
			s.nom, 
			CASE s.flg_fini WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' ELSE '?' end ser_fini,
			u.username, 
			sc.comment, 
			sc.note
		from 
			serie_comment sc 
			INNER JOIN bd_serie s ON s.id_serie = sc.id_serie
			INNER JOIN bd_serie u ON u.user_id = sc.user_id
		where 
			sc.comment is not null
		order by sc.dte_post desc
		";
	}
}
else {
	$rb_list = "album";
	$albcheck = "checked";
	$sercheck = "";

	if ($rb_mode == "top_rep") {
		$select = "
		select
			count(1) as nb_rep,
			t.moyenne,
			t.nb_vote,
			t.titre, 
			t.id_tome,
			sc.pseudo p_scenar, 
			t.id_scenar, 
			de.pseudo p_dessin, 
			t.id_dessin,
			er.nom editeur, 
			s.nom serie, 
			s.id_serie,  
			s.flg_fini, 
			t.num_tome, 
			t.flg_int, 
			t.flg_type,
			endef.img_couv
		from
			users_album ua
			INNER JOIN bd_edition en ON en.id_edition = ua.id_edition 
			INNER JOIN bd_tome t ON en.id_tome = t.id_tome 
			INNER JOIN bd_edition endef ON endef.id_edition = t.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur = t.id_scenar
			INNER JOIN bd_auteur de ON de.id_auteur = t.id_dessin
			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_collection c ON c.id_collection = endef.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
		where 
		ua.flg_achat = 'N' 
		and s.id_genre = ".$DB->escape($sel_genre). "
		group by t.id_tome
		order by nb_rep DESC";
	}
	else {
		$select = "
		select
			count(1) as nb_rep,
			t.moyenne,
			t.nb_vote,
			t.titre, 
			t.id_tome,
			sc.pseudo p_scenar, 
			t.id_scenar, 
			de.pseudo p_dessin, 
			t.id_dessin,
			er.nom editeur, 
			s.nom serie, 
			s.id_serie,  
			s.flg_fini, 
			t.num_tome, 
			t.flg_int, 
			t.flg_type,
			endef.img_couv
		from
			users_album ua
			INNER JOIN bd_edition en ON en.id_edition = ua.id_edition 
			INNER JOIN bd_tome t ON en.id_tome = t.id_tome 
			INNER JOIN bd_edition endef ON endef.id_edition = t.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur = t.id_scenar
			INNER JOIN bd_auteur de ON de.id_auteur = t.id_dessin
			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_collection c ON c.id_collection = endef.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
WHERE t.nb_vote > 0 
and s.id_genre = ".$DB->escape($sel_genre). "
group by t.id_tome
order by t.moyenne DESC,nb_rep DESC";
	}
}
if (!$page ) { $page=1;}

$limit = " limit ".(($page-1)*20).",20";
$select.=$limit;
$DB->query($select);
$t->set_block('tpAccueil','AlbBlock','ABlock');
if ($rb_list == "album") {

	while ($DB->next_record()) {
		if ($DB->f("flg_int") == 'O') {
			$lib_serie = stripslashes($DB->f("serie"))." - Intégrale";
		}
		else {
			if ($DB->f("flg_fini") == 2 ) {
				$lib_serie = stripslashes($DB->f("serie")). " (One shot)";
			}
			else {
				if ($DB->f("flg_type") == 1) {
					$lib_serie = stripslashes($DB->f("serie"))." - Coffret";
				}
				else {
					if ($DB->f("num_tome") == 0 || !$DB->f("num_tome")) {
						$lib_serie = stripslashes($DB->f("serie"))." - HS";
					}
					else {
						$lib_serie = stripslashes($DB->f("serie"))." n°".$DB->f("num_tome");
					}
				}
			}
		}
		$t->set_var (array
		(
		"URLALBUM" => getAlbumUrl($DB->f("id_tome")),//'"#"'.' onclick="window.open('."'membres/album.php?id_tome=".$DB->f("id_tome")."','Album','width=500,height=400,scrollbars=1')".'"',
		"TITRE" => stripslashes($DB->f("titre")),
		"SERIE" => $lib_serie,
		"EDITEUR" => $DB->f("editeur"),
		"URLSERIE"=> "serie.php?id_serie=".$DB->f("id_serie"),
		"URLDELETE" => $urldelete,
		"IDALBUM"=> $DB->f("id_tome"),
		"NBVOTE"=>$DB->f("nb_vote"),
		"MOYENNE"=>$DB->f("moyenne"),
		"NBUSER"=>$DB->f("nb_rep"),
		"IMGCOUV"=>$DB->f("img_couv")
		));
		// ajout de l'edition si admin
		if (minAccessLevel(0,false)) {
			$t->set_var(array(
			"EDITALBUM"=> sprintf($editAlbum,$DB->f("id_tome")),
			"EDITSERIE" => sprintf($editSerie,$DB->f("id_serie"))
			));
		}
		// Assigne Auteur et Scenariste si necessaire

		$t->set_var (array
		("SCEN" => $DB->f("p_scenar"),
		"URLSCEN" => "browser.php?rb_browse=aut&lev_id=".$DB->f("id_scenar")."&let=".htmlspecialchars($DB->f("p_scenar")),
		"DESS" => $DB->f("p_dessin"),
		"URLDESS" => "browser.php?rb_browse=aut&lev_id=".$DB->f("id_dessin")."&let=".htmlspecialchars($DB->f("p_dessin"))
		));

		$t->parse ("ABlock", "AlbBlock",true);
	}
}

$t->set_block('tpAccueil','SerBlock','SBlock');

if ($rb_list == "serie") {
	// affichage liste des série

	while ($DB->next_record()) {
		$t->set_var (array
		(
		"URLSERIE" => '"serie.php?id_serie='.$DB->f("id_serie").'"',
		"SERTITRE" => stripslashes($DB->f("nom")),
		"SERAVCT" => $DB->f("ser_fini"),
		"MOYENNE"=>round($DB->f("moy"),2),
		"NBUSER"=>$DB->f("nbuser"),
		"IMGCOUV"=>$DB->f("img_couv")
		));
		$t->parse ("SBlock", "SerBlock",true);
	}
}
$querystring.="rb_mode=$rb_mode&sel_genre=$sel_genre&rb_list=$rb_list";
$t->set_var(array(
"PREC"=>$querystring."&page=".max(0,$page-1),
"SUIV"=>$querystring."&page=".($page + 1)
));
$t->set_var(array(
"ALBCHECK" => $albcheck,
"SERCHECK" => $sercheck
));
$t->set_var("LOGINBARRE",GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("PAGETITLE","Bdovore.com : le top des albums");
$t->parse("BODY","tpAccueil");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBody");
