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

            $validationdelay = 21; //nbre de jours apr�s lesquels on ne valide pas (pour les parutions futures)
            $datebeforevalid = "Ne pas valider les albums qui paraissent après le " . date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $validationdelay, date("Y"))) . " ($validationdelay jours)";

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
                    "DATEBEFOREVALID" => $datebeforevalid,
                    "validationdelay" => $validationdelay));
            }


            $this->view->set_var($this->User_album_prop->getAllStat());
            $this->view->set_var("PAGETITLE", "Administration des propositions");
            $this->view->render();
        } else {
            die("Vous n'avez pas accès à cette page.");
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
            $this->User_album_prop->set_dataPaste(array("ID_PROPSAL" => $id));
            $this->User_album_prop->load();
            
            
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

