<?php





include (BDO_DIR."inc/queryfunction.php");



if (isset($_SESSION["UserName"]))
{
	$add = 1;
}
else{
	if ($src == 'serie' ) {
		$add = 0;
	}
	else {
		minAccessLevel(2);
	}
}

$t = new template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "comment.serie.tpl",
"tpComment" => "viewcomment.tpl"));

// on declare le block
if ($id_serie && $lib_tome) {
	$t->set_var(array(
	"SERTITLE"=>stripslashes($lib_tome),
	"SERID"=>$id_serie
	));

}

if (isset($_POST["id_serie"]) && isset($_POST["note"])) {
	//ajout de commentaire/note
	//validation de la session pour l'insertion de note/commentaire

	minAccessLevel(2);

	$id_serie = $_POST["id_serie"];
	$note = $_POST["note"];
	$comment = $_POST["comment"];
	$verif = "select note from serie_comment where user_id = ".$DB->escape($_SESSION["UserId"])." and id_serie=".$DB->escape($id_serie);
	$DB->query($verif);
	//echo $DB->num_rows;
	if ($DB->num_rows() > 0) {
		//mise à jour du commentaire
		$update = "
		update serie_comment set 
			note = ".$DB->escape($note).", 
			comment = '".$DB->escape($comment)."',
			dte_post = CURRENT_TIMESTAMP() 
		where 
			user_id = ".$DB->escape($_SESSION["UserId"])." 
			and id_serie =".$DB->escape($id_serie);

		$DB->next_record();
		$old_note = $DB->f("note");
		$DB->query($update);
		//on récupere le nombre de note pour la moyenne
		$select = "select sum(note) tot, count(note) nb from serie_comment where id_serie =".$DB->escape($id_serie);
		$DB->query($select);
		$DB->next_record();
		$nb_note = $DB->f("nb");
		$tot = $DB->f("tot");
		$moy = $tot/$nb_note;

		$update = "
		update bd_serie set 
		note =".$DB->escape($moy).", 
		nb_note =".$DB->escape($nb_note)." 
		where id_serie =".$DB->escape($id_serie);

		$DB->query($update);
	}
	else {
		$insert = "
		insert into serie_comment (
			user_id, id_serie, note, comment,dte_post
		) values (
			".$DB->escape($_SESSION["UserId"]).",
			".$DB->escape($id_serie).",
			".$DB->escape($note).",
			'".$DB->escape($comment)."',
			CURRENT_TIMESTAMP()
		)";
		$DB->query($insert);
		$select = sprintf("select sum(note) tot, count(*) nb from serie_comment where id_serie = %d",$DB->escape($id_serie));
		$DB->query($select);
		$DB->next_record();
		$nb_note = $DB->f("nb");
		$tot = $DB->f("tot");
		$moy = $tot/$nb_note;
		$update = sprintf("update bd_serie set note =%d, nb_note =%d where id_serie = %d",$DB->escape($moy),$DB->escape($nb_note),$DB->escape($id_serie));
		$DB->query($update);
	}

}
// affichage des commentaires
$select = "
select
	u.username, 
	us.comment, 
	DATE_FORMAT(us.dte_post,'%d/%m/%Y %H:%i'),
	us.note, 
	us.dte_post
from 
	serie_comment us
	INNER JOIN users u ON u.user_id = us.user_id 
where 
	us.comment is not null 
	and us.comment <> '' 
	and us.id_serie=".$DB->escape($id_serie)." 
order by dte_post DESC
";
$DB->query($select);
$t->set_block("tpComment","CmtBlock","CBLOC");
if ($DB->num_rows() > 0) {
	while ($DB->next_record()) {

		$t->set_var(array(
		"USERNAME"=>$DB->f("username"),
		"CMTDATE"=>$DB->f("dte_post"),
		"NOTE"=>$DB->f("note"),
		"CMTTEXTE"=>nl2br(stripslashes($DB->f("comment")))
		));
		$t->parse("CBLOC", "CmtBlock",true);
	}
	$t->parse("LISTCOMMENT","tpComment");
} else
{
	$t->set_var("LISTCOMMENT","<em>Aucun commentaire actuellement</em>");
}

$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->pparse("Output","tpBody");
