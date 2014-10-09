<?php

/**
 *
 * @author laurent
 *        
 */

class Guest extends Bdo_Controller
{

    /**
     * Fonction encodage du user pour partie guest
     */
    public function encodeUserId ($id)
    {
        if (! empty($id)) {
            return ($id * 1209) + 951;
        }
        return false;
    }

    /**
     * Fonction decodage du user pour partie guest
     */
    public function decodeUserId ($id)
    {
        return ($id - 951) / 1209;
    }

    /**
     */
    public function Index ()
    {
        
        /*
         * user=4578 OK user=899 KO
         */
        
        // Vérifie qu'un parametre a été passé
        $user = $this->getUserInfo();

        if ($this->verifUserID($user)) {
            // Charge les statistisques
            $this->loadModel("Useralbum");
            $stat = $this->Useralbum->getStatistiques($user->user_id);
      
            // Selections des 9 albums les mieux notés
            $this->view->set_var('a_carre',  $this->Useralbum->carre($user));
                
            // Selection des 4 derniers achats
            $this->view->set_var('a_lastAchat', $this->Useralbum->lastAchat($user->user_id));
        
            
            // Récupère les contributions
            $this->loadModel("User_album_prop");
            $prop_stat = $this->User_album_prop->getUserStat($user->user_id);
            $user_prop_alb =  $prop_stat["user_prop_alb"];
            $user_prop_corr = $prop_stat["user_prop_corr"];
            
            // envoie les variables a la vue
            $this->view->set_var(
                    array(
                            "PAGETITLE" => "Bdovore.com : visitez la collection de $user->username",
                            "IDUSER" => $user->user_id,
                            //"DTEARRIVEE" => $user_regdate,
                            //"NBPOSTS" => $user_posts,
                            "NBPROPS" => $user_prop_alb,
                            "NBCORRECTIONS" => $user_prop_corr,
                            "USERNAME" => $user->username,
                            "DTEARRIVEE" => strftime('%d-%m-%Y', $user_regdate),
                            "NBALBUMS" => $stat["nbtomes"],
                            "NBINTEGRALES" => $stat["nbintegrales"],
                            "NBCOFFRETS" => $stat["nbcoffrets"],
                            "NBSERIES" => $stat["nbseries"],
                            "NBACHATS" => $stat["nbfuturs_achats"],
                            "URLBROWSER" => BDO_URL . 'guest/browser?user=' . $ori_user,
                            "USERID" => $ori_user,
                            "URLCOLLEC" => "www.bdovore.com/guest?user=" . $ori_user
                    ));
        
            if (issetNotEmpty($_GET["user"])) {
                $this->view->set_var("ADDTHIS", "<img src='" . BDO_URL_IMAGE . "site/lg-addthis-fr.gif' width='125' height='16' alt='Partager cette page' style='border:0'/>");
            }
        }
        $this->view->render();
    }
    
