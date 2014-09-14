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
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function Proposition() {
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            $update = getVal("chkUpdate", "");
            if ($update == 'O') {
                $act = "update";
            }
            $type = getVal("type", "AJOUT");

            $validationdelay = 21; //nbre de jours aprés lesquels on ne valide pas (pour les parutions futures)
            
            // LISTE LES PROPOSALS
            if ($act == "") {
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
            }


            $this->view->set_var($this->User_album_prop->getAllStat());
            $this->view->set_var("PAGETITLE", "Administration des propositions");
            $this->view->render();
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }
    
    private function getDateBeforeValid() {
         $validationdelay = 21; //nbre de jours aprés lesquels on ne valide pas (pour les parutions futures)
         $datebeforevalid = "Ne pas valider les albums qui paraissent après le " . date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $validationdelay, date("Y"))) . " ($validationdelay jours)";
         
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
                    
                    if (file_exists(BDO_DIR_UPLOAD.$prop_img)) {
                        unlink(BDO_DIR_UPLOAD.$prop_img);
                    }
                }
                if ($mail <> "") {
                    
                    $mail_sujet = "Votre proposition d'ajout dans la base BDOVORE";
                    $mail_entete = "From: no-reply@bdovore.com";
                    $mail_text = stripslashes($mail)."\n\n";
                    mail($notif_mail,$mail_sujet,$mail_text,$mail_entete);
                }
               if ($src == "list") {
                   
               }   else {
                   // on charge la fiche suivante
                   
               }
                   
            }
            $this->view->render();
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editPropositionAjout() {
        if (User::minAccesslevel(1)) {
            $id = getValInteger("ID");
            $this->loadModel("User_album_prop");
            $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $id));
           // chargement des données complètes
            $this->User_album_prop->setWithAlbumInfo($bool=true);
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
            
            $opt_action[0] = "Insérer dans la collection";
            $opt_action[1] = "Insérer comme achat futur";
            $opt_action[2] = "Aucune";
            $opt_type[0][0] = 0;
            
            $opt_status[0][0] = 0;
            $opt_status[0][1] = "En cours";
            $opt_status[1][0] = 2;
            $opt_status[1][1] = "En pause";
            $opt_status[2][0] = 3;
            $opt_status[2][1] = "Aide requise";
            $opt_status[3][0] = 4;
            $opt_status[3][1] = "Aide apportée";

            $opt_type[0][1] = 'Album';
            $opt_type[1][0] = 1;
            $opt_type[1][1] = 'Coffret';
            
            // Determine l'URL image
            if (is_null($this->User_album_prop->IMG_COUV) | ($this->User_album_prop->IMG_COUV=='')){
                    $url_image = BDO_URL_COUV."default.png";
            }else{
                    $url_image = BDO_DIR_UPLOAD.$this->User_album_prop->IMG_COUV;
                    $dim_image = imgdim($url_image);
            }
        
            $this->view->set_var (array(
                "DATEBEFOREVALID" => $this->getDateBeforeValid(),
                "PROPID" => stripslashes($this->User_album_prop->ID_PROPOSAL),
                "TITRE" => stripslashes($this->User_album_prop->TITRE),
                "CLTITRE" => ($this->User_album_prop->TITRE !='' ? "flat" : "to_be_corrected"),
                "ORITITRE" => stripslashes($this->User_album_prop->TITRE),
                "IDSERIE" => stripslashes($this->User_album_prop->ID_SERIE),
                "CLIDSERIE" => (is_numeric($this->User_album_prop->ID_SERIE) ? "flat" : "to_be_corrected"),
                "ORISERIE" => stripslashes($this->User_album_prop->SERIE),
                "TOME" => $this->User_album_prop->NUM_TOME,
                "CLTOME" => "flat",
                "PRIX_VENTE" => $this->User_album_prop->PRIX,
                "ISINT" => (($this->User_album_prop->FLG_INT=='O') ? 'checked' : ''),
                "OPTTYPE" => GetOptionValue($opt_type,$this->User_album_prop->FLG_TYPE),
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
                "URLEAN" => "http://www.bdnet.com/".$this->User_album_prop->EAN."/alb.htm",
                "ISEAN" => check_EAN($this->User_album_prop->EAN) ? "" : "*",
                "ISBN" => $this->User_album_prop->ISBN,
                "URLISBN" => "http://www.amazon.fr/exec/obidos/ASIN/".$this->User_album_prop->ISBN,
                "ISISBN" => check_ISBN($this->User_album_prop->ISBN) ? "" : "*",
                "PRIX" => $this->User_album_prop->PRIX,
                "ISTT" => (($this->User_album_prop->FLG_TT == 'O') ? 'checked' : ''),
                "CLDTPAR" => "flat",
                "URLIMAGE" => $url_image,
                "DIMIMAGE" => $dim_image,
                "HISTOIRE" => stripslashes($this->User_album_prop->HISTOIRE),
                "SERIE" => is_null($this->User_album_prop->ID_SERIE) ? stripslashes($this->User_album_prop->SERIE) : stripslashes($this->User_album_prop->ACTUSERIE),
                "CLSERIE" => ($this->User_album_prop->SERIE==$this->User_album_prop->ACTUSERIE ? "flat" : "has_changed"),
                "GENRE" => is_null($this->User_album_prop->ID_GENRE) ? $this->User_album_prop->GENRE : $this->User_album_prop->ACTUGENRE,
                "CLGENRE" => ($this->User_album_prop->GENRE==$this->User_album_prop->ACTUGENRE ? "flat" : "has_changed"),
                "SCENARISTE" => is_null($this->User_album_prop->ID_SCENAR) ?  $this->User_album_prop->ACTUSCENAR :($this->User_album_prop->PSEUDO_SCENAR),
                "CLSCENARISTE" => ($this->User_album_prop->PSEUDO_SCENAR==$this->User_album_prop->SCENAR ? "flat" : "has_changed"),
                "SCENARISTEALT" => is_null($this->User_album_prop->ID_SCENAR_ALT) ?  ($this->User_album_prop->SCENARALT) :($this->User_album_prop->PSEUDO_SCENAR_ALT),
                "CLSCENARISTEALT" => ($this->User_album_prop->PSEUDO_SCENAR_ALT==$this->User_album_prop->SCENARALT ? "flat" : "has_changed"),
                "DESSINATEUR" => is_null($this->User_album_prop->ID_DESSIN) ?  ($this->User_album_prop->DESSIN) :($this->User_album_prop->PSEUDO_DESSIN),
                "CLDESSINATEUR" => ($this->User_album_prop->PSEUDO_DESSIN==$this->User_album_prop->DESSIN ? "flat" : "has_changed"),
                "DESSINATEURALT" => is_null($this->User_album_prop->ID_DESSIN_ALT) ?  ($this->User_album_prop->DESSINALT) :($this->User_album_prop->PSEUDO_DESSIN_ALT),
                "CLDESSINATEURALT" => ($this->User_album_prop->PSEUDO_DESSIN_ALT==$this->User_album_prop->ORIDESSINALT ? "flat" : "has_changed"),
                "COLORISTE" => is_null($this->User_album_prop->ID_COLOR) ?  ($this->User_album_prop->COLOR) :($this->User_album_prop->PSEUDO_COLOR),
                "CLCOLORISTE" => ($this->User_album_prop->PSEUDO_COLOR==$this->User_album_prop->COLOR ? "flat" : "has_changed"),
                "COLORISTEALT" => is_null($this->User_album_prop->ID_COLOR_ALT) ?  ($this->User_album_prop->COLORALT) :($this->User_album_prop->PSEUDO_COLOR_ALT),
                "CLCOLORISTEALT" => ($this->User_album_prop->PSEUDO_COLOR_ALT==$this->User_album_prop->COLORALT ? "flat" : "has_changed"),
                "EDITEUR" => is_null($this->User_album_prop->ID_EDITEUR) ?  ($this->User_album_prop->EDITEUR) :($this->User_album_prop->ACTUEDITEUR),
                "CLEDITEUR" => ($this->User_album_prop->ACTUEDITEUR==$this->User_album_prop->EDITEUR ? "flat" : "has_changed"),
                "COLLECTION" => is_null($this->User_album_prop->ID_COLLECTION) ?  ($this->User_album_prop->COLLECTION) :($this->User_album_prop->COLLECTION),
                "CLCOLLECTION" => ($this->User_album_prop->COLLECTION==$this->User_album_prop->COLLECTION ? "flat" : "has_changed"),
                "COMMENT" => stripslashes($this->User_album_prop->DESCRIB_EDITION),
                "CORRCOMMENT" => $this->User_album_prop->CORR_COMMENT,
                "OPTIONSTATUS" => GetOptionValue($opt_status,$this->User_album_prop->STATUS),
                "COLOR_STATUS" => $color_status,
                "PROPACTION" => $this->User_album_prop->ACTION,
                "ACTIONUTIL" => $opt_action[$this->User_album_prop->ACTION],
                "ACTIONNAME" => "Valider",
                "URLACTION" => BDO_URL."admin/adminproposals.php?act=append&propid=".$this->User_album_prop->ID_PROPOSAL,
                "URLUTILVALID" => BDO_URL."admin/adminproposals.php?act=merge&propid=".$this->User_album_prop->ID_PROPOSAL,
                "URLCOMMENTCORR" => BDO_URL."admin/adminproposals.php?act=comment&propid=".$this->User_album_prop->ID_PROPOSAL,
                "URLDELETE" => BDO_URL."admin/deleteProposition?src=fiche&ID=".$this->User_album_prop->ID_PROPOSAL,
                ));

                // Exemple d'email en cas de suppression
                $mail_sujet = "Votre proposition d'ajout dans la base BDOVORE";
                $mail_body = "Bonjour, \n";
                $mail_body .= "Votre proposition ";
                $mail_body .= '"'.$titre.'"';
                $mail_body .= " a été refusée par l'équipe de correction. \n";
                $mail_body .= "- Les informations que vous avez fournies n'étaient pas suffisantes. \n";
                $mail_body .= "- La proposition d'un autre membre a été préférée ou validée avant. \n";
                $mail_body .= "- Nous considérons que cet album n'a pas de rapport suffisamment proche à la bande dessinée pour être intégré à la base de données du site. \n";
                $mail_body .= "- Cet album figurait déjà dans votre collection. \n";
                $mail_body .= "Si l'édition par défaut de cet album ne correspond pas à celle que vous possédez,";
                $mail_body .= "	d'autres éditions sont peut-être déjà présentes dans la base et peuvent étre sélectionnées en cliquant sur l'album en question depuis votre garde-manger (menu déroulant [Mon édition] des fiches album). \n";
                $mail_body .= "Si ce n'est pas le cas, vous pouvez faire une proposition de nouvelle édition via ce même menu déroulant.\n\n";
                $mail_body .= "Merci de votre compréhension, \n";
                $mail_body .= "L'équipe BDOVORE";
                $this->view->set_var (array(
                "SUJET_EMAIL" => $mail_sujet,
                "CORPS_EMAIL" => $mail_body
                ));

                if ($this->User_album_prop->ID_SERIE != 0){
                        $this->view->set_var (
                        "LIENEDITSERIE" , "<a href='".BDO_URL."admin/editserie?serie_id=".stripslashes($this->User_album_prop->ID_SERIE)."'><img src='".BDO_URL_IMAGE."edit.gif' width='18' height='13' border='0'></a>"
                        );
                }
                // Détermine les albums ayant une syntaxe approchante
                $main_words = main_words(stripslashes($this->User_album_prop->TITRE));
                if ($main_words[1][0] != ''){
                        $query = "
                        where 
                                bd_tome.titre like '%".  Db_Escape_String($main_words[0][0])."%".DB_Escape_String($main_words[1][0])."%'
                                or bd_tome.titre like '%".DB_Escape_String($main_words[1][0])."%".DB_Escape_String($main_words[0][0])."%' 
                                LIMIT 0,30;";
                }else{
                        $query = "
                       where 
                                bd_tome.titre like '%".DB_Escape_String($main_words[0][0])."%' 
                        LIMIT 0,30;";
                }
                $this->loadModel("Tome");
                $dbs_tome =  $this->Tome->load("c",$query);

                // on déclare le block é utiliser
               
                $this->view->set_var (array(
                        "dbs_tome" => $dbs_tome
                ));

                // Récupére l'adresse mail de l'utilisateur
            
            $mail_adress = $this->User_album_prop->EMAIL;
            $pseudo = $this->User_album_prop->USERNAME;

            $this->view->set_var (array(
            "ADRESSEMAIL" => $mail_adress,
            "MAILSUBJECT" => "Votre proposition BDovore : ".$titre,
            "MEMBRE" => $pseudo
            ));

            // url suivant et précédent
            $this->User_album_prop->load("c", " WHERE 
                    id_proposal <".$id." 
                    AND status = 0 
                    AND prop_type = 'AJOUT' 
            ORDER BY id_proposal desc limit 0,1");
            
            // URL précédent : proposition avec ID inférieur
            if ($this->User_album_prop->ID_PROPOSAL <> $id) {
               $prev_url = BDO_URL."admin/editpropositionajout?ID=".$this->User_album_prop->ID_PROPOSAL;
                $this->view->set_var ("BOUTONPRECEDENT" , "<a href='".$prev_url."'><input type='button' value='Précédent' /></a>");
            }else{
                    $this->view->set_var ("BOUTONPRECEDENT" , "<del>Précédent</del>");
            }
           $this->User_album_prop->load("c", " WHERE 
                    id_proposal > ".$id." 
                    AND status = 0 
                    AND prop_type = 'AJOUT' 
            ORDER BY id_proposal desc limit 0,1
            ");
            // URL précédent : proposition avec ID supérieur
            if ($this->User_album_prop->ID_PROPOSAL <> $id) {
                   
                   
                    $next_url = BDO_URL."admin/editpropositionajout?ID=".$this->User_album_prop->ID_PROPOSAL;
                    $this->view->set_var ("BOUTONSUIVANT" , "<a href='".$next_url."'><input type='button' value='Suivant'></a>");
            }else{
                    $this->view->set_var ("BOUTONSUIVANT" , "<del>Suivant</del>");
            }

            
            $this->view->render();
            
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editEdition() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editAlbum() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editAuteur() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editSerie() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editGenre() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

    public function editCollection() {
        if (User::minAccesslevel(1)) {
            
        } else {
            die("Vous n'avez pas accès à cette page.");
        }
    }

}

