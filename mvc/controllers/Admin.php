<?php

/**
 * @author Tom
 * 
 */
class Admin extends Bdo_Controller {

    /**
     */
    public function Index() {

        if (User::minAccesslevel(1)) {
            $this->loadModel("User_album_prop");

            $this->view->set_var($this->User_album_prop->getAllStat());
            $this->view->set_var("PAGETITLE", "Administration Bdovore - Accueil");
            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    private function getDateBeforeValid() {
        $validationdelay = 21; //nbre de jours après lesquels on ne valide pas (pour les parutions futures)
        $datebeforevalid = "Ne pas valider les albums qui paraissent apr&egrave;s le " . date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $validationdelay, date("Y"))) . " ($validationdelay jours)";

        return $datebeforevalid;
    }

    public function Ajout() {
        if (User::minAccesslevel(1)) {
            $list = getVal("list", "");
            if ($list == "genre") {
                /*
                 * On charge la liste des genres pour permettre leur édition
                 */
                $this->loadModel("Genre");
                $this->view->set_var(array(
                    "listGenreBD" => $this->Genre->BD(),
                    "listGenreMangas" => $this->Genre->Mangas(),
                    "listGenreComics" => $this->Genre->Comics()));
            }


            $this->view->set_var("PAGETITLE", "Administration Bdovore - Ajout");
            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function Proposition() {
        $this->view->set_var(array("PAGETITLE" => "Proposition : liste"));
        /*
         * Page principale de gestion des propositions
         * Affiche les listes de proposition en attente
         * La gestion proprement dite est effectuée dans editPropositoin
         */
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            $update = getVal("chkUpdate", "");
            if ($update == 'O') {
                $act = "update";
            }
            $type = getVal("type", "AJOUT");

            $validationdelay = 21; //nbre de jours après lesquels on ne valide pas (pour les parutions futures)
            // LISTE LES PROPOSALS
            $this->loadModel("User_album_prop");
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
                    $urledit = "./editPropositionAjout?ID=";
                    break;
                case "CORRECTION" :
                    $urledit = "./editPropositionCorrection?ID=";
                    break;
                case "EDITION" :
                    $urledit = "./editedition?edition_id=";
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
                        unlink(BDO_DIR_UPLOAD . $prop_img);
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
                    $next_url = BDO_URL . "admin/proposition";
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
            } else {
                $url_image = BDO_URL_IMAGE . "tmp/" . $this->User_album_prop->IMG_COUV;
                $dim_image = imgdim($url_image);
            }

            $this->view->set_var(array(
                "DATEBEFOREVALID" => $this->getDateBeforeValid(),
                "PROPID" => stripslashes($this->User_album_prop->ID_PROPOSAL),
                "TITRE" => stripslashes($this->User_album_prop->TITRE),
                "CLTITRE" => ($this->User_album_prop->TITRE != '' ? "flat" : "to_be_corrected"),
                "ORITITRE" => stripslashes($this->User_album_prop->TITRE),
                "IDSERIE" => stripslashes($this->User_album_prop->ID_SERIE),
                "CLIDSERIE" => (is_numeric($this->User_album_prop->ID_SERIE) ? "flat" : "to_be_corrected"),
                "ORISERIE" => stripslashes($this->User_album_prop->SERIE),
                "TOME" => $this->User_album_prop->NUM_TOME,
                "CLTOME" => "flat",
                "PRIX_VENTE" => $this->User_album_prop->PRIX,
                "ISINT" => (($this->User_album_prop->FLG_INT == 'O') ? 'checked' : ''),
                "OPTTYPE" => GetOptionValue($opt_type, $this->User_album_prop->FLG_TYPE),
                "IDGENRE" => $this->User_album_prop->ID_GENRE,
                "CLIDGENRE" => (is_numeric($this->User_album_prop->ID_GENRE) ? "flat" : "to_be_corrected"),
                "ORIGENRE" => $this->User_album_prop->GENRE,
                "IDSCEN" => $this->User_album_prop->ID_SCENAR,
                "CLIDSCEN" => (is_numeric($this->User_album_prop->ID_SCENAR) ? "flat" : "to_be_corrected"),
                "ORISCENARISTE" => $this->User_album_prop->SCENAR,
                "IDSCENALT" => $this->User_album_prop->ID_SCENAR_ALT,
                "CLIDSCENALT" => "flat",
                "ORISCENARISTEALT" => $this->User_album_prop->SCENARALT,
                "IDEDIT" => $this->User_album_prop->ID_EDITEUR,
                "CLIDEDIT" => (is_numeric($this->User_album_prop->ID_EDITEUR) ? "flat" : "to_be_corrected"),
                "ORIEDITEUR" => $this->User_album_prop->EDITEUR,
                "IDDESS" => $this->User_album_prop->ID_DESSIN,
                "CLIDDESS" => (is_numeric($this->User_album_prop->ID_DESSIN) ? "flat" : "to_be_corrected"),
                "ORIDESSINATEUR" => $this->User_album_prop->DESSIN,
                "IDDESSALT" => $this->User_album_prop->ID_DESSIN_ALT,
                "CLIDDESSALT" => "flat",
                "ORIDESSINATEURALT" => $this->User_album_prop->DESSINALT,
                "IDCOLOR" => $this->User_album_prop->ID_COLOR,
                "CLIDCOLOR" => (is_numeric($this->User_album_prop->ID_COLOR) ? "flat" : "to_be_corrected"),
                "ORICOLORISTE" => $this->User_album_prop->COLOR,
                "IDCOLORALT" => $this->User_album_prop->ID_COLOR_ALT,
                "CLIDCOLORALT" => "flat",
                "ORICOLORISTEALT" => $this->User_album_prop->COLORALT,
                "IDCOLLEC" => $this->User_album_prop->ID_COLLECTION,
                "CLIDCOLLEC" => (is_numeric($this->User_album_prop->ID_COLLECTION) ? "flat" : "to_be_corrected"),
                "ORICOLLECTION" => $this->User_album_prop->COLLECTION,
                "DTPAR" => $this->User_album_prop->DTE_PARUTION,
                "EAN" => $this->User_album_prop->EAN,
                "URLEAN" => "http://www.bdnet.com/" . $this->User_album_prop->EAN . "/alb.htm",
                "ISEAN" => check_EAN($this->User_album_prop->EAN) ? "" : "*",
                "ISBN" => $this->User_album_prop->ISBN,
                "URLISBN" => "http://www.amazon.fr/exec/obidos/ASIN/" . $this->User_album_prop->ISBN,
                "ISISBN" => check_ISBN($this->User_album_prop->ISBN) ? "" : "*",
                "PRIX" => $this->User_album_prop->PRIX,
                "ISTT" => (($this->User_album_prop->FLG_TT == 'O') ? 'checked' : ''),
                "CLDTPAR" => "flat",
                "URLIMAGE" => $url_image,
                "DIMIMAGE" => $dim_image,
                "HISTOIRE" => stripslashes($this->User_album_prop->HISTOIRE),
                "SERIE" => is_null($this->User_album_prop->ID_SERIE) ? stripslashes($this->User_album_prop->SERIE) : stripslashes($this->User_album_prop->ACTUSERIE),
                "CLSERIE" => ($this->User_album_prop->SERIE == $this->User_album_prop->ACTUSERIE ? "flat" : "has_changed"),
                "GENRE" => is_null($this->User_album_prop->ID_GENRE) ? $this->User_album_prop->GENRE : $this->User_album_prop->ACTUGENRE,
                "CLGENRE" => ($this->User_album_prop->GENRE == $this->User_album_prop->ACTUGENRE ? "flat" : "has_changed"),
                "SCENARISTE" => is_null($this->User_album_prop->ID_SCENAR) ? $this->User_album_prop->ACTUSCENAR : ($this->User_album_prop->PSEUDO_SCENAR),
                "CLSCENARISTE" => ($this->User_album_prop->PSEUDO_SCENAR == $this->User_album_prop->SCENAR ? "flat" : "has_changed"),
                "SCENARISTEALT" => is_null($this->User_album_prop->ID_SCENAR_ALT) ? ($this->User_album_prop->SCENARALT) : ($this->User_album_prop->PSEUDO_SCENAR_ALT),
                "CLSCENARISTEALT" => ($this->User_album_prop->PSEUDO_SCENAR_ALT == $this->User_album_prop->SCENARALT ? "flat" : "has_changed"),
                "DESSINATEUR" => is_null($this->User_album_prop->ID_DESSIN) ? ($this->User_album_prop->DESSIN) : ($this->User_album_prop->PSEUDO_DESSIN),
                "CLDESSINATEUR" => ($this->User_album_prop->PSEUDO_DESSIN == $this->User_album_prop->DESSIN ? "flat" : "has_changed"),
                "DESSINATEURALT" => is_null($this->User_album_prop->ID_DESSIN_ALT) ? ($this->User_album_prop->DESSINALT) : ($this->User_album_prop->PSEUDO_DESSIN_ALT),
                "CLDESSINATEURALT" => ($this->User_album_prop->PSEUDO_DESSIN_ALT == $this->User_album_prop->ORIDESSINALT ? "flat" : "has_changed"),
                "COLORISTE" => is_null($this->User_album_prop->ID_COLOR) ? ($this->User_album_prop->COLOR) : ($this->User_album_prop->PSEUDO_COLOR),
                "CLCOLORISTE" => ($this->User_album_prop->PSEUDO_COLOR == $this->User_album_prop->COLOR ? "flat" : "has_changed"),
                "COLORISTEALT" => is_null($this->User_album_prop->ID_COLOR_ALT) ? ($this->User_album_prop->COLORALT) : ($this->User_album_prop->PSEUDO_COLOR_ALT),
                "CLCOLORISTEALT" => ($this->User_album_prop->PSEUDO_COLOR_ALT == $this->User_album_prop->COLORALT ? "flat" : "has_changed"),
                "EDITEUR" => is_null($this->User_album_prop->ID_EDITEUR) ? ($this->User_album_prop->EDITEUR) : ($this->User_album_prop->ACTUEDITEUR),
                "CLEDITEUR" => ($this->User_album_prop->ACTUEDITEUR == $this->User_album_prop->EDITEUR ? "flat" : "has_changed"),
                "COLLECTION" => is_null($this->User_album_prop->ID_COLLECTION) ? ($this->User_album_prop->COLLECTION) : ($this->User_album_prop->COLLECTION),
                "CLCOLLECTION" => ($this->User_album_prop->COLLECTION == $this->User_album_prop->COLLECTION ? "flat" : "has_changed"),
                "COMMENT" => stripslashes($this->User_album_prop->DESCRIB_EDITION),
                "CORRCOMMENT" => $this->User_album_prop->CORR_COMMENT,
                "OPTIONSTATUS" => GetOptionValue($opt_status, $this->User_album_prop->STATUS),
                "COLOR_STATUS" => $color_status,
                "PROPACTION" => $this->User_album_prop->ACTION,
                "ACTIONUTIL" => $opt_action[$this->User_album_prop->ACTION],
                "ACTIONNAME" => "Valider",
                "URLACTION" => BDO_URL . "admin/appendProposition?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLUTILVALID" => BDO_URL . "admin/mergeProposition?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLCOMMENTCORR" => BDO_URL . "admin/commentProposition?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLDELETE" => BDO_URL . "admin/deleteProposition?src=fiche&ID=" . $this->User_album_prop->ID_PROPOSAL,
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
            $mail_body .= "	d'autres &eacute;ditions sont peut-&ecirc;tre d&eacute;j&agrave; pr&eacute;sentes dans la base et peuvent &ecirc;tre s&eacute;lectionn&eacute;es en cliquant sur l'album en question depuis votre garde-manger (menu d&eacute;roulant [Mon &eacute;dition] des fiches album). \n";
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
            }
            // Détermine les albums ayant une syntaxe approchante
            $main_words = main_words(stripslashes($this->User_album_prop->TITRE));
            if ($main_words[1][0] != '') {
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
                $prev_url = BDO_URL . "admin/editpropositionajout?ID=" . $this->User_album_prop->ID_PROPOSAL;
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


                $next_url = BDO_URL . "admin/editpropositionajout?ID=" . $this->User_album_prop->ID_PROPOSAL;
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
            $prop_action = $this->User_album_prop->IMG_COUV;
            $notif_mail = $this->User_album_prop->NOTIF_MAIL;

            // On vérifie s'il s'agit d'une mise à jour simple ou d'une validation
            $check = postVal("chkUpdate", "N");

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
                    "DESCRIB_EDITION" => postVal("txtCommentEdition")
                ));
                $this->User_album_prop->update();
                if (issetNotEmpty($this->User_album_prop->error)) {
                    var_dump($this->User_album_prop->error);
                    exit();
                }
                // Retourne sur la page proposition
                header("Location:" . BDO_URL . "admin/editPropositionAjout?ID=$id");
                exit();
            } else { // validation de la proposition
                // on crée l'album etc...
                // n'insère dans bd_tome que s'il s'agit d'une nouvelle édition
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
                    "DTE_PARUTION" => postVal('txtDateParution'),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "EAN" => postVal('txtEAN'),
                    "ISBN" => postVal('txtISBN'),
                    "COMMENT" => postVal('txtCommentEdition'),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s')
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
                        $img_couv = $this->imgCouvFromForm($lid_tome, $lid_edition);
                    } else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal("txtFileURL"), $url_ary)) { // un fichier à télécharger
                        $img_couv = $this->imgCouvFromUrl($url_ary, $lid_tome, $lid_edition);
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

                    $max_size = 180;
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
                                imagejpeg($new_image, BDO_DIR_COUV . $newfilename, 100);

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
            ORDER BY id_proposal asc limit 0,1
            ");

                if ($this->User_album_prop->ID_PROPOSAL > $id) {

                    $next_url = BDO_URL . "admin/editProposition?ID=" . $this->User_album_prop->ID_PROPOSAL;
                } else {
                    $next_url = BDO_URL . "admin/editAlbum?id_tome=" . $lid_tome;
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

        $prop_action = $this->User_album_prop->IMG_COUV;
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
                $next_url = BDO_URL . "admin/editProposition?ID=" . $this->User_album_prop->ID_PROPOSAL;
            } else {
                $next_url = BDO_URL . "admin/editAlbum?id_tome=" . $lid_tome;
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
            header("Location:" . BDO_URL . "admin/editPropositionAjout?ID=$id");
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
                "URLACTION" => BDO_URL . "admin/updatecorrection?ID=" . $this->User_album_prop->ID_PROPOSAL,
                "URLDELETE" => BDO_URL . "admin/deleteProposition?ID=" . $this->User_album_prop->ID_PROPOSAL
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
                    $ori_dim_image = imgdim("$ori_url_image");
                }
            } else {
                // force l'édition
                $this->Edition->set_dataPaste(array("ID_EDITION" => $edition_id));
                $this->Edition->load();
                // Determine l'URL image courante
                if (is_null($this->Edition->IMG_COUV) | ($this->Edition->IMG_COUV == '')) {
                    $ori_url_image = BDO_URL_COUV . "default.png";
                } else {
                    $ori_url_image = BDO_URL_COUV . "" . $this->Edition->IMG_COUV;
                    $ori_dim_image = imgdim("$ori_url_image");
                }
            }




            // Determine l'URL image modifiée
            if (is_null($this->User_album_prop->IMG_COUV) | ($this->User_album_prop->IMG_COUV == '')) {
                $url_image = $ori_url_image;
            } else {
                $url_image = BDO_URL_IMAGE . "tmp/" . $this->User_album_prop->IMG_COUV;
                $dim_image = imgdim("$url_image");
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
                $prev_url = BDO_URL . "admin/editpropositioncorrection?ID=" . $this->User_album_prop->ID_PROPOSAL;
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


                $next_url = BDO_URL . "admin/editpropositioncorrection?ID=" . $this->User_album_prop->ID_PROPOSAL;
                $this->view->set_var("BOUTONSUIVANT", "<a href='" . $next_url . "'><input type='button' value='Suivant'></a>");
            } else {
                $this->view->set_var("BOUTONSUIVANT", "<del>Suivant</del>");
            }

            $this->view->set_var('PAGETITLE', "Validatoin d'une correction ");
            $this->view->render();
        }
    }

    public function addSerie() {
        /*
         * Ajout rapide d'une série
         */
        $this->view->set_var(array("PAGETITLE" => "Ajout rapide : serie"));
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            // Mettre à jour les informations
            if ($act == "insert") {
                $tri = substr(trim(clean_article(postVal('txtNomSerie'))), 0, 3);
                $this->loadModel("Serie");
                $this->Serie->set_dataPaste(array(
                    "NOM" => postVal('txtNomSerie'),
                    "ID_GENRE" => postValInteger("txtGenreId"),
                    "NOTE" => "0",
                    "FLG_FINI" => "1",
                    "TRI" => $tri
                ));
                $this->Serie->update();

                // fichier à utiliser
                $this->view->set_var(array(
                    "script" => "parent.$.fancybox.close();"
                ));
            }

            // Afficher le formulaire pré - remplis
            elseif ($act == "") {

                $this->view->set_var(array(
                    "URLACTION" => BDO_URL . "admin/addserie?act=insert"
                ));
            }
            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

    public function addAuteur() {
        $this->view->set_var(array("PAGETITLE" => "Ajout rapide : auteur"));
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            if ($act == "insert") {
                if (postVal('txtPrenom') != '')
                    $long_name = postVal('txtNom') . ", " . postVal('txtPrenom');
                else
                    $long_name = postVal('txtNom');

                $pseudo = notIssetOrEmpty(postVal('txtPseudo')) ? $long_name : postVal('txtPseudo');
                $this->loadModel("Auteur");
                $this->Auteur->set_dataPaste(array(
                    "PSEUDO" => $pseudo,
                    "NOM" => postVal('txtNom'),
                    "PRENOM" => postVal('txtPrenom')
                ));
                $this->Auteur->update();
                $this->view->set_var(array
                    (
                    "BODYONLOAD" => "parent.$.fancybox.close();"
                ));
            }

            // Afficher le formulaire pr� - remplis
            elseif ($act == "") {
                $this->view->set_var(array
                    (
                    "URLACTION" => BDO_URL . "admin/addauteur?act=insert"
                ));
            }
            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

    public function editEdition() {
        /*
         * Gestion de éditions
         */
        $this->view->set_var(array("PAGETITLE" => "Admin : Edition"));
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            $edition_id = getValInteger("edition_id");
            $conf = getVal("conf", "");
            $this->loadModel("Edition");
            $this->loadModel("Tome");
            $this->view->set_var(array("PAGETITLE" => "Administration des Editions"));

            // Mettre � jour les informations
            if ($act == "update") {
                $tome_id = postValInteger("txtTomeId");
                $edition_id = postValInteger("txtEditionId");
                if (is_file($_FILES["txtFileLoc"]["tmp_name"])) {// un fichier � uploader
                    $img_couv = $this->imgCouvFromForm($tome_id, $edition_id);
                } else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal('txtFileURL'), $url_ary)) { // un fichier � t�l�charger
                    $img_couv = $this->imgCouvFromUrl($url_ary, $tome_id, $edition_id);
                } else {
                    $img_couv = '';
                }

                if (postVal('FLAG_DTE_PARUTION') != "1")
                    $txtDateParution = completeDate(postVal('txtDateParution'));
                else
                    $txtDateParution = '';

                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $edition_id,
                    'DTE_PARUTION' => $txtDateParution,
                    'FLAG_DTE_PARUTION' => ((postVal('FLAG_DTE_PARUTION') == "1") ? "1" : ""),
                    'ID_EDITEUR' => postVal('txtEditeurId'),
                    'ID_COLLECTION' => postVal('txtCollecId'),
                    'EAN' => postVal('txtEAN'),
                    'ISBN' => postVal("txtISBN"),
                    'COMMENT' => postVal("txtComment"),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s')
                ));


                // v�rifie si la couverture a �t� chang�e
                if ($img_couv != '') {
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                }
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    var_dump($this->Edition->error);
                    exit();
                }

                echo 'Mise &agrave; jour effectu&eacute;e dans la table bd_edition<br />';


                // On rajoute un redimensionnement si le correcteur l'a voulu
                if (postVal("chkResize") == "checked") {
                    $id_edition = intval(postVal("txtEditionId"));
                    $this->resize_edition_image($id_edition, BDO_DIR_COUV);
                }

                $redirection = BDO_URL . "admin/editalbum?alb_id=" . postVal("txtTomeId");
                echo GetMetaTag(1, "L'&eactue;dition a &eactue;t&eactue; mise &agrave; jour", $redirection);
                exit();
            } elseif ($act == "delete") {// EFFACEMENT D'UNE EDITION
                if ($conf == "ok") {

                    // Determine s'il y a lieu d'effacer l'image
                    $this->Edition->set_dataPaste(array("ID_EDITION" => $edition_id));
                    $this->Edition->load();
                    $url_img = $this->Edition->IMG_COUV;
                    $id_tome = $this->Edition->ID_TOME;
                    if ($url_img != '') {
                        $filename = $url_img;
                        if (file_exists(BDO_DIR_COUV . "$filename")) {
                            unlink(BDO_DIR_COUV . "$filename");
                            echo "Couverture effac&eactue;e<br />";
                        }
                    }

                    // Efface l'�dition de la base
                    $this->Edition->delete();
                    $redirection = BDO_URL . "admin/editalbum?alb_id=" . $id_tome;
                    echo GetMetaTag(1, "L'&eactue;dition a &eactue;t&eacute; &eacute;ffac&eacute;e de la base", $redirection);
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer l\'&eacute;dition n. ' . $edition_id . ' ? <a href="' . BDO_URL . 'admin/editedition?act=delete&conf=ok&edition_id=' . $edition_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            } elseif ($act == "autorize") {// ACTIVATION D'UNE EDITION
                // Commence par activer l'�dition dans la base
                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $edition_id));
                $this->Edition->load();
                $this->Edition->set_dataPaste(array(
                    "PROP_STATUS" => "1",
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s')
                ));
                $this->Edition->update();
                echo GetMetaTag(1, "L'&eacute;dition a &eacute;t&eacute; activ&eacute;e", BDO_URL . "admin/editalbum?alb_id=" . $this->Edition->ID_TOME);
                exit();
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {
                // determine si une r�f�rence d'album a �t� pass�
                if (getVal("alb_id", "") <> "") {
                    $alb_id = getValInteger(alb_id);
                    $this->Tome->set_dataPaste(array("ID_TOME" => $alb_id));
                    $this->Tome->load();
                    $alb_titre = $this->Tome->TITRE_TOME;
                } else {
                    $alb_titre = '';
                    $alb_id = '';
                }

                $url_image = BDO_URL_COUV . "default.png";
                // Creation d'un nouveau Template
                $this->view->set_var(array(
                    "URLIMAGE" => $url_image,
                    "NBUSERS" => "0",
                    "IDTOME" => $alb_id,
                    "TITRE" => $alb_titre,
                    "URLDELETE" => "javascript:alert('D&eactue;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editedition?act=append"
                ));
                // assigne la barre de login
                $this->view->render();
            }

