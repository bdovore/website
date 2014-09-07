<?php

/** 
 * @author Tom
 * 
 */
class Admin extends Bdo_Controller
{

    /**
     */
    public function Index(){
        
         if (User::minAccesslevel(1)) {
             $this->loadModel("User_album_prop");
             
             $this->view->set_var($this->User_album_prop->getAllStat());
            $this->view->set_var("PAGETITLE","Administration Bdovore - Accueil");
            $this->view->render();
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    }
    
    public function Proposition() {
        if (User::minAccesslevel(1)) {
            $act = getVal("act","");
            $update = getVal("chkUpdate","");
            if ($update == 'O') {
                    $act = "update";
            }
           $type = getVal("type","AJOUT");

            $validationdelay = 21;//nbre de jours apr�s lesquels on ne valide pas (pour les parutions futures)
            $datebeforevalid = "Ne pas valider les albums qui paraissent après le " . date("d/m/Y", mktime(0, 0, 0, date("m"),date("d")+$validationdelay,date("Y"))) . " ($validationdelay jours)";

            // LISTE LES PROPOSALS
            if ($act==""){
                $this->loadModel("User_album_prop");
                    switch ($type){
                        case "AJOUT" : 
                             $titre_admin = "Nouveaux Albums en attente";
                             
                  
                            $dbs_prop = $this->User_album_prop->load("c"," WHERE users_alb_prop.status <> 98 AND
                                      users_alb_prop.status <> 99 AND
                                      users_alb_prop.status <> 1 AND
                                      users_alb_prop.PROP_TYPE='AJOUT'");
                            break;
                        case "CORRECTION" : 
                            $titre_admin = "Corrections en attente";
                            
                  
                            $dbs_prop = $this->User_album_prop->load("c"," WHERE users_alb_prop.status <> 98 AND
                                      users_alb_prop.status <> 99 AND
                                      users_alb_prop.status <> 1 AND
                                      users_alb_prop.PROP_TYPE='CORRECTION'");
                            break;
                        case "EDITION" :
                            $titre_admin = "Editions en attente";
                            $this->loadModel("Edition");
                            $dbs_edition = $this->Edition->load("c"," WHERE bd_edition.prop_status <> 98 AND
                                      bd_edition.prop_status <> 99 AND
                                      bd_edition.prop_status <> 1 ");
                            break;
                    }
                   
                   
                    $this->view->set_var (array(
                        "TITRE_ADMIN" => $titre_admin,
                        "opt_status" => $opt_status,
                        "dbs_prop" => $dbs_prop,
                        "dbs_edition" => $dbs_edition,
                        "DATEBEFOREVALID" => $datebeforevalid,
                        "validationdelay" => $validationdelay));

                    
             }

                   
            $this->view->set_var($this->User_album_prop->getAllStat());
             $this->view->set_var("PAGETITLE","Administration des propositions");
            $this->view->render();
        }
        else {
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
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    }
        
    public function editProposition(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    } 
    
    public function editEdition(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    } 
    
    
    public function editAlbum(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    } 
    
    
    public function editAuteur(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    } 
    
    public function editSerie(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    }
    
    
    public function editGenre(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    }
    
    public function editCollection(){
         if (User::minAccesslevel(1)) {
             
         }
         else {
             die("Vous n'avez pas accès à cette page.");
         }
    }
    
    
    
    

}

