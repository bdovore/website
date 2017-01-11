<?php




include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

if (isset($_POST["lstEdition"]))
{
    //var_dump_pre($_POST);

    $id_tome = $_POST["id_tome"];
    $note = $_POST["note"];
    $comment = $_POST["comment"];
    $remarque = $_POST["remarque"];
    $id_edition = $_POST["lstEdition"];
    $ori_id_edition = $_POST["id_edition"];

    $tete = 'N';
    $dedi = "N";
    $pret = 'N';
    $achat = 'N';
    $cadeau = 'N';
    if ($cb_tete){
        $tete = "O";
    }
    if ($cb_cadeau){
        $cadeau = "O";
    }
    if ($cb_dedicace){
        $dedi = "O";
    }
    if ($cb_achat){
        $achat = "O";
    }
    if ($cb_pret){
        $pret = "O";
    }
    // si l'utilisateur sélection "Proposer une nouvelle edition/J'ai plusieurs editions", et que plus tard il clique sur "Envoyer",
    // il y a un "bug", $id_edition n'est pas valide (0 ou 1) --> erreur SQL
    //solutions : réutiliser l'édition de départ ou retirer les deux options de la liste des éditions et les mettre ailleurs
    /*thanaos
    à revoir */
    if ($id_edition == 0 || $id_edition == 1) {
        $id_edition = $ori_id_edition;
    }

    // en cas de changement d'edition, on vérifie que l'edition selectionnée n'est pas déjà dans la collection
    if ($id_edition != $ori_id_edition){
        $query = "select count(*) as nb from users_album where user_id=".$DB->escape($_SESSION["UserId"])." and id_edition = ".$DB->escape($id_edition);
        $DB->query($query);
        $DB->next_record();
        if ($DB->f("nb") > 0){
            echo $DB->f("nb");
            // erreur, l'edition existe déjà
            // dans ce cas, on met bien à jour les autres champs, mais on ne modifie pas l'edition et on affiche un message d'erreur
            $id_edition = $ori_id_edition;//pas de changement
            $onload = 'onload="javascript:alert('."'Attention : vous possédez déjà cette édition dans votre collection !'".');"';
        }
    }

    if (!$date_achat || $date_achat == ''){
        $dte_achat = 'NULL';
    }
    else {
        $dte_achat = "'".substr($date_achat,6,4)."-".substr($date_achat,3,2)."-".substr($date_achat,0,2)."'";
    }
    if (!$prix || $prix == '') {
        $prix = 'null';
    }
    else {
        $prix = str_replace (',','.',$prix);
    }

    $update = "
    update users_album set
        id_edition = ".$DB->escape($id_edition).",
        comment = '".$DB->escape($remarque)."',
        flg_tete='".$DB->escape($tete)."',
        flg_pret = '".$DB->escape($pret)."',
        flg_achat = '".$DB->escape($achat)."',
        flg_dedicace = '".$DB->escape($dedi)."',
        flg_cadeau = '".$DB->escape($cadeau)."',
        nom_pret = '".$DB->escape($name_pret)."',
        email_pret = '".$DB->escape($email_pret)."',
        date_achat = ".$dte_achat.",
        cote = ".$DB->escape($prix)."
    where
        id_edition = ".$DB->escape($ori_id_edition)."
        and user_id =".$DB->escape($_SESSION["UserId"])."
        ";
    $DB->query($update);


    // gestion des notes de l'album
    if ($note != 'NULL' || $comment) {
        $update = "
            INSERT INTO users_comment (
                user_id,
                id_tome,
                note,
                comment,
                dte_post
            ) VALUES (
                ".$DB->escape($_SESSION["UserId"]).",
                ".$DB->escape($id_tome).",
                ".$DB->escape($note).",
                '".$DB->escape($comment)."',
                CURRENT_TIMESTAMP()
            )
            ON DUPLICATE KEY UPDATE
                note=".$DB->escape($note).",
                comment='".$DB->escape($comment)."',
                dte_post=CURRENT_TIMESTAMP()
            ";
        $DB->query($update);

    }
    if ($note != 'NULL') {
        // si une note, mise à jour de la moyenne de l'album
        $select = "select sum(note) tot, count(note) nb from users_comment where id_tome =".$DB->escape($id_tome);
        $DB->query($select);
        $DB->next_record();
        $moyenne = $DB->f("tot")/$DB->f("nb");
        $nb_vote = $DB->f("nb");
        $update = "update bd_tome set moyenne=$moyenne, nb_vote=$nb_vote where id_tome=".$DB->escape($id_tome);
        $DB->query($update);
    }
}
else {
    $onload = "";
}

// Creation d'un nouveau Template
$t = new Template(BDO_DIR."public/templates");

// fichier à utiliser
$t->set_file(array(
"tpBody" => "useralbum.tpl",
"tpHead" => "head.tpl"
));