// INSERE UNE NOUVELLE EDITION DANS LA BASE
            elseif ($act == "append") {
                $id_tome = postValInteger('txtTomeId');

                $txtDateParution = completeDate(postVal('txtDateParution'));
                if (postVal('txtDateParution') == "")
                    $flag_dte_par = 1;
                else
                    $flag_dte_par = ((postVal('FLAG_DTE_PARUTION') == "1") ? "1" : "");

                $this->Edition->set_dataPaste(array(
                    "ID_TOME" => $id_tome,
                    "ID_EDITEUR" => postValInteger('txtEditeurId'),
                    "ID_COLLECTION" => postValInteger('txtCollecId'),
                    "DTE_PARUTION" => $txtDateParution,
                    "FLAG_DTE_PARUTION" => $flag_dte_par,
                    "EAN" => postVal("txtEAN"),
                    'ISBN' => postVal("txtISBN"),
                    'COMMENT' => postVal("txtComment"),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s')
                ));
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    var_dump($this->Edition->error);
                    exit();
                }
                // r�cup�re la valeur de la derni�re insertion
                $lid = $this->Edition->ID_EDITION;

                // Verifie la pr�sence d'une image � t�l�charger
                if (is_file($_FILES["txtFileLoc"]["tmp_name"])) { // un fichier � uploader
                    $img_couv = $this->imgCouvFromForm($id_tome, $lid);
                } else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal('txtFileURL'), $url_ary)) { // un fichier � t�l�charger
                    $img_couv = $this->imgCouvFromUrl($url_ary, $id_tome, $lid);
                } else {
                    $img_couv = '';
                }

                if ($img_couv != '') {
                    // met � jours la r�f�rence au fichier dans la table bd_edition
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                    $this->Edition->update();
                }

                // On rajoute un redimensionnement si le correcteur l'a voulu
                if (postVal("chkResize") == "checked") {
                    $this->resize_edition_image($lid, BDO_DIR_COUV);
                }

                echo GetMetaTag(2, "L'&eacute;dition a &eacute;t&eacute; ajout&eacute;e", (BDO_URL . "admin/editalbum?alb_id=" . $id_tome));
            }

