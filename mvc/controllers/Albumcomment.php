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

    public function setCommentPrivate() {
        /*
         * Pour passer un commentaire en privé
         */
        if (User::minAccesslevel(1)) {
            // seulement l'admin
            $id_tome = getValInteger("id_tome");
            $user_id = decodeUserId(getValInteger("user_id"));
            $this->loadModel('Comment');

            $this->Comment->load(c," WHERE c.user_id = ".$user_id . " and c.id_tome = ".$id_tome);
            $comment = $this->Comment->COMMENT;
            $this->Comment->add_dataPaste("COMMENT","");
            $this->Comment->update();

            $this->loadModel("Useralbum");
            $this->Useralbum->load(c," WHERE ua.user_id = ".$user_id . " and bd_tome.ID_TOME = ".$id_tome);
            $id_edition = $this->Useralbum->ID_EDITION;
            $comment .= " ".$this->Useralbum->comment;

            $this->Useralbum->set_dataPaste(array("comment"=> $comment, "user_id" => $user_id,"id_edition" => $id_edition ));
            $this->Useralbum->update();


            $this->view->set_var('json', json_encode($this->Comment->error));
            $this->view->layout = "ajax";
            $this->view->render();
        }
    }
    
    public function likeComment () {
         $id_tome = getValInteger('id_tome',0);
         $user_id_source = decodeUserId(getValInteger("user_id",0));
        /* pour "liker" une critique sur un album */
         if (! empty($_SESSION['userConnect']->user_id)) {
             $this->loadModel("Social");
             // on check si un like existe déjà, dans ce cas on l'enlève, et dans le cas contraire ou l'ajoute
             if ($id_tome AND $user_id_source) {
                $dbSocial = $this->Social->getLikeForUser($_SESSION['userConnect']->user_id, $id_tome, $user_id_source);
                
                if ($dbSocial->nbLineResult > 0) {
                    // dislike
                    $this->Social->deleteLike($_SESSION['userConnect']->user_id,$id_tome, $user_id_source );
                } else {
                    $this->Social->set_dataPaste(array(
                        "TYPE_ACTION" => "L",
                        "USER_ID" => $_SESSION['userConnect']->user_id,
                        "SOURCE_ID_TOME" => $id_tome,
                        "SOURCE_USER" => $user_id_source
                        ));
                    $this->Social->update();                            
                } 
                $this->view->set_var('json', json_encode($this->Social->error));
               
             }
         }else {
            $this->view->set_var('json', json_encode(array("error_connect" => "not connected")));
        }     
         $this->view->layout = "ajax";
         $this->view->render();
    }
    
    public function getLikeForComment () {
        $id_tome = getValInteger('id_tome',0);
        $user_id_source = decodeUserId(getValInteger("user_id",0));
        $this->loadModel("Social");
        $nb = $this->Social->getNbLikeForComment($id_tome, $user_id_source);
        
        $this->loadModel("Socialreview");
        $nbcmt = $this->Socialreview->getNbCommentForSource($id_tome."-".$user_id_source);
        $nb_user = 0;
        if (! empty($_SESSION['userConnect']->user_id)) {
            $dbSocial = $this->Social->getLikeForUser($_SESSION['userConnect']->user_id, $id_tome, $user_id_source);
            $nb_user = $dbSocial->nbLineResult;
        }
        $obj = array("nbLike" => $nb, "userLike" => $nb_user, "nbCmt" => $nbcmt);
        $this->view->set_var('json', json_encode($obj));
        $this->view->layout = "ajax";
        $this->view->render();
    }

    public function addCommentToReview () {
        $id_tome = getValInteger('id_tome',0);
        $user_id_source = decodeUserId(getValInteger("user_id",0));
        $cmt = getVal("comment","");
        if (! empty($_SESSION['userConnect']->user_id)) {
             $this->loadModel("Socialreview");
             $this->Socialreview->set_dataPaste(array(
                 "source" => $id_tome."-".$user_id_source,
                 "user_id" => $_SESSION['userConnect']->user_id,
                 "date_comment" => date('d/m/Y H:i:s'),
                 "comment" =>  $cmt
             ));
             $this->Socialreview->update();
             $this->view->set_var('json', json_encode($this->Socialreview->error));
             
        } else {
            $this->view->set_var('json', json_encode(array("error_connect" => "not connected")));
        }
        $this->view->layout = "ajax";
        $this->view->render();
     }
     
     public function getCommentToReview () {
        $id_tome = getValInteger('id_tome',0);
        $user_id_source = decodeUserId(getValInteger("user_id",0));
        $this->loadModel("Socialreview");
        $this->Socialreview->load("c", " WHERE source = '".$id_tome."-".$user_id_source ."' order by date_comment ASC");
        $this->view->set_var('json', json_encode($this->Socialreview->dbSelect->a_dataQuery));
        $this->view->layout = "ajax";
        $this->view->render();
     }
}
?>
