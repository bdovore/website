<?php

/*
 * @author : Tom
 * Contrôleur pour l'ajout, la consultation, l'édition des infos persos de la colleciton
 * Il alimente la plupart des pages perso de la collection
 *
 */

class Macollection extends Bdo_Controller {

    public function Index () {
        // page d'accueil de la collection
        // liste les albums

        if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION["userConnect"]->user_id);
            $this->loadModel('Useralbum');
            $user = $this->getUserInfo();

            $open_collec = postVal("open_collec","");

            if ($open_collec <> "") {
                if ($open_collec <> 'Y')
                    $open_collec = 'N';

                // mise à jour des paramètres de la collection
                $user->set_dataPaste(array("OPEN_COLLEC" => $open_collec));
                $user->update();
            }

            $this->view->set_var('a_lastAchat',$this->Useralbum->lastAchat($user_id));
            $this->view->set_var('a_lastFuturAchat',$this->Useralbum->lastFuturAchat($user_id));

            // Récupère les contributions
            $this->loadModel("User_album_prop");
            $prop_stat = $this->User_album_prop->getUserStat($user_id);

            $user_prop_alb =  $prop_stat["user_prop_alb"];
            $user_prop_corr = $prop_stat["user_prop_corr"];

            $this->view->set_var( array (
                "stat" => $this->Useralbum->getStatistiques($user_id),
                "user_prop_alb" => $user_prop_alb ,
                "user_prop_corr" => $user_prop_corr,
                "a_carre" => $this->Useralbum->carre($this->getUserInfo()),
                "user_id" => $user_id,
                "carre_type" => $user->CARRE_TYPE,
                "open_collec" => $user->OPEN_COLLEC
                ));
        }
        else {
            die("Vous devez vous authentifier pour accéder à cette page.");
        }

        $this->view->set_var("PAGETITLE","Ma Collection de sur Bdovore");
        $this->view->render();
    }

    private function getUserInfo() {
        /*
         * Récupère les infos du user connecté
         */
        $user = new User(intval($_SESSION["userConnect"]->user_id));
        $user->load();

        return $user;
    }

    public function majCollection () {
        $id_tome = getValInteger('id_edition',0);
        /* Fonction pour ajouter / mettre à jour un album dans la collection
         *
         * seul le user connecté peut mettre à jour
         * prévu pour un appel en mode ajax
         * retour : vide si ok, code erreur sinon
         */
        if (! empty($_SESSION['userConnect']->user_id)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
            $id_edition = getValInteger("id_edition",0);
            $flg_achat = getVal('flg_achat','N');

            $flg_pret = getVal('flg_pret','N');
            $email_pret = getVal('email_pret','');
            $flg_dedicace = getVal("flg_dedicace",'N');
            $flg_tete = getVal("flg_tete",'N');
            $flg_lu = getVal("flg_lu",'N');

            $flg_cadeau = getVal("flg_cadeau",'N');
            $cote = getVal("cote",'');
            $nom_pret = getVal("nom_pret","");
            $date_achat = getVal("date_achat","");
            $comment = getVal('comment','');

            //echo $comment;

            $this->loadModel('Useralbum');

            if ($id_edition <> 0) {
                //echo $this->Useralbum->select();
                $this->Useralbum->load(c," WHERE ua.user_id = ".$user_id . " and ua.id_edition = ".$id_edition);

                if (isset($this->Useralbum->DATE_AJOUT) and $this->Useralbum->FLG_ACHAT=='N' ) {
                    $date_ajout = $this->Useralbum->DATE_AJOUT; // si l'album a déja été ajouté dans la collection on en change pas la date
                }
                else {
                    $date_ajout = date('d/m/Y H:i:s');
                    if ($flg_achat == 'N') {
                        $date_achat = $date_ajout;
                    }
                }
                $this->Useralbum->set_dataPaste(
                       array(

                            'id_edition' => $id_tome,
                            'user_id' => $user_id,
                            'date_ajout' => $date_ajout,

                            'flg_pret' => $flg_pret,
                            'nom_pret' =>  $nom_pret,
                            'email_pret' =>  $email_pret,
                            'flg_dedicace' => $flg_dedicace,
                            'flg_tete' => $flg_tete,
                            'comment' => $comment,

                            'flg_achat' => $flg_achat,
                            'date_achat' => $date_achat,
                            'cote' => $cote,
                            'flg_cadeau' => $flg_cadeau,
                           'FLG_LU' => $flg_lu

                    )

                );

                $this->Useralbum->update();
                $this->view->set_var('json', json_encode($this->Useralbum->error));
            }
            else {
                $this->view->set_var('json', json_encode(array('CODE'=> 'ERR_EDITION', 'MSG' => "Id edition nécessaire")));

            }
            $this->view->layout = "ajax";
            $this->view->render();
        }
    }
    
    public function addSerie () {
        /*
         * Ajut d'une série complète dans sa collection pour aller plus vite
         * On met l'édition par défaut pour tous les albums de la série
         * Possible de passer toute ue série en futur achat via flg_achat
         */
         if (! empty($_SESSION['userConnect']->user_id)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
            $id_serie = getValInteger("id_serie",0);
            $flg_achat = getVal('flg_achat','N');
            
            $this->loadModel('Useralbum');
            if ($id_serie <> 0) {
                $this->Useralbum->addSerieForUser($id_serie, $user_id, $flg_achat);
                $this->view->set_var('json', json_encode($this->Useralbum->error));
            } else {
                $this->view->set_var('json', json_encode(array('CODE'=> 'ERR_SERIE', 'MSG' => "Id serie nécessaire")));

            }
            
         }
        
         $this->view->layout = "ajax";
         $this->view->render();
        
    }

    public function mesEtageres () {
      if (User::minAccesslevel(2)) {
          $user_id = intval($_SESSION["userConnect"]->user_id);
          $this->loadModel('Useralbum');

          $page = getValInteger("page",1);
          $length = getValInteger("length",0);
          $sel_type = getVal("sel_type","Tous");
          //TODO mettre une longueur max. pour la recherche ?
          if (!$length) {
              if ($_COOKIE["l_etageres"] ) {
                  // récupére la valeur dans un coockie
                  $length = $_COOKIE["l_etageres"];
              } else {
                  $length = 10;
              }
          }
          setcookie("l_etageres",$length,time()+2592000);

          $l_search = getVal("l_search","" );


          // variable $sort donne la colonne pour le tri
          // on s'assure que la variable est dans le bon intervale de valeur
          $sort = getValInteger("sort",9);
          $sort = max(min($sort,17),1);
          //if ($sort <= 0) $sort = 1;//inutile grace a max(min()) juste au-dessus

          //TODO getValInArray("order",array("ASC","DESC")); ou quelque chose du genre
          $order = getVal("order","DESC");

          //évite les injections SQL (inutile quand on aura écrit getValInArray())
          if ( strcmp($order,"ASC") !== 0 )
              $order = "DESC";//si ça n'est pas ASC, ça doit être DESC ...

          // tableau pour gérer les order by
          $a_order[0]= "IMG_COUV";
          $a_order[1]= "TITRE_TOME $order, NUM_TOME"; // MySQL: "ORDER BY column1 TRI1, column2 TRI2". TRI1, TRI2 dans [ASC, DESC]
          $a_order[2]= "NOM_SERIE $order, NUM_TOME";
          $a_order[3]= "NUM_TOME";
          $a_order[4]= "NOM_EDITION";
          $a_order[5]= "NOM_COLLECTION";
          $a_order[6]= "scpseudo";
          $a_order[7]= "depseudo";
          $a_order[8]= "DATE_ACHAT";
          $a_order[9]= "cote";
          $a_order[10]= "FLG_PRET";
          $a_order[11]= "FLG_CADEAU";
          $a_order[12]= "FLG_LU";
          $a_order[13]= "FLG_TETE";
          $a_order[14]= "FLG_DEDICACE";
          $a_order[15]= "comment";
          $a_order[16]= "NOM_PRET";
          $a_order[17]= "EMAIL_PRET";

          $pret = getVal("cb_pret","N");

          $cadeau = getVal("cb_cadeau","N");
          $eo = getVal("cb_tete","N");
          $dedicace = getVal("cb_dedicace","N");
          $non_lu = getVal("cb_lu","N");

          $limit = " limit ".(($page - 1)*$length).", ".$length;
          $orderby = " order by ".$a_order[$sort-1]." ".$order;

          $where = " where ua.user_id = ".$user_id ." and flg_achat = 'N' ";

          if ($pret == "O") $where .= " and flg_pret='O' ";
          if ($cadeau == "O") $where .= " and flg_cadeau = 'O' ";
          if ($eo == "O") $where .= " and flg_tete = 'O' ";
          if ($dedicace== "O") $where .= " and flg_dedicace = 'O' ";
          if ($non_lu== "O") $where .= " and FLG_LU <> 'O' ";

          if ($sel_type <> "Tous") {
              $where .= " and g.ORIGINE = '".Db_Escape_String($sel_type) ."'";
          }
          if($l_search <> "") {
              $searchvalue = Db_Escape_String($l_search);
              $where .= " and ( bd_tome.titre like '%". $searchvalue ."%' OR s.nom like '%". $searchvalue ."%' OR er.nom like  '%". $searchvalue ."%' OR sc.pseudo like  '%". $searchvalue ."%' OR de.pseudo like  '%". $searchvalue ."%'  ) ";
          }

          // echo  $this->Useralbum->select()." where ua.user_id = ".$user_id ." and flg_achat = 'N' ".$orderby. $limit;
          $dbs_tome = $this->Useralbum->load("c",$where.$orderby. $limit);

          $nbr = Db_CountRow($this->Useralbum->select().$where);

          $this->view->set_var( array (
              "dbs_tome" => $dbs_tome,
              "page" => $page,
              "length" => $length,
              "nbr" => $nbr,
              "sort" => $sort,
              "order" => $order,
              "pret" => $pret,
              "cadeau" => $cadeau,
              "eo" => $eo,
              "non_lu" => $non_lu,
              "dedicace" => $dedicace,
              "searchvalue" => $l_search,
               "sel_type" => $sel_type
              ));
      }
      else {
          die("Vous devez vous authentifier pour accéder à cette page.");
      }

      $this->view->set_var("PAGETITLE","Ma Collection de sur Bdovore");
      $this->view->render();
    }

    public function mesAuteurs () {
      if (User::minAccesslevel(2)) {
          $user_id = intval($_SESSION["userConnect"]->user_id);
          $this->loadModel('Useralbum');

          $page = getValInteger("page",1);
          $length = getValInteger("length",0);

          // Filtres
          $sel_type = getVal("sel_type","Tous");
          $sel_trav = getVal("sel_trav","Tous");

          // FRED - Pour le moment, on utilise la meme longueur max
          //        que pour la collection par album.
          //        voir si cela fait du sens de dissocier ces 2 longueurs ?
          //TODO mettre une longueur max. pour la recherche ?
          if (!$length) {
              if ($_COOKIE["l_etageres"] ) {
                  // récupére la valeur dans un coockie
                  $length = $_COOKIE["l_etageres"];
              } else {
                  $length = 10;
              }
          }
          setcookie("l_etageres",$length,time()+2592000);

          $l_search = getVal("l_search","" );

          if ($sel_type <> "Tous") {
              $origin = Db_Escape_String($sel_type);
          } else {
              $origin = "";
          }

          if ($sel_trav <> "Tous") {
              $travail = Db_Escape_String($sel_trav);
          } else {
              $travail = "";
          }

          if($l_search <> "") {
              $searchvalue = Db_Escape_String($l_search);
          } else {
              $searchvalue = "";
          }

          // Récupération des auteurs
          $dbs_auteur = $this->Useralbum->getUserAuteur($user_id, $page, $length, $searchvalue, $origin, $travail);
          $stat = $this->Useralbum->getStatistiques($user_id,"auteur",$origin,$travail);
          $nbr = $stat["nbauteurs"];

          $this->view->set_var( array (
              "dbs_auteur" => $dbs_auteur,
              "page" => $page,
              "length" => $length,
              "nbr" => $nbr,
              "searchvalue" => $l_search,
              "sel_type" => $sel_type,
              "sel_trav" => $sel_trav
              ));
      }
      else {
          die("Vous devez vous authentifier pour accéder à cette page.");
      }

      $this->view->set_var("PAGETITLE","Mes Auteurs sur Bdovore");
      $this->view->render();
    }

    public function mesSeries () {
      if (User::minAccesslevel(2)) {
          $user_id = intval($_SESSION["userConnect"]->user_id);
          $this->loadModel('Useralbum');
          $this->loadModel("Users_exclusions");

          // variable d'action pour l'annulation de la suppression de série à compléter
          $action = getVal("action","none"); 
          if ($action == "raz") {
              $idSerieExclu = getValInteger("idSerieExclu",0);
              $this->Users_exclusions->delSerieExclude($user_id,$idSerieExclu);
          }

          // Filtres
          $flg_incomplete = getValInteger("flg_incomplete",0); // On filtre sur les séries incomplètes ?
          $flg_achat = getValInteger("flg_achat",0);           // On considère les futurs achats comme achetés (pour considérer une série comme complète) ? 
          $auteur = getVal("auteur","");
          if ($auteur <> "") {
            $this->loadModel("Auteur");
            $pseudo = getVal("pseudo", "");
          }

          // Récupération des séries comprenant des albums à compléter
          $listSerie = $this->Users_exclusions->getListSerieToComplete($user_id,!$flg_achat);
          $ll = count($listSerie);
          // Pour simplifier les traitements plus loin, on mets le résultat dans un tableau d'ID_SERIE
          $incomplets = array();
          for ($i = 0; $i < $ll; $i++) $incomplets[] = $listSerie[$i]->ID_SERIE;

          // Résupération des séries avec des exclusions
          $listExclu = $this->Users_exclusions->getListSerieExcluSource($user_id);

          $page = getValInteger("page",1);
          $length = getValInteger("length",0);
          $sel_type = getVal("sel_type","Tous");
          // FRED - Pour le moment, on utilise la meme longueur max
          //        que pour la collection par album.
          //        voir si cela fait du sens de dissocier ces 2 longueurs ?
          //TODO mettre une longueur max. pour la recherche ?
          if (!$length) {
              if ($_COOKIE["l_etageres"] ) {
                  // récupére la valeur dans un coockie
                  $length = $_COOKIE["l_etageres"];
              } else {
                  $length = 10;
              }
          }
          setcookie("l_etageres",$length,time()+2592000);

          $l_search = getVal("l_search","" );

          if ($sel_type <> "Tous") {
            $origin = Db_Escape_String($sel_type);
          } else {
              $origin = "";
          }

          if($l_search <> "") {
              $searchvalue = Db_Escape_String($l_search);
          } else {
              $searchvalue = "";
          }

          // Récupération des séries avec filtre sur les incomplètes ou non
          $dbs_serie = $flg_incomplete ? $this->Useralbum->getUserSerie($user_id, $page, $length,$searchvalue,$origin,$auteur,implode(',',$incomplets))
                                       : $this->Useralbum->getUserSerie($user_id, $page, $length,$searchvalue,$origin,$auteur);
          $stat = $this->Useralbum->getStatistiques($user_id,"album");
          $nbr = $stat["nbseries"];

          $this->view->set_var( array (
              "dbs_serie" => $dbs_serie,
              "incomplets" => $incomplets,
              "listExclu" => $listExclu,
              "page" => $page,
              "length" => $length,
              "nbr" => $nbr,
              "searchvalue" => $l_search,
              "sel_type" => $sel_type,
              "flg_incomplete" => $flg_incomplete,
              "flg_achat" => $flg_achat,
              "auteur" => $auteur,
              "pseudo" => $pseudo
              ));
      }
      else {
          die("Vous devez vous authentifier pour accéder à cette page.");
      }

      $this->view->set_var("PAGETITLE","Mes Séries sur Bdovore");
      $this->view->render();
    }

    public function futursAchats () {
      if (User::minAccesslevel(2)) {
          $user_id = intval($_SESSION["userConnect"]->user_id);
          $this->loadModel('Useralbum');

          $page = getValInteger("page",1);
          $length = getValInteger("length",10);
          $l_search = getVal("l_search","" );

          //TODO remplacer les 3 lignes suivantes par getValInArray
          $order = getVal("order","DESC");

          if ( strcmp($order,"ASC") !== 0 )
              $order = "DESC";

          // tableau pour gérer les order by
          $a_order[0]= "IMG_COUV";
          $a_order[1]= "TITRE_TOME $order, NUM_TOME";
          $a_order[2]= "NOM_SERIE $order, NUM_TOME";
          $a_order[3]= "NOM_EDITION";
          $a_order[4]= "NOM_COLLECTION";
          $a_order[5]= "scpseudo";
          $a_order[6]= "depseudo";
          $a_order[7]= "DATE_AJOUT";
          $a_order[8]= "DTE_PARUTION";

          // variable $sort donne la colonne pour le tri
          // on s'assure que la variable est dans le bon intervale de valeur
          $sort = getValInteger("sort",9);
          $sort = max(min($sort,9),1);
          //if ($sort <= 0) $sort = 1;//inutile grace a max(min()) juste au-dessus

          $limit = " limit ".(($page - 1)*$length).", ".$length;
          $orderby = " order by ".$a_order[$sort-1]." ".$order;

          $where = " where ua.user_id = ".$user_id ." and flg_achat = 'O' ";


          if($l_search <> "") {
              $searchvalue = Db_Escape_String($l_search);
              $where .= " and ( bd_tome.titre like '%". $searchvalue ."%' OR s.nom like '%". $searchvalue ."%' OR er.nom like  '%". $searchvalue ."%' OR sc.pseudo like  '%". $searchvalue ."%' OR de.pseudo like  '%". $searchvalue ."%'  ) ";
          }
          // echo  $this->Useralbum->select()." where ua.user_id = ".$user_id ." and flg_achat = 'N' ".$orderby. $limit;
          $dbs_tome = $this->Useralbum->load("c",$where.$orderby. $limit);

          $nbr = Db_CountRow($this->Useralbum->select().$where);

          $this->view->set_var( array (
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
              "searchvalue" => $l_search
              ));
      }
      else {
          die("Vous devez vous authentifier pour accéder à cette page.");
      }

      $this->view->set_var("PAGETITLE","Mes Futurs Achats");
      $this->view->render();
    }

    public function deleteAlbum() {
        /*
         * fonction pour supprimer un album de sa collection
         * s'execute en mode ajax
         *
         */
        if (User::minAccesslevel(2)) {
            $id_edition = getValInteger("id_edition",0);
            $this->loadModel("Useralbum");

            $this->Useralbum->set_dataPaste(array(
                "id_edition" => $id_edition,
                "user_id" => intval($_SESSION["userConnect"]->user_id)
            ));
            //$this->Useralbum->load();
            $this->Useralbum->delete();
            $this->view->set_var('json', json_encode($this->Useralbum->error));
            $this->view->layout = "ajax";
            $this->view->render();
        }
    }

    public function monActu() {
        /*
         * Onglet mon actualité
         */
        if (User::minAccesslevel(2)) {
            $this->loadModel("Tome");
            $nb_mois = getValInteger("nb_mois",1);
            $page = getValInteger("page",1);
            $mode = getValInteger("mode",1);
            // creation du filtre par défaut sur les série
            $dbs_tome = $this->Tome->getUserActualite($mode, $nb_mois,$page);

            $this->view->set_var(array(
                "dbs_tome" => $dbs_tome,
                "page" => $page,
                "nb_mois" => $nb_mois,
                "mode" => $mode
                ));
            $this->view->set_var("PAGETITLE","Mon actualité BD");
            $this->view->render();
        }
        else {
            die("Vous devez vous authentifier pour accéder à cette page.");
        }
    }

    public function serieComplete () {
        /*
         * Séries à compléter !
         *
         */
        if (User::minAccesslevel(2)) {
             $user_id = intval($_SESSION["userConnect"]->user_id);
             $id_serie = getValInteger("lstSerie",0);
             $action = getVal("action","none"); // variable d'action pour l'ajout ou suppression de série à compléter
             $flg_achat = getValInteger("flg_achat",0); // filtre ou non sur les futurs achats
             
             $this->loadModel("Tome");
             $this->loadModel("Users_exclusions");

             if ($action == "exclude") {
                 $this->Users_exclusions->addSerieExclude($user_id,$id_serie);
                 $id_serie = 0;
             }
             if ($action == "raz") {
                 $idSerieExclu = getValInteger("idSerieExclu",0);
                 $this->Users_exclusions->delSerieExclude($user_id,$idSerieExclu);
             }

             if($action== "exclude_tome") {
                 $listAlbum = getVal("sel_tome",array());

                 foreach ($listAlbum as $id_tome) {
                      $this->Users_exclusions->addAlbumExclude($user_id,$id_serie,intval($id_tome));
                 }

             }


             $listSerie = $this->Users_exclusions->getListSerieToComplete($user_id, $flg_achat);

             $listExclu = $this->Users_exclusions->getListSerieExclu($user_id);

             $checkserie = false;
             if ($id_serie == 0 and count($listSerie) > 0) {
                 // selection de la première série de la liste
                 $id_serie = $listSerie[0]->ID_SERIE;
                 $checkserie = true;
             } else if ($id_serie > 0 and count($listSerie) > 0) {
                 foreach ($listSerie as $serie) {
                     if ($id_serie == $serie->ID_SERIE) {$checkserie = true;} // vérification que la série demandé est bien récupérée
                 }
             }
             if (!$checkserie) { $id_serie = $listSerie[0]->ID_SERIE;}
             
             $dbs_tome  = $this->Tome->getListAlbumToComplete($user_id,$id_serie, !$flg_achat);
             // si la liste est vide, il faut re
             $this->view->set_var(array(
                 "listSerie" =>  $listSerie,
                 "id_serie" => $id_serie,
                 "dbs_tome" => $dbs_tome,
                 "listExclu" => $listExclu,
                  "flg_achat" => $flg_achat
             ));

             $this->view->set_var("PAGETITLE","Séries à compléter");
             $this->view->render();
        }
        else {
            die("Vous devez vous authentifier pour accéder à cette page.");
        }
    }

    public function Addition() {
        /*
         * L'addition : reprise quasi tel quel du code de Latruffe...
         * Un peu compliqué à refaire en 5 mn !
         */
        // Variables générales

        if (User::minAccesslevel(2)) {

            $user_id = intval($_SESSION["userConnect"]->user_id);
            $annee = getVal("annee",'');
            $mois = getVal("mois",'');
            $info = getVal("info",'');

            if ($annee=='') $annee=date("Y");
            if ($mois=='') $mois=date("n");
            if ($info=='') $info=1;


            // Valeurs d'option
            //$tb_mois=array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
            //for ($i=0; $i<12; $i++)
            //{
            //        $opt_mois[$i][0] = $i+1;
            //        $opt_mois[$i][1] = $tb_mois[$i];
            //}

            // initialise les compteurs
            $count = 0;

            // Récupère les valeurs par défaut
            $this->loadModel("User");
            $this->User->load("c"," WHERE user_id = ".$user_id);

            $defval[0] = $this->User->VAL_ALB;
            $defval[1] = $this->User->VAL_INT;
            $defval[2] = $this->User->VAL_COF;
            $defcoffret = $this->User->VAL_COF_TYPE;

            // Récupère la collection
            $this->loadModel("Useralbum");
            $dbs_album = $this->Useralbum->load("c"," WHERE flg_achat = 'N' and ua.user_id = ".$user_id . " ORDER BY IFNULL(ua.date_achat, ua.date_ajout) DESC");


            $a_album = array();
            $count = 0;
            foreach ($dbs_album->a_dataQuery as $album)
            {
                /*
                 * On parcours la liste des albums de la colleciton pour faire l'addition
                 * Probablement jouable en une requête SQL mais pas si évident : il faut tenir compte des règles de valorisation
                 *
                 */
                    if ($album->FLG_INT_TOME == 'O')
                        $type = 1;
                    else
                        $type = 0;

                    if ($album->FLG_TYPE_TOME == 1) $type = 2;

                    // Vérifie si l'album est coté par l'utilisateur
                    if (($album->cote != '') & (($album->FLG_TYPE_TOME == 0) | ($defcoffret == 1)))
                    {
                            $tot_prix[$type] += $album->cote;
                            $tot_count[$type]++;
                            $prix_retenu = $album->cote;
                    }
                    // Verifie si l'album est noté par bdovore
                    elseif (($album->PRIX_BDNET != '') & ($album->FLG_TYPE_TOME == 0 | ($defcoffret == 1)))
                    {
                            $tot_prix[$type] += $album->PRIX_BDNET;
                            $tot_count[$type]++;
                            $prix_retenu = $album->PRIX_BDNET;
                    }
                    // Non valorisé
                    elseif (($album->FLG_TYPE_TOME == 0) | ($defcoffret == 1))
                    {
                            if ($defval[$type] == '')
                            {
                                    $tot_count[$type]++;
                                    $prix_retenu = 0;
                            }else{
                                    $tot_prix[$type] += $defval[$type];
                                    $tot_count[$type]++;
                                    $prix_retenu = $defval[$type];
                            }
                    }
                    // Coffret valorisé album par album
                    elseif (($album->FLG_TYPE_TOME == 1) & ($defcoffret == 0))
                    {
                            $tot_prix[$type] += $defval[2];
                            $tot_count[$type]++;
                            $prix_retenu = $defval[2];
                    }

                    // stocke les mini et les maxi
                    $year =$album->annee_achat;
                    $depense[$year] += $prix_retenu;
                    $nbalbums[$year]++;

                    // stocke le détail par mois
                    $month = $album->mois_achat;

                    if (($year == $annee) & ($month == $mois))
                    {
                            $a_album [] = $album;
                            $prixretenu[$count] = $prix_retenu;
                            $count++;
                    }
            }

            $this->view->set_var(array(
                "a_depense" => $depense,
                "a_nbalbum" => $nbalbums,
                "a_album" => $a_album,
                "info" => $info,
                "annee" => $annee,
                "mois" => $mois,
                "prixretenu" => $prixretenu,
                "PAGETITLE" => "L'addition"
            ));

            // on remplit le block detail par annee
        }
        else {
            $this->view->addAlertPage("Vous devez vous authentifier pour accéder à cette page !");
            $this->view->addPhtmlFile('alert', 'BODY');
        }

        $this->view->render();
    }

    public function Detailvalorisation() {
        /*
         * Affiche le détail de la valorisation et les options
         * Pour le moment, on ne reprend pas la saisies des cout par album... à refaire plus tard
         */
        if (User::minAccesslevel(2)) {
            /*
             * On commence par mettre à jour les valeurs dans User si nécessaire
             */
            $user_id = intval($_SESSION["userConnect"]->user_id);
            $user = new User($user_id);
            $user->load();
            if (getVal("action","") == "Recalculer") {
                $txtPrixAlbum = getVal("txtPrixAlbum","12");
                $txtPrixIntegrale = getVal("txtPrixIntegrale","30");
                $txtPrixCoffret = getVal("txtPrixCoffret","5.0");
                $lstCoffret = getVal("lstCoffret",0);


                // on met à jour le user

                $user->set_dataPaste(array(
                    "VAL_ALB" => floatval($txtPrixAlbum),
                    "VAL_INT" => floatval($txtPrixIntegrale) ,
                    "VAL_COF" => floatval($txtPrixCoffret),
                    "VAL_COF_TYPE" => intval($lstCoffret)
                ));

                $user->update();
            }

            $txtPrixAlbum = $user->VAL_ALB;
            $txtPrixIntegrale = $user->VAL_INT;
            $txtPrixCoffret = $user->VAL_COF;
            $lstCoffret = $user->VAL_COF_TYPE;

            $this->loadModel("Useralbum");

            $o_val = $this->Useralbum->getValorisation($user_id);

            $this->view->set_var(array(
                "o_val" => $o_val,
                "lstCoffret" => $lstCoffret,
                "txtPrixAlbum" => $txtPrixAlbum,
                "txtPrixIntegrale" => $txtPrixIntegrale,
                "txtPrixCoffret" => $txtPrixCoffret
            ));
        }
        else {
             $this->view->addAlertPage("Vous devez vous authentifier pour accéder à cette page !");

            $this->view->addPhtmlFile('alert', 'BODY');
        }
        $this->view->set_var("PAGETITLE","Détail de la valorisation");
        $this->view->layout = "iframe";
        $this->view->render();
    }

    public function Proposition () {
        if (User::minAccesslevel(2)) {
            $this->loadModel("User_album_prop");

            $user_id = intval($_SESSION["userConnect"]->user_id);

            // Tableau pour les choix d'options

            $opt_source[0][0] = 0;
            $opt_source[0][1] = 'Albums';
            $opt_source[1][0] = 1;
            $opt_source[1][1] = 'Editions';
            $opt_source[2][0] = 2;
            $opt_source[2][1] = 'Corrections';

            $opt_filtre[0][0] = -1;
            $opt_filtre[0][1] = 'Tous';
            $opt_filtre[1][0] = 0;
            $opt_filtre[1][1] = 'En attente';
            $opt_filtre[2][0] = 1;
            $opt_filtre[2][1] = 'Valid&eacute';
            $opt_filtre[3][0] = 99;
            $opt_filtre[3][1] = 'Refus&eacute;';
            $opt_filtre[4][0] = 98;
            $opt_filtre[4][1] = 'Effac&eacute; par l\'utilisateur';

            $act = getVal("act","");
            $propid = getValInteger("propid",0);

            $lstSource = getVal("lstSource",0);
            $lstFiltre= getVal("lstFiltre","");

            if ($lstSource == 2){
                $criteria = "CORRECTION";
                $choix_source = 2;
            }else if ($lstSource == 1){
                $criteria = "EDITION";
                $choix_source = 1;
            }else{
                $criteria = "AJOUT";
                $choix_source = 0;
            }

            if ($act == "suppr") {
                // annulation de la proposition courante : on passe le statut à 98
                if ($lstSource == 1) {
                    $this->loadModel("Edition");
                    $this->Edition->set_dataPaste(array("ID_EDITION" => $propid));
                    $this->Edition->load();
                     if ($this->Edition->USER_ID == $user_id ) {
                        $this->Edition->set_dataPaste(array("PROP_STATUS"=>98));
                        $this->Edition->update();
                    }
                    else {
                        die("Vous ne pouvez pas supprimer une proposition d'un autre utilisateur !");
                    }
                }
                else {
                    $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $propid));
                    $this->User_album_prop->load();
                    if ($this->User_album_prop->USER_ID == $user_id ) {
                        $this->User_album_prop->set_dataPaste(array("STATUS"=>98));
                        $this->User_album_prop->update();
                    }
                    else {
                        die("Vous ne pouvez pas supprimer une proposition d'un autre utilisateur !");
                    }
                }

            }
            if ($lstFiltre == 0) {
                if ($lstSource == 1){
                    $filtre = " AND (prop_status = 0 OR prop_status = 2 OR prop_status = 3 OR prop_status = 4)";
                }
                else{
                    $filtre = " AND (status = 0 OR status = 2 OR status = 3 OR status = 4)";
                }
                $choix_filtre = $lstFiltre;
            }
            elseif ($lstFiltre != -1 && $lstFiltre != ""){
                if ($lstSource == 1){
                    $filtre = " AND prop_status = ".  Db_Escape_String($lstFiltre);
                }else{
                    $filtre = " AND status = ".Db_Escape_String($lstFiltre);
                }
                $choix_filtre = $lstFiltre;
            }
            else{
                $filtre = "";
                $choix_filtre = -1;
            }

            $status[0] = "En attente";
            $status[1] = "Valid&eacute;";
            $status[2] = "En attente";
            $status[3] = "En attente";
            $status[4] = "En attente";
            $status[99] = "Refus&eacute;";
            $status[98] = "Effac&eacute; par l'utilisateur";

            // Requete sur les données à afficher
            if ($lstSource == 1){
                // requête sur les éditions
                $this->loadModel("Edition");

                $dbs_edition = $this->Edition->load("c"," WHERE bd_edition.user_id = ".$user_id." ".$filtre);
                $this->view->set_var("dbs_edition",$dbs_edition);
            }
            else{

                $dbs_prop= $this->User_album_prop->load("c"," WHERE user_id = ".$user_id. "
                    AND prop_type = '".  Db_Escape_String($criteria)."' $filtre ");
                $this->view->set_var("dbs_prop",$dbs_prop);
            }

            $this->view->set_var($this->User_album_prop->getAllStat());
            $this->view->set_var (array(
                "OPTIONSOURCE" => GetOptionValue($opt_source,$choix_source),
                "OPTIONFILTRE" => GetOptionValue($opt_filtre,$choix_filtre),
                "lstSource" => $lstSource
                ));
        }
        else {
            $this->view->addAlertPage("Vous devez vous authentifier pour accéder à cette page !");
            $this->view->addPhtmlFile('alert', 'BODY');
        }

        $this->view->set_var("PAGETITLE","Suivi des mes propositions");
        $this->view->render();
    }

     public function statistiques () {
        // statistiques sur la collection : on appelle les graphes jpgrap
         if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION["userConnect"]->user_id);
            $this->loadModel('Useralbum');

            $this->view->set_var("stat",$this->Useralbum->getStatistiques($user_id));

             $this->view->set_var("PAGETITLE","Statistiques de ma collection");
            $this->view->render();
         }
     }


}
