<?php

/*
 * Gestion des auteurs favoris pour les actualités
 * 
 */

class Auteurfavoris extends Bdo_Controller {
    
    public function Index () {
        if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION["userConnect"]->user_id);
            $listmaj = getValInteger("txtAuteurId",0);

            $this->loadModel("Users_list_aut");
            
            if ($listmaj <> 0) {
                $this->Users_list_aut->majListAuteur($user_id, $listmaj);
            }

            $where = " WHERE USER_ID= " . $user_id;
            //echo $this->Users_list_aut->select().$where;
            $this->Users_list_aut->load("c",$where);
            $this->view->set_var("a_listauteur",$this->Users_list_aut->dbSelect->a_dataQuery);
            $this->view->layout = "iframe";
            $this->view->render(); 
        }
        else {
            die("Vous devez vous authentifier pour accéder à cette page");
        }
    }
    
    public function majListAuteur() {
       if (User::minAccesslevel(2)) {
          $this->loadModel("Users_list_aut");
       }
        
       $this->view->layout = "json";
       $this->view->render(); 
        
    }
    
    public function suppAuteur(){
        if (User::minAccesslevel(2)) {
     
        }

        $this->view->layout = "json";
        $this->view->render(); 
    }
    
}
 
?>
