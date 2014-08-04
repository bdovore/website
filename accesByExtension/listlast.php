<?php




if (minAccessLevel(1,false)) {
	$editAlbum = "<a href='admin/adminalbums.php?alb_id=%d' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
	$editSerie = "<a href='admin/adminseries.php?serie_id=%d' target=_blank><img src='".BDO_URL_IMAGE."edit.gif' border=0></a>";
}

$querystring= "";

// dossier contenant les templates
$t = new Template(BDO_DIR."public/templates");

// fichiers à utiliser
$t->set_file(array(
"tpAccueil" => "lastalbum.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBody" => "body.tpl")
);

if (! $rb_mode) {
	$rb_mode = "last_ajout";
}

// selection des genres
$query = "select id_genre, libelle from bd_genre order by libelle";
$option = "";
$DB->query($query);
$DB->next_record();
$option.= '<option value="Tous"';
if ($sel_genre == "Tous" ) {
	$option.= " selected";
}
$option.=">Tous</option>";
$first = "Tous";
while ($DB->next_record()) {
	$option.= '<option value="'.$DB->f(id_genre).'"';
	if ($sel_genre == $DB->f("id_genre") ) {
		$option.= " selected";
	}
	$option.=">".htmlspecialchars($DB->f("libelle"))."</option>";
}
$t->set_var("OPTIONS",$option);
if ($rb_mode == "last_ajout") {
	$t->set_var (array(
	"AJOUTCHECKED"=>"checked",
	"PARCHECKED"=>"",
	"CMTCHECKED"=>"",
	"FUTCHECKED"=>"")
	);
}
else if ($rb_mode == "last_par"){
	$t->set_var(array(
	"AJOUTCHECKED"=>"",
	"PARCHECKED"=>"checked",
	"CMTCHECKED"=>"",
	"FUTCHECKED"=>"")
	);
}
else if ($rb_mode == "last_cmt"){
	$t->set_var(array(
	"AJOUTCHECKED"=>"",
	"PARCHECKED"=>"",
	"CMTCHECKED"=>"checked",
	"FUTCHECKED"=>"")
	);
}
else {
	$t->set_var(array(
	"AJOUTCHECKED"=>"",
	"PARCHECKED"=>"",
	"CMTCHECKED"=>"",
	"FUTCHECKED"=>"checked")
	);
}

// construction de la requete en fonction du mode et de l'affichage
switch ($rb_mode) {

	case "last_par":
		$select = "
		select 
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
			en.img_couv , 
			en.dte_parution
		from 
			bd_tome t
			INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur = t.id_scenar
			INNER JOIN bd_auteur de ON de.id_auteur = t.id_dessin
			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_collection c ON c.id_collection = en.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
		where 
			en.dte_parution <= CURDATE()";
		if ($sel_genre && $sel_genre != 'Tous') {
			$select.= " and s.id_genre = ".$DB->escape($sel_genre) ;
		}
		$select .=" order by en.dte_parution DESC, t.titre";
		break;

	case "last_ajout":
		$select = "
		select 
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
			en.img_couv , 
			en.dte_parution
		from 
			bd_tome t
			INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur = t.id_scenar
			INNER JOIN bd_auteur de ON de.id_auteur = t.id_dessin
			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_collection c ON c.id_collection = en.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
		where 1 
			";
		if ($sel_genre && $sel_genre != 'Tous') {
			$select.= " and s.id_genre = ".$DB->escape($sel_genre) ;
		}
		$select .=" order by t.id_tome DESC";
		break;

	case "last_cmt": // Derniers commentaires
		$select = "
		select 
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
			en.img_couv , 
			en.dte_parution,
			 
			u.username, 
			u.user_id, 
			u.open_collec, 
			uc.comment, 
			DATE_FORMAT(uc.dte_post,'%d/%m/%Y %H:%i') date_post,
			uc.note
		from 
			users_comment uc
			INNER JOIN users u ON u.user_id=uc.user_id
			INNER JOIN bd_tome t ON uc.id_tome = t.id_tome 
			INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur = t.id_scenar
			INNER JOIN bd_auteur de ON de.id_auteur = t.id_dessin
			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_collection c ON c.id_collection = en.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
		where 
			uc.comment <> '' ";
		if ($sel_genre && $sel_genre != 'Tous') {
			$select.= " and s.id_genre = ".$DB->escape($sel_genre) ;
		}
		$select .=" order by uc.dte_post DESC ";
	break;

	case "futur":
		$select = "
		select 
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
			en.img_couv , 
			en.dte_parution
		from 
			bd_tome t
			INNER JOIN bd_edition en ON en.id_edition = t.id_edition 
			INNER JOIN bd_auteur sc ON sc.id_auteur = t.id_scenar
			INNER JOIN bd_auteur de ON de.id_auteur = t.id_dessin
			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_collection c ON c.id_collection = en.id_collection 
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur
		where 
		en.dte_parution > CURDATE()";
		if ($sel_genre && $sel_genre != 'Tous') {
			$select.= " and s.id_genre = ".$DB->escape($sel_genre) ;
		}
		$select .=" order by en.dte_parution, t.titre";

}