if ($id_tome or $id_edition)
{
    if (notIssetOrEmpty($id_edition) and issetNotEmpty($id_tome))
    {
        $id_edition = idEditionByIdTome($id_tome);
    }
    if (notIssetOrEmpty($id_tome) and issetNotEmpty($id_edition))
    {
        $id_tome = idTomeByIdEdition($id_edition);
    }


    // prepare la liste des editions disponibles
    $query = q_AllEditionByIdTome($DB->escape($id_tome),"ORDER BY en.dte_parution");
    $DB->query($query);
    /*
    $opt_edition[0][0] = 0;
    $opt_edition[0][1] = "Proposer une nouvelle edition...";
    $opt_edition[1][0] = 1;
    $opt_edition[1][1] = "J'ai plusieurs editions...";
    $i=2;
    */
    $nbEdition=0;

    while ($DB->next_record()){
        $opt_edition[$nbEdition][0] = $DB->f("id_edition");
        $opt_edition[$nbEdition][1] = $DB->f("enom")." - ".dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution"));
        $nbEdition++;
    }

    $query = "
    SELECT
        ua.comment,
        ua.flg_dedicace,
        ua.flg_achat,
        ua.flg_pret,
        ua.flg_cadeau,
        ua.nom_pret,
        ua.email_pret,
        ua.flg_tete,
        date_format(ua.date_achat,'%d/%m/%Y') dte_achat,
        date_format(ua.date_ajout,'%d/%m/%Y') dte_ajout,
        ua.cote,

        t.id_tome,
        t.id_edition as id_edition_default,
        t.titre,
        t.num_tome,
        t.prix_bdnet,
        t.flg_int,
        t.flg_type,
        t.histoire,

        s.id_serie,
        s.nom s_nom,
        s.tri,
        s.flg_fini,
        s.nb_tome,
        s.histoire histoire_serie,

        g.id_genre,
        g.libelle,

        en.id_edition,
        en.img_couv,
        en.ean,
        en.isbn,
        en.dte_parution,
        en.flag_dte_parution,
        en.comment comment_ed,

        c.id_collection,
        c.nom cnom,

        er.id_editeur,
        er.nom enom,

        t.id_scenar,
        sc.pseudo as scpseudo,
        t.id_dessin,
        de.pseudo as depseudo,
        t.id_color,
        co.pseudo as copseudo,
        t.id_scenar_alt,
        sca.pseudo as scapseudo,
        t.id_dessin_alt,
        dea.pseudo as deapseudo,
        t.id_color_alt,
        coa.pseudo as coapseudo
    FROM
        users_album ua

        INNER JOIN bd_edition en ON en.id_edition = ua.id_edition

        INNER JOIN bd_tome t ON t.id_tome = en.id_tome
        INNER JOIN bd_serie s ON t.id_serie = s.id_serie
        INNER JOIN bd_genre g ON s.id_genre = g.id_genre

        INNER JOIN bd_collection c ON en.id_collection = c.id_collection
        INNER JOIN bd_editeur er ON c.id_editeur = er.id_editeur

        LEFT JOIN bd_auteur sc ON t.id_scenar = sc.id_auteur
        LEFT JOIN bd_auteur de ON t.id_dessin = de.id_auteur
        LEFT JOIN bd_auteur co ON t.id_color = co.id_auteur
        LEFT JOIN bd_auteur sca ON t.id_scenar_alt = sca.id_auteur
        LEFT JOIN bd_auteur dea ON t.id_dessin_alt = dea.id_auteur
        LEFT JOIN bd_auteur coa ON t.id_color_alt = coa.id_auteur
    WHERE
        ua.id_edition =".$DB->escape($id_edition)."
        AND ua.user_id =".$DB->escape($_SESSION["UserId"]);

    $DB->query($query);

    if ($DB->nf() != 0) {
        $DB->next_record();
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
        if ($DB->f("flg_cadeau") == "O") {
            $cadeau = "checked";
        }
        if ($DB->f("flg_pret") == "O") {
            $pret = "checked";
            $nom_pret = $DB->f("nom_pret");
            $email_pret = $DB->f("email_pret");
        }

        $dte_achat = $DB->f("dte_achat") ;
        if ( $DB->f("id_scenar_alt") != 0) {
            $scenar = $DB->f("scpseudo")."-".$DB->f("scapseudo");
        }
        else {
            $scenar = $DB->f("scpseudo");
        }

        if ( $DB->f("id_dessin_alt") != 0) {
            $dessin = $DB->f("depseudo")."-".$DB->f("deapseudo");
        }
        else {
            $dessin = $DB->f("depseudo");
        }

        if ( $DB->f("id_color_alt") != 0) {
            $color = $DB->f("copseudo")."-".$DB->f("coapseudo");
        }
        else {
            $color = $DB->f("copseudo");
        }

        if ($DB->f("flg_int") == 'O') {
            $num_tome = "Intégrale";
        }
        else {
            if ($DB->f("flg_fini") == 2 ) {
                $num_tome = "One shot";
            }
            else {
                if ($DB->f("flg_type") == 1) {
                    $num_tome = "Coffret";
                }
                else {
                    if ($DB->f("num_tome") == 0 || !$DB->f("num_tome")) {
                        $num_tome = "HS";
                    }
                    else {
                        $num_tome = $DB->f("num_tome");
                    }
                }
            }
        }
        if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")=='')) {
            $couv = "default.png";
        }
        else {
            $couv = $DB->f("img_couv");
        }
        if ($DB->f("comment_ed")) {
            $comment_edition = "<tr><td class=\"case_entete_fonce\"> Description de l'édition :</td>";
            $comment_edition .= "<td class=\"case_fond_clair\">" . stripslashes($DB->f('comment_ed'));
            $comment_edition .= "</td></tr>";
        }else {
            $comment_edition = "";
        }
        if ($DB->f("cnom") <> "<N/A>") {
            $collection = "<br />Collection ".$DB->f("cnom");
        }else {
            $collection = "";
        }


        $newedit = "<input name=\"Submit\" id=\"newedit\" type='button' value=\"Proposer une nouvelle édition\" onClick=\"window.open('addedition.php?id_tome=".$id_tome."','myEdition','scrollbars=yes,resizable=yes,width=630,height=460')\"/>";

        if ($nbEdition > 1)
        {
            $optionEdition = '
            <select id="lstEdition" name="lstEdition" size="1" onChange="test_value()">
            '.GetOptionValue($opt_edition,$DB->f("id_edition")).'
            </select>
            ';
            $otheredit = "<input name=\"Submit\" id=\"otheredit\" type='button' value=\"J'ai plusieurs éditions\"  onClick=\"window.open('userdupalb.php?id_tome=".$id_tome."','myEdition','scrollbars=yes,resizable=yes,width=630,height=380')\"/>";
        }
        else {
            $optionEdition = '<input type=hidden name="lstEdition" value="'.$opt_edition[0][0].'">'.$opt_edition[0][1];
            $otheredit = "";
        }

        $t->set_var(
        array(
        "WNDTITLE" => $DB->f("titre"),
        "COUVALBUM" => $couv,
        "SERID" => $DB->f("id_serie"),
        "ALBTITLE" => stripslashes($DB->f("titre")),
        "SERTITLE"=> $DB->f("s_nom"),
        "NUMTOME" => $num_tome,
        "ALBGENRE" => stripslashes($DB->f("libelle")),
        "ALBSCENAR" => $scenar,
        "ALBDESSIN" => $dessin,
        "ALBCOLOR" => $color,
        "COLLECTION" => $collection,
        "TETECHECKED" => $tete,
        "ACHATCHECKED"=>$achat,
        "DEDICHECKED"=>$dedi,
        "PRETCHECKED"=>$pret,
        "CADEAUCHECKED"=>$cadeau,
        "NOMPRET"=>$nom_pret,
        "EMAILPRET"=>$email_pret,
        "USERRQUE"=>stripslashes($DB->f("comment")),
        "ALBSTORY" => stripslashes($DB->f("histoire")),
        "TOMEID"=> $DB->f("id_tome"),
        "EDITIONID"=> $DB->f("id_edition"),
        "DTEAJOUT" => $DB->f("dte_ajout"),
        "DTEACHAT" => $dte_achat,
        "PRIX" => $DB->f("cote"),
        "URLEDIT"=> BDO_URL."membres/prop_correct.php?alb_id=".$DB->f("id_tome")."&id_edition=".$DB->f("id_edition"),
        "EAN" => $DB->f("ean"),
        "ISBN" => $DB->f("isbn"),
        "COMMENT_EDITION" => $comment_edition,
        "NEWEDIT" => $newedit,
        "OPTEDITION" => $optionEdition,
        "OTHEREDIT" => $otheredit,
        )
        );

        // commentaire
        $select = "select note, comment from users_comment where id_tome =".$DB->escape($id_tome)." and user_id =".$DB->escape($_SESSION["UserId"])."";
        $DB->query($select);
        $DB->next_record();
        $note = $DB->f("note");
        $option_note = "";
        if (!$note) {
            $option_note = '<option value="NULL" selected ></option>';
        }
        for ($i=1;$i<11;$i++) {
            $option_note.='<option value ="'.$i.'"';
            if ($note==$i) {$option_note.=" selected";}
            $option_note.=">".$i."</option>";
        }
        $t->set_var(
        array(
        "OPTIONNOTE" => $option_note,
        "USERCOMMENT"=> stripslashes($DB->f("comment"))
        )
        );
    }

    $t->set_var("JSCRIPT",$onload);
    $t->set_var("URLSITE",BDO_URL);
$t->set_var("URLSITEIMAGE",BDO_URL_IMAGE);
    $t->parse("HEAD","tpHead");
    $t->pparse("Output","tpBody");
}
