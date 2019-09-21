<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Bdtheque extends Bdo_Controller
{

     public function Index ()
    {
          if (User::minAccesslevel(2)) {
              header('Location: '.BDO_URL."macollection");
          }
         $this->view->set_var(array("PAGETITLE" => "Votre Bdtheque avec Bdovore !"));
         $this->view->render();
         
     }
    
}

