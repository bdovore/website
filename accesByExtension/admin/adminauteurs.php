<?php



minAccessLevel(1);

// Mettre à jour les informations
if ($act=="update")
{
	$nom = ($_POST['txtNomAuteur'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtNomAuteur'])."'");
	$prenom = ($_POST['txtPrenomAuteur'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtPrenomAuteur'])."'");
	$pseudo = ($_POST['txtPseudoAuteur'] == '' ? "'".$DB->escape($_POST['txtNomAuteur']).", ".
	$_POST['txtPrenomAuteur']."'" : "'".$DB->escape($_POST['txtPseudoAuteur'])."'");

	$query = "UPDATE bd_auteur SET";
	$query .= " `prenom` = ".$prenom.", ";
	$query .= " `nom` = ".$nom.", ";
	$query .= " pseudo =".$pseudo.",";
	$query .= " `flg_scenar` = ".($_POST['chkScen']=='checked' ? 1 : 0).", ";
	$query .= " `flg_dessin` = ".($_POST['chkDess']=='checked' ? 1 : 0).", ";
	$query .= " `flg_color` = ".($_POST['chkColor']=='checked' ? 1 : 0).", ";
	$query .= " `comment` = ".($_POST['txtCommentaire'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtCommentaire'])."'").", ";
	$query .= " `dte_nais` = ".($_POST['txtDateNaiss'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtDateNaiss'])."'").", ";
	$query .= " `dte_deces` = ".($_POST['txtDateDeces'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtDateDeces'])."'").", ";
	$query .= " `nationalite` = ".($_POST['txtNation'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtNation'])."'");
	$query .=" WHERE (`id_auteur`=".$DB->escape($_POST["txtIdAuteur"]).");";
	$DB->query($query);
	echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">'."Mise à jour effectuée";
}

// effacement d'un auteur
elseif($act=="delete")
{
	if ($conf == "ok")
	{
		if (minAccessLevel(1))//Revérifie que c'est bien l'administrateur qui travaille
		{
			$query = "DELETE FROM bd_auteur WHERE id_auteur=".$DB->escape($auteur_id);
			$DB->query ($query);
			$redirection = BDO_URL."admin/index.php";
			echo '<META http-equiv="refresh" content="2; URL='.$redirection.'">L\'auteur a été effacée de la base.';
			exit();
		}
	}
	else
	{// Affiche la demande de confirmation
		echo 'Etes-vous s&ucirc;r de vouloir effacer l\'auteur n. '.$auteur_id.' ? <a href="'.BDO_URL.'admin/adminauteurs.php?act=delete&conf=ok&auteur_id='.$auteur_id.'">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
		exit();
	}
}
// AFFICHE UN FORMULAIRE VIDE
elseif($act=="new")
{
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.auteur.tpl",
	"tpBase" => "body.tpl"));
	//echo $query;
	//echo htmlspecialchars($DB->f("id_color"));
	$t->set_var (array
	("NBALBUMS" => "0",
	"URLDELETE" => "javascript:alert('Désactivé');",
	"URLFUSION" => "javascript:alert('Désactivé');",
	"ACTIONNAME" => "Enregistrer",
	"URLACTION" => BDO_URL."admin/adminauteurs.php?act=append"
	));
	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}

// INSERE UN NOUVEL ALBUM DANS LA BASE
elseif($act=="append")
{
	$nom = ($_POST['txtNomAuteur'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtNomAuteur'])."'");
	$prenom = ($_POST['txtPrenomAuteur'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtPrenomAuteur'])."'");
	$pseudo = ($_POST['txtPseudoAuteur'] == '' ? "'".$DB->escape($_POST['txtNomAuteur']).", ".
	$_POST['txtPrenomAuteur']."'" : "'".$DB->escape($_POST['txtPseudoAuteur'])."'");


	$query = "INSERT INTO `bd_auteur` (`PSEUDO`, `PRENOM`, `NOM`, `FLG_SCENAR`, `FLG_DESSIN`, `FLG_COLOR`, `COMMENT`, `DTE_NAIS`, `DTE_DECES`, `NATIONALITE`)";
	$query .= " VALUES (".$pseudo.",";
	$query .= $prenom.",";
	$query .= $nom.",";
	$query .= ($_POST['chkScen']=='checked' ? 1 : 0).", ";
	$query .= ($_POST['chkDess']=='checked' ? 1 : 0).", ";
	$query .= ($_POST['chkDess']=='checked' ? 1 : 0).", ";
	$query .= ($_POST['txtCommentaire'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtCommentaire'])."'").", ";
	$query .= ($_POST['txtDateNaiss'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtDateNaiss'])."'").", ";
	$query .= ($_POST['txtDateDeces'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtDateDeces'])."'").", ";
	$query .= ($_POST['txtNation'] == '' ? 'NULL' : "'".$DB->escape($_POST['txtNation'])."'").")";
	$DB->query ($query);
	$lid= mysql_insert_id();
	echo GetMetaTag(2,"L'auteur a été ajouté",(BDO_URL."admin/adminauteurs.php?auteur_id=".$lid));
}

// AFFICHER UN EDITEUR
elseif($act=="")
{
	// Creation d'un nouveau Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "admin.auteur.tpl",
	"tpBase" => "body.tpl"));
	// Compte les albums pour lesquels les auteurs ont travaillé
	$query = "
	select 
		count(*) as nbtome 
	from 
		bd_tome 
	where 
		id_scenar = ".$DB->escape($auteur_id)." 
		or id_dessin = ".$DB->escape($auteur_id)." 
		or id_color = ".$DB->escape($auteur_id)."";
	$DB->query ($query);
	$DB->next_record();
	$nb_auteur = $DB->f("nbtome");

	//récupère les données utilisateur dans la base de donnée
	$query = "
	SELECT 
		id_auteur, 
		pseudo, 
		prenom, 
		nom, 
		flg_scenar, 
		flg_dessin, 
		flg_color, 
		comment, 
		dte_nais, 
		dte_deces, 
		nationalite 
	FROM 
		bd_auteur 
	WHERE 
		id_auteur=".$DB->escape($auteur_id);
	
	$DB->query ($query);
	$DB->next_record();
	$t->set_var (array
	("IDAUTEUR" => $DB->f("id_auteur"),
	"PSEUDO" => htmlentities(stripslashes($DB->f("pseudo"))),
	"NOM" => htmlentities(stripslashes($DB->f("nom"))),
	"PRENOM" => htmlentities(stripslashes($DB->f("prenom"))),
	"ISSCENAR" => $DB->f("flg_scenar")==1 ? checked : '',
	"ISDESSIN" => $DB->f("flg_dessin")==1 ? checked : '',
	"ISCOLOR" => $DB->f("flg_color")==1 ? checked : '',
	"COMMENT" => htmlentities(stripslashes($DB->f("comment"))),
	"DTNAIS" => htmlentities(stripslashes($DB->f("dte_nais"))),
	"DTDECES" => htmlentities(stripslashes($DB->f("dte_deces"))),
	"DTNATION" => htmlentities(stripslashes($DB->f("nationalite"))),
	"NBALBUMS" => $nb_auteur,
	"URLDELETE" => BDO_URL."admin/adminauteurs.php?act=delete&auteur_id=".$DB->f("id_auteur"),
	"URLFUSION" => BDO_URL."admin/mergeauteurs.php?source_id=".$DB->f("id_auteur"),
	"ACTIONNAME" => "Valider les Modifications",
	"URLACTION" => BDO_URL."admin/adminauteurs.php?act=update"));
	// assigne la barre de login
	$t->set_var (array
	("LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}
