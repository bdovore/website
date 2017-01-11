<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

// **************** nombre d'album à afficher par page : 5
$limPage = 5;

// **********************************************
// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche_Membre" => "form_recherche_membre.tpl",
"tpBody" => "userseries.tpl",
"tpMenu" => "user.menu.tpl",
"tpBase" => "body.tpl"
));

$t->set_block('tpBody','AlbBlock','ABlock');

// ****************************************************
// S'il faut mettre à jour des infos....
// ****************************************************
if (isset($_POST["B_Add"]))
{
	$id_serie = $_POST["id_serie"];
	$sernote = $_POST["ser_note"];
	$sercomment = $_POST["cmtserie"];
	if ($sernote <> "NULL" && $sernote > 0) {
		$verif = "select note from serie_comment where user_id =".$DB->escape($_SESSION["UserId"])." and id_serie=".$DB->escape($id_serie);
		$DB->query($verif);
		//echo $DB->num_rows;
		if ($DB->num_rows() > 0) {
			//mise à jour du commentaire
			$update = "
			update serie_comment
                set note = ".$DB->escape($sernote).",
                comment = '".$DB->escape($sercomment)."',
                dte_post = CURRENT_TIMESTAMP()
                where user_id = ";
			$update .= sprintf("%d and id_serie =%d",$DB->escape($_SESSION["UserId"]), $DB->escape($id_serie));
			$DB->next_record();
			$old_note = $DB->f("note");
			$DB->query($update);
			//on récupere le nombre de note pour la moyenne
			$select = sprintf("select sum(note) tot, count(note) nb from serie_comment where id_serie = %d",$DB->escape($id_serie));
			$DB->query($select);
			$DB->next_record();
			$nb_note = $DB->f("nb");
			$tot = $DB->f("tot");
			$moy = $tot/$nb_note;
			$update = sprintf("update bd_serie set note =%d, nb_note =%d where id_serie = %d",$moy,$nb_note,$DB->escape($id_serie));
			$DB->query($update);
		}
		else {
			$insert = "insert into serie_comment (user_id, id_serie, note, comment,dte_post) ";
			$insert .= sprintf(" values (%d,%d,%d,'%s',CURRENT_TIMESTAMP())", $DB->escape($_SESSION["UserId"]),$DB->escape($id_serie),$DB->escape($sernote),$DB->escape($sercomment));
			$DB->query($insert);
			$select = sprintf("select sum(note) tot, count(note) nb from serie_comment where id_serie = %d",$DB->escape($id_serie));
			$DB->query($select);
			$DB->next_record();
			$nb_note = $DB->f("nb");
			$tot = $DB->f("tot");
			$moy = $tot/$nb_note;
			$update = sprintf("update bd_serie set note =%d, nb_note =%d where id_serie = %d",$moy,$nb_note,$DB->escape($id_serie));
			$DB->query($update);
		}
	}
	$jtete = 0;
	$jdedi = 0;
	$jachat = 0;
	$jpret = 0;
	for($i =0; $i < count($id_edition); $i++) {
		$tete = 'N';
		$dedi = "N";
		$pret = 'N';
		$achat = 'N';
		if ($cb_tete[$jtete]== $id_edition[$i]) {
			$tete = "O";
			$jtete++;
		}
		if ($cb_dedicace[$jdedi]== $id_edition[$i]) {
			$dedi = "O";
			$jdedi++;
		}
		if ($cb_achat[$jachat]== $id_edition[$i] ) {
			$achat = "O";
			$jachat++;
		}
		if ($cb_pret[$jpret]== $id_edition[$i]) {
			$pret = "O";
			$jpret++;
		}
		if (!$dte_achat[$i] || $dte_achat[$i] == '') {
			$i_achat = 'NULL';
		}
		else {
			$i_achat = "'".substr($dte_achat[$i],6,4)."-".substr($dte_achat[$i],3,2)."-".substr($dte_achat[$i],0,2)."'";
		}
		if (!$prix[$i] || $prix[$i] == "") {
			$i_prix = "null";
		}
		else {
			$i_prix = str_replace (',','.',$prix[$i]);
		}

		$update = "
		update users_album
		set
			comment = '".$DB->escape($remarque[$i])."',
			flg_tete='".$DB->escape($tete)."',
			flg_pret = '".$DB->escape($pret)."',
			flg_achat = '".$DB->escape($achat)."',
			flg_dedicace = '".$DB->escape($dedi)."',
			nom_pret = '".$DB->escape($name_pret[$i])."',
			email_pret = '".$DB->escape($email_pret[$i])."',
			date_achat = ".$i_achat.",
			cote = ".$DB->escape($i_prix)."
		where
			id_edition = ".$DB->escape($id_edition[$i])."
			and user_id =".$DB->escape($_SESSION["UserId"]);

		$DB->query($update);
	}

	for($i =0; $i < count($id_tome); $i++) {


		$select = "
		select
		note,
		comment
		from
		users_comment
		where
		user_id =".$DB->escape($_SESSION["UserId"])."
		and id_tome = ".$DB->escape($id_tome[$i]);

		$DB->query($select);
		if ($note[$i] != 'NULL' || $comment[$i]) {
			// si un commentaire ou une note a été donné on insert dans la table
			if ($DB->num_rows() > 0) {
				$update = "update users_comment set note = ".$DB->escape($note[$i]).", comment='".$DB->escape($comment[$i])."'
		where user_id =".$DB->escape($_SESSION["UserId"])." and id_tome = ".$DB->escape($id_tome[$i]);
				$DB->query($update);
			}
			else {
				$update = "
				insert into users_comment (
					user_id, id_tome,note, comment, dte_post
				) values (
					".$DB->escape($_SESSION["UserId"]).",
					".$DB->escape($id_tome[$i]).",
					".$DB->escape($note[$i]).",
					'".$DB->escape($comment[$i])."',
					CURRENT_TIMESTAMP()
				)";
				$DB->query($update);
			}
		}
		// on vérifie s'il y a des notes/comment
		$DB->query("select count(*) nb from users_comment where user_id =".$DB->escape($_SESSION["UserId"])." and id_tome = ".$DB->escape($id_tome[$i]));
		$DB->next_record();
		$nb = $DB->f("nb");
		if ($note[$i] != 'NULL' || $nb == 1) {
			// si une note, mise à jour de la moyenne de l'album
			$select = "select sum(note) tot, count(note) nb from users_comment where id_tome =".$DB->escape($id_tome[$i]);
			$DB->query($select);
			$DB->next_record();
			$nb_vote = $DB->f("nb");
			if ($nb_vote > 0) {
				$moyenne = $DB->f("tot")/$DB->f("nb");
			}
			else {
				$moyenne = 0;
			}
			$update = "update bd_tome set moyenne = ".$DB->escape($moyenne).", nb_vote = ".$DB->escape($nb_vote)." where id_tome =".$DB->escape($id_tome[$i]);
			$DB->query($update);
		}

	}
	echo GetMetaTag(3,"Les albums de la série ont été mis à jour avec succès !",("userserie.php?id_serie=".$id_serie));
	exit();
}