//echo $rb_mode;
if (!$page ) {
	$page=1;
}
$limit = " limit ".(($page-1)*20).",20";
$select.=$limit;
$DB->query($select);
$t->set_block('tpAccueil','AlbBlock','ABlock');

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
	$id_tome = $DB->f("id_tome");
	$user_id = $DB->f("user_id");
	$ancre = $id_tome.'-'.$user_id;
	$t->set_var(ANCRE,$ancre);
	$t->set_var (array (
	"URLALBUM" => getAlbumUrl($DB->f("id_tome")),//"#"'.' onclick="window.open('."'membres/album.php?id_tome=".$DB->f("id_tome")."','Album','width=500,height=400,scrollbars=1')".'"',
	"TITRE" => stripslashes($DB->f("titre")),
	"SERIE" => $lib_serie,
	"EDITEUR" => $DB->f("editeur"),
	"URLSERIE"=> "serie.php?id_serie=".$DB->f("id_serie"),
	"URLDELETE" => $urldelete,
	"IDALBUM"=> $DB->f("id_tome"),
	"DTEPARU" => dateParution($DB->f("dte_parution")),
	"IMGCOUV"=>$DB->f("img_couv"),
	"ANCRE" => $ancre
	));

	// ajout de l'edition si admin
	if (minAccessLevel(0,false)) {
		$t->set_var(array(
		"EDITALBUM"=> sprintf($editAlbum,$DB->f("id_tome")),
		"EDITSERIE" => sprintf($editSerie,$DB->f("id_serie"))
		));
	}

	// Assigne Auteur et Scenariste si necessaire
	$t->set_var (array (
	"SCEN" => $DB->f("p_scenar"),
	"URLSCEN" => "browser.php?rb_browse=aut&lev_id=".$DB->f("id_scenar")."&let=".htmlspecialchars($DB->f("p_scenar")),
	"DESS" => $DB->f("p_dessin"),
	"URLDESS" => "browser.php?rb_browse=aut&lev_id=".$DB->f("id_dessin")."&let=".htmlspecialchars($DB->f("p_dessin"))
	));
	if ($rb_mode == "last_cmt") {
		$userid = $DB->f("user_id");
		if ($DB->f("open_collec") == 'Y'){
			$t->set_var('USERNAME','<a href="'.BDO_URL.'guest.php?user='.encodeUserId($userid).'" style="color: #990000;">'.$DB->f("username").'</a>');
		}else{
			$t->set_var('USERNAME',$DB->f('username'));
		}
		$t->set_var(array (
		"CMTDATE"=>$DB->f("date_post"),
		"NOTE"=>$DB->f("note"),
		"CMTTEXTE"=>nl2br(stripslashes($DB->f("comment")))
		));
	}
	else {
		$t->set_var("DISPLAY","none");
	}
	$t->parse ("ABlock", "AlbBlock",true);
}
$querystring.="rb_mode=$rb_mode&sel_genre=$sel_genre";
$t->set_var(array(
"PREC"=>$querystring."&page=".max(0,$page-1),
"SUIV"=>$querystring."&page=".($page + 1)
));


$nav = "";
for ($i = 1; $i < $nb_page; $i++) {
	$nav.='<a href="listlast.php?'.$querystring.'&page='.i.'">';
	if ($i == $page) {
		$nav.= "<strong>$i</strong></a>&nbsp;";
	}
	else {
		$nav.= "$i</a>&nbsp;";
	}
}
$t->set_var("NAVBLOCK",$nav);


$t->set_var(array(
"ALBCHECK" => $albcheck,
"SERCHECK" => $sercheck)
);
$t->set_var("LOGINBARRE",GetIdentificationBar());
$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("PAGETITLE","Bdovore.com : le top des albums");
$t->parse("BODY","tpAccueil");
$t->parse("MENUBARRE","tpMenu");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBody");
