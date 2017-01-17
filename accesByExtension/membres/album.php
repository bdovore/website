<?php

include (BDO_DIR . "inc/queryfunction.php");

if (($act == 'add') and ($id_edition or $id_tome) and minAccessLevel ( 2 )) {

    if (notIssetOrEmpty ( $id_edition ) and issetNotEmpty ( $id_tome )) {
        $id_edition = idEditionByIdTome ( $id_tome );
    }
    if (notIssetOrEmpty ( $id_tome ) and issetNotEmpty ( $id_edition )) {
        $id_tome = idTomeByIdEdition ( $id_edition );
    }

    $flg_achat = $_GET ["flg_achat"]; // Déclaration de la variable et
                                     // récupération dans l'url
    $verif = "
    select
        flg_achat
    from
        users_album
    where
        id_edition=" . $DB->escape ( $id_edition ) . "
        and user_id=" . $DB->escape ( $_SESSION ["UserId"] );

    $DB->query ( $verif );
    if ($DB->num_rows () > 0) { // Album déjà présent dans la collection
        $DB->next_record ();
        if ($DB->f ( "flg_achat" ) == "O" and $flg_achat == "N")        // "O" : Achat futur
                                                              // ; "N" : Achat;
        {
            $DB->query ( "
            update users_album set
                flg_achat = 'N',
                date_achat = CURRENT_TIMESTAMP()
            where
                id_edition=" . $DB->escape ( $id_edition ) . "
                and user_id=" . $DB->escape ( $_SESSION ["UserId"] ) );

            echo GetMetaTag ( 4, "L'édition était présente dans vos achats futurs et a été placé dans votre collection. Vous allez être redirigé vers votre fiche côté Collection", (BDO_URL . "/membres/useralbum.php?id_tome=$id_tome&id_edition=$id_edition") );
            exit ();
        } else {
            echo GetMetaTag ( 3, "L'édition est déjà présente dans votre collection, vous allez être redirigé vers votre fiche côté Collection", (BDO_URL . "/membres/useralbum.php?id_tome=$id_tome&id_edition=$id_edition") );
            exit ();
        }
    } else {
        // Album non présent dans la collection
        // ajout de l'album dans la collection
        $insert_new = "
        INSERT INTO users_album (
            user_id,
            date_ajout,
            flg_achat,
            id_edition
        ) VALUES (
            " . $DB->escape ( $_SESSION ["UserId"] ) . ",
            CURRENT_TIMESTAMP(),
            '" . $DB->escape ( $flg_achat ) . "',
            " . $DB->escape ( $id_edition ) . "
        )";

        $DB->query ( $insert_new );
        echo GetMetaTag ( 3, "L'album a été ajouté dans votre collection, vous allez être redirigé vers sa fiche côté Collection", (BDO_URL . "/membres/useralbum.php?id_tome=$id_tome&id_edition=$id_edition") );
        exit ();
    }
}
if (isset ( $_POST ["id_tome"] ) && isset ( $_POST ["note"] )) {
    // ajout de commentaire/note

    minAccessLevel ( 2 );

    $id_tome = $_POST ["id_tome"];
    $note = $_POST ["note"];
    $comment = $_POST ["comment"];
    $verif = "select note from users_comment where user_id = " . $DB->escape ( $_SESSION ["UserId"] ) . " and id_tome=" . $DB->escape ( $id_tome );
    $DB->query ( $verif );
    // echo $DB->num_rows;
    if ($DB->num_rows () > 0) {
        // mise à jour du commentaire
        $update = "update users_comment set note = " . $DB->escape ( $note ) . ", comment = '" . $DB->escape ( $comment ) . "',dte_post = CURRENT_TIMESTAMP() where user_id = ";
        $update .= sprintf ( "%d and id_tome =%d", $DB->escape ( $_SESSION ["UserId"] ), $DB->escape ( $id_tome ) );
        $DB->next_record ();
        $old_note = $DB->f ( "note" );
        $DB->query ( $update );
        // on récupere le nombre de note pour la moyenne
        $select = sprintf ( "select sum(note) tot, count(note) nb from users_comment where id_tome = %d", $DB->escape ( $id_tome ) );
        $DB->query ( $select );
        $DB->next_record ();
        $nb_note = $DB->f ( "nb" );
        $tot = $DB->f ( "tot" );
        $moy = $tot / $nb_note;
        $update = sprintf ( "update bd_tome set moyenne =%d, nb_vote =%d where id_tome = %d", $DB->escape ( $moy ), $DB->escape ( $nb_note ), $DB->escape ( $id_tome ) );
        $DB->query ( $update );
    } else {
        $insert = "insert into users_comment (user_id, id_tome, note, comment, dte_post) ";
        $insert .= sprintf ( " values (%d,%d,%d,'%s',CURRENT_TIMESTAMP())", $DB->escape ( $_SESSION ["UserId"] ), $DB->escape ( $id_tome ), $DB->escape ( $note ), $DB->escape ( $comment ) );
        $DB->query ( $insert );
        $select = sprintf ( "select sum(note) tot, count(note) nb from users_comment where id_tome = %d", $DB->escape ( $id_tome ) );
        $DB->query ( $select );
        $DB->next_record ();
        $nb_note = $DB->f ( "nb" );
        $tot = $DB->f ( "tot" );
        $moy = $tot / $nb_note;
        $update = sprintf ( "update bd_tome set moyenne =%d, nb_vote =%d where id_tome = %d", $DB->escape ( $moy ), $DB->escape ( $nb_note ), $DB->escape ( $id_tome ) );
        $DB->query ( $update );
    }
}

// Creation d'un nouveau Template
$t = new Template ( BDO_DIR . "public/templates" );

// fichier à utiliser
$t->set_file ( array (
        "tpBody" => "album.tpl",
        "tpComment" => "viewcomment.tpl"
) );

if ($id_tome or $id_edition) {

    if (issetNotEmpty ( $id_edition )) {
        $request = q_edition ( "en.id_edition =" . $DB->escape ( $id_edition ) . " AND en.prop_status = 1" );
    } else {
        $request = q_tome ( "t.id_tome =" . $DB->escape ( $id_tome ) . " AND en.prop_status = 1" );
    }

    $DB->query ( $request );

    if ($DB->nf () != 0) {
        $DB->next_record ();

        if (notIssetOrEmpty ( $id_edition )) {
            $id_edition = $DB->f ( "id_edition" );
        }
        if (notIssetOrEmpty ( $id_tome )) {
            $id_tome = $DB->f ( "id_tome" );
        }
        $titre = $DB->f ( "titre" );

        if ($DB->f ( "nb_vote" ) > 0) {
            $note = "<img src='" . BDO_URL . "images/imgnote.php?note=" . $DB->f ( "moyenne" ) . "' title='" . $DB->f ( "moyenne" ) . "'>" . "<br> (" . $DB->f ( "nb_vote" ) . " note" . (($DB->f ( "nb_vote" ) > 1) ? "s" : "") . ", moyenne " . $DB->f ( "moyenne" ) . ")";
        }
        $location = "";
        $location2 = "";
        if ($DB->f ( "id_scenar_alt" ) != 0) {
            $location = "'../browser.php?rb_browse=aut&lev_id=" . $DB->f ( "id_scenar" ) . "&let=" . htmlspecialchars ( $DB->f ( "scpseudo" ) ) . "'";
            $location2 = "'../browser.php?rb_browse=aut&lev_id=" . $DB->f ( "id_scenar_alt" ) . "&let=" . htmlspecialchars ( $DB->f ( "scapseudo" ) ) . "'";
            $scenar = '<a href="#" onclick="if (window.name == ' . "'Album') {" . 'window.opener.location=' . $location . ';} else {parent.location =' . $location . ";}" . '"' . ">" . $DB->f ( "scpseudo" ) . "</a>";
            $scenar .= "&nbsp;-&nbsp; " . '<a href="#" onclick="if (window.name == ' . "'Album') {" . 'window.opener.location=' . $location2 . ';} else {parent.location =' . $location2 . ";}" . '"' . ">" . $DB->f ( "scapseudo" ) . "</a>";

            /*
             *
             * "'../browser.php?rb_browse=aut&lev_id=".$DB->f("id_scenar")."&let=".
             * htmlspecialchars($DB->f("scpseudo"))."'".'"'.">".$DB->f("scpseudo")."</a>
             * &nbsp;-&nbsp; ".'<a href="#" onclick="window.opener.location='.
             * "'../browser.php?rb_browse=aut&lev_id=".$DB->f("id_scenar_alt")."&let=".
             * htmlspecialchars($DB->f("scapseudo"))."'".'"'.">".$DB->f("scapseudo")."</a>";
             */
        } else {
            $location = "'../browser.php?rb_browse=aut&lev_id=" . $DB->f ( "id_scenar" ) . "&let=" . htmlspecialchars ( $DB->f ( "scpseudo" ) ) . "'";
            $scenar = '<a href="#" onclick="if (window.name == ' . "'Album') {" . 'window.opener.location=' . $location . ';} else {parent.location =' . $location . ";}" . '"' . ">" . $DB->f ( "scpseudo" ) . "</a>";
            /*
             * $scenar = '<a href="#" onclick="window.opener.location='.
             * "'../browser.php?rb_browse=aut&lev_id=".$DB->f("id_scenar")."&let=".
             * htmlspecialchars($DB->f("scpseudo"))."'".'"'.">".$DB->f("scpseudo")."</a>";
             */
        }
        if ($DB->f ( "id_dessin_alt" ) != 0) {

            $location = "'../browser.php?rb_browse=aut&lev_id=" . $DB->f ( "id_dessin" ) . "&let=" . htmlspecialchars ( $DB->f ( "depseudo" ) ) . "'";
            $location2 = "'../browser.php?rb_browse=aut&lev_id=" . $DB->f ( "id_dessin_alt" ) . "&let=" . htmlspecialchars ( $DB->f ( "deapseudo" ) ) . "'";
            $dessin = '<a href="#" onclick="if (window.name == ' . "'Album') {" . 'window.opener.location=' . $location . ';} else {parent.location =' . $location . ";}" . '"' . ">" . $DB->f ( "depseudo" ) . "</a>";
            $dessin .= "&nbsp;-&nbsp; " . '<a href="#" onclick="if (window.name == ' . "'Album') {" . 'window.opener.location=' . $location2 . ';} else {parent.location =' . $location2 . ";}" . '"' . ">" . $DB->f ( "deapseudo" ) . "</a>";

            /*
             * $dessin = '<a href="#" onclick="window.opener.location='.
             * "'../browser.php?rb_browse=aut&lev_id=".$DB->f("id_dessin")."&let=".
             * htmlspecialchars($DB->f("depseudo"))."'".'"'.">".$DB->f("depseudo")."</a>
             * &nbsp;-&nbsp; ".'<a href="#" onclick="window.opener.location='.
             * "'../browser.php?rb_browse=aut&lev_id=".$DB->f("id_dessin_alt")."&let=".
             * htmlspecialchars($DB->f("deapseudo"))."'".'"'.">".$DB->f("deapseudo")."</a>";
             */
        } else {
            $location = "'../browser.php?rb_browse=aut&lev_id=" . $DB->f ( "id_dessin" ) . "&let=" . htmlspecialchars ( $DB->f ( "depseudo" ) ) . "'";
            $dessin = '<a href="#" onclick="if (window.name == ' . "'Album') {" . 'window.opener.location=' . $location . ';} else {parent.location =' . $location . ";}" . '"' . ">" . $DB->f ( "depseudo" ) . "</a>";
            // echo $dessin;
            /*
             * $dessin = '<a href="#" onclick="window.opener.location='.
             * "'../browser.php?rb_browse=aut&lev_id=".$DB->f("id_dessin")."&let=".
             * htmlspecialchars($DB->f("depseudo"))."'".'"'.">".$DB->f("depseudo")."</a>";
             */
        }
        if ($DB->f ( "flg_int" ) == 'O') {
            $num_tome = "Intégrale";
        } else {
            if ($DB->f ( "flg_fini" ) == 2) {
                $num_tome = "One shot";
                $tome = " ";
            } else {
                if ($DB->f ( "flg_type" ) == 1) {
                    $num_tome = "Coffret";
                    $tome = " ";
                } else {
                    if ($DB->f ( "num_tome" ) == 0 || ! $DB->f ( "num_tome" )) {
                        $num_tome = "HS";
                        $tome = " ";
                    } else {
                        $num_tome = $DB->f ( "num_tome" );
                        $tome = " Tome ";
                    }
                }
            }
        }

        $scenar1 = explode ( ",", stripslashes ( $DB->f ( 'scpseudo' ) ) );
        $nom_scenar1 = $scenar1 [0];
        $dessin1 = explode ( ",", stripslashes ( $DB->f ( 'depseudo' ) ) );
        $nom_dessin1 = $dessin1 [0];
        $copyright = "©" . $nom_scenar1 . "/" . $nom_dessin1 . "-" . stripslashes ( $DB->f ( 'enom' ) );

        $albauteur = $nom_scenar1 . "/" . $nom_dessin1;

        $t->set_var ( array (
                "WNDTITLE" => stripslashes ( $DB->f ( "titre" ) ) . " (" . stripslashes ( $DB->f ( "s_nom" ) ) . ")",
                "ALBAUTEUR" => $albauteur,
                "ALBTITLE" => stripslashes ( $DB->f ( "titre" ) ),
                "SERTITLE" => stripslashes ( $DB->f ( "s_nom" ) ) . $tome . $num_tome,
                "NUMTOME" => $num_tome,
                "ALBGENRE" => $DB->f ( "libelle" ),
                "ALBSCENAR" => $scenar,
                "ALBDESSIN" => $dessin,
                "ALBCOLOR" => htmlspecialchars ( $DB->f ( "copseudo" ) ),
                "ALBNOTE" => $note,
                "ALBSTORY" => nl2br ( stripslashes ( $DB->f ( "histoire" ) ) ),
                "DESCRIPTION" => htmlspecialchars ( $copyright . " - " . nl2br ( stripslashes ( $DB->f ( "histoire" ) ) ) ),
                "TOMEID" => $DB->f ( "id_tome" ),
                "URLEDIT" => BDO_URL . "membres/prop_correct.php?alb_id=" . $DB->f ( "id_tome" ) . "&id_edition=" . $DB->f ( "id_edition" ),
                "SERID" => $DB->f ( "id_serie" ),
                "NSCENAR" => $DB->f ( "scpseudo" ),
                "NDESSIN" => $DB->f ( "depseudo" ),
                "COPYRIGHT" => $copyright,
                "URLIMAGE" => BDO_URL_IMAGE
        ) );

        if (is_null ( $DB->f ( "img_couv" ) ) | ($DB->f ( "img_couv" ) == '')) {
            $couv = "default.png";
        } else {
            $couv = $DB->f ( "img_couv" );
        }
        if ($DB->f ( "isbn" )) {
            $amazon = BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/ASIN/" . $DB->f ( "isbn" ) . "/bdovorecom-21/";
        } else {
            $amazon = BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/external-search?tag=bdovorecom-21&keyword=" . htmlspecialchars ( stripslashes ( $DB->f ( "s_nom" ) ) ) . "%20" . htmlspecialchars ( stripslashes ( $DB->f ( "titre" ) ) ) . "&mode=books-fr";
        }
        if ($DB->f ( "cnom" ) != "<N/A>") {
            $collection = "<br />Collection " . $DB->f ( "cnom" );
        } else {
            $collection = "";
        }
        if ($DB->f ( "comment" )) {
            $comment_edition = '<div class="case_fond_clair" style="border: 1px solid #000; padding: 5px;"><span style="font-weight: bold;">Description de l\'édition</span><br />-----<br />' . stripslashes ( $DB->f ( 'comment' ) ) . '</div>';
        } else {
            $comment_edition = "";
        }
        $t->set_var ( "COUVALBUM", $couv );
        $t->set_var ( "ISBN", $DB->f ( "isbn" ) );
        $t->set_var ( "EAN", $DB->f ( "ean" ) );
        $t->set_var ( "URLAMAZON", $amazon );
        $t->set_var ( "COLLECTION", $collection );
        $t->set_var ( "COMMENT_EDITION", $comment_edition );

        // recherche de toutes les editions
        $query = q_AllEditionByIdTome ( $DB->escape ( $id_tome ), "ORDER BY en.dte_parution" );
        $DB->query ( $query );
        $edition = '';
        if ($DB->nf () > 1) {
            $edition = '<select name="select" onChange="changeEdition()" id="sel_edition">';
            while ( $DB->next_record () ) {
                $selected = ($DB->f ( "id_edition" ) == $id_edition) ? "SELECTED" : '';
                $edition .= '<option value="' . $DB->f ( "id_edition" ) . '"' . $selected . '>' . $DB->f ( "enom" ) . " " . dateParution ( $DB->f ( "dte_parution" ) ) . "</option>";
            }
            $edition .= '</select>';
            $t->set_var ( "OPTEDITION", $edition );
        } else {
            $DB->next_record ();
            $edition = '<input type=hidden name="select" id="sel_edition" value="' . $DB->f ( "id_edition" ) . '">';
            $edition .= $DB->f ( "enom" ) . " - " . dateParution ( $DB->f ( "dte_parution" ) );
            $t->set_var ( "OPTEDITION", $edition );
        }
    }

    // affichage des commentaires
    $select = "
    select
        u.user_id,
        u.username,
        u.open_collec,
        uc.comment,
        DATE_FORMAT(uc.dte_post,'%d/%m/%Y %H:%i') date_post,
        uc.note
    FROM
        users_comment uc
        INNER JOIN users u ON u.user_id=uc.user_id
    WHERE
        uc.comment is not null
        AND uc.comment <> ''
        AND uc.id_tome=" . $DB->escape ( $id_tome );

    // $select = sprintf($select."%d",$id_tome);
    $select .= " order by uc.dte_post DESC";
    $DB->query ( $select );
    $t->set_block ( "tpComment", "CmtBlock", "CBLOC" );
    if ($DB->num_rows () > 0) {
        while ( $DB->next_record () ) {

            $userid = $DB->f ( "user_id" );
            if ($DB->f ( "open_collec" ) == 'Y') {
                $t->set_var ( 'USERNAME', '<a href="' . BDO_URL . 'guest.php?user=' . encodeUserId ( $userid ) . '" style="color: #990000;">' . $DB->f ( "username" ) . '</a>' );
            } else {
                $t->set_var ( 'USERNAME', $DB->f ( 'username' ) );
            }
            $t->set_var ( array (
                    "CMTDATE" => $DB->f ( "date_post" ),
                    "NOTE" => $DB->f ( "note" ),
                    "CMTTEXTE" => nl2br ( stripslashes ( $DB->f ( "comment" ) ) )
            ) );
            $t->parse ( "CBLOC", "CmtBlock", true );
        }
        $t->parse ( "COMMENT", "tpComment" );
    } else {
        $t->set_var ( "COMMENT", "<em>Aucun commentaire actuellement</em>" );
    }
    $t->set_var ( "URLSITE", BDO_URL );
    $t->set_var ( "URLSITEIMAGE", BDO_URL_IMAGE );
    $t->set_var ( "URLSITEFORUM",BDO_URL_FORUM );

    // Envoi de la fiche album par email
    $t->set_var ( array (
            "MAILSUBJECT" => '"' . $titre . '" sur BDovore.com',
            "URLALBUM_RSS" => BDO_URL . "membres/album.php?id_edition=" . $id_edition
    ) );
    $t->pparse ( "Output", "tpBody" );
}
