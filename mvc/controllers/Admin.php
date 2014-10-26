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

    public function Proposition() {
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
                    $urledit = "./editEdition?ID=";
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
                    
                } else {
                    // on charge la fiche suivante
                }
            }
            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editPropositionAjout() {
        /*
         * Affichage d'une proposition d'ajout ou correction
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

            $opt_action[0] = "Ins&eacuterer dans la collection";
            $opt_action[1] = "Ins&eacuterer comme achat futur";
            $opt_action[2] = "Aucune";
            $opt_type[0][0] = 0;

            $opt_status[0][0] = 0;
            $opt_status[0][1] = "En cours";
            $opt_status[1][0] = 2;
            $opt_status[1][1] = "En pause";
            $opt_status[2][0] = 3;
            $opt_status[2][1] = "Aide requise";
            $opt_status[3][0] = 4;
            $opt_status[3][1] = "Aide apport&eacutee";

            $opt_type[0][1] = 'Album';
            $opt_type[1][0] = 1;
            $opt_type[1][1] = 'Coffret';

            // Determine l'URL image
            if (is_null($this->User_album_prop->IMG_COUV) | ($this->User_album_prop->IMG_COUV == '')) {
                $url_image = BDO_URL_COUV . "default.png";
            } else {
                $url_image = BDO_URL_IMAGE ."tmp/". $this->User_album_prop->IMG_COUV;
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
            $mail_body .= " a &eacutet&eacute refus&eacutee par l'&eacutequipe de correction. \n";
            $mail_body .= "- Les informations que vous avez fournies n'&eacutetaient pas suffisantes. \n";
            $mail_body .= "- La proposition d'un autre membre a &eacutet&eacute pr&eacutef&eacuter&eacutee ou valid&eacutee avant. \n";
            $mail_body .= "- Nous consid&eacuterons que cet album n'a pas de rapport suffisamment proche &agrave; la bande dessin&eacutee pour &ecirc;tre int&eacutegr&eacute &agrave; la base de donn&eacutees du site. \n";
            $mail_body .= "- Cet album figurait d&eacutej&agrave; dans votre collection. \n";
            $mail_body .= "Si l'&eacutedition par d&eacutefaut de cet album ne correspond pas &agrave; celle que vous poss&eacutedez,";
            $mail_body .= "	d'autres &eacuteditions sont peut-&ecirc;tre d&eacutej&agrave; pr&eacutesentes dans la base et peuvent &eacutetre s&eacutelectionn&eacutees en cliquant sur l'album en question depuis votre garde-manger (menu d&eacuteroulant [Mon &eacutedition] des fiches album). \n";
            $mail_body .= "Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle &eacutedition via ce m&ecirc;me menu d&eacuteroulant.\n\n";
            $mail_body .= "Merci de votre compr&eacutehension, \n";
            $mail_body .= "L'&eacutequipe BDOVORE";
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
                    "SERIE"=> postVal("txtSerie"),
                    "TITRE"=> postVal("txtTitre"),
                    "FLG_TYPE"=> postVal("lstType"),
                    "NUM_TOME"=> postVal("txtNumTome"),
                    "FLG_INT" => ((postVal("chkIntegrale") == "checkbox") ? "O" : "N"),
                    "PRIX"=> postVal("txtPrixVente"),
                    "HISTOIRE"=> postVal("txtHistoire"),
                    "ID_GENRE"=> postVal("txtGenreId"),
                    "GENRE"=> postVal("txtGenre"),
                    "ID_SCENAR"=> postVal("txtScenarId"),
                    "SCENAR"=> postVal("txtScenar"),
                    "ID_SCENAR_ALT"=> postVal("txtScenarAltId"),
                    "SCENAR_ALT"=> postVal("txtScenarAlt"),
                    "ID_DESSIN"=> postVal("txtDessiId"),
                    "DESSIN"=> postVal("txtDessi"),
                    "ID_DESSIN_ALT"=> postVal("txtDessiAltId"),
                    "DESSIN_ALT"=> postVal("txtDessiAlt"),
                    "ID_COLOR"=> postVal("txtColorId"),
                    "COLOR"=> postVal("txtColor"),
                    "ID_COLOR_ALT"=> postVal("txtColorAltId"),
                    "COLOR_ALT"=> postVal("txtColorAlt"),
                    "ID_EDITEUR"=> postVal("txtEditeurId"),
                    "EDITEUR"=> postVal("txtEditeur"),
                    "ID_COLLECTION"=> postVal("txtCollecId"),
                    "COLLECTION"=> postVal("txtCollec"),
                    "ISBN"=> postVal("txtISBN"),
                    "EAN"=> postVal("txtEAN"),
                    "DTE_PARUTION"=> postVal("txtDateParution"),
                    "FLG_TT" => ((postVal("chkTT") == "checkbox") ? "O" : "N"),
                    "DESCRIB_EDITION"=> postVal("txtCommentEdition")
                ));
                $this->User_album_prop->update();
                if (issetNotEmpty($this->User_album_prop->error)) {
                    var_dump($this->User_album_prop->error);
                    exit();
                }
                // Retourne sur la page proposition
                header("Location:".BDO_URL."admin/editPropositionAjout?ID=$id");
                exit ();
             
	
                
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
                    echo "Album ajout&eacute dans la table bd_tome<br />";

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
                echo "Nouvelle &eacutedition ins&eacuter&eacutee dans la table id_edition<br />";

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


                    echo "Nouvelle image ins&eacuter&eacutee dans la base<br />";
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


                        echo "Image propos&eacutee ins&eacuter&eacutee dans la base<br />";
                    }
                }

                // On rajoute un redimensionnement si le correcteur l'a voulu

                if ($_POST["chkResize"] == "checked") {

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
                    echo "Image redimensionn&eacutee<br />";
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
                    echo "Album ajout&eacute dans la collection de l'utilisateur<br />";
                }

                //Efface le fichier de la base et passe le status de l'album à validé
                if ($prop_img != '') {
                    if (file_exists(BDO_DIR_UPLOAD . $prop_img)) {
                        @unlink(BDO_DIR_UPLOAD . $prop_img);
                    }
                }
                $this->User_album_prop->set_dataPaste(array("STATUS" => 1, "VALIDATOR" => $_SESSION["userConnect"]->user_id));
                $this->User_album_prop->update();

                // Envoie un mail si n�cessaire pour pr�venir l'utilisateur
                if ($notif_mail == 1) {
                    $mail_action[0] = "L'album a &eacutet&eacute ajout&eacute &agrave; votre collection, comme demand&eacute.\n\n";
                    $mail_action[1] = "L'album a &eacutet&eacute ajout&eacute dans vos achats futurs, comme demand&eacute.\n\n";
                    $mail_action[2] = "L'album n'a pas &eacutet&eacute ajout&eacute &agrave; votre collection, comme demand&eacute.";


                    $mail_adress = $this->User_album_prop->EMAIL;
                    $mail_sujet = "Ajout d'un album dans la base BDOVORE";
                    $mail_entete = "From: no-reply@bdovore.com";
                    $mail_text = "Bonjour, \n\n";
                    $mail_text .="Votre proposition d'ajout &agrave; la base de donn&eacutees de BDOVORE a &eacutet&eacute valid&eacutee.\n\n";
                    $mail_text .="Titre : " . $_POST['txtTitre'] . "\n";
                    $mail_text .=$mail_action[$prop_action];
                    $mail_text .="Merci pour votre participation\n\n";
                    $mail_text .="L'&eacutequipe BDOVORE";
                    mail($mail_adress, $mail_sujet, $mail_text, $mail_entete);
                    echo "Email de confirmation envoy&eacute<br />";
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

                echo GetMetaTag(1, "L'album a &eacutet&eacute ajout&eacute", $next_url);
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

        if ($this->Useralbum->nbLineResult > 0) {
            echo GetMetaTag(1, "Cet album est d&eacutej&agrave; pr&eacutesent dans la collection de l'utilisateur", BDO_URL . "admin/adminproposals.php?act=valid&propid=" . $propid);
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
            echo "L'album s&eacutelectionn&eacute a &eacutet&eacute ajout&eacute &agrave; la collection de l'utilisateur<br />";

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
                $mail_action[0] = "Cet album a &eacutet&eacute plac&eacute dans votre collection, comme demand&eacute.\n\n";
                $mail_action[1] = "Cet album a &eacutet&eacute plac&eacute dans vos achats futurs, comme demand&eacute.\n\n";

                // Récupère l'adresse du posteur et compose l'email

                $mail_adress = $this->User_album_prop->EMAIL;
                $mail_sujet = "Ajout d'un album dans la base BDOVORE";
                $mail_entete = "From: no-reply@bdovore.com";
                $mail_text = "Bonjour, \n\n";
                $mail_text .="Proposition : " . postVal('txtTitre') . "\n";
                $mail_text .= "Votre proposition d'ajout &agrave; la base de donn&eacutees n'a pas &eacutet&eacute accept&eacutee car l'album en question y figurait d&eacuteja. \n";
                $mail_text .=$mail_action[$prop_action];
                $mail_text .= "Si l'&eacutedition par d&eacutefaut de cet album ne correspond pas &agrave; celle que vous poss&eacutedez,
							d'autres &eacuteditions sont peut-&ecirc;tre d&eacutej&agrave; pr&eacutesentes dans la base et peuvent &ecirc;tre
							s&eacutelectionn&eacutees en cliquant sur l'album en question depuis votre garde-manger (menu d&eacuteroulant [Mon &eacutedition]
							des fiches album). Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle &eacutedition via ce m&ecirc;me
							menu d&eacuteroulant.\n\n";
                $mail_text .="L'&eacutequipe BDOVORE";
                mail($mail_adress, $mail_sujet, $mail_text, $mail_entete);
                echo "Email d'information envoy&eacute &agrave; l'utilisateur<br />";
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
        // echo GetMetaTag(1, "Bien jou&eacute; !", $next_url);
    }

    public function commentProposition() {
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

    public function editEdition() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editAlbum() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editAuteur() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editSerie() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editGenre() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editCollection() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    private function imgCouvFromUrl($url_ary, $lid_tome, $lid_edition) {
        /*
         * Récupère une image de couvertue et la copie dans le répertoire fournit en paramètre
         * Return : nom du fichier
         */
        if (empty($url_ary[4])) {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incompl�te. Vous allez &ecirc;tre redirig&eacute.';
            exit();
        }
        $base_get = '/' . $url_ary[4];
        $port = (!empty($url_ary[3]) ) ? $url_ary[3] : 80;
        // Connection au serveur hébergeant l'image
        if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr))) {
            $error = true;
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez �tre redirig�.';
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
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du t�l�chargement de l\'image. Vous allez �tre redirig�.';
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
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez �tre redirig�.';
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
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers PNG, JPEG ou GIF peuvent &ecirc;tre charg&eacutes. Vous allez &ecirc;tre redirig&eacute.';
                exit();
                break;
        }

        //move_uploaded_file fait un copy(), mais en plus il vérifie que le fichier est bien un upload
        //et pas un fichier local (genre constante.php, au hasard)
        if (!move_uploaded_file($_FILES['txtFileLoc']['tmp_name'], BDO_DIR_COUV . $newfilename)) {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez &ecirc;tre redirig&eacute.';
            exit();
        }
        return $newfilename;
    }

}

