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
        
        $filter = getVal("filter","0");
        $query = ""; 
        if ($filter == "1") {
            $query .= " WHERE COMMENT = '' OR COMMENT IS NULL ";
        }
        if ($filter == "2") {
            $query .= " WHERE FLG_SCENAR <> 1 AND"
                    . " FLG_DESSIN <> 1 AND FLG_COLOR <> 1  ";
        }
        $query .= " ORDER BY VALID_DTE DESC LIMIT 0, 100";
        $dbs_auteur = $this->Auteur->load("c",$query);
        $this->view->set_var("dbs_auteur", $dbs_auteur);
        $this->view->set_var("filter", $filter);
        $this->view->set_var("PAGETITLE", "Administration Bdovore - Auteur");
        $this->view->render();
    }
}