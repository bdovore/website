<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * @author : Tom
 * Contrôleur pour l'ajout, la consultation, l'édition des infos persos de la colleciton
 * Il alimente la plupart des pages perso de la collection
 * 
 */

class Paramcarre extends Bdo_Controller {

    public function Index() {
        // page d'accueil de la collection 
        // liste les albums

        if (User::minAccesslevel(2)) {
            $user_id = $_SESSION["userConnect"]->user_id;
            $this->loadModel('Useralbum');
            $user = $this->getUserInfo();

             if (postVal("Submit") == "Enregister") {
                 /*
                  * Sauvegarde du carré magique
                  */
                 $carre_type=0;
                 $auto = postVal("auto","N");
                 if ($auto == N) $carre_type=1;
                 
                 if ($carre_type == 1) {
                     
                 }
             }       

            $this->view->set_var(array(
                "a_carre" => $this->Useralbum->carre($user),
                "carre_type" => $user->CARRE_TYPE,
                "PAGETITLE" => "Ma Collection de sur Bdovore"
                ));
            $this->view->layout = "iframe";
            
            $this->view->render();
        } else {
            die("Vous devez vous authentifier pour accéder à cette page.");
        }
    }

    private function getUserInfo() {
        /*
         * Récupère les infos du user connecté
         */


        $user = new User($_SESSION["userConnect"]->user_id);

        $user->load();

        return $user;
    }

}

?>
