<?php



include (BDO_DIR."inc/queryfunction.php");
//$user = 3;
$user = $_SESSION["userConnect"]->user_id; //  --->>> marche pas ... Thanaos

	// **************** nombre d'album à afficher par page : 5
	// **********************************************
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array("tpBase" => "ajax.serie.album.tpl"
	));
	
		// Bloc albums
		$t->set_block('tpBase','AlbBlock','ABlock');
		// Parcours les albums de la série
		$query_album = "
		SELECT 
			base.id_tome  b_tome
			, user.id_tome u_tome
			, exclu.id_tome e_tome
			, base.num_tome b_numtome
			, base.titre b_titre
			, base.img_couv b_imgcouv
			, user.nedit u_nedit
			, user.achat u_flgachat
			, user.edition u_edition
		FROM 
			(SELECT 
				t.id_tome
				,t.num_tome
				,t.titre
				,e.img_couv
			FROM 
				bd_tome t
				,bd_edition e
			WHERE 
				id_serie = $id_serie
			AND t.id_edition = e.id_edition
			) base 
		LEFT JOIN 
			(SELECT 
				t.id_tome
				,count(u.id_edition) nedit
				,IF(count(u.id_edition)>1, 'M', flg_achat) achat
				,IF(count(u.id_edition)>1, 'M', u.id_edition) edition
			FROM 
				users_album u
				, bd_edition e
				, bd_tome t
			WHERE 
				u.id_edition = e.id_edition 
			AND e.id_tome = t.id_tome
			AND u.user_id = $user
			AND t.id_serie = $id_serie
			GROUP BY t.id_tome
			) user ON base.id_tome=user.id_tome
		LEFT JOIN 
			(SELECT 
				id_tome 
			FROM 
				users_exclusions
			WHERE 
				user_id = $user 
			AND id_serie = $id_serie
			) exclu ON base.id_tome=exclu.id_tome
		ORDER BY base.num_tome, base.titre";
		
		//echo $query_album;
		
		$DB->query($query_album);
		
		// Pour chaque album
		while ($DB->next_record()){
		
			$id_tome = $DB->f("b_tome");
			
			
			// Check si l'album est dans la collection
			switch ($DB->f("u_nedit")) {
				case 0 : // l'album n'est pas dans la collection de l'utilisateur
					if (!(is_null($DB->f("e_tome"))) || ($DB->f("e_tome")<>'')) {
						$statut_alb = "excluded";
						$button_status_rem = "";
					}else{
					$statut_alb = "default";
					}
					$button = "Add";
					$button_status = "";
					$button_status_rem = "";
					break;
				case 1 : // l'album est dans la collection de l'utilisateur
					if ($DB->f("u_flgachat")=='O') {
						$statut_alb = "alb_af";
					}else{
						$statut_alb = "incollec";
					}
					$button = "Remove";
					$button_status = "";
					$button_status_rem = "disabled";
					break;
				default : // l'album comporte plusieurs éditions
					$statut_alb = "incollec";
					$button = "Remove";
					$button_status = "disabled";
					$button_status_rem = "disabled";
					break;
			}
			
			// Couverture
			if (is_null($DB->f("b_imgcouv")) | ($DB->f("b_imgcouv")=='')) {
				$couv = BDO_URL_COUV."default.png";
			}else {
				$couv = BDO_URL_COUV.$DB->f("b_imgcouv");
			}

			$t->set_var(array(
			"COUVALBUM" => $couv
			,"ALBTITLE" => stripslashes($DB->f("b_titre"))
			,"NUMTOME" => $DB->f("b_numtome")
			,"IDTOME" => $id_tome
			,"ALBCLASS" => $statut_alb
			, "BUTTONTEXT" => $button
			, "ISDISABLED" => $button_status
			, "ISDISABLEDREM" => $button_status_rem
			, "IDEDITION" => $DB->f("u_edition")
			));

			$t->parse ("ABlock", "AlbBlock",true);
			$keyword.=stripslashes($DB->f("titre")).",";
		}

/*
// assigne la barre de login
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
"PAGETITLE" => $pagetitle,
"DESCRIPTION" => htmlspecialchars($copyright." - ".$description1),
"PAGEKEYWORD" => $keyword
));
/*$t->parse("BODY","tpBody");
'$t->parse("MENUBARRE","tpMenu");
'$t->parse("FORM_RECHERCHE","tpForm_Recherche");
'$t->parse("MENUBARREUSER","tpMenuUser");*/
$t->pparse("MyFinalOutput","tpBase");