// ******************************************
// Affichage de la série
// ******************************************
if ($id_serie) {
	// information général sur la série
	$query_serie = "
	select
	s.nom,
	g.libelle,
    CASE s.flg_fini WHEN 0 then 'Fini' when 1 then 'En cours' when 2 then 'One Shot' ELSE '?' end ser_fini,
    count(distinct(t.id_tome)) nbalb
    from
	    users_album ua
	    INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
	    INNER JOIN bd_tome t ON en.id_tome=t.id_tome
	    INNER JOIN bd_serie s ON t.id_serie=s.id_serie
	    INNER JOIN bd_genre g ON s.id_genre=g.id_genre
    where
	    s.id_serie = ".$DB->escape($id_serie)."
	    and ua.user_id =".$DB->escape($_SESSION["UserId"])."
    group by s.id_serie
    ";
	$DB->query($query_serie);
	if ($DB->nf() != 0) {
		$DB->next_record();
		$t->set_var(array(
		"SERTITLE"=>$DB->f("nom"),
		"SERGENRE" => $DB->f("libelle"),
		"SERFIN" => $DB->f("ser_fini"),
		"NBALB" => $DB->f("nbalb"),
		"SERID" => $id_serie
		));
		$pagetitle = "Série : ".$DB->f("nom");

		// nombre de page
		$nb_tome = $DB->f("nbalb");
		if ( !$page ) {
			$page = 1;
		}
		$t->set_var("NUMPAGE",$page);
		$debut = (($page - 1)*$limPage);

		// ajout commentaire/ note s'il y a lieu
		$select = "select comment, note from serie_comment where
                user_id =".$DB->escape($_SESSION["UserId"])." and id_serie = ".$DB->escape($id_serie);
		$DB->query($select);
		$DB->next_record();
		$note = $DB->f("note");
		$option_note = "";
		if (!$DB->f("note") || $note == 0) {
			$option_note = '<option value="NULL" selected ></option>';
		}
		for ($i=1;$i<11;$i++) {
			$option_note.='<option value ="'.$i.'"';
			if ($note==$i) {$option_note.=" selected";}
			$option_note.=">".$i."</option>";
		}
		$t->set_var(array(
		"OPTIONSERNOTE" => $option_note,
		"CMTSERIE"=> $DB->f("comment")
		));

		// La série est elle complète ?
		// -----------------------------------------------------
		$select = "
		SELECT
			count(distinct(t.id_tome))  nb
		FROM
			bd_tome t
			INNER JOIN bd_edition en ON t.id_tome=en.id_tome
			LEFT JOIN users_album ua ON en.id_edition = ua.id_edition and ua.user_id =".$DB->escape($_SESSION["UserId"])."
		where
			t.id_serie = ".$DB->escape($id_serie)."
			and ua.id_edition is null
			and t.num_tome > 0
			and t.flg_int = 'N'
			and t.flg_type = 0
		";

		$DB->query($select);
		$DB->next_record();
		if ($DB->f("nb") > 0) {
			$t->set_var("SERCOMPLETE","<a href='albmanquant.php?id_serie=".$id_serie."'>".$DB->f("nb")." album(s)</a>");
		}
		else{
			$t->set_var("SERCOMPLETE","Aucun");
		}

		// ****************************
		// Liste des albums...
		// ----------------------------------------------------
		$query_album = "
		SELECT
			en.id_edition,
			t.id_tome,
			t.titre,
			t.histoire,
			sc.pseudo p_scenar,
			t.id_scenar,
			de.pseudo p_dessin,
			t.id_dessin,
			er.nom editeur,
			s.nom serie,
			s.id_serie,
			uc.note,
			uc.comment,
			en.img_couv,
			g.libelle,
			ua.cote,
			CASE t.flg_int when 'O' then 'Intégrale' else t.num_tome end ntome,
			ua.flg_dedicace,
			ua.flg_achat,
			ua.flg_tete,
			ua.flg_pret,
			ua.nom_pret,
			ua.email_pret,
			ua.comment rque,
			date_format(ua.date_achat,'%d/%m/%Y') dte_achat,
			date_format(ua.date_ajout,'%d/%m/%Y') dte_ajout,
			ua.cote,
			en.isbn,
			en.ean,
			en.comment comment_ed,
			c.nom collection,
			co.pseudo p_color,
			en.dte_parution,
			en.flag_dte_parution
		FROM
			users_album ua
			INNER JOIN bd_edition en ON ua.id_edition=en.id_edition
			INNER JOIN bd_tome t ON en.id_tome=t.id_tome
			left outer join users_comment uc on uc.user_id = ua.user_id and uc.id_tome = t.id_tome

			INNER JOIN bd_serie s ON t.id_serie = s.id_serie
			INNER JOIN bd_genre g ON s.id_genre = g.id_genre

			INNER JOIN bd_collection c ON en.id_collection = c.id_collection
			INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur

			LEFT JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
			LEFT JOIN bd_auteur de ON t.id_dessin = de.id_auteur
			LEFT JOIN bd_auteur co ON t.id_color = co.id_auteur
		WHERE
			t.id_serie= ".$DB->escape($id_serie)."
			and ua.user_id =".$DB->escape($_SESSION["UserId"])."
		ORDER BY t.flg_int DESC, t.flg_type, num_tome, t.titre
		LIMIT ".$DB->escape($debut).", ".$DB->escape($limPage)."
            ";

		$DB->query($query_album);

		$a_tome = array();
		while ($DB->next_record()) {

			$idTome = $DB->f("id_tome");
			$note = $DB->f("note");
			$option_note = "";
			if (!$note) {
				$option_note = '<option value="NULL" selected ></option>';
			}
			else {
				$option_note = '<option value="NULL"></option>';
			}
			for ($i=1;$i<11;$i++) {
				$option_note.='<option value ="'.$i.'"';
				if ($note==$i) {$option_note.=" selected";}
				$option_note.=">".$i."</option>";
			}
			if (!in_array($idTome,$a_tome))
			{


				$t->set_var(array(
				"OPTIONNOTE" => $option_note,
				"USERCOMMENT"=> stripslashes($DB->f("comment")),
				"DISABLEDUSERCOMMENT"=> ''
				));
			}
			else {

				$t->set_var(array(
				"OPTIONNOTE" => $option_note,
				"USERCOMMENT"=> ("--- Voir l'édition ci-dessus pour modifier le commentaire et la note de cet album ---.\n\n".$DB->f("comment")),
				"DISABLEDUSERCOMMENT"=> 'DISABLED'
				));

			}
			$achat = "";
			$pret = "";
			$dedi = "";
			$tete = "";
			$nom_pret = "";
			$email_pret = "";
			if ($DB->f("flg_achat") == "O") {
				$achat = "checked";
			}
			if ($DB->f("flg_dedicace") == "O") {
				$dedi = "checked";
			}
			if ($DB->f("flg_tete") == "O") {
				$tete = "checked";
			}
			if ($DB->f("flg_pret") == "O") {
				$pret = "checked";
				$nom_pret = $DB->f("nom_pret");
				$email_pret = $DB->f("email_pret");
			}
			if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')) {
				$couv = "default.png";
			}
			else {
				$couv = $DB->f("img_couv");
			}
			$edition = $DB->f("editeur") ." ".dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution"));
			if ($DB->f("collection") != "<N/A>") {
				$edition.="<br />Collection ".$DB->f("collection");
			}
			if ($DB->f("comment_ed")) {
				$comment_edition = "<tr><td class=\"case_entete_fonce\"> Description de l'édition :</td>";
				$comment_edition .= "<td class=\"case_fond_clair\">" . stripslashes($DB->f('comment_ed'));
				$comment_edition .= "</td></tr>";
			} else {
				$comment_edition = "";
			}

			$t->set_var(array(
			"ALBNOTE" => $note,
			"COUVALBUM" => $couv,
			"ALBTITLE" => htmlspecialchars(stripslashes($DB->f("titre")),ENT_QUOTES),
			"NUMTOME" => $DB->f("ntome"),
			"ALBGENRE" => $DB->f("libelle"),
			"ALBSCENAR" => htmlspecialchars($DB->f("p_scenar"),ENT_QUOTES),
			"ALBSTORY" => stripslashes($DB->f("histoire")),
			"ALBDESSIN" => htmlspecialchars($DB->f("p_dessin"),ENT_QUOTES),
			"ALBCOLOR" => htmlspecialchars($DB->f("p_color"),ENT_QUOTES),
			"ALBEDITION" => $edition,
			"TOMEID"=> $idTome,
			"EDITIONID"=> $DB->f("id_edition"),
			"TETECHECKED" => $tete,
			"ACHATCHECKED"=>$achat,
			"DEDICHECKED"=>$dedi,
			"PRETCHECKED"=>$pret,
			"DTEAJOUT" => $DB->f("dte_ajout"),
			"DTEACHAT" => $DB->f("dte_achat"),
			"PRIX" => $DB->f("cote"),
			"NOMPRET"=>$nom_pret,
			"EMAILPRET"=>$email_pret,
			"USERRQUE"=>stripslashes($DB->f("rque")),
			"ISBN" => $DB->f("isbn"),
			"EAN" => $DB->f("ean"),
			"COMMENT_EDITION" => $comment_edition
			));
			$t->parse ("ABlock", "AlbBlock",true);


			if (!in_array($idTome,$a_tome)) $a_tome[] = $idTome;
		}
		$totalPage = ceil($nb_tome/ $limPage) + 1;
		$nav = "";
		for ($i=1;$i < $totalPage;$i++) {
			$nav.= "<a href='".$_SERVER["PHP_SELF"]."?page=$i&id_serie=$id_serie'>";
			if ($i == $page) {
				$nav.="<strong>$i</strong></a>&nbsp;";
			}
			else {
				$nav.= "$i</a>&nbsp;";
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
"PAGETITLE" =>$pagetitle,
"PAGEKEYWORD" => $keyword));
$t->parse("BODY","tpBody");
$t->parse("FORM_RECHERCHE_MEMBRE","tpForm_Recherche_Membre");
$t->parse("MENUBARRE","tpMenu");
$t->pparse("MyFinalOutput","tpBase");