// AFFICHER UNE EDITION
            elseif ($act == "") {

                // r�cup�rer le nombres dutilisateurs avec cette edition dans leur collection
                $this->Edition->set_dataPaste(array("ID_EDITION" => $edition_id));

                $this->Edition->load();
                $nbusers = intval($this->Edition->NBR_USER_ID);

                // R�cup�re l'adresse mail de l'utilisateur

                $mail_adress = $this->Edition->EMAIL;
                $mailsubject = "Votre proposition de nouvelle &eacute;dition pour l'album : " . $this->Edition->TITRE_TOME;
                $pseudo = $this->Edition->USERNAME;




                // Determine l'URL image
                if (is_null($this->Edition->IMG_COUV) | ($this->Edition->IMG_COUV == '')) {
                    $url_image = BDO_URL_COUV . "default.png";
                } else {
                    $url_image = BDO_URL_COUV . $this->Edition->IMG_COUV;
                    $dim_image = imgdim("$url_image");
                }

                // d�termine s'il est possible d'effacer cet album
                if (($this->Edition->ID_EDITION == $this->Edition->ID_EDITION_DEFAULT) | ($nbusers > 0)) {
                    $url_delete = "javascript:alert('Impossible d\'effacer cette &eacute;dition');";
                } else {
                    $url_delete = BDO_URL . "admin/editedition?act=delete&edition_id=" . $edition_id;
                }
                // Activation de l'edition
                if ($this->Edition->PROP_STATUS == 0) {
                    $actionautorise = "<a href=\"" . BDO_URL . "admin/editedition?act=autorize&edition_id=" . $edition_id . "\">Activer cette &eacute;dition</a>";
                    $contactuser = "propos&eacute;e par <a href=\"mailto:" . $mail_adress . "?subject=" . $mailsubject . "\" style=\"font-weight: bold;\">" . $pseudo . "</a> (" . $mail_adress . ")<br />";
                }

                $this->view->set_var(array(
                    "IDTOME" => $this->Edition->ID_TOME,
                    "IDEDITION" => $edition_id,
                    "TITRE" => stripslashes($this->Edition->TITRE_TOME),
                    "IDEDIT" => $this->Edition->ID_EDITEUR,
                    "EDITEUR" => $this->Edition->NOM_EDITEUR,
                    "IDCOLLEC" => $this->Edition->ID_COLLECTION,
                    "COLLECTION" => $this->Edition->NOM_COLLECTION,
                    "DTPAR" => $this->Edition->DATE_PARUTION_EDITION,
                    "CHKFLAG_DTE_PARUTION" => (($this->Edition->FLAG_DTE_PARUTION == 1) ? 'CHECKED' : ''),
                    "COMMENT" => stripslashes($this->Edition->COMMENT_EDITION),
                    "ISTT" => (($this->Edition->FLG_TT == 'O') ? 'checked' : ''),
                    "FLGDEF" => (($this->Edition->ID_EDITION == $this->Edition->ID_EDITION_DEFAULT ? 'O' : '')),
                    "EAN" => $this->Edition->EAN_EDITION,
                    "URLEAN" => "http://www.bdnet.com/" . $this->Edition->EAN_EDITION . "/alb.htm",
                    "ISBN" => $this->Edition->ISBN_EDITION,
                    "URLISBN" => "http://www.amazon.fr/exec/obidos/ASIN/" . $this->Edition->ISBN_EDITION,
                    "URLIMAGE" => $url_image,
                    "DIMIMAGE" => $dim_image,
                    "NBUSERS" => $nbusers,
                    "VIEWUSEREDITION" => "<a href='" . BDO_URL . "admin/viewUserEdition.php?id_edition=" . $edition_id . "'>(voir les utilisateurs)</a>",
                    "ACTIONAUTORIZE" => $actionautorise,
                    "CONTACTUSER" => $contactuser,
                    "URLDELETE" => $url_delete,
                    "URLFUSION" => BDO_URL . "admin/mergealbums?source_id=" . $this->Edition->ID_TOME,
                    "URLFUSIONEDITION" => BDO_URL . "admin/mergeeditions?source_id=" . $edition_id,
                    "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Edition->ID_EDITEUR,
                    "URLEDITCOLL" => BDO_URL . "admin/editcollection?collec_id=" . $this->Edition->ID_COLLECTION,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLACTION" => BDO_URL . "admin/editedition?act=update"
                ));
                if ($this->Edition->DTE_PARUTION == "0000-00-00") {
                    $this->view->set_var("PARUTION_0", "to_be_corrected");
                }

                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editAlbum() {
        /*
         * Methode d'édition / modification / suppression d'un album
         * 
         */
        $this->view->set_var(array("PAGETITLE" => "Admin : Album"));
        if (User::minAccesslevel(1)) {
            $this->loadModel("Tome");
            $this->loadModel("Serie");
            // Tableau pour les choix d'options du status des series
            $opt_status[0][0] = 0;
            $opt_status[0][1] = 'Finie';
            $opt_status[1][0] = 1;
            $opt_status[1][1] = 'En cours';
            $opt_status[2][0] = 2;
            $opt_status[2][1] = 'One Shot';
            $opt_status[3][0] = 3;
            $opt_status[3][1] = 'Interrompue/Abandonn&eacute;e';

// Tableau pour les choix d'options
            $opt_type[0][0] = 0;
            $opt_type[0][1] = 'Album';
            $opt_type[1][0] = 1;
            $opt_type[1][1] = 'Coffret';


            $act = getVal("act");
            $conf = getVal("conf");
            $idtome = getValInteger("idtome");
// Mettre � jour les informations
            if ($act == "update") {
                $this->Serie->set_dataPaste(array("ID_SERIE" => postValInteger("txtSerieId")));
                $this->Serie->load(); // chargement de la série pour récupérer le genre de l'album

                $this->Tome->set_dataPaste(array(
                    "ID_TOME" => postValInteger("txtTomeId"),
                    "TITRE" => postVal("txtTitre"),
                    "NUM_TOME" => postVal("txtNumTome", ""),
                    "ID_SERIE" => postValInteger("txtSerieId"),
                    "PRIX_BDNET" => postVal("txtPrixVente"),
                    "ID_SCENAR" => postValInteger("txtScenarId"),
                    "ID_DESSIN" => postValInteger("txtDessiId"),
                    "ID_DESSIN_ALT" => postValInteger('txtDessiAltId') ? postValInteger('txtDessiAltId') : '0',
                    "ID_SCENAR_ALT" => postValInteger('txtScenarAltId') ? postValInteger('txtScenarAltId') : '0',
                    "ID_COLOR" => postValInteger("txtColorId", "0"),
                    "ID_COLOR_ALT" => postValInteger("txtColorAltId") ? postValInteger("txtColorAltId") : "0",
                    "FLG_INT" => (postVal("chkIntegrale") == "checkbox") ? "O" : "N",
                    "FLG_TYPE" => postVal("lstType"),
                    "HISTOIRE" => postVal("txtHistoire"),
                    "ID_GENRE" => $this->Serie->ID_GENRE,
                    "ID_EDITION" => postVal("btnDefEdit")
                ));

                $this->Tome->update();
                if (issetNotEmpty($this->Tome->error)) {
                    var_dump($this->Tome->error);
                    exit();
                }
                echo GetMetaTag(2, "Mise &agrave; jour effectu&eacute;e", (BDO_URL . "admin/editalbum?alb_id=" . postValInteger("txtTomeId")));
            }


// EFFACEMENT D'UN ALBUM
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    //Rev�rifie que c'est bien l'administrateur qui travaille
                    if (User::minAccesslevel(1)) {
                        // Efface les éditions et les couvertures correspondantes
                        $this->loadModel("Edition");
                        $dbs_edition = $this->Edition->load(c, "where bd_tome.id_tome =" . $idtome);
                        foreach ($dbs_edition->a_dataQuery as $edition) {
                            if ($edition->IMG_COUV != '') {
                                $filename = $edition->IMG_COUV;
                                if (file_exists(BDO_DIR_COUV . "$filename")) {
                                    @unlink(BDO_DIR_COUV . "$filename");
                                    echo "Couverture effac&eacute;e pour l'eacute;dition N" . $edition->ID_EDITION . "<br />";
                                }
                            }
                        }
                        // vide la table bd_edition
                        $this->Tome->deleteEditionForAlbum($idtome);
                        echo 'R&eacute;f&eacute;rence(s) &agrave; l\'album supprim&eacute;e(s) dans la table bd_edition<br />';

                        $this->Tome->set_dataPaste(array("ID_TOME" => $idtome));
                        $this->Tome->delete();

                        $redirection = BDO_URL . "admin";
                        echo '<META http-equiv="refresh" content="1; URL=' . $redirection . '">L\'album a &eacute;t&eacute; effac&eacute; de la table bd_tome.';
                        exit();
                    }
                } else {
                    // Affiche la demande de confirmation
                    echo 'Etes-vous sur de vouloir effacer l\'album n. ' . $idtome . ' ? <a href="' . BDO_URL . 'admin/editalbum?act=delete&conf=ok&idtome=' . $idtome . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            } elseif ($act == "new") {
                // AFFICHE UN FORMULAIRE VIDE
                $url_image = BDO_URL_COUV . "default.png";
                $champ_form_style = 'champ_form_desactive';



                $this->view->set_var(array(
                    "CHAMPFORMSTYLE" => $champ_form_style,
                    "URLIMAGE" => $url_image,
                    "OPTTYPE" => GetOptionValue($opt_type, 0),
                    "NBUSERS" => "0",
                    "NBUSERS2" => "0",
                    "URLSERIE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITSERIE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITGENRE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITSCEN" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITDESS" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITDESSALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLOR" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLORALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITEDIT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLL" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLLALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editalbum?act=append"
                ));
                $this->view->render();
            }


// AFFICHE UN FORMULAIRE pr�rempli
            elseif ($act == "newfserie") {
                $url_image = BDO_URL . "images/couv/default.png";
                $champ_form_style = 'champ_form_desactive';
                $champ_form_style_newfserie = 'champ_form_desactive_newfserie';
                // Creation d'un nouveau Template


                $id_serie = getValInteger("id_serie");
                $this->Tome->load("c", " WHERE bd_serie.id_serie =" . $id_serie . " ORDER BY t.num_tome DESC LIMIT 1");


                $t->set_var(array(
                    "CHAMPFORMSTYLE" => $champ_form_style,
                    "CHAMPFORMSTYLE_NEWFSERIE" => $champ_form_style_newfserie,
                    "URLIMAGE" => $url_image,
                    "OPTTYPE" => GetOptionValue($opt_type, 0),
                    "NBUSERS" => "0",
                    "NBUSERS2" => "0",
                    "TOME" => $this->Tome->NUM_TOME + 1,
                    "IDSERIE" => $id_serie,
                    "SERIE" => $this->Tome->NOM_SERIE,
                    "IDSCEN" => $this->Tome->ID_SCENAR,
                    "SCENARISTE" => $this->Tome->scpseudo,
                    "IDSCENALT" => $this->Tome->ID_SCENAR_ALT,
                    "SCENARISTEALT" => ($this->Tome->ID_SCENAR_ALT == 0 ) ? "" : $this->Tome->scapseudo,
                    "IDDESS" => $this->Tome->ID_DESSIN,
                    "DESSINATEUR" => $this->Tome->depseudo,
                    "IDDESSALT" => $this->Tome->ID_DESSIN_ALT,
                    "DESSINATEURALT" => ($this->Tome->ID_DESSIN_ALT == 0 ) ? "" : $this->Tome->deapseudo,
                    "IDCOLOR" => $this->Tome->ID_COLOR,
                    "COLORISTE" => $this->Tome->copseudo,
                    "IDCOLORALT" => $this->Tome->ID_COLOR_ALT,
                    "COLORISTEALT" => ($this->Tome->ID_COLOR_ALT == 0 ) ? "" : $this->Tome->coapseudo,
                    "IDEDIT" => $this->Tome->ID_EDITEUR,
                    "EDITEUR" => $this->Tome->NOM_EDITEUR,
                    "IDCOLLEC" => $this->Tome->ID_COLLECTION,
                    "COLLECTION" => $this->Tome->NOM_COLLECTION,
                    "URLSERIE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITSERIE" => BDO_URL . "admin/editserie?serie_id=" . $this->Tome->ID_SERIE,
                    "URLEDITGENRE" => BDO_URL . "admin/editgenre?genre_id=" . $this->Tome->ID_GENRE,
                    "URLEDITSCEN" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR,
                    "URLEDITDESS" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN,
                    "URLEDITCOLOR" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR,
                    "URLEDITSCENALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR_ALT,
                    "URLEDITDESSALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN_ALT,
                    "URLEDITCOLORALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR_ALT,
                    "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Tome->ID_EDITEUR,
                    "URLEDITCOLL" => BDO_URL . "admin/editcollection?collec_id=" . $this->Tome->ID_COLLECTION,
                    "URLEDITCOLLALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editalbum?act=append"
                ));

                $this->view->render();
            }


// INSERE UN NOUVEL ALBUM DANS LA BASE
            elseif ($act == "append") {
                $this->Serie->set_dataPaste(array("ID_SERIE" => postValInteger("txtSerieId")));
                $this->Serie->load(); // chargement de la série pour récupérer le genre de l'album

                $this->Tome->set_dataPaste(array(
                    "TITRE" => postVal("txtTitre"),
                    "NUM_TOME" => postVal("txtNumTome", ""),
                    "ID_SERIE" => postValInteger("txtSerieId"),
                    "PRIX_BDNET" => postVal("txtPrixVente"),
                    "ID_SCENAR" => postValInteger("txtScenarId"),
                    "ID_DESSIN" => postValInteger("txtDessiId"),
                    "ID_DESSIN_ALT" => postValInteger('txtDessiAltId') ? postValInteger('txtDessiAltId') : '0',
                    "ID_SCENAR_ALT" => postValInteger('txtScenarAltId') ? postValInteger('txtScenarAltId') : '0',
                    "ID_COLOR" => postValInteger("txtColorId", "0"),
                    "ID_COLOR_ALT" => postValInteger("txtColorAltId") ? postValInteger("txtColorAltId") : "0",
                    "FLG_INT" => (postVal("chkIntegrale") == "checkbox") ? "O" : "N",
                    "FLG_TYPE" => postVal("lstType"),
                    "HISTOIRE" => postVal("txtHistoire"),
                    "ID_GENRE" => $this->Serie->ID_GENRE
                ));
                $this->Tome->update();
                if (issetNotEmpty($this->Tome->error)) {
                    var_dump($this->Tome->error);
                    exit();
                }
                // r�cup�re la valeur de la derni�re insertion
                $lid_tome = $this->Tome->ID_TOME;


                // ins�re un champ dans la table id_edition
                $this->loadModel("Edition");
                $txtDateParution = completeDate(postVal('txtDateParution'));


                $this->Edition->set_dataPaste(array(
                    'DTE_PARUTION' => $txtDateParution,
                    'FLAG_DTE_PARUTION' => ((postVal('FLAG_DTE_PARUTION') == "1") ? "1" : ""),
                    'ID_EDITEUR' => postVal('txtEditeurId'),
                    'ID_COLLECTION' => postVal('txtCollecId'),
                    'EAN' => postVal('txtEAN'),
                    'ISBN' => postVal("txtISBN"),
                    'COMMENT' => postVal("txtComment"),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "ID_TOME" => $lid_tome,
                    "VALID_DTE" => date('d/m/Y H:i:s')
                ));
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    echo "Erreur lors de la création de l'édition !";
                    var_dump($this->Edition->error);
                    exit();
                }

                // r�cup�re la valeur de la derni�re insertion
                $lid_edition = $this->Edition->ID_EDITION;

                // renseigne cette edition comme defaut pour bd_tome
                $this->Tome->set_dataPaste(array("ID_EDITION" => $lid_edition));
                $this->Tome->update();
                if (issetNotEmpty($this->Tome->error)) {
                    var_dump($this->Tome->error);
                    exit();
                }
                // Verifie la pr�sence d'une image � t�l�charger
                if (is_file($txtFileLoc) | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postval('txtFileURL'), $url_ary))) {
                    if (is_file($txtFileLoc)) { // un fichier � uploader
                        $img_couv = $this->imgCouvFromForm($lid_tome, $lid_edition);
                    } else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)) { // un fichier � t�l�charger
                        if (empty($url_ary[4])) {
                            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incomplete. Vous allez etre redirige.';
                            exit();
                        }
                        $img_couv = $this->imgCouvFromUrl($url_ary, $lid_tome, $lid_edition);
                    } else {
                        $img_couv = '';
                    }
                    if (postVal("chkResize") == "checked" && $img_couv != '') {
                        //Redimensionnement
                        $max_size = 180;
                        $imageproperties = getimagesize(BDO_DIR . "images/couv/$img_couv");
                        if ($imageproperties != false) {
                            $imagetype = $imageproperties[2];
                            $imagelargeur = $imageproperties[0];
                            $imagehauteur = $imageproperties[1];

                            //D�termine s'il y a lieu de redimensionner l'image
                            if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $maxsize)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {
                                if ($imagelargeur < $imagehauteur) {
                                    // image de type panorama : on limite la largeur � 128
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
                                    $source = imagecreatefromgif(BDO_DIR . "images/couv/$img_couv");
                                    break;
                                case "2":
                                    $source = imagecreatefromjpeg(BDO_DIR . "images/couv/$img_couv");
                                    break;
                                case "3":
                                    $source = imagecreatefrompng(BDO_DIR . "images/couv/$img_couv");
                                    break;
                                case "6":
                                    $source = imagecreatefrombmp(BDO_DIR . "images/couv/$img_couv");
                                    break;
                            }
                            imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);
                            switch ($imagetype) {
                                case "2":
                                    unlink(BDO_DIR . "images/couv/$img_couv");
                                    imagejpeg($new_image, BDO_DIR . "images/couv/$img_couv", 100);
                                    break;
                                case "1":
                                case "3":
                                case "6":
                                    unlink(BDO_DIR . "images/couv/$img_couv");
                                    $img_couv = substr($img_couv, 0, strlen($img_couv) - 3) . "jpg";
                                    imagejpeg($new_image, BDO_DIR . "images/couv/$img_couv", 100);
                            }
                        }
                        echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
                        echo "Image redimensionn&eaccute;e<br />";
                    }

                    // met � jours la r�f�rence au fichier dans la table bd_edition
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                    $this->Edition->update();
                }
                echo GetMetaTag(2, "L'album a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editalbum?alb_id=" . $lid_tome));
            }


