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
        
         if (User::minAccesslevel(0)) {
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
        if (User::minAccesslevel(0)) {
            $act = $_REQUEST["act"];
            $update = $_REQUEST["chkUpdate"];
            if ($update == 'O') {
                    $act = "update";
            }
           

            $validationdelay = 21;//nbre de jours apr�s lesquels on ne valide pas (pour les parutions futures)
            $datebeforevalid = "Ne pas valider les albums qui paraissent après le " . date("d/m/Y", mktime(0, 0, 0, date("m"),date("d")+$validationdelay,date("Y"))) . " ($validationdelay jours)";

            // LISTE LES PROPOSALS
            if ($act==""){
                    $titre_admin = "Nouveaux Albums en attente";
                   
                   $this->loadModel("User_album_prop");
                  
                   $dbs_prop = $this->User_album_prop->load("c"," WHERE users_alb_prop.status <> 98 AND
                                      users_alb_prop.status <> 99 AND
                                      users_alb_prop.status <> 1 AND
                                      users_alb_prop.PROP_TYPE='AJOUT'");
                    
                   
                     

                    $this->view->set_var (array(
                        "TITRE_ADMIN" => $titre_admin,
                        "opt_status" => $opt_status,
                        "dbs_prop" => $dbs_prop,
                        "DATEBEFOREVALID" => $datebeforevalid));

                    
             }

                   
            $this->view->set_var($this->User_album_prop->getAllStat());
             $this->view->set_var("PAGETITLE","Administration des propositions");
            $this->view->render();
        }
        else {
             die("Vous n'avez pas accès à cette page.");
         }
    }
    
}