    public function Collection() {
        /*
         * Affiche les albums de la collection d'un utilisateur
         */
        // Vérifie qu'un parametre a été passé
        $user = $this->getUserInfo();

        if ($this->verifUserID($user)) {
            // controle d'acces 
            if (($user->user_id != $_SESSION["userConnect"]->user_id) and ! $user->is_openCollection()) {

                $this->view->addAlertPage("Cet utilisateur n'a pas rendu sa collection publique !");
                $this->view->addPhtmlFile('alert', 'BODY');
                $this->view->render();

                return;
            }
            
            $this->loadModel('Useralbum');

            $page = getValInteger("page",1);
            $length = getValInteger("length",10);
            $searchvalue = Db_Escape_String(getVal("l_search",""));

            // tableau pour gérer les order by
       
            $a_order[0]= "IMG_COUV";
            $a_order[1]= "TITRE_TOME";
            $a_order[2]= "NOM_SERIE";
            $a_order[3]= "NUM_TOME";
            $a_order[4]= "NOM_EDITION";
            $a_order[5]= "NOM_COLLECTION";
            $a_order[6]="scpseudo";
            $a_order[7]= "depseudo";
            $a_order[8]= "DATE_ACHAT";

            //TODO getValInArray("order",array("ASC","DESC")); ou quelque chose du genre
            $order = getVal("order","DESC");

            //évite les injections SQL (inutile quand on aura écrit getValInArray())
            if ( strcmp($order,"ASC") !== 0 )
                $order = "DESC";//si ça n'est pas ASC, ça doit être DESC ...
            
            // variable $sort donne la colonne pour le tri
            // on s'assure que la variable est dans le bon intervale de valeur
            $sort = getValInteger("sort",9);
            $sort = max(min($sort,9),0);
            //if ($sort < 0) $sort = 0;

            $eo = getVal("cb_tete","N");
            $dedicace = getVal("cb_dedicace","N");

            $limit = " limit ".(($page - 1)*$length).", ".$length;
            $orderby = " order by ".$a_order[$sort-1]." ".$order;
            $where = " where ua.user_id = ".intval($user->user_id) ." and flg_achat = 'N' ";

            if ($eo == "O") $where .= " and flg_tete = 'O' ";
            if ($dedicace== "O") $where .= " and flg_dedicace = 'O' ";
            if ($searchvalue <> "") $where .= " and ( bd_tome.titre like '%". $searchvalue ."%' OR s.nom like '%". $searchvalue ."%' OR er.nom like  '%". $searchvalue ."%' OR sc.pseudo like  '%". $searchvalue ."%' OR de.pseudo like  '%". $searchvalue ."%'  ) ";
           // echo  $this->Useralbum->select()." where ua.user_id = ".$user_id ." and flg_achat = 'N' ".$orderby. $limit;
            $dbs_tome = $this->Useralbum->load("c",$where.$orderby. $limit);

            $nbr = Db_CountRow($this->Useralbum->select().$where);

            $this->view->set_var( array (
                "PAGETITLE" => "Bdovore.com : visitez la collection de $user->username",
                "dbs_tome" => $dbs_tome,
                "page" => $page,
                "length" => $length,
                "nbr" => $nbr,
                "sort" => $sort,
                "order" => $order,
                "pret" => $pret,
                "cadeau" => $cadeau,
                "eo" => $eo,
                "dedicace" => $dedicace,
                "searchvalue" => $searchvalue,
                "IDUSER" => $user->user_id,
                "USERNAME" => $user->username
                    )
                  ) ;
        }
        
        $this->view->render();
    }
    