// AFFICHER UN ALBUM
            elseif ($act == "") {
                $alb_id = getValInteger("alb_id");
                $this->Tome->set_dataPaste(array("ID_TOME" => $alb_id));
                $this->Tome->load();
                // r�cup�re le nombre d'utilisateurs

                $nb_users = $this->Tome->NBR_USER_ID_TOME;


                $nb_comments = $this->Tome->NB_NOTE_TOME;

                $id_edition_default = $this->Tome->ID_EDITION;

                $champ_form_style = 'champ_form_desactive';

                // d�termine s'il est possible d'effacer cet album
                if (($nb_users == 0) & ($nb_comments == 0)) {
                    $url_delete = BDO_URL . "admin/editalbum?act=delete&idtome=" . $this->Tome->ID_TOME;
                } else {
                    $url_delete = "javascript:alert('Impossible');";
                }

                $this->view->set_var(array(
                    "CHAMPFORMSTYLE" => $champ_form_style,
                    "IDTOME" => $this->Tome->ID_TOME,
                    "TITRE" => $this->Tome->TITRE_TOME,
                    "IDSERIE" => $this->Tome->ID_SERIE,
                    "SERIE" => $this->Tome->NOM_SERIE,
                    "TRI" => $this->Tome->TRI,
                    "IDGENRE" => $this->Tome->ID_GENRE,
                    "GENRE" => $this->Tome->NOM_GENRE,
                    "OPTSTATUS" => GetOptionValue($opt_status, $this->Tome->FLG_FINI),
                    "NBTOME" => $this->Tome->NB_TOME,
                    "HISTOIRE_SERIE" => $this->Tome->HISTOIRE_SERIE,
                    "TOME" => $this->Tome->NUM_TOME,
                    "PRIX_VENTE" => $this->Tome->PRIX_BDNET,
                    "IDSCEN" => $this->Tome->ID_SCENAR,
                    "SCENARISTE" => $this->Tome->scpseudo,
                    "IDSCENALT" => $this->Tome->ID_SCENAR_ALT,
                    "SCENARISTEALT" => ($this->Tome->ID_SCENAR_ALT == 0 ) ? "" : $this->Tome->scapseudo,
                    "IDDESS" => $this->Tome->ID_DESSIN,
                    "DESSINATEUR" => $this->Tome->depseudo,
                    "IDDESSALT" => $this->Tome->ID_DESSIN_ALT,
                    "DESSINATEURALT" => ($this->Tome->ID_DESSIN_ALT == 0 ) ? "" : $this->Tome->deapseudo,
                    "IDCOLOR" => $this->Tome->ID_COLOR,
                    "COLORISTE" => $this->Tome->copseudo,
                    "IDCOLORALT" => $this->Tome->ID_COLOR_ALT,
                    "COLORISTEALT" => ($this->Tome->ID_COLOR_ALT == 0 ) ? "" : $this->Tome->coapseudo,
                    "IDEDIT" => $this->Tome->ID_EDITEUR,
                    "EDITEUR" => $this->Tome->NOM_EDITEUR,
                    "IDCOLLEC" => $this->Tome->ID_COLLECTION,
                    "COLLECTION" => $this->Tome->NOM_COLLECTION,
                    "HISTOIRE" => $this->Tome->HISTOIRE_TOME,
                    "ID_EDITION" => $this->Tome->ID_EDITION,
                    "ISINT" => (($this->Tome->FLG_INT == 'O') ? 'checked' : ''),
                    "OPTTYPE" => GetOptionValue($opt_type, $this->Tome->FLG_TYPE),
                    "NBUSERS" => $nb_users,
                    "NBUSERS2" => $nb_comments,
                    "URLDELETE" => $url_delete,
                    "URLFUSION" => BDO_URL . "admin/mergealbum?source_id=" . $this->Tome->ID_TOME,
                    "URLSPLIT" => BDO_URL . "admin/split?alb_id=" . $this->Tome->ID_TOME,
                    "URLFUSIONDELETE" => BDO_URL . "admin/fusion.delete?alb_id=" . $this->Tome->ID_TOME,
                    "URLEDITSERIE" => BDO_URL . "admin/editserie?serie_id=" . $this->Tome->ID_SERIE,
                    "URLEDITGENRE" => BDO_URL . "admin/editgenre?genre_id=" . $this->Tome->ID_GENRE,
                    "URLEDITSCEN" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR,
                    "URLEDITDESS" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN,
                    "URLEDITCOLOR" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR,
                    "URLEDITSCENALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR_ALT,
                    "URLEDITDESSALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN_ALT,
                    "URLEDITCOLORALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR_ALT,
                    "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Tome->ID_EDITEUR,
                    "URLEDITCOLL" => BDO_URL . "admin/editcollection?collec_id=" . $this->Tome->ID_COLLECTION,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLACTION" => BDO_URL . "admin/editalbum?act=update"
                ));

                // Affiche les informations relatives aux diff�rentes �ditions
                $this->loadModel("Edition");
                $dbs_edition = $this->Edition->load(c, "where bd_tome.id_tome =" . $this->Tome->ID_TOME);



                $this->view->set_var(array(
                    "NBEDITIONS" => count($dbs_edition->a_dataQuery),
                    "dbs_edition" => $dbs_edition,
                    "URLAJOUTEDITION" => BDO_URL . "admin/editedition?act=new&alb_id=" . $alb_id
                ));

                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editAuteur() {
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            $conf = getVal("conf");
            $auteur_id = getVal("auteur_id");
            $this->view->layout = "iframe";
            $this->loadModel("Auteur");
            // Mettre � jour les informations
            if ($act == "update") {
                $nom = postVal('txtNomAuteur');
                $prenom = postVal('txtPrenomAuteur');
                $pseudo = (postVal('txtPseudoAuteur') == '' ? postVal('txtNomAuteur') . ", " .
                                postVal('txtPrenomAuteur') . "'" : "'" . postVal('txtPseudoAuteur') );

                $this->Auteur->set_dataPaste(array(
                    "ID_AUTEUR" => postVal("txtIdAuteur"),
                    "PRENOM" => $prenom,
                    "NOM" => $nom,
                    "FLG_SCENAR" => postVal('chkScen') == 'checked' ? 1 : 0,
                    "FLG_DESSIN" => postVal('chkDess') == 'checked' ? 1 : 0,
                    "FLG_COLOR" => (postVal('chkColor') == 'checked' ? 1 : 0),
                    "COMMENT" => postVal('txtCommentaire'),
                    "DTE_NAIS" => postVal('txtDateNaiss'),
                    "DTE_DECES" => postVal('txtDateDeces'),
                    "NATIONALITE" => postVal('txtNation')
                ));
                $this->Auteur->update();
                echo '<META http-equiv="refresh" content="1; URL=javascript:history.go(-1)">' . "Mise &agrave; jour effectu&eacute;e";
            }

// effacement d'un auteur
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    if (User::minAccesslevel(1)) {//Rev�rifie que c'est bien l'administrateur qui travaille
                        $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $auteur_id));
                        $this->Auteur->delete();
                        echo 'L\'auteur a &eacute;t&eacute; effac&eacute;e de la base.';
                        exit();
                    }
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer l\'auteur n. ' . $auteur_id . ' ? <a href="' . BDO_URL . 'admin/editauteur?act=delete&conf=ok&auteur_id=' . $auteur_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {

                $this->view->set_var(array
                    ("NBALBUMS" => "0",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editauteur?act=append"
                ));

                $this->view->render();
            }

