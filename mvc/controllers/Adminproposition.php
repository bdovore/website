<?php

/**
 * @author Tom
 *
 */
class Adminproposition extends Bdo_Controller {

    /**
     */
    public function Index() {
        $this->view->set_var(array("PAGETITLE" => "Proposition : liste"));
        /*
         * Page principale de gestion des propositions
         * Affiche les listes de proposition en attente
         * La gestion proprement dite est effectuée dans editProposition
         */
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            $update = getVal("chkUpdate", "");
            if ($update == 'O') {
                $act = "update";
            }
            $type = getVal("type", "AJOUT");

            $validationdelay = 30; //nbre de jours après lesquels on ne valide pas (pour les parutions futures)
            // LISTE LES PROPOSALS
            $this->loadModel("User_album_prop");
            $dbs_prop = NULL;
            $dbs_edition = NULL;
            switch ($type) {
                case "AJOUT" :
                    $titre_admin = "Nouveaux Albums en attente";


                    $dbs_prop = $this->User_album_prop->load("c", " WHERE users_alb_prop.status <> 98 AND
                                      users_alb_prop.status <> 99 AND
                                      users_alb_prop.status <> 1 AND
                                      users_alb_prop.PROP_TYPE='AJOUT'");
                    break;
                case "CORRECTION" :
                    $titre_admin = "Corrections en attente";


                    $dbs_prop = $this->User_album_prop->load("c", " WHERE users_alb_prop.status <> 98 AND
                                      users_alb_prop.status <> 99 AND
                                      users_alb_prop.status <> 1 AND
                                      users_alb_prop.PROP_TYPE='CORRECTION'");
                    break;
                case "EDITION" :
                    $titre_admin = "Editions en attente";
                    $this->loadModel("Edition");
                    $dbs_edition = $this->Edition->load("c", " WHERE bd_edition.prop_status <> 98 AND
                                      bd_edition.prop_status <> 99 AND
                                      bd_edition.prop_status <> 1 ");
                    break;
            }
            switch ($type) {
                case "AJOUT" :
                    $urledit =  BDO_URL . "adminproposition/editPropositionAjout?ID=";
                    break;
                case "CORRECTION" :
                    $urledit =  BDO_URL . "adminproposition/editPropositionCorrection?ID=";
                    break;
                case "EDITION" :
                    $urledit = BDO_URL."./Admin/editedition?edition_id=";
                    break;
            }

            $this->view->set_var(array(
                "TITRE_ADMIN" => $titre_admin,
                "dbs_prop" => $dbs_prop,
                "dbs_edition" => $dbs_edition,
                "URLEDIT" => $urledit,
                "DATEBEFOREVALID" => $this->getDateBeforeValid(),
                "validationdelay" => $validationdelay));

            $this->view->set_var($this->User_album_prop->getAllStat());
            $this->view->set_var("PAGETITLE", "Administration des propositions");
            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

private function getDateBeforeValid() {
        $validationdelay = 30; //nbre de jours après lesquels on ne valide pas (pour les parutions futures)
        $datebeforevalid = "Ne pas valider les albums qui paraissent apr&egrave;s le " . date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $validationdelay, date("Y"))) . " ($validationdelay jours)";

        return $datebeforevalid;
    }

    public function deleteProposition() {
        /*
         * Fonction de suppression d'une proposition dont l'id est passé en paramètre
         * ainsi que le type : type = AJOUT, CORRECTION ou EDITION
         * La suppression fait simplement changer le statut, et envoie éventuellement un mail à l'utilisateur
         *
         */
        if (User::minAccesslevel(1)) {
            $src = getVal("src", "");

            if ($src == "list") {
                // supppression depuis la liste : mode AJAX, on récupère l'id via GET et pas de mail
                $this->view->layout = "ajax";
                $id = getValInteger("ID", 0);
                $type = getVal("type", "");
                $mail = "";
            } else {
                /*
                 * Depuis la fiche d'édition : accès via POST, on récupère un email
                 */
                $id = postValInteger("ID", 0);

                $type = postVal("type", "");
                $mail = postVal("txtMailRefus", "");
            }
            if ($id > 0) {
                /*
                 * On a bien un id en paramètre, on met à jour le statut
                 */
                if ($type == "AJOUT" or $type == "CORRECTION") {
                    $this->loadModel("User_album_prop");
                    $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
                    // on charge la proposition à supprimer
                    $this->User_album_prop->load();
                    // modification du statut
                    $this->User_album_prop->set_dataPaste(array("STATUS" => '99'));
                    // mise à jour
                    $this->User_album_prop->update();
                    $this->view->set_var(array("json" => json_encode($this->User_album_prop->error)));
                    // on garde en mémoire les valeurs des champs
                    $prop_user = $this->User_album_prop->USER_ID;
                    $prop_img = $this->User_album_prop->IMG_COUV;
                    $prop_action = $this->User_album_prop->ACTION;
                    $prop_titre = $this->User_album_prop->TITRE;
                    $notif_mail = $this->User_album_prop->EMAIL;
                } else {
                    // edition
                    $this->loadModel("Edition");
                    $this->Edition->set_dataPaste(array("ID_EDITION" => $id));
                    $this->Edition->load();
                    $this->Edition->set_dataPaste(array("PROP_STATUS" => '99'));
                    $this->Edition->update();
                    $this->view->set_var(array("json" => json_encode($this->Edition->error)));
                    $prop_user = $this->Edition->USER_ID;
                    $prop_img = $this->Edition->IMG_COUV;
                    $prop_action = "";
                    $prop_titre = $this->Edition->TITRE_TOME;
                    $notif_mail = $this->Edition->EMAIL;
                }
                // on supprime l'image si nécessaire
                if ($prop_img != '') {

                    if (file_exists(BDO_DIR_UPLOAD . $prop_img)) {
                        @unlink(BDO_DIR_UPLOAD . $prop_img);
                    }
                }
                if ($mail <> "") {

                    $mail_sujet = "Votre proposition d'ajout dans la base BDOVORE";
                    $mail_entete = "From: no-reply@bdovore.com";
                    $mail_text = stripslashes($mail) . "\n\n";
                    mail($notif_mail, $mail_sujet, $mail_text, $mail_entete);
                }
                if ($src == "list") {
                    $this->view->render();
                } else {
                    // on charge la fiche suivante
                    $next_url = BDO_URL . "Adminproposition";
                    echo GetMetaTag(1, "La proposition a &eacute;t&eacute; supprim&eacute;", $next_url);
                }
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editPropositionAjout() {
        $this->view->set_var(array("PAGETITLE" => "Proposition : Ajout"));
        /*
         * Affichage d'une proposition d'ajout
         * Gère aussi les actions :
         * - append : ajoute un album
         * - merge : fusionne des infos avec un album
         * - comment : enregistre un commentaire sur la proposition
         */
        if (User::minAccesslevel(1)) {
            $id = getValInteger("ID");
            $this->loadModel("User_album_prop");
            $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
            // chargement des données complètes
            $this->User_album_prop->setWithAlbumInfo($bool = true);
            $this->User_album_prop->load();

            $titre = stripslashes($this->User_album_prop->TITRE);

            $color_status = "";
            switch ($this->User_album_prop->STATUS) {
                case "0":
                    $color_status = "#FFFFFF";
                    break;
                case "2":
                    $color_status = "#FFDB70";
                    break;
                case "3":
                    $color_status = "#8374E7";
                    break;
            }

            $opt_action[0] = "Ins&eacute;rer dans la collection";
            $opt_action[1] = "Ins&eacute;rer comme achat futur";
            $opt_action[2] = "Aucune";
            $opt_type[0][0] = 0;

            $opt_status[0][0] = 0;
            $opt_status[0][1] = "En cours";
            $opt_status[1][0] = 2;
            $opt_status[1][1] = "En pause";
            $opt_status[2][0] = 3;
            $opt_status[2][1] = "Aide requise";
            $opt_status[3][0] = 4;
            $opt_status[3][1] = "Aide apport&eacute;e";

            $opt_type[0][1] = 'Album';
            $opt_type[1][0] = 1;
            $opt_type[1][1] = 'Coffret';

            // Determine l'URL image
            if (is_null($this->User_album_prop->IMG_COUV) | ($this->User_album_prop->IMG_COUV == '')) {
                $url_image = BDO_URL_COUV . "default.png";
                $dim_image = imgdim($url_image);
            } else {
                $url_image = BDO_URL_IMAGE . "tmp" . DS . $this->User_album_prop->IMG_COUV;
                $dim_image = imgdim(BDO_DIR_UPLOAD . $this->User_album_prop->IMG_COUV);
            }

            $this->view->set_var(array(
                "DATEBEFOREVALID" => $this->getDateBeforeValid(),
                "PROPID" => stripslashes(if_null_quote($this->User_album_prop->ID_PROPOSAL)),
                "TITRE" => stripslashes(if_null_quote($this->User_album_prop->TITRE)),
                "CLTITRE" => (if_null_quote($this->User_album_prop->TITRE) != '' ? "flat" : "to_be_corrected"),
                "ORITITRE" => stripslashes(if_null_quote($this->User_album_prop->TITRE)),
                "IDSERIE" => stripslashes(if_null_quote($this->User_album_prop->ID_SERIE)),
                "CLIDSERIE" => (is_numeric(if_null_quote($this->User_album_prop->ID_SERIE)) ? "flat" : "to_be_corrected"),
                "ORISERIE" => stripslashes(if_null_quote($this->User_album_prop->SERIE)),
                "TOME" => if_null_quote($this->User_album_prop->NUM_TOME),
                "CLTOME" => "flat",
                "PRIX_VENTE" => if_null_quote($this->User_album_prop->PRIX),
                "ISINT" => ((if_null_quote($this->User_album_prop->FLG_INT) == 'O') ? 'checked' : ''),
                "OPTTYPE" => GetOptionValue($opt_type, if_null_quote($this->User_album_prop->FLG_TYPE)),
                "IDGENRE" => if_null_quote($this->User_album_prop->ID_GENRE),
                "CLIDGENRE" => (is_numeric(if_null_quote($this->User_album_prop->ID_GENRE)) ? "flat" : "to_be_corrected"),
                "ORIGENRE" => if_null_quote($this->User_album_prop->GENRE),
                "IDSCEN" => if_null_quote($this->User_album_prop->ID_SCENAR),
                "CLIDSCEN" => (is_numeric($this->User_album_prop->ID_SCENAR) ? "flat" : "to_be_corrected"),
                "ORISCENARISTE" => $this->User_album_prop->SCENAR,
                "IDSCENALT" => $this->User_album_prop->ID_SCENAR_ALT,
                "CLIDSCENALT" => "flat",
                "ORISCENARISTEALT" => isset($this->User_album_prop->SCENARALT) ? $this->User_album_prop->SCENARALT: NULL,
                "IDEDIT" => $this->User_album_prop->ID_EDITEUR,
                "CLIDEDIT" => (is_numeric($this->User_album_prop->ID_EDITEUR) ? "flat" : "to_be_corrected"),
                "ORIEDITEUR" => $this->User_album_prop->EDITEUR,
                "IDDESS" => $this->User_album_prop->ID_DESSIN,
                "CLIDDESS" => (is_numeric($this->User_album_prop->ID_DESSIN) ? "flat" : "to_be_corrected"),
                "ORIDESSINATEUR" => $this->User_album_prop->DESSIN,
                "IDDESSALT" => $this->User_album_prop->ID_DESSIN_ALT,
                "CLIDDESSALT" => "flat",
                "ORIDESSINATEURALT" => isset($this->User_album_prop->DESSINALT) ? $this->User_album_prop->DESSINALT : NULL,
                "IDCOLOR" => $this->User_album_prop->ID_COLOR,
                "CLIDCOLOR" => (is_numeric($this->User_album_prop->ID_COLOR) ? "flat" : "to_be_corrected"),
                "ORICOLORISTE" => $this->User_album_prop->COLOR,
                "IDCOLORALT" => $this->User_album_prop->ID_COLOR_ALT,
                "CLIDCOLORALT" => "flat",
                "ORICOLORISTEALT" => isset($this->User_album_prop->COLORALT) ? $this->User_album_prop->COLORALT : "",
                "IDCOLLEC" => $this->User_album_prop->ID_COLLECTION,
                "CLIDCOLLEC" => (is_numeric($this->User_album_prop->ID_COLLECTION) ? "flat" : "to_be_corrected"),
                "ORICOLLECTION" => $this->User_album_prop->COLLECTION,
                "DTPAR" => $this->User_album_prop->DTE_PARUTION,
                "EAN" => $this->User_album_prop->EAN,
                "URLEAN" => "http://www.bdnet.com/" . $this->User_album_prop->EAN . "/alb.htm",
                "ISEAN" => check_EAN($this->User_album_prop->EAN) ? "" : "*",
                "ISBN" => $this->User_album_prop->ISBN,
                "URLISBN" => BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/ASIN/" . $this->User_album_prop->ISBN,
                "ISISBN" => check_ISBN($this->User_album_prop->ISBN) ? "" : "*",
                "PRIX" => $this->User_album_prop->PRIX,
                "ISTT" => (($this->User_album_prop->FLG_TT == 'O') ? 'checked' : ''),
                "CLDTPAR" => "flat",
                "URLIMAGE" => $url_image,
                "DIMIMAGE" => $dim_image,
                "HISTOIRE" => stripslashes(if_null_quote($this->User_album_prop->HISTOIRE)),
                "SERIE" => is_null($this->User_album_prop->ID_SERIE) ? stripslashes(if_null_quote($this->User_album_prop->SERIE)) : stripslashes(if_null_quote($this->User_album_prop->ACTUSERIE)),
                "CLSERIE" => ($this->User_album_prop->SERIE == $this->User_album_prop->ACTUSERIE ? "flat" : "has_changed"),
                "GENRE" => is_null($this->User_album_prop->ID_GENRE) ? $this->User_album_prop->GENRE : $this->User_album_prop->ACTUGENRE,
                "CLGENRE" => ($this->User_album_prop->GENRE == $this->User_album_prop->ACTUGENRE ? "flat" : "has_changed"),
                "SCENARISTE" => is_null($this->User_album_prop->ID_SCENAR) ? $this->User_album_prop->PSEUDO_SCENAR : ($this->User_album_prop->SCENAR),
                "CLSCENARISTE" => ($this->User_album_prop->PSEUDO_SCENAR == $this->User_album_prop->SCENAR ? "flat" : "has_changed"),
                "SCENARISTEALT" => is_null($this->User_album_prop->ID_SCENAR_ALT) ? ($this->User_album_prop->SCENAR_ALT) : ($this->User_album_prop->PSEUDO_SCENAR_ALT),
                "CLSCENARISTEALT" => ($this->User_album_prop->PSEUDO_SCENAR_ALT == $this->User_album_prop->SCENAR_ALT ? "flat" : "has_changed"),
                "DESSINATEUR" => is_null($this->User_album_prop->ID_DESSIN) ? ($this->User_album_prop->DESSIN) : ($this->User_album_prop->PSEUDO_DESSIN),
                "CLDESSINATEUR" => ($this->User_album_prop->PSEUDO_DESSIN == $this->User_album_prop->DESSIN ? "flat" : "has_changed"),
                "DESSINATEURALT" => is_null($this->User_album_prop->ID_DESSIN_ALT) ? ($this->User_album_prop->DESSIN_ALT) : ($this->User_album_prop->PSEUDO_DESSIN_ALT),
                "CLDESSINATEURALT" => ($this->User_album_prop->PSEUDO_DESSIN_ALT == $this->User_album_prop->DESSIN_ALT ? "flat" : "has_changed"),
                "COLORISTE" => is_null($this->User_album_prop->ID_COLOR) ? ($this->User_album_prop->COLOR) : ($this->User_album_prop->PSEUDO_COLOR),
                "CLCOLORISTE" => ($this->User_album_prop->PSEUDO_COLOR == $this->User_album_prop->COLOR ? "flat" : "has_changed"),
                "COLORISTEALT" => is_null($this->User_album_prop->ID_COLOR_ALT) ? ($this->User_album_prop->COLOR_ALT) : ($this->User_album_prop->PSEUDO_COLOR_ALT),
                "CLCOLORISTEALT" => ($this->User_album_prop->PSEUDO_COLOR_ALT == $this->User_album_prop->COLOR_ALT ? "flat" : "has_changed"),
                "EDITEUR" => is_null($this->User_album_prop->ID_EDITEUR) ? ($this->User_album_prop->EDITEUR) : ($this->User_album_prop->ACTUEDITEUR),
                "CLEDITEUR" => ($this->User_album_prop->ACTUEDITEUR == $this->User_album_prop->EDITEUR ? "flat" : "has_changed"),
                "COLLECTION" => is_null($this->User_album_prop->ID_COLLECTION) ? ($this->User_album_prop->COLLECTION) : ($this->User_album_prop->COLLECTION),
                "CLCOLLECTION" => ($this->User_album_prop->COLLECTION == $this->User_album_prop->COLLECTION ? "flat" : "has_changed"),
                "COMMENT" => $this->User_album_prop->DESCRIB_EDITION ? stripslashes($this->User_album_prop->DESCRIB_EDITION) : "",
                "CORRCOMMENT" => $this->User_album_prop->CORR_COMMENT,
                "OPTIONSTATUS" => GetOptionValue($opt_status, $this->User_album_prop->STATUS),
                "COLOR_STATUS" => $color_status,
                "PROPACTION" => $this->User_album_prop->ACTION,
                "ACTIONUTIL" => $opt_action[$this->User_album_prop->ACTION],
                "ACTIONNAME" => "Valider",
                "URLACTION" => BDO_URL . "adminproposition/appendProposition?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLUTILVALID" => BDO_URL . "adminproposition/mergeProposition?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLCOMMENTCORR" => BDO_URL . "adminproposition/commentProposition?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLDELETE" => BDO_URL . "adminproposition/deleteProposition?src=fiche&ID=" . $this->User_album_prop->ID_PROPOSAL,
                "EXPLICIT_CHECKED" => ($this->User_album_prop->FLG_EXPLICIT  ? "checked" : "" )
            ));

            // Exemple d'email en cas de suppression
            $mail_sujet = "Votre proposition d'ajout dans la base BDOVORE";
            $mail_body = "Bonjour, \n";
            $mail_body .= "Votre proposition ";
            $mail_body .= '"' . $titre . '"';
            $mail_body .= " a &eacute;t&eacute; refus&eacute;e par l'&eacute;quipe de correction. \n";
            $mail_body .= "- Les informations que vous avez fournies n'&eacute;taient pas suffisantes. \n";
            $mail_body .= "- La proposition d'un autre membre a &eacute;t&eacute; pr&eacute;f&eacute;r&eacute;e ou valid&eacute;e avant. \n";
            $mail_body .= "- Nous consid&eacute;rons que cet album n'a pas de rapport suffisamment proche &agrave; la bande dessin&eacute;e pour &ecirc;tre int&eacute;gr&eacute; &agrave; la base de donn&eacute;es du site. \n";
            $mail_body .= "- Cet album figurait d&eacute;j&agrave; dans votre collection. \n";
            $mail_body .= "Si l'&eacute;dition par d&eacute;faut de cet album ne correspond pas &agrave; celle que vous poss&eacute;dez,";
            $mail_body .= " d'autres &eacute;ditions sont peut-&ecirc;tre d&eacute;j&agrave; pr&eacute;sentes dans la base et peuvent &ecirc;tre s&eacute;lectionn&eacute;es en cliquant sur l'album en question depuis votre garde-manger (menu d&eacute;roulant [Mon &eacute;dition] des fiches album). \n";
            $mail_body .= "Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle &eacute;dition via ce m&ecirc;me menu d&eacute;roulant.\n\n";
            $mail_body .= "Merci de votre compr&eacute;hension, \n";
            $mail_body .= "L'&eacute;quipe BDOVORE";
            $this->view->set_var(array(
                "SUJET_EMAIL" => $mail_sujet,
                "CORPS_EMAIL" => $mail_body
            ));

            if ($this->User_album_prop->ID_SERIE != 0) {
                $this->view->set_var(
                        "LIENEDITSERIE", "<a href='" . BDO_URL . "admin/editserie?serie_id=" . stripslashes($this->User_album_prop->ID_SERIE) . "'><img src='" . BDO_URL_IMAGE . "edit.gif' width='18' height='13' border='0'></a>"
                );
            } else {
                $this->view->set_var("LIENEDITSERIE", "");
            }
            // Détermine les albums ayant une syntaxe approchante
            $main_words = main_words(stripslashes($this->User_album_prop->TITRE));
            if (isset($main_words[1][0])) {
                $query = "
                        where
                                bd_tome.titre like '%" . Db_Escape_String($main_words[0][0]) . "%" . DB_Escape_String($main_words[1][0]) . "%'
                                or bd_tome.titre like '%" . DB_Escape_String($main_words[1][0]) . "%" . DB_Escape_String($main_words[0][0]) . "%'
                                LIMIT 0,30;";
            } else {
                $query = "
                       where
                                bd_tome.titre like '%" . DB_Escape_String($main_words[0][0]) . "%'
                        LIMIT 0,30;";
            }
            $this->loadModel("Tome");
            $dbs_tome = $this->Tome->load("c", $query);

            // on déclare le block à utiliser

            $this->view->set_var(array(
                "dbs_tome" => $dbs_tome
            ));

            // Récupère l'adresse mail de l'utilisateur

            $mail_adress = $this->User_album_prop->EMAIL;
            $pseudo = $this->User_album_prop->USERNAME;

            $this->view->set_var(array(
                "ADRESSEMAIL" => $mail_adress,
                "MAILSUBJECT" => "Votre proposition BDovore : " . $titre,
                "MEMBRE" => $pseudo
            ));

            // url suivant et précédent
            $this->User_album_prop->load("c", " WHERE
                    id_proposal <" . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'AJOUT'
            ORDER BY id_proposal desc limit 0,1");

            // URL précédent : proposition avec ID inférieur
            if ($this->User_album_prop->ID_PROPOSAL < $id) {
                $prev_url = BDO_URL . "adminproposition/editpropositionajout?ID=" . $this->User_album_prop->ID_PROPOSAL;
                $this->view->set_var("BOUTONPRECEDENT", "<a href='" . $prev_url . "'><input type='button' value='Précédent' /></a>");
            } else {
                $this->view->set_var("BOUTONPRECEDENT", "<del>Précédent</del>");
            }
            $this->User_album_prop->load("c", " WHERE
                    id_proposal > " . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'AJOUT'
            ORDER BY id_proposal asc limit 0,1
            ");
            // URL précédent : proposition avec ID supérieur
            if ($this->User_album_prop->ID_PROPOSAL > $id) {


                $next_url = BDO_URL . "adminproposition/editpropositionajout?ID=" . $this->User_album_prop->ID_PROPOSAL;
                $this->view->set_var("BOUTONSUIVANT", "<a href='" . $next_url . "'><input type='button' value='Suivant'></a>");
            } else {
                $this->view->set_var("BOUTONSUIVANT", "<del>Suivant</del>");
            }


            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function appendProposition() {
        if (User::minAccesslevel(1)) {

            $id = getValInteger("ID"); // id de la proposition
            $this->loadModel("User_album_prop");

            // Récupère l'utilisateur et l'image de couv
            $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
            $this->User_album_prop->load();
            $prop_user = $this->User_album_prop->USER_ID;
            $prop_img = $this->User_album_prop->IMG_COUV;
            $prop_action = $this->User_album_prop->ACTION;
            $notif_mail = $this->User_album_prop->NOTIF_MAIL;

            // On vérifie s'il s'agit d'une mise à jour simple ou d'une validation
            $check = postVal("chkUpdate", "N");
            $explicit = postVal("chkExplicite") == "checked" ? 1 : 0 ;
            if ($check == "O") {
                // simple mise à jour des données de la proposition
                $this->User_album_prop->set_dataPaste(array(
                    "SERIE" => postVal("txtSerie"),
                    "TITRE" => postVal("txtTitre"),
                    "FLG_TYPE" => postVal("lstType"),
                    "NUM_TOME" => postVal("txtNumTome"),
                    "FLG_INT" => ((postVal("chkIntegrale") == "checkbox") ? "O" : "N"),
                    "PRIX" => postVal("txtPrixVente"),
                    "HISTOIRE" => postVal("txtHistoire"),
                    "ID_GENRE" => postVal("txtGenreId"),
                    "GENRE" => postVal("txtGenre"),
                    "ID_SCENAR" => postVal("txtScenarId"),
                    "SCENAR" => postVal("txtScenar"),
                    "ID_SCENAR_ALT" => postVal("txtScenarAltId"),
                    "SCENAR_ALT" => postVal("txtScenarAlt"),
                    "ID_DESSIN" => postVal("txtDessiId"),
                    "DESSIN" => postVal("txtDessi"),
                    "ID_DESSIN_ALT" => postVal("txtDessiAltId"),
                    "DESSIN_ALT" => postVal("txtDessiAlt"),
                    "ID_COLOR" => postVal("txtColorId"),
                    "COLOR" => postVal("txtColor"),
                    "ID_COLOR_ALT" => postVal("txtColorAltId"),
                    "COLOR_ALT" => postVal("txtColorAlt"),
                    "ID_EDITEUR" => postVal("txtEditeurId"),
                    "EDITEUR" => postVal("txtEditeur"),
                    "ID_COLLECTION" => postVal("txtCollecId"),
                    "COLLECTION" => postVal("txtCollec"),
                    "ISBN" => postVal("txtISBN"),
                    "EAN" => postVal("txtEAN"),
                    "DTE_PARUTION" => postVal("txtDateParution"),
                    "FLG_TT" => ((postVal("chkTT") == "checkbox") ? "O" : "N"),
                    "DESCRIB_EDITION" => postVal("txtCommentEdition"),
                    "FLG_EXPLICIT" => $explicit
                ));
                $this->User_album_prop->update();
                if (issetNotEmpty($this->User_album_prop->error)) {
                    var_dump($this->User_album_prop->error);
                    exit();
                }
                // Retourne sur la page proposition
                header("Location:" . BDO_URL . "adminproposition/editPropositionAjout?ID=$id");
                exit();
            } else { // validation de la proposition
                // on crée l'album etc...
                // n'insère dans bd_tome que s'il s'agit d'une nouvelle édition
                $txtDateParution = completeDate(postVal('txtDateParution'));
                if (!validateDate($txtDateParution)) {
                     echo "Erreur : la date fournie est invalide et risque de g&eacute;n&eacute;rer un album fantome :D";
                     exit();
                }
                if (postVal('txtExistingTomeId', '') == '') {
                    // Récupère le genre de la série
                    $this->loadModel("Serie");
                    $this->Serie->set_dataPaste(array("ID_SERIE" => postValInteger('txtSerieId')));
                    $this->Serie->load();

                    $this->loadModel("Tome");

                    $this->Tome->set_dataPaste(array(
                        "TITRE" => postVal('txtTitre'),
                        "NUM_TOME" => postVal('txtNumTome'),
                        "ID_SERIE" => postValInteger('txtSerieId'),
                        "PRIX_BDNET" => postVal('txtPrixVente'),
                        "ID_GENRE" => $this->Serie->ID_GENRE,
                        "ID_SCENAR" => postValInteger('txtScenarId', 0),
                        "ID_SCENAR_ALT" => postValInteger('txtScenarAltId') ? postValInteger('txtScenarAltId') : '0',
                        "ID_DESSIN" => postValInteger('txtDessiId', 0),
                        "ID_DESSIN_ALT" => postValInteger('txtDessiAltId') ? postValInteger('txtDessiAltId') : '0',
                        "ID_COLOR" => postValInteger('txtColorId') ? postValInteger('txtColorId') : '0',
                        "ID_COLOR_ALT" => postValInteger('txtColorAltId') ? postValInteger('txtColorAltId') : '0',
                        "HISTOIRE" => postVal('txtHistoire', ''),
                        "FLG_INT" => ((postVal('chkIntegrale') == "checkbox") ? "O" : "N"),
                        "FLG_TYPE" => postVal('lstType')));

                    // Insère l'information dans la table bd_tome
                    $this->Tome->update();
                    if (issetNotEmpty($this->Tome->error)) {
                        var_dump($this->Tome->error);
                        exit();
                    }
                    echo "Album ajout&eacute; dans la table bd_tome<br />";

                    // récupère la valeur de la dernière insertion
                    $lid_tome = $this->Tome->ID_TOME;
                    $nouv_edition = "O";
                } else {
                    $lid_tome = postValInteger('txtExistingTomeId');
                    $nouv_edition = "N";
                }

                // insère un champ dans la table bd_edition
                $this->loadModel("Edition");
                $this->Edition->set_dataPaste(array(
                    "ID_TOME" => $lid_tome,
                    "ID_EDITEUR" => postValInteger('txtEditeurId'),
                    "ID_COLLECTION" => postValInteger('txtCollecId'),
                    "DTE_PARUTION" => $txtDateParution,
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "EAN" => postVal('txtEAN'),
                    "ISBN" => postVal('txtISBN'),
                    "COMMENT" => postVal('txtCommentEdition'),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s'),
                    "FLG_EXPLICIT" => (postVal("chkExplicit") == "checked" ? 1 : 0)
                ));
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    var_dump($this->Edition->error);
                    exit();
                }
                echo "Nouvelle &eacute;dition ins&eacute;r&eacute;e dans la table id_edition<br />";

                // récupère la valeur de la dernière insertion
                $lid_edition = $this->Edition->ID_EDITION;

                if ($nouv_edition == "O") {
                    // renseigne cette edition comme defaut pour bd_tome
                    $this->loadModel("Tome");
                    $this->Tome->set_dataPaste(array("ID_TOME" => $lid_tome));
                    $this->Tome->load();
                    $this->Tome->set_dataPaste(array("ID_EDITION" => $lid_edition));
                    $this->Tome->update();
                }

                // Verifie la présence d'une image à télécharger
                if ($_FILES['txtFileLoc']['size'] > 0 | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal("txtFileURL"), $url_ary))) {
                    if ($_FILES['txtFileLoc']['size'] > 0) { // un fichier à uploader
                        $img_couv = imgCouvFromForm($lid_tome, $lid_edition);
                    } else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal("txtFileURL"), $url_ary)) { // un fichier à télécharger
                        $img_couv = imgCouvFromUrl($url_ary, $lid_tome, $lid_edition);
                    } else {
                        $img_couv = '';
                    }

                    // met à jour la référence au fichier dans la table bd_edition
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                    $this->Edition->update();


                    echo "Nouvelle image ins&eacute;r&eactue;e dans la base<br />";
                } else {
                    // vérifie si une image a été proposée
                    if (($prop_img != '') && (postVal('chkDelete') != 'checked')) {// copie l'image dans les couvertures
                        $newfilename = "CV-" . sprintf("%06d", $lid_tome) . "-" . sprintf("%06d", $lid_edition);
                        $strLen = strlen($prop_img);
                        $newfilename .= substr($prop_img, $strLen - 4, $strLen);
                        @copy(BDO_DIR_UPLOAD . $prop_img, BDO_DIR_COUV . $newfilename);
                        @unlink(BDO_DIR_UPLOAD . $prop_img);

                        // met à jour la référence au fichier dans la table bd_edition
                        $this->Edition->set_dataPaste(array("IMG_COUV" => $newfilename));
                        $this->Edition->update();


                        echo "Image propos&eacute;e ins&eacute;r&eacute;e dans la base<br />";
                    }
                }

                // On rajoute un redimensionnement si le correcteur l'a voulu

                if (postVal("chkResize") == "checked") {

                    //Redimensionnement
                    //*****************

                    $max_size = 360;
                    $imageproperties = getimagesize(BDO_DIR_COUV . $newfilename);
                    if ($imageproperties != false) {
                        $imagetype = $imageproperties[2];
                        $imagelargeur = $imageproperties[0];
                        $imagehauteur = $imageproperties[1];

                        //Détermine s'il y a lieu de redimensionner l'image
                        if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $maxsize)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

                            if ($imagelargeur < $imagehauteur) {
                                // image de type panorama : on limite la largeur à 128
                                $new_w = $max_size;
                                $new_h = round($imagehauteur * $max_size / $imagelargeur);
                            } else {
                                // image de type portrait : on limite la hauteur au maxi
                                $new_h = $max_size;
                                $new_w = round($imagelargeur * $max_size / $imagehauteur);
                            }
                        } else {
                            $new_h = $imagehauteur;
                            $new_w = $imagelargeur;
                        }

                        $new_image = imagecreatetruecolor($new_w, $new_h);
                        switch ($imagetype) {
                            case "1":
                                $source = imagecreatefromgif(BDO_DIR_COUV . $newfilename);
                                break;

                            case "2":
                                $source = imagecreatefromjpeg(BDO_DIR_COUV . $newfilename);
                                break;

                            case "3":
                                $source = imagecreatefrompng(BDO_DIR_COUV . $newfilename);
                                break;

                            case "6":
                                $source = imagecreatefrombmp(BDO_DIR_COUV . $newfilename);
                                break;
                        }

                        imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

                        switch ($imagetype) {
                            case "2":
                                unlink(BDO_DIR_COUV . $newfilename);
                                imagejpeg($new_image, BDO_DIR_COUV . $newfilename, 100);
                                break;

                            case "1":
                            case "3":
                            case "6":
                                unlink(BDO_DIR_COUV . $newfilename);
                                $img_couv = substr($newfilename, 0, strlen($newfilename) - 3) . "jpg";
                                imagejpeg($new_image, BDO_DIR_COUV . $img_couv, 100);

                                // met à jour la référence au fichier dans la table bd_edition
                                $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                                $this->Edition->update();
                        }
                    }

                    echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
                    echo "Image redimensionn&eacute;e<br />";
                }
                // Ajoute l'album à la collection de l'utilisateur
                if ($prop_action != 2) {
                    $this->loadModel("Useralbum");
                    $this->Useralbum->set_dataPaste(array(
                        "user_id" => $prop_user,
                        "date_ajout" => date('d/m/Y H:i:s'),
                        "flg_achat" => ($prop_action == 1 ? 'O' : 'N'),
                        "id_edition" => $lid_edition
                    ));
                    $this->Useralbum->update();
                    if (issetNotEmpty($this->Useralbum->error)) {
                        var_dump($this->Useralbum->error);
                        exit();
                    }
                    echo "Album ajout&eacute; dans la collection de l'utilisateur<br />";
                }

                //Efface le fichier de la base et passe le status de l'album à validé
                if ($prop_img != '') {
                    if (file_exists(BDO_DIR_UPLOAD . $prop_img)) {
                        @unlink(BDO_DIR_UPLOAD . $prop_img);
                    }
                }
                $this->User_album_prop->set_dataPaste(array("STATUS" => 1, "VALIDATOR" => $_SESSION["userConnect"]->user_id));
                $this->User_album_prop->update();

                // Envoie un mail si nécessaire pour prévenir l'utilisateur
                if ($notif_mail == 1) {
                    $mail_action[0] = "L'album a &eacute;t&eacute; ajout&eacute; &agrave; votre collection, comme demand&eacute;.\n\n";
                    $mail_action[1] = "L'album a &eacute;t&eacute; ajout&eacute; dans vos achats futurs, comme demand&eacute;.\n\n";
                    $mail_action[2] = "L'album n'a pas &eacute;t&eacute; ajout&eacute; &agrave; votre collection, comme demand&eacute;.";


                    $mail_adress = $this->User_album_prop->EMAIL;
                    $mail_sujet = "Ajout d'un album dans la base BDOVORE";
                    $mail_entete = "From: no-reply@bdovore.com";
                    $mail_text = "Bonjour, \n\n";
                    $mail_text .="Votre proposition d'ajout &agrave; la base de donn&eacute;es de BDOVORE a &eacute;t&eacute; valid&eacute;e.\n\n";
                    $mail_text .="Titre : " . postVal('txtTitre') . "\n";
                    $mail_text .=$mail_action[$prop_action];
                    $mail_text .="Merci pour votre participation\n\n";
                    $mail_text .="L'&eacute;quipe BDOVORE";
                    mail($mail_adress, $mail_sujet, $mail_text, $mail_entete);
                    echo "Email de confirmation envoy&eacute;<br />";
                }

                $this->User_album_prop->load("c", " WHERE
                    id_proposal > " . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'AJOUT'
                    ORDER BY id_proposal ASC LIMIT 0,1
                    ");

                if ($this->User_album_prop->ID_PROPOSAL > $id) {
                    $next_url = BDO_URL . "adminproposition/editPropositionAjout?ID=" . $this->User_album_prop->ID_PROPOSAL;
                } else {
                    $next_url = BDO_URL . "admin/editAlbum?alb_id=" . $lid_tome;
                }

                echo GetMetaTag(1, "L'album a &eacute;t&eacute; ajout&eacute;", $next_url);
            }
        }
    }

    public function mergeProposition() {
        $idtome = postValInteger('txtFutAlbId');
        $id = getValInteger("ID"); // id de la proposition
        // Récupère l'utilisateur et l'image de couv
        $this->loadModel("User_album_prop");
        $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
        $this->User_album_prop->load();
        $prop_user = $this->User_album_prop->USER_ID;

        $prop_action = $this->User_album_prop->ACTION;
        $notif_mail = $this->User_album_prop->NOTIF_MAIL;


        // Ajoute l'album existant &agrave; la collection ou aux futurs achats de l'utilisateur
        // Vérifie la présence de l'album existant dans la collection de l'utilisateur
        $this->loadModel("Useralbum");
        $this->Useralbum->load("c", " WHERE ua.user_id = " . $prop_user . " and bd_tome.ID_TOME =" . $idtome);

        if ($this->Useralbum->dbSelect->nbLineResult > 0) {
            echo GetMetaTag(1, "Cet album est d&eacute;j&agrave; pr&eacute;sent dans la collection de l'utilisateur", BDO_URL . "admin/adminproposals.php?act=valid&propid=" . $propid);
            exit();
        } else { // Ajoute l'album
            // on récupère l'id édition par défaut
            $this->loadModel("Tome");
            $this->Tome->set_dataPaste(array("ID_TOME" => $idtome));
            $this->Tome->load();


            // Assigne les variables
            $titre = stripslashes($this->Tome->TITRE_TOME);
            $id_edition = $this->Tome->ID_EDITION;

            $this->Useralbum->set_dataPaste(array(
                "user_id" => $prop_user,
                "date_ajout" => date('d/m/Y H:i:s'),
                "flg_achat" => ($prop_action == 1 ? 'O' : 'N'),
                "id_edition" => $id_edition));
            $this->Useralbum->update();
            if (issetNotEmpty($this->Useralbum->error)) {
                var_dump($this->Useralbum->error);
                exit();
            }
            echo "L'album s&eacute;lectionn&eacute; a &eacute;t&eacute; ajout&eacute; &agrave; la collection de l'utilisateur<br />";

            // Archive la proposition
            $this->User_album_prop->set_dataPaste(array(
                "STATUS" => 99,
                "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                "VALID_DTE" => date('d/m/Y H:i:s')
            ));
            $this->User_album_prop->update();
            if (issetNotEmpty($this->User_album_prop->error)) {
                var_dump($this->User_album_prop->error);
                exit();
            }
            // Envoie un mail si nécessaire pour prévenir l'utilisateur
            if ($notif_mail == 1) {
                $mail_action[0] = "Cet album a &eacute;t&eacute; plac&eacute; dans votre collection, comme demand&eacute;.\n\n";
                $mail_action[1] = "Cet album a &eacute;t&eacute; plac&eacute; dans vos achats futurs, comme demand&eacute;.\n\n";

                // Récupère l'adresse du posteur et compose l'email

                $mail_adress = $this->User_album_prop->EMAIL;
                $mail_sujet = "Ajout d'un album dans la base BDOVORE";
                $mail_entete = "From: no-reply@bdovore.com";
                $mail_text = "Bonjour, \n\n";
                $mail_text .="Proposition : " . postVal('txtTitre') . "\n";
                $mail_text .= "Votre proposition d'ajout &agrave; la base de donn&eacute;es n'a pas &eacute;t&eacute; accept&eacute;e car l'album en question y figurait d&eacute;j&agrave;. \n";
                $mail_text .=$mail_action[$prop_action];
                $mail_text .= "Si l'&eacute;dition par d&eacute;faut de cet album ne correspond pas &agrave; celle que vous poss&eacute;dez,
                            d'autres &eacute;ditions sont peut-&ecirc;tre d&eacute;j&agrave; pr&eacute;sentes dans la base et peuvent &ecirc;tre
                            s&eacute;lectionn&eacute;es en cliquant sur l'album en question depuis votre garde-manger (menu d&eacute;roulant [Mon &eacute;dition]
                            des fiches album). Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle &eacute;dition via ce m&ecirc;me
                            menu d&eacute;roulant.\n\n";
                $mail_text .="L'&eacute;quipe BDOVORE";
                mail($mail_adress, $mail_sujet, $mail_text, $mail_entete);
                echo "Email d'information envoy&eacute; &agrave; l'utilisateur<br />";
            }

            // Prépare la redirection vers la proposition suivante
            $this->User_album_prop->load("c", " WHERE
                    id_proposal > " . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'AJOUT'
                    ORDER BY id_proposal asc limit 0,1
                    ");

            if ($this->User_album_prop->ID_PROPOSAL > $id) {
                $next_url = BDO_URL . "adminproposition/editProposition?ID=" . $this->User_album_prop->ID_PROPOSAL;
            } else {
                $next_url = BDO_URL . "admin/editAlbum?alb_id=" . $lid_tome;
            }
        }
        echo GetMetaTag(1, "Bien jou&eacute; !", $next_url);
    }

    public function commentProposition() {
        if (User::minAccesslevel(1)) {
            $comment = postVal("txtCommentCorr");
            $id = getValInteger("ID");
            $status = postVal("cmbStatus");

            // Met à jour la case commentaire

            $this->loadModel("User_album_prop");
            $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
            $this->User_album_prop->load();

            $this->User_album_prop->set_dataPaste(array(
                "CORR_COMMENT" => $comment,
                "STATUS" => $status,
                "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                "VALID_DTE" => date('d/m/Y H:i:s')
            ));
            $this->User_album_prop->update();
            if (issetNotEmpty($this->User_album_prop->error)) {
                var_dump($this->User_album_prop->error);
                exit();
            }
            // Retourne sur la page proposition
            header("Location:" . BDO_URL . "adminproposition/editPropositionAjout?ID=$id");
            exit();
        }
    }

    public function editPropositionCorrection() {
        /*
         * Edition d'une proposition de correction d'un album
         * Permet ensuite les actions de validation
         */
        $this->view->set_var(array("PAGETITLE" => "Proposition : correction"));
        // Tableau pour les choix d'options
        // Avancement de la série
        $opt_status[0][0] = 0;
        $opt_status[0][1] = 'Finie';
        $opt_status[1][0] = 1;
        $opt_status[1][1] = 'En cours';
        $opt_status[2][0] = 2;
        $opt_status[2][1] = 'One Shot';
        $opt_status[3][0] = 3;
        $opt_status[3][1] = 'Interrompue/Abandonnée';

        // Type d'album
        $opt_type[0][0] = 0;
        $opt_type[0][1] = 'Album';
        $opt_type[1][0] = 1;
        $opt_type[1][1] = 'Coffret';
        if (User::minAccesslevel(1)) {
            $id = getValInteger("ID");
            $this->loadModel("User_album_prop");
            $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
            // chargement des données complètes
            $this->User_album_prop->setWithAlbumInfo($bool = true);
            $this->User_album_prop->load();


            $titre = stripslashes($this->User_album_prop->TITRE);

            $this->view->set_var(array(
                "PROPID" => $this->User_album_prop->ID_PROPOSAL,
                "TITRE" => $this->User_album_prop->TITRE,
                "CLTITRE" => ($this->User_album_prop->TITRE) != '' ? "flat" : "to_be_corrected",
                "IDSERIE" => $this->User_album_prop->ID_SERIE,
                "CLIDSERIE" => (is_numeric($this->User_album_prop->ID_SERIE) & ($this->User_album_prop->SERIE == $this->User_album_prop->ACTUSERIE)) ? "flat" : "to_be_corrected",
                "TOME" => $this->User_album_prop->NUM_TOME,
                "IDGENRE" => $this->User_album_prop->ID_GENRE,
                "CLIDGENRE" => (is_numeric($this->User_album_prop->ID_GENRE) & ($this->User_album_prop->GENRE == $this->User_album_prop->ACTUGENRE) ? "flat" : "to_be_corrected"),
                "IDSCEN" => $this->User_album_prop->ID_SCENAR,
                "CLIDSCEN" => (is_numeric($this->User_album_prop->ID_SCENAR) & ($this->User_album_prop->PSEUDO_SCENAR == $this->User_album_prop->SCENAR) ? "flat" : "to_be_corrected"),
                "IDSCENALT" => $this->User_album_prop->ID_SCENAR_ALT,
                "CLIDSCENALT" => (is_numeric($this->User_album_prop->ID_SCENAR_ALT) & ($this->User_album_prop->PSEUDO_SCENAR_ALT == $this->User_album_prop->SCENAR_ALT) ? "flat" : "to_be_corrected"),
                "IDEDIT" => $this->User_album_prop->ID_EDITEUR,
                "CLIDEDIT" => (is_numeric($this->User_album_prop->ID_EDITEUR) & ($this->User_album_prop->ACTUEDITEUR == $this->User_album_prop->EDITEUR) ? "flat" : "to_be_corrected"),
                "IDDESS" => $this->User_album_prop->ID_DESSIN,
                "CLIDDESS" => (is_numeric($this->User_album_prop->ID_DESSIN) & ($this->User_album_prop->PSEUDO_DESSIN == $this->User_album_prop->DESSIN) ? "flat" : "to_be_corrected"),
                "IDDESSALT" => $this->User_album_prop->ID_DESSIN_ALT,
                "CLIDDESSALT" => (is_numeric($this->User_album_prop->ID_DESSIN_ALT) & ($this->User_album_prop->PSEUDO_DESSIN_ALT == $this->User_album_prop->DESSIN_ALT) ? "flat" : "to_be_corrected"),
                "IDCOLOR" => $this->User_album_prop->ID_COLOR,
                "CLIDCOLOR" => (is_numeric($this->User_album_prop->ID_COLOR) & ($this->User_album_prop->PSEUDO_COLOR == $this->User_album_prop->COLOR) ? "flat" : "to_be_corrected"),
                "IDCOLLEC" => $this->User_album_prop->ID_COLLECTION,
                "CLIDCOLLEC" => (is_numeric($this->User_album_prop->ID_COLLECTION) & ($this->User_album_prop->ACTUCOLLECTION == $this->User_album_prop->COLLECTION) ? "flat" : "to_be_corrected"),
                "EAN" => $this->User_album_prop->EAN,
                "ISBN" => $this->User_album_prop->ISBN,
                "DTPAR" => $this->User_album_prop->DTE_PARUTION,
                "HISTOIRE" => stripslashes($this->User_album_prop->HISTOIRE),
                "USERCOMMENT" => stripslashes($this->User_album_prop->COMMENTAIRE),
                "SERIE" => stripslashes($this->User_album_prop->SERIE),
                "GENRE" => $this->User_album_prop->GENRE,
                "SCENARISTE" => stripslashes($this->User_album_prop->SCENAR),
                "SCENARISTEALT" => $this->User_album_prop->SCENAR_ALT,
                "DESSINATEUR" => stripslashes($this->User_album_prop->DESSIN),
                "DESSINATEURALT" => stripslashes($this->User_album_prop->DESSIN_ALT),
                "COLORISTE" => stripslashes($this->User_album_prop->COLOR),
                "EDITEUR" => stripslashes($this->User_album_prop->EDITEUR),
                "COLLECTION" => $this->User_album_prop->COLLECTION,
                "OPTSTATUS" => GetOptionValue($opt_status, $this->User_album_prop->FLG_FINI),
                "OPTTYPE" => GetOptionValue($opt_type, $this->User_album_prop->FLG_TYPE),
                "ISINT" => (($this->User_album_prop->FLG_INT == 'O') ? 'checked' : ''),
                "ACTIONNAME" => "Valider",
                "URLACTION" => BDO_URL . "adminproposition/updatecorrection?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLDELETE" => BDO_URL . "adminproposition/deleteProposition?ID=" . $this->User_album_prop->ID_PROPOSAL
            ));
            if ($this->User_album_prop->ID_SERIE != 0) {
                $this->view->set_var(
                        "LIENEDITNEWSERIE", "<a href='" . BDO_URL . "admin/editserie?serie_id=" . stripslashes($this->User_album_prop->ID_SERIE) . "'><img src='" . BDO_URL_IMAGE . "edit.gif' width='18' height='13' border='0'></a>"
                );
            }

            $alb_id = $this->User_album_prop->ID_TOME;
            $edition_id = $this->User_album_prop->ID_EDITION;
            $user_id = $this->User_album_prop->USER_ID;

            $this->loadModel("Useralbum");
            // Determine le statut de l'utilisateur par rapport à l'album qu'il corrige

            $this->Useralbum->load("c", " WHERE ua.USER_ID= " . $user_id . " and bd_tome.ID_TOME =" . $alb_id);

            if ($this->Useralbum->dbSelect->nbLineResult == 0) {
                $user_owns = 'L\'utilisateur <strong>ne poss&egrave;de pas</strong> cet album.';
            } else {
                $user_owns = 'L\'utilisateur <strong>poss&egrave;de</strong> cet album dans sa collection.';

                $user_edition = $this->Useralbum->ID_EDITION;
            }
            $this->loadModel("Tome");
            $this->loadModel("Edition");
            $this->Tome->set_dataPaste(array("ID_TOME" => $alb_id));
            $this->Tome->load();
            // Récupère l'édition définie par défaut

            $def_edition = $this->Tome->ID_EDITION;

            // Récupère l'info actuelle
            if ($edition_id == 0) {
                // édition par défaut
                // Determine l'URL image courante
                if (is_null($this->Tome->IMG_COUV) | ($this->Tome->IMG_COUV == '')) {
                    $ori_url_image = BDO_URL_COUV . "default.png";
                } else {
                    $ori_url_image = BDO_URL_COUV . $this->Tome->IMG_COUV;
                    $ori_dim_image = imgdim(BDO_DIR_COUV . $this->Tome->IMG_COUV);
                }
            } else {
                // force l'édition
                $this->Edition->set_dataPaste(array("ID_EDITION" => $edition_id));
                $this->Edition->load();
                // Determine l'URL image courante
                if (is_null($this->Edition->IMG_COUV) | ($this->Edition->IMG_COUV == '')) {
                    $ori_url_image = BDO_URL_COUV . "default.png";
                } else {
                    $ori_url_image = BDO_URL_COUV . $this->Edition->IMG_COUV;
                    $ori_dim_image = imgdim(BDO_DIR_COUV . $this->Edition->IMG_COUV);
                }
            }


            // Determine l'URL image modifiée
            if (is_null($this->User_album_prop->IMG_COUV) | ($this->User_album_prop->IMG_COUV == '')) {
                $url_image = $ori_url_image;
            } else {
                $url_image = BDO_URL_IMAGE . "tmp" . DS . $this->User_album_prop->IMG_COUV;
                $dim_image = imgdim(BDO_DIR_UPLOAD . $this->User_album_prop->IMG_COUV);
            }

            // Détermine la nature de la correction
            if ($edition_id == 0) {
                $has_edition = 'La correction porte sur <strong>toutes</strong> les &eacute;ditions.';
            } elseif ($edition_id == $user_edition) {
                $has_edition = 'La correction porte sur l\'&eacute;dition qu\'il poss&egrave;de.';
            } else {
                $has_edition = 'La correction porte sur une &eacute;dition qu\'il <b>ne poss&egrave;de pas</b>.';
            }

            // Détermine s'il s'agit de l'édition par défaut
            if (($edition_id == $def_edition) | ($edition_id == 0)) {
                $is_def_edition = '<b>L\'&eacute;dition utilis&eacute;e par d&eacute;faut va &ecirc;tre modifi&eacute;e.</b>';
            } else {
                $is_def_edition = 'L\'&eacute;dition utilis&eacute;e par d&eacute;faut ne sera pas modifi&eacute;e.';
            }

            // Récupère les données actuelles
            $this->view->set_var(array(
                "ORITITRE" => stripslashes($this->Tome->TITRE_TOME),
                "CLTITRE" => ($this->User_album_prop->TITRE == $this->Tome->TITRE_TOME ? "flat" : "has_changed"),
                "ORISERIE" => stripslashes($this->Tome->NOM_SERIE),
                "CLSERIE" => ($this->User_album_prop->SERIE == $this->Tome->NOM_SERIE ? "flat" : "has_changed"),
                "ORISERIEFINI" => ($this->Tome->FLG_FINI != '') ? $opt_status[$this->Tome->FLG_FINI][1] : '',
                "NEW_FLG_FINI" => ($this->User_album_prop->FLG_FINI == $this->Tome->FLG_FINI ? "" : "*"),
                "ORITOME" => $this->Tome->NUM_TOME,
                "CLTOME" => ($this->User_album_prop->NUM_TOME == $this->Tome->NUM_TOME ? "flat" : "has_changed"),
                "NEW_FLG_INT" => ($this->User_album_prop->FLG_INT == $this->Tome->FLG_INT ? "" : "*"),
                "NEW_FLG_TYPE" => ($this->User_album_prop->FLG_TYPE == $this->Tome->FLG_TYPE ? "" : "*"),
                "ORIGENRE" => $this->Tome->NOM_GENRE,
                "CLGENRE" => ($this->User_album_prop->GENRE == $this->Tome->NOM_GENRE ? "flat" : "has_changed"),
                "ORISCENARISTE" => stripslashes($this->Tome->scpseudo),
                "CLSCENARISTE" => ($this->User_album_prop->SCENAR == $this->Tome->scpseudo ? "flat" : "has_changed"),
                "ORISCENARISTEALT" => stripslashes($this->Tome->scapseudo),
                "CLSCENARISTEALT" => ($this->User_album_prop->SCENAR_ALT == $this->Tome->scapseudo ? "flat" : "has_changed"),
                "ORIEDITEUR" => stripslashes($this->Edition->NOM_EDITEUR),
                "CLEDITEUR" => ($this->User_album_prop->EDITEUR == $this->Edition->NOM_EDITEUR ? "flat" : "has_changed"),
                "ORIDESSINATEUR" => stripslashes($this->Tome->depseudo),
                "CLDESSINATEUR" => ($this->User_album_prop->DESSIN == $this->Tome->depseudo ? "flat" : "has_changed"),
                "ORIDESSINATEURALT" => stripslashes($this->Tome->deapseudo),
                "CLDESSINATEURALT" => ($this->User_album_prop->DESSIN_ALT == $this->Tome->deapseudo ? "flat" : "has_changed"),
                "ORICOLORISTE" => stripslashes(($this->Tome->copseudo)),
                "CLCOLORISTE" => ($this->User_album_prop->COLOR == $this->Tome->copseudo ? "flat" : "has_changed"),
                "ORICOLLECTION" => ($this->Edition->NOM_COLLECTION),
                "CLCOLLECTION" => ($this->User_album_prop->COLLECTION == $this->Edition->NOM_COLLECTION ? "flat" : "has_changed"),
                "ORIEAN" => ($this->Edition->EAN_EDITION == "") ? "&nbsp;" : $this->Edition->EAN_EDITION,
                "CLEAN" => ($this->User_album_prop->EAN == $this->Edition->EAN_EDITION ? "flat" : "has_changed"),
                "ORIISBN" => ($this->Tome->ISBN_EDITION == "") ? "&nbsp;" : $this->Edition->ISBN_EDITION,
                "CLISBN" => ($this->User_album_prop->ISBN == $this->Edition->ISBN_EDITION ? "flat" : "has_changed"),
                "ORIDTPAR" => $this->Edition->DTE_PARUTION,
                "CLDTPAR" => ($this->User_album_prop->DTE_PARUTION == $this->Edition->DTE_PARUTION ? "flat" : "has_changed"),
                "ORIHISTOIRE" => stripslashes($this->Edition->HISTOIRE),
                "CLHISTOIRE" => ($this->User_album_prop->HISTOIRE == $this->Edition->HISTOIRE ? "flat" : "has_changed"),
                "URLIMAGE" => $url_image,
                "URLORIIMAGE" => $ori_url_image,
                "DIMIMAGE" => $dim_image,
                "ORIDIMIMAGE" => $ori_dim_image,
                "USERHASEDITION" => $has_edition,
                "MODIFONDEFAULT" => $is_def_edition,
                "DEFEDITIONID" => $def_edition,
                "USEROWN" => $user_owns
            ));

            // Récupère l'adresse mail de l'utilisateur
            $mail_adress = $this->User_album_prop->EMAIL;
            $pseudo = $this->User_album_prop->USERNAME;
            $nom_album = $this->User_album_prop->TITRE;

            $this->view->set_var(array(
                "ADRESSEMAIL" => $mail_adress,
                "MAILSUBJECT" => "Votre proposition BDovore : " . $nom_album,
                "MEMBRE" => $pseudo
            ));

            // url suivant et précédent
            $this->User_album_prop->load("c", " WHERE
                    id_proposal <" . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'CORRECTION'
            ORDER BY id_proposal desc limit 0,1");

            // URL précédent : proposition avec ID inférieur
            if ($this->User_album_prop->ID_PROPOSAL < $id) {
                $prev_url = BDO_URL . "adminproposition/editpropositioncorrection?ID=" . $this->User_album_prop->ID_PROPOSAL;
                $this->view->set_var("BOUTONPRECEDENT", "<a href='" . $prev_url . "'><input type='button' value='Précédent' /></a>");
            } else {
                $this->view->set_var("BOUTONPRECEDENT", "<del>Précédent</del>");
            }
            $this->User_album_prop->load("c", " WHERE
                    id_proposal > " . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'CORRECTION'
            ORDER BY id_proposal asc limit 0,1
            ");
            // URL précédent : proposition avec ID supérieur
            if ($this->User_album_prop->ID_PROPOSAL > $id) {


                $next_url = BDO_URL . "adminproposition/editpropositioncorrection?ID=" . $this->User_album_prop->ID_PROPOSAL;
                $this->view->set_var("BOUTONSUIVANT", "<a href='" . $next_url . "'><input type='button' value='Suivant'></a>");
            } else {
                $this->view->set_var("BOUTONSUIVANT", "<del>Suivant</del>");
            }

            $this->view->set_var('PAGETITLE', "Validation d'une correction ");
            $this->view->render();
        }
    }

    public function updateCorrection() {
        /*
         * Fonction de validation d'une proposition de correction d'un album
         */
        // Récupère l'utilisateur et l'image de couv
        if (User::minAccesslevel(1)) {
            $id = getValInteger("ID"); // id de la proposition
            $this->loadModel("User_album_prop");

            // Récupère l'utilisateur et l'image de couv
            $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
            $this->User_album_prop->load();
            $prop_user = $this->User_album_prop->USER_ID;
            $prop_img = $this->User_album_prop->IMG_COUV;
            $prop_action = $this->User_album_prop->IMG_COUV;
            $notif_mail = $this->User_album_prop->NOTIF_MAIL;

            $prop_img = $this->User_album_prop->IMG_COUV;
            $lid = $this->User_album_prop->ID_TOME;
            $edition = $this->User_album_prop->ID_EDITION;
            $def_edition = postValInteger('txtDefEdition');

            // Met à jour l'information propre à l'album
            // Dans la base bd_tome
            $this->loadModel("Tome");
            $this->Tome->set_dataPaste(array("ID_TOME" => $lid));
            $this->Tome->load();
            $this->Tome->set_dataPaste(array(
                "TITRE" => postVal("txtTitre"),
                "NUM_TOME" => postVal("txtNumTome"),
                "FLG_INT" => (postVal("chkIntegrale") == "checkbox" ) ? "O" : "N",
                "FLG_TYPE" => postVal("lstType"),
                "ID_SERIE" => postVal("txtSerieId"),
                "ID_GENRE" => postVal("txtGenreId"),
                "ID_SCENAR" => postVal("txtScenarId") == "" ? "0" : postVal("txtScenarId"),
                "ID_SCENAR_ALT" => postVal("txtScenarAltId") == "" ? "0" : postVal("txtScenarAltId"),
                "ID_DESSIN" => postVal("txtDessiId") == "" ? "0" : postVal("txtDessiId"),
                "ID_DESSIN_ALT" => postVal("txtDessiAltId") == "" ? "0" : postVal("txtDessiAltId"),
                "ID_COLOR" => postVal("txtColorId") == "" ? "0" : postVal("txtColorId"),
                "ID_COLOR_ALT" => postVal("txtColorAltId") == "" ? "0" : postVal("txtColorAltId"),
                "HISTOIRE" => postVal("txtHistoire")
            ));
            $this->Tome->update();
            if (issetNotEmpty($this->Tome->error)) {
                var_dump($this->Tome->error);
                exit();
            }
            echo 'Info album : base bd_tome mise a jour.<br />';


            // Met à jour les informations série dans la table bd_tome
            $this->Tome->updateGenreForSerie(postVal("txtSerieId"), postVal("txtGenreId"));
            echo 'Info s&eacute;rie : base bd_tome mise a jour.<br>';

            $this->loadModel("Serie");
            $this->Serie->set_dataPaste(array("ID_SERIE" => postVal("txtSerieId")));
            $this->Serie->load();
            $this->Serie->set_dataPaste(array(
                "NOM" => postVal('txtSerie'),
                "ID_GENRE" => postVal("txtGenreId"),
                "FLG_FINI" => postVal("lstStatus")
            ));
            // Enfin, met à jour la table série
            $this->Serie->update();
            if (issetNotEmpty($this->Serie->error)) {
                var_dump($this->Serie->error);
                exit();
            }
            echo 'Info s&eacute;rie : base bd_serie mise a jour.<br />';

            // copie l'image dans les couvertures
            if (($prop_img != '') && (postVal('chkDelete') != 'checked') && $edition != 0) {
                $newfilename = "CV-" . sprintf("%06d", $lid) . "-" . sprintf("%06d", $edition);
                $strLen = strlen($prop_img);
                $newfilename .= substr($prop_img, $strLen - 4, $strLen); //file extension
                @copy(BDO_DIR_UPLOAD . "$prop_img", BDO_DIR_COUV . "$newfilename");
                @unlink(BDO_DIR_UPLOAD . "$prop_img");
            }

            if (postVal('chkModifEdition') != 'checked') {
                $this->loadModel("Edition");
                // Mise à jour de la table bd_edition
                if ($edition == 0) {
                    // Mise à jour de la table bd_edition

                    /*
                      $query = "UPDATE bd_edition SET ";
                      $query .= "`id_editeur` = ".$DB->escape(postVal('txtEditeurId']).", ";
                      $query .= "`id_collection` = ".$DB->escape(postVal('txtCollecId']).", ";
                      $query .= "`ean` = ".(postVal('txtEAN']=='' ? "NULL" :  "'".$DB->escape(postVal('txtEAN']). "'").", ";
                      $query .= "`isbn` = ".(postVal('txtISBN']=='' ? "NULL" :  "'".$DB->escape(postVal('txtISBN']). "'").", ";
                      $query .=" WHERE (`id_tome`=".$lid.");";
                      $DB->query($query);
                      echo 'Info édition : base bd_edition mise à jour.<br />';
                     *
                     */
                } else {
                    // Mise à jour de la table bd_edition
                    $this->Edition;
                    $this->Edition->set_dataPaste(array("ID_EDITION" => $edition));
                    $this->Edition->load();
                    $this->Edition->set_dataPaste(array(
                        "ID_EDITEUR" => postVal('txtEditeurId'),
                        "ID_COLLECTION" => postVal('txtCollecId'),
                        "EAN" => postVal('txtEAN'),
                        "ISBN" => postVal('txtISBN'),
                        "DTE_PARUTION" => postVal('txtDateParution')
                    ));

                    // vérifie si une image a été proposée
                    if (($prop_img != '') && (postVal('chkDelete') != 'checked')) {
                        $this->Edition->set_dataPaste(array("IMG_COUV" => $newfilename));
                    }
                    $this->Edition->update();
                    if (issetNotEmpty($this->Edition->error)) {
                        var_dump($this->Edition->error);
                        exit();
                    }
                    echo 'Info &eacute;dition : base bd_edition mise &agrave; jour.<br>';
                }
            }

            //Efface le fichier de la base et passe le status de l'album à valider
            if ($prop_img != '') {
                if (file_exists(BDO_DIR_UPLOAD . $prop_img)) {
                    @unlink(BDO_DIR_UPLOAD . $prop_img);
                }
            }

            if (postVal("chkResize") == "checked" && $edition != 0) {

                //Redimensionnement
                //*****************

                $max_size = 360;
                $imageproperties = getimagesize(BDO_DIR_COUV . "$newfilename");
                if ($imageproperties != false) {
                    $imagetype = $imageproperties[2];
                    $imagelargeur = $imageproperties[0];
                    $imagehauteur = $imageproperties[1];

                    //Détermine s'il y a lieu de redimensionner l'image
                    if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $maxsize)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

                        if ($imagelargeur < $imagehauteur) {
                            // image de type panorama : on limite la largeur à 128
                            $new_w = $max_size;
                            $new_h = round($imagehauteur * $max_size / $imagelargeur);
                        } else {
                            // imahe de type portrait : on limite la hauteur au maxi
                            $new_h = $max_size;
                            $new_w = round($imagelargeur * $max_size / $imagehauteur);
                        }
                    } else {
                        $new_h = $imagehauteur;
                        $new_w = $imagelargeur;
                    }

                    $new_image = imagecreatetruecolor($new_w, $new_h);
                    switch ($imagetype) {
                        case "1":
                            $source = imagecreatefromgif(BDO_DIR_COUV . "$newfilename");
                            break;

                        case "2":
                            $source = imagecreatefromjpeg(BDO_DIR_COUV . "$newfilename");
                            break;

                        case "3":
                            $source = imagecreatefrompng(BDO_DIR_COUV . "$newfilename");
                            break;

                        case "6":
                            $source = imagecreatefrombmp(BDO_DIR_COUV . "$newfilename");
                            break;
                    }

                    imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

                    switch ($imagetype) {
                        case "2":
                            unlink(BDO_DIR_COUV . "$newfilename");
                            imagejpeg($new_image, BDO_DIR_COUV . "$newfilename", 100);
                            break;

                        case "1":
                        case "3":
                        case "6":
                            unlink(BDO_DIR_COUV . "$newfilename");
                            $img_couv = substr($newfilename, 0, strlen($newfilename) - 3) . "jpg";
                            imagejpeg($new_image, BDO_DIR_COUV . "$img_couv", 100);

                            // met à jours la référence au fichier dans la table bd_edition
                            $this->Edition;
                            $this->Edition->set_dataPaste(array("ID_EDITION" => $edition));
                            $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                            $this->Edition->update();
                    }
                }

                echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
                echo "Image redimensionnée<br />";
            }


            $this->User_album_prop->set_dataPaste(array("STATUS" => 1, "VALIDATOR" => $_SESSION["userConnect"]->user_id));
            $this->User_album_prop->update();

            $this->User_album_prop->load("c", " WHERE
                    id_proposal > " . $id . "
                    AND status not in (98,99,1)
                    AND prop_type = 'CORRECTION'
            ORDER BY id_proposal asc limit 0,1
            ");

            if ($this->User_album_prop->ID_PROPOSAL > $id) {

                $next_url = BDO_URL . "admin/editPropositionCorrection?ID=" . $this->User_album_prop->ID_PROPOSAL;
            } else {
                $next_url = BDO_URL . "admin/editAlbum?alb_id=" . $lid;
            }

            echo GetMetaTag(2, "L'album a &eacute;t&eacute; mis a jour", $next_url);
        }
    }

}
