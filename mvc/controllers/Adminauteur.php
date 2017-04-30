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
        $search_filter = getVal("l_search","");
        $query = ""; 
         if ($search_filter)  {
            $query .= " WHERE pseudo like '%".$search_filter."%' ";
        }
        if ($filter == "1") {
            $query .= " AND (COMMENT = '' OR COMMENT IS NULL ) ";
        }
        if ($filter == "2") {
            $query .= " AND (FLG_SCENAR <> 1 AND"
                    . " FLG_DESSIN <> 1 AND FLG_COLOR <> 1  ) ";
        }
       
        $query .= " ORDER BY VALID_DTE DESC LIMIT 0, 100";
        $dbs_auteur = $this->Auteur->load("c",$query);
        $this->view->set_var("dbs_auteur", $dbs_auteur);
        $this->view->set_var("filter", $filter);
        $this->view->set_var("PAGETITLE", "Administration Bdovore - Auteur");
        $this->view->set_var("searchvalue", $search_filter);
        $this->view->render();
    }
}