// INSERE UN NOUVEL ALBUM DANS LA BASE
            elseif ($act == "append") {
                $nom = postVal('txtNomAuteur');
                $prenom = postVal('txtPrenomAuteur');
                $pseudo = (postVal('txtPseudoAuteur') == '' ? postVal('txtNomAuteur') . ", " .
                                postVal('txtPrenomAuteur') . "'" : "'" . postVal('txtPseudoAuteur') );

                $this->Auteur->set_dataPaste(array(
                    "PRENOM" => $prenom,
                    "NOM" => $nom,
                    "FLG_SCENAR" => postVal('chkScen') == 'checked' ? 1 : 0,
                    "FLG_DESSIN" => postVal('chkDess') == 'checked' ? 1 : 0,
                    "FLG_COLOR" => (postVal('chkColor') == 'checked' ? 1 : 0),
                    "COMMENT" => postVal('txtCommentaire'),
                    "DTE_NAIS" => postVal('txtDateNaiss'),
                    "DTE_DECES" => postVal('txtDateDeces'),
                    "NATIONALITE" => postVal('txtNation')
                ));
                $this->Auteur->update();
                $lid = $this->Auteur->ID_AUTEUR;
                echo GetMetaTag(2, "L'auteur a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editauteur?auteur_id=" . $lid));
            }

