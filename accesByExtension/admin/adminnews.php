<?php



minAccessLevel(1);


// Options pour le niveau de la news
$opt_level[0][0] = 5;
$opt_level[0][1] = 'Public';
$opt_level[1][0] = 2;
$opt_level[1][1] = 'Membres';
$opt_level[2][0] = 1;
$opt_level[2][1] = 'Equipe';

// LISTE LES NEWS
if ($act==""){
	if ($cle == ""){
		$cle=1;
	}
	if ($sort == "DESC"){
		$sort = " DESC";
	}else if ($sort == ""){
		$sort=" DESC";
	}else{
		$sort = " ASC";
	}

	// Selection des utilisateurs à afficher
	$clerep[1] = "news_id";
	$clerep[2] = "news_date";
	$clerep[3] = "news_posteur";
	$clerep[4] = "news_level";
	$clerep[5] = "news_titre";

	$query = "SELECT news_id, news_date, news_posteur, news_titre, news_level FROM news ORDER BY ".$clerep[$cle].$DB->escape($sort);

	$DB->query($query);

	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "adminnews.tpl",
	"tpBase" => "body.tpl"
	));
	// on déclare le block à utiliser
	$t->set_block('tpBody','NewsBlock','NBlock');

	//Liste les news
	while ($DB->next_record()){
		$news_id = $DB->f("news_id");
		$news_date = $DB->f("news_date");
		$news_posteur = $DB->f("news_posteur");
		$news_titre = $DB->f("news_titre");

		$newslevel = $DB->f("news_level");
		// $newslevel = $news_level[$newslevel];
		$newslevel = $user_status[$newslevel];

		$urledit = BDO_URL."admin/adminnews.php?act=modif&newsid=".$news_id;
		$urldelete = BDO_URL."admin/adminnews.php?act=supprim&newsid=".$news_id;

		$t->set_var(array(
		"ID" => $news_id,
		"DATE" => $news_date,
		"AUTEUR" => $news_posteur,
		"TITRE" => $news_titre,
		"LEVEL" => $newslevel,
		"URLEDIT"=> $urledit,
		"URLDELETE"=> $urldelete
		));

		$t->parse("NBlock", "NewsBlock",true);
	}

	// assigne la barre de login
	$t->set_var(array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}

//AJOUT DE NEWS
elseif($act=="ajout"){
	if($status=="ok"){//ajoute physiquement la news dans la base

		$query = "INSERT INTO `news` (`news_level`, `news_posteur`, `news_date`, `news_titre`, `news_text`)";
		$query .= " VALUES ('".$DB->escape($_POST["cmblevel"])."', '".$_SESSION["UserName"]."', NOW(), '".$DB->escape($_POST["txttitre"])."', 
		'".$DB->escape($_POST["txtcontent"])."');";
		$DB->query($query);
		//rouvre la page
		echo GetMetaTag(2,"La nouvelle news a été ajoutée",(BDO_URL."admin/adminnews.php"));
		exit;
	}
	//sinon affiche le formulaire de saisie de news
	// Creation d'une nouvelle instance Fast Template
	$t = new Template(BDO_DIR."public/templates");
	// fichier à utiliser
	$t->set_file(array(
	"tpBody" => "addnews.tpl",
	"tpBase" => "body.tpl"
	));

	$t->set_var (array(
	"AUTEUR" => $_SESSION["UserName"],
	"LEVEL" => GetOptionValue($opt_level,5),
	"TITRE" => "",
	"NEWSCONTENT"=> "",
	"BUTTON"=> "Poster",
	"ACTION"=> (BDO_URL."admin/adminnews.php?act=ajout&status=ok")
	));

	// assigne la barre de login
	$t->set_var (array(
	"LOGINBARRE" => GetIdentificationBar(),
	"MENUBARRE" => admin_menu(),
	"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
	));
	$t->parse("BODY","tpBody");
	$t->pparse("MyFinalOutput","tpBase");
}

//SUPPRESSION DE NEWS

elseif($act=="supprim"){
	if($status=="ok"){//supreesion de la news

		$query = "DELETE FROM news WHERE news_id=".$DB->escape($newsid);
		$DB->query($query);
		//rouvre la page
		echo GetMetaTag(2,"La news a été effacée",(BDO_URL."admin/adminnews.php"));
		exit;
	}else{
		// affiche la confirmation de la demande d'éffacement
		echo 'Etes-vous s&ucirc;r de vouloir effacer la news n&deg;'.$newsid.'  ?   <a href="adminnews.php?act=supprim&newsid='.$newsid.'&status=ok">oui</a>
          - <a href="javascript:history.go(-1)">non</a>';
		exit();
	}
}

// MODIFICATION DE NEWS
elseif($act=="modif"){
	if($status=="ok"){
		//modifie physiquement la news dans la base

		$query = "UPDATE news SET 
		`news_level`='".$DB->escape($_POST["cmblevel"])."', 
		`news_titre`='".$DB->escape($_POST["txttitre"])."', 
		`news_text`='".$DB->escape($_POST["txtcontent"])."' 
		WHERE `news_id`=".$DB->escape($newsid).";";
		$DB->query($query);
		//rouvre la page
		echo GetMetaTag(2,"La news a été éditée.",(BDO_URL."admin/adminnews.php"));
		exit;
	}else{
		//sinon affiche le formulaire de saisie de news et remplie la vieille news
		//Récupération des informations de la news

		$DB->query("SELECT * FROM news WHERE news_id=".$DB->escape($newsid));
		$DB->next_record();
		$titre = $DB->f("news_titre");
		$auteur = $DB->f("news_posteur");
		$newscontent = $DB->f("news_text");

		// Creation d'une nouvelle instance Fast Template
		$t = new Template(BDO_DIR."public/templates");
		// fichier à utiliser
		$t->set_file(array(
		"tpBody" => "addnews.tpl",
		"tpBase" => "body.tpl"
		));

		$t->set_var(array(
		"AUTEUR" => $auteur,
		"TITRE" => $titre,
		"NEWSCONTENT"=> $newscontent,
		"LEVEL" => GetOptionValue($opt_level,$DB->f("news_level")),
		"BUTTON"=> "Modifier",
		"ACTION"=>(BDO_URL."admin/adminnews.php?act=modif&status=ok&newsid=".$newsid)
		));

		// assigne la barre de login
		$t->set_var(array(
		"LOGINBARRE" => GetIdentificationBar(),
		"MENUBARRE" => admin_menu(),
		"URLSITE" => BDO_URL,
	"URLSITEIMAGE" => BDO_URL_IMAGE,
		));
		$t->parse("BODY","tpBody");
		$t->pparse("MyFinalOutput","tpBase");
	}
}
