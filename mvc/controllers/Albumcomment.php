<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * @author : Tom
 * Classe de manipulation des commentaires
 * Principe retenu : on transmet des commentaires via json, pour intégration dans les pages par javascript
 */

class AlbumComment extends Bdo_Controller {
    
    public function Index () {
    
        $id_tome = getValInteger('id_tome',0);
        $user_id = getValInteger('user_id',0);
        $page = getValInteger('page',1);
        $all = getValInteger('all',0);
        //echo ("ok");
        $this->loadModel('Comment');
        
        $where = "where 1 ";
        
        if ($user_id <> 0) {
            $where .= " and c.user_id = " .$user_id;
            
        }
        if ($id_tome <> 0) {
            $where .= " and c.id_tome = " .$id_tome;
          
            
        }
        if ($all == 0){ // seulement les commentaire écrits
            $where .= " and c.comment <> '' ";
            
            
        }
        
        $where .= " order by dte_post desc ";
        $where .= " limit ". (($page-1)*20).",20";
      // echo ($this->Comment->select().$where);
        $this->Comment->load(c,$where);
        $this->view->set_var('json', json_encode($this->Comment->dbSelect->a_dataQuery));
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    public function writeComment () {
        $id_tome = getValInteger('id_tome',0);
       /* Fonction pour écrire un commentaire 
        * 
        * seul le user connecté peut écrire un commentaire d'un album
        * la fonction est conçue pour être appelé par javascript
        * return : code erreur en json s'il y en a
        */
        if (! empty($_SESSION['userConnect']->user_id)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
            $comment = getVal('comment','');
            $note = getValInteger("note",0);
            $this->loadModel('Comment');
            
            $this->Comment->load(c," WHERE c.user_id = ".$user_id . " and c.id_tome = ".$id_tome);
            
            $this->Comment->set_dataPaste(
                array(
                   'ID_TOME' => $id_tome,
                   'USER_ID' => $user_id,
                   'NOTE' => $note,
                   'COMMENT' => $comment, // ne pas ajouter un contrôle DB_escape_string : il est opéré lors de l'insertion
                   'DTE_POST' => date('d/m/Y H:i:s')
                )
            );
            
            $this->Comment->update();
            // maj des stats sur l'album
            $this->loadModel("Notetome");
            $this->Notetome->update_stat($id_tome);
            $this->view->set_var('json', json_encode($this->Comment->error));
             $this->view->layout = "ajax";
            $this->view->render();
        }
    }
    
    
}
?>
