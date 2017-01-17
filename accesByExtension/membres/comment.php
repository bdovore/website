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

if (($act == 'del') and (($_SESSION["UserId"] == $user_id) or (minAccessLevel(2,false))))
{
    // si une seule edition sans commentaire
    $query = "
    SELECT
        bd_edition.ID_EDITION,
        users_album.comment
    FROM
        users_album
        INNER JOIN bd_edition ON users_album.ID_EDITION=bd_edition.ID_EDITION
    WHERE
        bd_edition.ID_TOME='".$DB->escape($id_tome)."'
        and users_album.user_id='".$DB->escape($user_id)."'";

    $DB->query($query);

    $num = $DB->num_rows();
    $DB->next_record();
    $comment_album = $DB->f("comment");

    if (( $num == 1) and empty($comment_album))
    {
        // on reporte
        $query = "
        UPDATE users_album
        SET COMMENT=(
            SELECT
                comment
            FROM
                users_comment
            where
                ID_TOME='".$DB->escape($id_tome)."'
                and USER_ID='".$DB->escape($user_id)."')
        WHERE
        ID_EDITION='".$DB->f("ID_EDITION")."'
        and USER_ID='".$DB->escape($user_id)."'";
        $DB->query($query);
    }

    //dans tout les cas on supprime le commentaire
    $DB->query("UPDATE `users_comment` SET COMMENT=NULL where ID_TOME='".$DB->escape($id_tome)."' and USER_ID='".$DB->escape($user_id)."'");

    // nettoyage global : si pas de note et pas de commentaire : suppression
    $DB->query("SELECT * FROM `users_comment` WHERE `NOTE` IS NULL AND `COMMENT` IS NULL");
}


$t = new template(BDO_DIR."public/templates");
$t->set_file(array(
"tpBody" => "comment.tpl",
"tpComment" => "viewcomment.tpl"));
if ($add == 1)
{
    $select = "select note, comment from users_comment  where user_id =%d and id_tome=%d";
    $select = sprintf($select,$DB->escape($_SESSION["UserId"]),$DB->escape($id_tome));
    $DB->query($select);
    if ($DB->num_rows() > 0) {
        $DB->next_record();
        $sel_note = $DB->f("note");
        $cmt = $DB->f("comment");

    }
    else {
        $sel_note = 5;

    }

}
$option_note = "";
for ($i=1;$i<11;$i++) {
    $selected= "";
    if ($i == $sel_note) {$selected = "selected";}
    $option_note .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';

}
$t->set_var(array(
"COMMENT" => $cmt,
"OPTNOTE" => $option_note
));
// on declare le block
$t->set_block('tpBody','AddBlock','ABlock');
if ($add == 1) {

    if ($id_tome && $lib_tome && $aut) {
        $t->set_var(array(
        "ALBTITLE"=>stripslashes($lib_tome),
        "ALBAUT"=>stripslashes($aut),
        "TOMEID"=>$id_tome
        ));

    }
    $t->parse ("ABlock", "AddBlock",true);
}
// affichage des commentaires
$select = "select us.user_id,username, comment, DATE_FORMAT(dte_post,'%d/%m/%Y %H:%i') date_post, note, u.email
                   from users_comment us, users u
               where comment is not null and comment <> '' and u.user_id = us.user_id and
                     us.id_tome=".$DB->escape($id_tome);
//$select = sprintf($select."%d",$id_tome);
$select .= " order by dte_post DESC";
$DB->query($select);
$t->set_block("tpComment","CmtBlock","CBLOC");
if ($DB->num_rows() > 0) {
    while ($DB->next_record()) {

        $t->set_var(array(
        "CMTDATE"=>$DB->f("date_post"),
        "NOTE"=>$DB->f("note"),
        "CMTTEXTE"=>nl2br(stripslashes($DB->f("comment")))
        ));

        if (($_SESSION["UserId"] == $DB->f("user_id")) or minAccessLevel(1,false))
        {
            $t->set_var(array(
            "URLDELETE"=>'<a onClick="if (confirm(\'Transférer de public à privé ce commentaire posté par '.$DB->f("username").' ?\'))
            window.location.href=\''.BDO_URL.'membres/comment.php?act=del&aut='.$aut.'&lib_tome='.urlencode($lib_tome).'&id_tome='.$id_tome.'&user_id='.$DB->f("user_id").'\'">
            <img src="'.BDO_URL_IMAGE.'poubelle.gif" border=0 /></a>',
            //          "URLDELETE"=>'<a onClick="if (confirm(\'Supprimer ce commentaire/note posté par '.$DB->f("username").' ?\')) alert(\''.$DB->f("user_id").'\');"><img src="'.BDO_URL_IMAGE.'poubelle.gif" border=0 ></a>',
            "USERNAME"=>'<a href="mailto:'.$DB->f("email").'?Subject=BDOVORE - Erreur de saisie de remarque personnelle&Body=Bonjour, %0A
                Vous avez enregistré une remarque de nature personnelle en tant que commentaire public sur le site. %0A Les remarques personnelles ne sont pas à saisir en tant que note publique sur le site mais en bas de page de votre fiche album, dans le champ REMARQUE PERSONNELLE (consultation par vous uniquement). %0A
                Votre commentaire a été converti en remarque personnelle. %0A BDovorement.">'.$DB->f("username").'</a>',
                ));
        }
        else {
            $t->set_var(array(
            "URLDELETE"=>'',
            "USERNAME"=>$DB->f("username"),
            ));
        }

        $t->parse("CBLOC", "CmtBlock",true);
    }
    $t->parse("LISTCOMMENT","tpComment");
} else
{
    $t->set_var("LISTCOMMENT","<em>Aucun commentaire actuellement</em>");
}

$t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
$t->set_var("URLSITEFORUM",BDO_URL_FORUM);
$t->pparse("Output","tpBody");