    public function Wishlist() {
        /*
         * Affiche les futurs achats de la collection d'un utilisateur
         */
        // Vérifie qu'un parametre a été passé
        $user = $this->getUserInfo();
       
        if ($this->verifUserID($user)) {
            // controle d'acces 
            if (($user->user_id != $_SESSION["userConnect"]->user_id) and ! $user->is_openCollection()) {
                $this->view->addAlertPage("Cet utilisateur n'a pas rendu sa collection publique !");
                $this->view->addPhtmlFile('alert', 'BODY');
                $this->view->render();

                return;
            }
            
            $this->loadModel('Useralbum');

            $page = getValInteger("page",1);
            $length = getValInteger("length",10);
            $searchvalue = Db_Escape_String(getVal("l_search","" ));

            // tableau pour gérer les order by
       
            $a_order[0]= "IMG_COUV";
            $a_order[1]= "TITRE_TOME";
            $a_order[2]= "NOM_SERIE";
            $a_order[3]= "NUM_TOME";
            $a_order[4]= "NOM_EDITION";
            $a_order[5]= "NOM_COLLECTION";
            $a_order[6]="scpseudo";
            $a_order[7]= "depseudo";
            $a_order[8]= "DATE_AJOUT";

            //TODO getValInArray("order",array("ASC","DESC")); ou quelque chose du genre
            $order = getVal("order","DESC");

            //évite les injections SQL (inutile quand on aura écrit getValInArray())
            if ( strcmp($order,"ASC") !== 0 )
                $order = "DESC";//si ça n'est pas ASC, ça doit être DESC ...

            // variable $sort donne la colonne pour le tri
            // on s'assure que la variable est dans le bon intervale de valeur
            $sort = getValInteger("sort",9);
            $sort = max(min($sort,9),0);
            //if ($sort < 0) $sort = 0;

            $limit = " limit ".(($page - 1)*$length).", ".$length;
            $orderby = " order by ".$a_order[$sort-1]." ".$order;
            $where = " where ua.user_id = ".intval($user->user_id) ." and flg_achat = 'O' ";

            if($searchvalue <> "") $where .= " and ( bd_tome.titre like '%". $searchvalue ."%' OR s.nom like '%". $searchvalue ."%' OR er.nom like  '%". $searchvalue ."%' OR sc.pseudo like  '%". $searchvalue ."%' OR de.pseudo like  '%". $searchvalue ."%'  ) ";
           // echo  $this->Useralbum->select()." where ua.user_id = ".$user_id ." and flg_achat = 'N' ".$orderby. $limit;
            $dbs_tome = $this->Useralbum->load("c",$where.$orderby. $limit);

            $nbr = Db_CountRow($this->Useralbum->select().$where);

            $this->view->set_var( array (
                "PAGETITLE" => "Bdovore.com : visitez la collection de $user->username",
                "dbs_tome" => $dbs_tome,
                "page" => $page,
                "length" => $length,
                "nbr" => $nbr,
                "sort" => $sort,
                "order" => $order,
                "pret" => $pret,
                "cadeau" => $cadeau,
                "eo" => $eo,
                "dedicace" => $dedicace,
                "searchvalue" => $searchvalue,
                "IDUSER" => $user->user_id,
                "USERNAME" => $user->username
                    )
                  ) ;
        }
        
        $this->view->render();
    }
    
    private function getUserInfo() {
        /*
         * Récupère les infos du user, soit en paramètre soit user connecté
         */
        if (! isset($_GET['user']) and issetNotEmpty($_SESSION["userConnect"]->user_id)) {
            $user_id = intval($_SESSION["userConnect"]->user_id);
            $ori_user_id = $this->encodeUserId($user_id);
        }
        else { // BUG: on entre ici même si $_GET['user'] is !isset() quand la 2ème condition du if ci-dessus est false ...
            $ori_user_id = $_GET['user'];
            $user_id = intval($this->decodeUserId($ori_user_id));
        }

        $user = new User($user_id);
        $user->load();

        return $user;
    }
    
    private function verifUserID($user){
        // controle d'acces de l'utilisateur
        // renvoie false et message d'erreur si la collection n'est pas accessible
        if (($user->user_id != $_SESSION["userConnect"]->user_id) and ! $user->is_openCollection() ) {
            $this->view->addAlertPage("Cet utilisateur n'a pas rendu sa collection publique !");
            $this->view->addPhtmlFile('alert', 'BODY');

            return false;
        } 
        else if (!$user->user_id) {
            $this->view->addAlertPage("Cet utilisateur n'existe pas !");
            $this->view->addPhtmlFile('alert', 'BODY');

            return false;
        } 
        else {
            return true;
        }
    }
    
    public function Avis () {
        /*
         * Liste les avis de lecture de l'utilisateur
         */
        $user = $this->getUserInfo();
       
        if ($this->verifUserID($user)) {
            $this->loadModel('Comment');
            $page = getValInteger('page',1);
           
            $limit = "LIMIT ".(($page-1)*20).",20";
          
            $dbs_comment = $this->Comment->load('c', "
                  WHERE c.comment <> '' and c.user_id = ".$user->user_id
                  ." ORDER BY c.`DTE_POST` DESC ".$limit 
                  );
           
            $this->view->set_var(array(
               "PAGETITLE" => "Bdovore.com : les avis BD de $user->username",
               "dbs_comment" => $dbs_comment,
               "NUM_PAGE" => $page,
               "IDUSER" => $user->user_id,
               "USERNAME" => $user->username
           ));
        }

        $this->view->render();
    }
}

