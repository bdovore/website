<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Adminauteur extends Bdo_Controller {
  
    public function Index() {
        if (!User::minAccesslevel(1)) {
                   die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
               }
           
        $this->loadModel("Auteur");
        
        $flg_filter_bio = getVal("filter_bio","N");
        $query = ""; 
        if ($flg_filter_bio == "O") {
            $query .= " WHERE COMMENT = '' ";
        }
        $query .= " ORDER BY VALID_DTE DESC LIMIT 0, 100";
        $dbs_auteur = $this->Auteur->load("c",$query);
        $this->view->set_var("dbs_auteur", $dbs_auteur);
        $this->view->set_var("PAGETITLE", "Administration Bdovore - Auteur");
        $this->view->render();
    }
}