// AFFICHER UN AUTEUR
            elseif ($act == "") {

                // Compte les albums pour lesquels les auteurs ont travaill�
                $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $auteur_id));
                $this->Auteur->load();
                $nb_auteur = intval($this->Auteur->getNbAlbumForAuteur($auteur_id));


                $this->view->set_var(array
                    ("IDAUTEUR" => $this->Auteur->ID_AUTEUR,
                    "PSEUDO" => stripslashes($this->Auteur->PSEUDO),
                    "NOM" => (stripslashes($this->Auteur->NOM)),
                    "PRENOM" => (stripslashes($this->Auteur->PRENOM)),
                    "ISSCENAR" => $this->Auteur->FLG_SCENAR == 1 ? checked : '',
                    "ISDESSIN" => $this->Auteur->FLG_DESSIN == 1 ? checked : '',
                    "ISCOLOR" => $this->Auteur->FLG_COLOR == 1 ? checked : '',
                    "COMMENT" => (stripslashes($this->Auteur->COMMENT)),
                    "DTNAIS" => $this->Auteur->DTE_NAIS,
                    "DTDECES" => $this->Auteur->DTE_DECES,
                    "DTNATION" => $this->Auteur->NATIONALITE,
                    "NBALBUMS" => $nb_auteur,
                    "URLDELETE" => BDO_URL . "admin/editauteur?act=delete&auteur_id=" . $this->Auteur->ID_AUTEUR,
                    "URLFUSION" => BDO_URL . "admin/mergeauteurs?source_id=" . $this->Auteur->ID_AUTEUR,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLACTION" => BDO_URL . "admin/editauteur?act=update"));

                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editSerie() {
        if (User::minAccesslevel(1)) {

// Tableau pour les choix d'options du status des series
            $opt_status[0][0] = 0;
            $opt_status[0][1] = 'Finie';
            $opt_status[1][0] = 1;
            $opt_status[1][1] = 'En cours';
            $opt_status[2][0] = 2;
            $opt_status[2][1] = 'One Shot';
            $opt_status[3][0] = 3;
            $opt_status[3][1] = 'Interrompue/Abandonn�e';

            $act = getVal("act");
            $conf = getVal("conf");
            $idserie = getVal("idserie");
            $this->loadModel("Serie");
            $this->loadModel("Tome");
// Mettre � jour les informations
            if ($act == "update") {
                $this->Serie->set_dataPaste(array(
                    "ID_SERIE" => postVal("txtSerieId"),
                    "NOM" => postVal("txtSerie"),
                    "ID_GENRE" => postVal('txtGenreId'),
                    "FLG_FINI" => postVal('chkFini'),
                    "NB_TOME" => postVal('txtNbTome'),
                    "TRI" => postVal('txtTri'),
                    "HISTOIRE" => postVal("txtSerieHist")
                ));
                $this->Serie->update();
                if (issetNotEmpty($this->Serie->error)) {
                    var_dump($this->Serie->error);
                    exit();
                }
                $this->Tome->updateGenreForSerie(postValInteger("txtSerieId"), postValInteger('txtGenreId'));

                echo '<META http-equiv="refresh" content="1; URL=editserie?serie_id=' . postVal("txtSerieId") . '">' . "Mise &agrave; jour effectu&eacute;e";
            }

// EFFACEMENT D'UN ALBUM
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    $this->Tome->load("c", " WHERE BD_TOME.ID_SERIE = " . $idserie);

                    $nb_tome = $this->Tome->dbSelect->nbLineResult;
                    if ($nb_tome > 0)
                        exit('La s&eacute;rie contient encore ' . $nb_tome . ' album(s). Suppression interdite.');
                    $this->Serie->set_dataPaste(array("ID_SERIE" => $idserie));
                    $this->Serie->delete();
                    if (issetNotEmpty($this->Serie->error)) {
                        var_dump($this->Serie->error);
                        exit();
                    }
                    echo 'La serie a &eacute;t&eacute; effac&eacute;e de la base.';
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous sur de vouloir effacer la s&eacute;rie n. ' . $idserie . ' ? <a href="' . BDO_URL . 'admin/editserie?act=delete&conf=ok&idserie=' . $idserie . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';

                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {

                $this->view->set_var(array(
                    "NBALBUMS" => "0",
                    "NBAUTEURS" => "0",
                    "NBNOTES" => "0",
                    "NBCOMMENTS" => "0",
                    "STYLE_NOTATION" => "",
                    "OPTSTATUS" => GetOptionValue($opt_status, 1),
                    "URLDELETE" => "javascript:alert('Déeactue;sactiv&eactue;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITGENRE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editserie?act=append"
                ));
                // assigne la barre de login
                $this->view->layout = "iframe";
                $this->view->render();
            }

// INSERE UNE NOUVELLE SERIE DANS LA BASE
            elseif ($act == "append") {
                if (postVal('txtTri') == '') {
                    $tri = substr(trim(clean_article(stripslashes(postVal('txtSerie')))), 0, 3);
                } else {
                    $tri = postVal('txtTri');
                }
                $this->Serie->set_dataPaste(array(
                    "NOM" => postVal("txtSerie"),
                    "ID_GENRE" => postVal('txtGenreId'),
                    "FLG_FINI" => postVal('chkFini'),
                    "NB_TOME" => postVal('txtNbTome'),
                    "TRI" => $tri,
                    "HISTOIRE" => postVal("txtSerieHist")
                ));
                $this->Serie->update();
                $lid = $this->Serie->ID_SERIE;
                echo GetMetaTag(2, "La s&eacute;rie a &eacute;t&eacute; ajout&eacute;e", (BDO_URL . "admin/editserie?serie_id=" . $lid));
            }

// AFFICHER UNE SERIE
            elseif ($act == "") {
                $champ_form_style = 'champ_form_desactive';
                $serie_id = getVal("serie_id");
                // Selectionne les albums pr�sents dans la s�rie
                $dbs_tome = $this->Tome->load("c", " WHERE bd_tome.ID_SERIE=" . $serie_id);

                $nb_tome = $this->Tome->dbSelect->nbLineResult;


                // Selectionne les auteurs ayant travaill� pour la s�rie
                $this->loadModel("Auteur");
                $dbs_auteur = $this->Auteur->getAuteurForSerie($serie_id);
                $nb_auteur = count($dbs_auteur);


                //r�cup�re les donn�es dans la base
                $this->Serie->set_dataPaste(array("ID_SERIE" => $serie_id));
                $this->Serie->load();

                //affichage du message de notification de note/commentaire de membre sur la serie
                $warning_note = "";
                if ($this->Serie->NB_NOTE_SERIE == '0') {
                    $warning_note = '<div>Aucun membre n\'a not&eacute;/comment&eacute; la s&eacute;rie.</div>';
                } else {
                    $warning_note = '<div class="b">Des membres ont not&eacute;/comment&eacute; la s&eacute;rie.</div>';
                }

                $this->view->set_var(array(
                    "IDSERIE" => $this->Serie->ID_SERIE,
                    "SERIE" => stripslashes($this->Serie->NOM_SERIE),
                    "TRI" => $this->Serie->TRI_SERIE,
                    "IDGENRE" => $this->Serie->ID_GENRE,
                    "GENRE" => $this->Serie->NOM_GENRE,
                    "NOTE" => $this->Serie->NB_NOTE_SERIE,
                    "WARNING_NOTE" => $warning_note,
                    "HISTOIRE_SERIE" => $this->Serie->HISTOIRE_SERIE,
                    "OPTSTATUS" => GetOptionValue($opt_status, $this->Serie->FLG_FINI),
                    "NBTOME" => $this->Serie->NB_TOME,
                    "NBALBUMS" => $this->Serie->NB_ALBUM,
                    "NBAUTEURS" => $nb_auteur,
                    "URLDELETE" => BDO_URL . "admin/editserie?act=delete&idserie=" . $this->Serie->ID_SERIE,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLEDITGENRE" => BDO_URL . "admin/editgenre?genre_id=" . $this->Serie->ID_GENRE,
                    "URLMASSDETAIL" => BDO_URL . "admin/mu_detail.php?serie=" . $this->Serie->ID_SERIE,
                    "URLMASSUPDATE" => BDO_URL . "admin/mu_serie.php?serie=" . $this->Serie->ID_SERIE,
                    "URLMASSRENAME" => BDO_URL . "admin/mu_rename.php?serie=" . $this->Serie->ID_SERIE,
                    "URLMASSCOUV" => BDO_URL . "admin/mu_couv.php?serie=" . $this->Serie->ID_SERIE,
                    "URLAJOUTALB" => BDO_URL . "admin/editalbum?act=newfserie&id_serie=" . $this->Serie->ID_SERIE,
                    "URLACTION" => BDO_URL . "admin/editserie?act=update",
                    "dbs_tome" => $dbs_tome,
                    "dbs_auteur" => $dbs_auteur
                ));

                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editGenre() {
        if (User::minAccesslevel(1)) {
            $this->loadModel("Genre");
            $act = getVal("act", "");
            $genre_id = getValInteger("genre_id");
            if ($act == "update") {
                $this->Genre->set_dataPaste(array("ID_GENRE" => postValInteger("txtIdGenre")));
                $this->Genre->load();
                $this->Genre->set_dataPaste(array("LIBELLE" => postVal('txtGenre'), "ORIGINE" => postVal('origine')));
                $this->Genre->update();
                if (issetNotEmpty($this->Genre->error)) {
                    /*
                     * Erreur : on envoit les infos
                     */
                    var_dump($this->Genre->error);
                    exit;
                }

                $this->view->addAlertPage("Mise &agrave; jour effectu&eacute;e");
                $this->view->addPhtmlFile('alert', 'BODY');
                $this->view->set_var("BODYONLOAD", "history.go(-1);");
                $this->view->layout = "iframe";
                $this->view->render();
            }
            // EFFACEMENT D'UN GENRE
            elseif ($act == "delete") {
                $conf = getVal("conf");
                if ($conf == "ok") {


                    $this->Genre->set_dataPaste(array("ID_GENRE" => $genre_id));
                    $this->Genre->load();
                    $this->Genre->delete();
                    $redirection = BDO_URL . "admin";
                    echo 'Le genre a &eacute;t&eacute; effac&eacute; de la base.<script>window.close();</script>';
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer le genre n. ' . $genre_id . ' ? <a href="' . BDO_URL . 'admin/editgenre?act=delete&conf=ok&genre_id=' . $genre_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
            // AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {
                // Creation d'un nouveau Template

                $this->view->set_var(array(
                    "NBSERIES" => "0",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editgenre?act=append"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }

// INSERE UN NOUVEAU GENRE DANS LA BASE
            elseif ($act == "append") {
                $this->Genre->set_dataPaste(array("LIBELLE" => postVal('txtGenre'), "ORIGINE" => postVal('origine')));
                $this->Genre->update();
                $lid = $this->Genre->ID_GENRE;
                echo GetMetaTag(2, "Le nouveau genre a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editgenre?genre_id=" . $lid));
            }

// AFFICHER UN GENRE
            elseif ($act == "") {

                // Compte les albums pour lesquels les auteurs ont travaill�
                $genre_id = getValInteger("genre_id");
                $nb_serie = $this->Genre->getNbSerieForGenre($genre_id);

                //r�cup�re les donn�es utilisateur dans la base de donn�e
                $this->Genre->set_dataPaste(array("ID_GENRE" => $genre_id));
                $this->Genre->load();
                $this->view->set_var(array(
                    "IDGENRE" => $this->Genre->ID_GENRE,
                    "GENRE" => stripslashes($this->Genre->LIBELLE),
                    "ORIGINE" => $this->Genre->ORIGINE,
                    "NBSERIES" => $nb_serie,
                    "URLDELETE" => BDO_URL . "admin/editgenre?act=delete&genre_id=" . $genre_id,
                    "URLFUSION" => BDO_URL . "admin/mergegenre?source_id=" . $genre_id,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLACTION" => BDO_URL . "admin/editgenre?act=update"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editEditeur() {
        if (User::minAccesslevel(1)) {
            $this->loadModel("Editeur");
            $act = getVal("act", "");
            $editeur_id = getValInteger("editeur_id");
            // Mettre � jour les informations
            if ($act == "update") {
                $this->Editeur->set_dataPaste(array(
                    "ID_EDITEUR" => postValInteger("txtIdEditeur"),
                    "NOM" => postVal('txtNomEditeur'),
                    "URL_SITE" => postVal('txtUrlSite')
                ));
                $this->Editeur->update();
                if (notIssetOrEmpty($this->Editeur->error)) {
                    var_dump($this->Editeur->error);
                    exit;
                }
                $this->view->addAlertPage("Mise &agrave; jour effectu&eacute;e");
                $this->view->addPhtmlFile('alert', 'BODY');
                //$this->view->set_var("BODYONLOAD", "history.go(-1);");
                $this->view->layout = "iframe";
                $this->view->render();
            }

// EFFACEMENT D'UN ALBUM
            elseif ($act == "delete") {
                $conf = getVal("conf", "");
                if ($conf == "ok") {
                    $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $editeur_id));
                    $this->Editeur->load();
                    $this->Editeur->delete();

                    echo 'L\'&eacutediteur a &eacutet&eacute effac&eacute de la base.';
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous sur de vouloir effacer l\'&eacutediteur n. ' . $editeur_id . ' ? <a href="' . BDO_URL . 'admin/editediteur?act=delete&conf=ok&editeur_id=' . $editeur_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {

                $this->view->set_var(array
                    ("NBCOLLEC" => "0",
                    "URLDELETE" => "javascript:alert('D&eacutesactiv&eacute');",
                    "URLADDCOLLEC" => "javascript:alert('D&eacutesactiv&eacute');",
                    "URLFUSION" => "javascript:alert('D&eacutesactiv&eacute');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editediteur?act=append"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }
// INSERE UN NOUVEL EDITEUR DANS LA BASE
            elseif ($act == "append") {
                $this->Editeur->set_dataPaste(array(
                    "NOM" => postVal("txtNomEditeur"),
                    "URL_SITE" => postVal("txtUrlSite")
                ));
                $this->Editeur->update();
                $lid = $this->Editeur->ID_EDITEUR;

                // on ajpute la collection par défaut
                // Insère un collection <N/A> pour cet �diteur
                $this->loadModel("Collection");
                $this->Collection->set_dataPaste(array(
                    "ID_EDITEUR" => $lid,
                    "NOM" => "<N/A>"
                ));
                $this->Collection->update();

                echo GetMetaTag(2, "L'&eacute;diteur a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editediteur?editeur_id=" . $lid));
            }

// AFFICHER UN EDITEUR
            elseif ($act == "") {

                $this->loadModel("Collection");
                $dbs_collection = $this->Collection->load("c", " WHERE bd_editeur.id_editeur=" . $editeur_id);


                $nb_collec = $this->Collection->dbSelect->nbLineResult;

                //r�cup�re les donn�es editeur dans la base de donn�e
                $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $editeur_id));
                $this->Editeur->load();
                $this->view->set_var(array
                    ("IDEDITEUR" => $this->Editeur->ID_EDITEUR,
                    "NOM" => $this->Editeur->NOM,
                    "URLWEBSITE" => $this->Editeur->URL_SITE,
                    "NBCOLLEC" => $nb_collec,
                    "URLDELETE" => BDO_URL . "admin/editediteur?act=delete&editeur_id=" . $editeur_id,
                    "URLFUSION" => BDO_URL . "admin/mergeediteur?source_id=" . $editeur_id,
                    "URLADDCOLLEC" => BDO_URL . "admin/editcollection?act=new&editeur_id=" . $editeur_id,
                    "ACTIONNAME" => "Valider les Modifications",
                    "dbs_collection" => $dbs_collection,
                    "URLACTION" => BDO_URL . "admin/editediteur?act=update"));
                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editCollection() {
        /*
         * Methode d'ajout / suppression / modification d'une collection
         */
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            $conf = getVal("conf", "");
            $collec_id = getValInteger("collec_id");
            $this->loadModel("Collection");
            // Mettre à jour les informations
            if ($act == "update") {
                $this->Collection->set_dataPaste(array(
                    "NOM" => postVal('txtNomColl'),
                    "ID_EDITEUR" => postVal('txtEditeurId'),
                    "ID_COLLECTION" => postValInteger("txtIdColl")
                ));
                $this->Collection->update();
                echo '<META http-equiv="refresh" content="2; URL=javascript:history.go(-1)">' . "Mise &agrave; jour effectu&eacute;e";
            }

// EFFACEMENT D'UNE COLLECTION
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    $this->Collection->set_dataPaste(array("ID_COLLECTION" => $collec_id));
                    $this->Collection->delete();

                    echo 'La collection a &eacute;t&eacute; effac&eacute;e de la base.';
                    exit();
                } else {
                    // Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer la collection  n. ' . $collec_id . ' ? <a href="' . BDO_URL . 'admin/editcollection?act=delete&conf=ok&collec_id=' . $collec_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {
                $editeur_id = getValInteger("editeur_id", 0);
                if ($editeur_id) {// Un �diteur a �t� pass� dans l'URL
                    $this->loadModel("Editeur");
                    $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $editeur_id));
                    $this->Editeur->load();

                    $this->view->set_var(array(
                        "IDEDITEUR" => $this->Editeur->ID_EDITEUR,
                        "EDITEUR" => htmlentities(stripslashes($this->Editeur->NOM)),
                    ));
                }

                $this->view->set_var(array(
                    "NBCOLALB" => "0",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITEDIT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editcollection?act=append"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }

// INSERE UNE NOUVELLE COLLECTION DANS LA BASE
            elseif ($act == "append") {
                $this->Collection->set_dataPaste(array(
                    'NOM' => postVal('txtNomColl'),
                    'ID_EDITEUR' => postValInteger("txtEditeurId")
                ));
                $this->Collection->update();
                $lid = $this->Collection->ID_COLLECTION;
                echo GetMetaTag(2, "La collection a &eacute;t&eacute; ajout&eacute", (BDO_URL . "admin/editcollection?collec_id=" . $lid));
                exit();
            }

// AFFICHER UNE COLLECTION
            elseif ($act == "") {
                // on compte le nombre d'albums dans la collection
                $nb_albums = $this->Collection->getNbAlbumForCollection($collec_id);
                $this->Collection->set_dataPaste(array("ID_COLLECTION" => $collec_id));
                $this->Collection->load();
                //r�cup�re les donn�es



                $this->view->set_var(array(
                    "IDCOLL" => $this->Collection->ID_COLLECTION,
                    "NOM" => htmlentities(stripslashes($this->Collection->NOM)),
                    "IDEDITEUR" => $this->Collection->ID_EDITEUR,
                    "EDITEUR" => htmlentities(stripslashes($this->Collection->NOM_EDITEUR)),
                    "NBCOLALB" => $nb_albums,
                    "URLDELETE" => BDO_URL . "admin/editcollection?act=delete&collec_id=" . $this->Collection->ID_COLLECTION,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Collection->ID_EDITEUR,
                    "URLACTION" => BDO_URL . "admin/editcollection?act=update"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
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
            $edition = $this->User_album_prop->ID_EDTION;
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
                if (file_exists(BDO_DIR . "images/tmp/$prop_img")) {
                    @unlink(BDO_DIR . "images/tmp/$prop_img");
                }
            }

            if (postVal("chkResize") == "checked" && $edition != 0) {

                //Redimensionnement
                //*****************

                $max_size = 180;
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
                $next_url = BDO_URL . "admin/editAlbum?id_tome=" . $lid;
            }

            echo GetMetaTag(2, "L'album a &eacute;t&eacute; mis a jour", $next_url);
        }
    }

    private function imgCouvFromUrl($url_ary, $lid_tome, $lid_edition) {
        /*
         * Récupère une image de couvertue et la copie dans le répertoire fournit en paramètre
         * Return : nom du fichier
         */
        if (empty($url_ary[4])) {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incompl&egrave;te. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }
        $base_get = '/' . $url_ary[4];
        $port = (!empty($url_ary[3]) ) ? $url_ary[3] : 80;
        // Connection au serveur hébergeant l'image
        if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr))) {
            $error = true;
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }

        // Récupère l'image
        @fputs($fsock, "GET $base_get HTTP/1.1\r\n");
        @fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
        @fputs($fsock, "Connection: close\r\n\r\n");

        unset($avatar_data);
        while (!@feof($fsock)) {
            $avatar_data .= @fread($fsock, 102400);
        }
        @fclose($fsock);

        // Check la validité de l'image
        if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)) {
            $error = true;
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du t&eacute;l&eacute;chargement de l\'image. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }
        $avatar_filesize = $file_data1[1];
        $avatar_filetype = $file_data2[1];
        $avatar_data = substr($avatar_data, strlen($avatar_data) - $avatar_filesize, $avatar_filesize);
        $tmp_path = BDO_DIR_UPLOAD;
        $tmp_filename = tempnam($tmp_path, uniqid(rand()) . '-');
        $fptr = @fopen($tmp_filename, 'wb');
        $bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);
        @fclose($fptr);

        if ($bytes_written != $avatar_filesize) {
            @unlink($tmp_filename);
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }

        // newfilemname
        if (!($imgtype = check_image_type($avatar_filetype, $error))) {
            exit;
        }
        $newfilename = "CV-" . sprintf("%06d", $lid_tome) . "-" . sprintf("%06d", $lid_edition) . $imgtype;

        // si le fichier existe, on l'efface
        if (file_exists(BDO_DIR_COUV . "$newfilename")) {
            @unlink(BDO_DIR_COUV . "$newfilename");
        }

        // copie le fichier temporaire dans le repertoire image
        @copy($tmp_filename, BDO_DIR_COUV . "$newfilename");
        unlink($tmp_filename);
        return $newfilename;
    }

    private function imgCouvFromForm($lid_tome, $lid_edition) {
        $imageproperties = getimagesize($_FILES['txtFileLoc']['tmp_name']);
        $imagetype = $imageproperties[2];

        $newfilename = "CV-" . sprintf("%06d", $lid_tome) . "-" . sprintf("%06d", $lid_edition);
        // vérifie le type d'image
        switch ($imagetype) {
            case IMAGETYPE_GIF:
                $newfilename .=".gif";
                break;
            case IMAGETYPE_JPEG:
                $newfilename .=".jpg";
                break;
            case IMAGETYPE_PNG:
                $newfilename .=".png";
                break;
            default:
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers PNG, JPEG ou GIF peuvent &ecirc;tre charg&eacute;s. Vous allez &ecirc;tre redirig&eacute;.';
                exit();
                break;
        }

        //move_uploaded_file fait un copy(), mais en plus il vérifie que le fichier est bien un upload
        //et pas un fichier local (genre constante.php, au hasard)
        if (!move_uploaded_file($_FILES['txtFileLoc']['tmp_name'], BDO_DIR_COUV . $newfilename)) {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }
        return $newfilename;
    }

    private function resize_edition_image($id_edition, $imagedir) {
        //Redimensionnement : à revoir
        //*****************
        // cherche les infos de cette �dition
        $this->loadModel("Edition");
        $this->Edition->set_dataPaste(array("ID_EDITION" => $id_edition));
        $this->Edition->load();


        $id_tome = $this->Edition->ID_TOME;
        $url_img = $this->Edition->IMG_COUV;


        if ($url_img == '') {
            echo "error : no image in database<br/>";
        } else {
            $newfilename = $url_img;

            $max_size = 180;

            //if ($_SERVER["SERVER_NAME"] != 'localhost')
            $imageproperties = getimagesize($imagedir . $newfilename);
            //else $imageproperties = false;

            if ($imageproperties != false) {
                $imagetype = $imageproperties[2];
                $imagelargeur = $imageproperties[0];
                $imagehauteur = $imageproperties[1];

                //D�termine s'il y a lieu de redimensionner l'image
                if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $max_size)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

                    if ($imagelargeur < $imagehauteur) {
                        // image de type panorama : on limite la largeur � 128
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
                        $source = imagecreatefromgif($imagedir . $newfilename);
                        break;

                    case "2":
                        $source = imagecreatefromjpeg($imagedir . $newfilename);
                        break;

                    case "3":
                        $source = imagecreatefrompng($imagedir . $newfilename);
                        break;

                    case "6":
                        $source = imagecreatefrombmp($imagedir . $newfilename);
                        break;
                }

                imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

                switch ($imagetype) {
                    case "2":
                        unlink($imagedir . $newfilename);
                        imagejpeg($new_image, $imagedir . $newfilename, 100);
                        break;

                    case "1":
                    case "3":
                    case "6":
                        unlink($imagedir . $newfilename);
                        $img_couv = substr($newfilename, 0, strlen($newfilename) - 3) . "jpg";
                        imagejpeg($new_image, $imagedir . $img_couv, 100);
                }
            } else {
                echo "error : no image properties <br/>";
            }

            echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
            echo "Image redimensionn�e<br />";
        }
    }

    public function Controle() {


        if (User::minAccesslevel(1)) {
            ob_start();

            set_time_limit(360);

            $a_queryRegle = array(
                array(
                    "title" => "Nom de série ne contenant pas la valeur de la colonne TRI",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    `ID_SERIE`,
                    `NOM`,
                    `TRI` 
            FROM 
                    `bd_serie` 
            WHERE 
                    `NOM` NOT LIKE concat( '%', `TRI`, '%' )",
                    "url" => BDO_URL . "admin/editserie?serie_id=",
                    "colUrl" => "ID_SERIE",
                ),
                array(
                    "title" => "EAN référencés plusieurs fois dans la table des éditions pour des albums différents (parution >31/12/2006 ou non-renseignée)",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    COUNT(DISTINCT(`ID_TOME`)) AS 'ID albums différents',
                    `EAN` ,
                    GROUP_CONCAT(distinct(`ID_EDITION`) SEPARATOR ';') as 'Liens vers les éditions (séparateur ;)'
            FROM 
                    `bd_edition` 
            WHERE 
                    `EAN` IS NOT NULL 
                                    AND TRIM(`EAN`)<>''
            AND (`DTE_PARUTION` > '2006-12-31' OR `DTE_PARUTION` IS NULL )
            GROUP BY `EAN` 
            HAVING COUNT(DISTINCT(`ID_TOME`))>1  
            ORDER BY 1 DESC",
                    "colExplode" => 'Liens vers les éditions (séparateur ;)',
                    "urlExplode" => "<a href='" . BDO_URL . "admin/editedition?edition_id={col}' target='_blank'>{col}</a>",
                ),
                array(
                    "title" => "ISBN référencés plusieurs fois dans la table des éditions pour des albums différents (parution >31/12/1973 ou non-renseignée)",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    COUNT(DISTINCT(`ID_TOME`)) AS 'ID albums différents', 
                    `ISBN` ,
                    GROUP_CONCAT(distinct(`ID_EDITION`) SEPARATOR ';') as 'Liens vers les �ditions (s�parateur ;)'
            FROM 
                    `bd_edition` 
            WHERE 
                    `ISBN` IS NOT NULL 
                    AND TRIM(`ISBN`)<>''
            AND (`DTE_PARUTION` > '1973-12-31' OR `DTE_PARUTION` IS NULL) 

            GROUP BY `ISBN`  
            HAVING COUNT(DISTINCT(`ID_TOME`))>1  
            ORDER BY 1 DESC",
                    "colExplode" => 'Liens vers les éditions (séparateur ;)',
                    "urlExplode" => "<a href='" . BDO_URL . "admin/editedition?edition_id={col}' target='_blank'>{col}</a>",
                ),
                array(
                    "title" => "Triplet PSEUDO, NOM, PRENOM référencés plusieurs fois dans la table des auteurs",
                    "query" => "
            SELECT 
                    bd_auteur.ID_AUTEUR,
                    bd_auteur.`PSEUDO`,
                    bd_auteur.`PRENOM`,
                    bd_auteur.`NOM`  
            FROM `bd_auteur`, 
            (
                    SELECT 
                    `PSEUDO`,
                    `PRENOM`,
                    `NOM` 
                    FROM `bd_auteur` 
                    GROUP BY `PSEUDO`,`PRENOM`,`NOM` 
                    HAVING count(*)>1
            ) withDoublon
            WHERE 
                    bd_auteur.`PSEUDO`=withDoublon.`PSEUDO`
                    AND bd_auteur.`PRENOM`=withDoublon.`PRENOM`
                    AND bd_auteur.`NOM`=withDoublon.`NOM`",
                    "url" => BDO_URL . "admin/adminauteurs.php?auteur_id=",
                    "colUrl" => "ID_AUTEUR",
                ),
                array(
                    "title" => "PSEUDO référencés plusieurs fois dans la table des auteurs",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    COUNT(*) AS `Enregistrements`, 
                    `PSEUDO` 
            FROM 
                    `bd_auteur` 
            GROUP BY `PSEUDO` 
            HAVING count(*)>1  
            ORDER BY `Enregistrements` DESC",
                ),
                array(
                    "title" => "Couple NOM, PRENOM référencés plusieurs fois dans la table des auteurs",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    COUNT(*) AS `Enregistrements`, 
                    `NOM`,
                    `PRENOM`,
                    GROUP_CONCAT(`PSEUDO` SEPARATOR ' ; ') as 'Liste des pseudos (séparateur ;)' 
            FROM 
                    `bd_auteur`
            WHERE 
                    `NOM` IS NOT NULL
                    AND `PRENOM` IS NOT NULL
            GROUP BY `NOM`,`PRENOM` 
            HAVING count(*)>1 
            ORDER BY `Enregistrements` DESC",
                ),
                array(
                    "title" => "Couple NOM, ID_EDITEUR référencés plusieurs fois dans la table des collections",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS
                    `bd_collection`.`ID_COLLECTION`,
                    `bd_collection`.`NOM`,
                    `bd_collection`.`ID_EDITEUR`  
            FROM `bd_collection`, 
            (
                    SELECT 
                            `NOM`,
                            `ID_EDITEUR` 
                    FROM 
                            `bd_collection` 
                    GROUP BY `NOM`,`ID_EDITEUR` 
                    HAVING count(*)>1
            ) withDoublon
            WHERE 
                    `bd_collection`.`NOM`=withDoublon.`NOM`
                    AND `bd_collection`.`ID_EDITEUR`=withDoublon.`ID_EDITEUR`",
                    "url" => BDO_URL . "admin/editcollection?collec_id=",
                    "colUrl" => "ID_COLLECTION",
                ),
                array(
                    "title" => "triplet date / collection / Tome présent dans la table des éditions",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    COUNT(*) AS `Enregistrements`, 
                    `ID_TOME` 
            FROM 
                    `bd_edition` 
            GROUP BY `ID_TOME`,`ID_COLLECTION`,`DTE_PARUTION` 
            HAVING COUNT(*)>1  
            ORDER BY `Enregistrements` DESC",
                    "url" => BDO_URL . "admin/editalbum?alb_id=",
                    "colUrl" => "ID_TOME",
                ),
                array(
                    "title" => "Séries déclarées one-shot (FLG_FINI=2) avec plus de 1 tome",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS
                    `bd_serie`.`ID_SERIE` , 
                    count( bd_tome.ID_TOME ) as 'nbr de tomes'
            FROM 
                    `bd_serie`
                    INNER JOIN `bd_tome` ON `bd_tome`.`ID_SERIE` = `bd_serie`.`ID_SERIE`
            WHERE 
                    `bd_serie`.`FLG_FINI` =2
            GROUP BY `bd_serie`.`ID_SERIE`
            HAVING count( `bd_tome`.`ID_TOME` ) >1  
            ORDER BY 2 DESC",
                    "url" => BDO_URL . "admin/editserie?serie_id=",
                    "colUrl" => "ID_SERIE",
                ),
                array(
                    "title" => "Albums de série one-shot (1 seul album) titre différent de celui de la série",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS 
                    `bd_serie`.`ID_SERIE` , 
                    `bd_serie`.`NOM` AS 'Titre de la série', 
                    `bd_tome`.`ID_TOME` , 
                    `bd_tome`.`TITRE` AS 'Titre de l''album'
            FROM 
                    `bd_serie`
                    INNER JOIN `bd_tome` ON `bd_tome`.`ID_SERIE` = `bd_serie`.`ID_SERIE`
            WHERE 
                    `bd_serie`.`FLG_FINI` =2
                    AND `bd_serie`.`NOM` <> `bd_tome`.`TITRE`
            GROUP BY `bd_serie`.`ID_SERIE`
            HAVING count(`bd_tome`.`ID_TOME`)=1",
                    "url" => BDO_URL . "admin/editalbum?alb_id=",
                    "colUrl" => "ID_TOME",
                ),
                array(
                    "title" => "Editions dont la date de parution n'est pas renseignée (ou < 1800-01-01) (non marquées 'Introuvable')",
                    "query" => "
            SELECT SQL_CALC_FOUND_ROWS
                    `ID_EDITION` , 
                    `DTE_PARUTION`
            FROM `bd_edition`
            WHERE 
                    (`DTE_PARUTION` IS NULL
                    OR `DTE_PARUTION` < '1800-01-01')
                    AND `FLAG_DTE_PARUTION` IS NULL
            ORDER BY `bd_edition`.`DTE_PARUTION` DESC",
                    "url" => BDO_URL . "admin/editedition?edition_id=",
                    "colUrl" => "ID_EDITION"
            ));


          
            echo '(Le resultat est limit&eacute; &agrave; 200 lignes)';

            echo '<form name="formregle" method="post">
                <div>
                Controle : 
                <select name="id_queryRegle">';
            foreach ($a_queryRegle as $id_queryRegle => $queryRegle) {
                $selected = ($id_queryRegle == $_POST['id_queryRegle']) ? 'SELECTED' : '';
                echo '<option value="' . $id_queryRegle . '" ' . $selected . ' >' . $queryRegle['title'] . '</option>';
            };
            echo '</select><br />
            <label for="viewQuery"><input type="checkbox" id="viewQuery" name="viewQuery" value="checked" ' . postVal('viewQuery') . '> voir la requete</label>
            <br /><input type="submit" name="execformvalue" value="Chercher">
            </div>
            </form>';


            if (issetNotEmpty(postVal('execformvalue')) and issetNotEmpty(postVal('id_queryRegle')) ) {
                $title = $a_queryRegle[postVal('id_queryRegle]')]["title"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["url"])) $url = $a_queryRegle[postVal('id_queryRegle')]["url"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["colUrl"]))
                    $colUrl = $a_queryRegle[postVal('id_queryRegle')]["colUrl"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["colExplode"]))
                    $colExplode = $a_queryRegle[postVal('id_queryRegle')]["colExplode"];
                if (isset($a_queryRegle[postVal('id_queryRegle')]["urlExplode"]))
                    $urlExplode = $a_queryRegle[postVal('id_queryRegle')]["urlExplode"];

                $query = $a_queryRegle[postVal('id_queryRegle')]["query"];
                $query .= " LIMIT 0,200";

                if (issetNotEmpty(postVal('viewQuery'))) {
                    echo_pre($query);
                }

                $resultat = Db_query($query);

                $nbr = Db_CountRow($resultat);

                $a_obj = array();
                $cmpt = 0;
                while ($obj = Db_fetch_object($resultat)) {
                    if (isset($colUrl)) {
                        $obj->voir = '<a href="' . $url . $obj->$colUrl . '" target="_blank">Voir</a>';
                    }
                    if (isset($colExplode)) {
                        $a_fieldData = explode(';', $obj->{$colExplode});
                        foreach ($a_fieldData as $key => $data) {
                            $a_fieldData[$key] = str_replace('{col}', $data, $urlExplode);
                        }
                        $obj->{$colExplode} = implode(' ; ', $a_fieldData);
                    }
                    $a_obj[] = $obj;
                    $cmpt++;
                }


                if ($nbr > 0) {
                    echo '<h3>' . $title . '</h3>';
                    echo $cmpt . ' lignes sur ' . $nbr;
                    tableOfFetchObj($a_obj, $a_onlyCol, false);
                } else {
                    echo 'Aucune ligne de resultat !';
                }
               
            }



            $this->view->set_var("PAGE_OB", ob_get_clean());

            $this->view->render();
        };
    }

}

