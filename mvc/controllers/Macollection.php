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
            $user_id = $_SESSION["userConnect"]->user_id;
            $this->loadModel('Useralbum');
            $user = $this->getUserInfo();

            $open_collec = postVal("open_collec","");
            
            if ($open_collec <> "") {
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
                "a_carre" =>  $this->Useralbum->carre($this->getUserInfo()),
                 "user_id" => $user_id,
                "carre_type" => $user->CARRE_TYPE,
                "open_collec" => $user->OPEN_COLLEC
                    )
                  ) ;
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
            

        $user = new User($_SESSION["userConnect"]->user_id);

        $user->load();

        return $user;
    }
    
    public function majCollection () {
        $id_tome = getVal('id_edition',0);
       /* Fonction pour ajouter / mettre à jour un album dans la collection 
        * 
        * seul le user connecté peut mettre à jour 
        * prévu pour un appel en mode ajax
        * retour : vide si ok, code erreur sinon
        */
        if (! empty($_SESSION['userConnect']->user_id)) {
            $user_id = $_SESSION['userConnect']->user_id;
            $id_edition = getVal("id_edition",0);
            $flg_achat = getVal('flg_achat','N');
            
            $flg_pret = getVal('flg_pret','N');
            $email_pret = getVal('email_pret','');
            $flg_dedicace = getVal("flg_dedicace",'N');
            $flg_tete = getVal("flg_tete",'N');
            
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
                
                if (isset($this->Useralbum->DATE_AJOUT)) {
                    $date_ajout = $this->Useralbum->DATE_AJOUT;
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
    
     public function mesEtageres () {
          if (User::minAccesslevel(2)) {
            $user_id = $_SESSION["userConnect"]->user_id;
            $this->loadModel('Useralbum');
           
            // tableau pour gérer les order by
           
            $a_order[0]= "IMG_COUV";
            $a_order[1]= "TITRE_TOME, NUM_TOME";
            $a_order[2]= "NOM_SERIE, NUM_TOME";
            $a_order[3]= "NUM_TOME";
            $a_order[4]= "NOM_EDITION";
            $a_order[5]= "NOM_COLLECTION";
            $a_order[6]="scpseudo";
            $a_order[7]= "depseudo";
            $a_order[8]= "DATE_AJOUT";
            
            $page = getVal("page",1);
            $length = getVal("length",10);
            $searchvalue = Db_Escape_String(getVal("l_search","" ));
           
            
            // variable $sort donne la colonne pour le tri
            // on s'assure que la variable est dans le bon intervale de valeur
            $sort = intval(getVal("sort",9));
            $sort = max(min($sort,9),0);
            if ($sort < 0) $sort =1;
            
            $order = getVal("order","DESC");
            
            $pret = getVal("cb_pret","N");
            
            $cadeau = getVal("cb_cadeau","N");
            $eo = getVal("cb_tete","N");
            $dedicace = getVal("cb_dedicace","N");
            
            $limit = " limit ".(($page - 1)*$length).", ".$length;
            $orderby = " order by ".$a_order[$sort-1]." ".$order;
            
           
            
            $where = " where ua.user_id = ".$user_id ." and flg_achat = 'N' ";
            
            if ($pret == "O") $where .= " and flg_pret='O' ";
            if ($cadeau == "O") $where .= " and flg_cadeau = 'O' ";
            if ($eo == "O") $where .= " and flg_tete = 'O' ";
            if ($dedicace== "O") $where .= " and flg_dedicace = 'O' ";
            
            if(searchvalue <> "") $where .= " and ( bd_tome.titre like '%". $searchvalue ."%' OR s.nom like '%". $searchvalue ."%' OR er.nom like  '%". $searchvalue ."%' OR sc.pseudo like  '%". $searchvalue ."%' OR de.pseudo like  '%". $searchvalue ."%'  ) ";
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
                "searchvalue" => $searchvalue
               
                    )
                  )     ;
        }
        else {
            die("Vous devez vous authentifier pour accéder à cette page.");
        }
        
        
        $this->view->set_var("PAGETITLE","Ma Collection de sur Bdovore");
        $this->view->render();
         
     }
     
     public function futursAchats () {
          if (User::minAccesslevel(2)) {
            $user_id = $_SESSION["userConnect"]->user_id;
            $this->loadModel('Useralbum');
           
             
            $page = getVal("page",1);
            $length = getVal("length",10);
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
            
            // variable $sort donne la colonne pour le tri
            // on s'assure que la variable est dans le bon intervale de valeur
            $sort = intval(getVal("sort",9));
           $sort = max(min($sort,9),0);
            if ($sort < 0) $sort =1;
            
           
            
            $limit = " limit ".(($page - 1)*$length).", ".$length;
            $orderby = " order by ".$a_order[$sort-1]." ".$order;
            
            $where = " where ua.user_id = ".$user_id ." and flg_achat = 'O' ";
            
           
            
            if(searchvalue <> "") $where .= " and ( bd_tome.titre like '%". $searchvalue ."%' OR s.nom like '%". $searchvalue ."%' OR er.nom like  '%". $searchvalue ."%' OR sc.pseudo like  '%". $searchvalue ."%' OR de.pseudo like  '%". $searchvalue ."%'  ) ";
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
                "searchvalue" => $searchvalue
               
                    )
                  )     ;
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
             $id_edition = getVal("id_edition",0);
             $this->loadModel("Useralbum");
             
             $this->Useralbum->set_dataPaste(array(
                 "id_edition" => $id_edition,
                 "user_id" => $_SESSION["userConnect"]->user_id
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
               $nb_mois = getVal("nb_mois",1);
               $page = getVal("page",1);
               $mode = getVal("mode",1);
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
              $id_serie = getVal("lstSerie",0);
              
              $action = getVal("action","none"); // variable d'action pour l'ajout ou suppression de série à compléter
              
              $this->loadModel("Tome");
              $this->loadModel("Users_exclusions");
              
              if ($action == "exclude") {
                  $this->Users_exclusions->addSerieExclude($_SESSION["userConnect"]->user_id,$id_serie);
                  $id_serie = 0;
              }
              if ($action == "raz") {
                  $idSerieExclu = getVal("idSerieExclu",0);
                  $this->Users_exclusions->delSerieExclude($_SESSION["userConnect"]->user_id,$idSerieExclu);
              }
             
              if($action== "exclude_tome") {
                  $listAlbum = getVal("sel_tome",array());
                  
                  foreach ($listAlbum as $id_tome) {
                       $this->Users_exclusions->addAlbumExclude($_SESSION["userConnect"]->user_id,$id_serie,$id_tome);
                  }
                  
              }
              
             
              $listSerie = $this->Users_exclusions->getListSerieToComplete($_SESSION["userConnect"]->user_id);
              
              $listExclu = $this->Users_exclusions->getListSerieExclu($_SESSION["userConnect"]->user_id);
              
               if ($id_serie == 0 and count($listSerie) > 0) {
                   // selection de la première série de la liste
                   $id_serie = $listSerie[0]->ID_SERIE;
               }
               
              $dbs_tome  = $this->Tome->getListAlbumToComplete($_SESSION["userConnect"]->user_id,$id_serie);
              $this->view->set_var(array(
                  "listSerie" =>  $listSerie,
                  "id_serie" => $id_serie,
                  "dbs_tome" => $dbs_tome,
                  "listExclu" => $listExclu
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
         * L'addition : reprise quasi telquel du code de Latruffe... 
         * Un peu compliqué à refaire en 5 mn !
         */
        // Variables générales

         if (User::minAccesslevel(2)) {

            $annee = getVal("annee",'');
            $mois = getVal("mois",'');
            $info = getVal("info",'');

            if ($annee=='') $annee=date("Y");

            if ($mois=='') $mois=date("n");

            if ($info=='') $info=1;





            // Valeurs d'option

            $tb_mois=array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");

            for ($i=0; $i<12; $i++)

            {

                    $opt_mois[$i][0] = $i+1;

                    $opt_mois[$i][1] = $tb_mois[$i];

            }



            // initialise les compteurs

            $count = 0;



            // R�cup�re les valeurs par d�faut

            $this->loadModel("User");
            $this->User->load("c"," WHERE user_id = ".$_SESSION["userConnect"]->user_id);



            $defval[0] = $this->User->VAL_ALB;

            $defval[1] = $this->User->VAL_INT;

            $defval[2] = $this->User->VAL_COF;

            $defcoffret = $this->User->VAL_COF_TYPE;



            // R�cup�re la collection
            $this->loadModel("Useralbum");
            $dbs_album = $this->Useralbum->load("c"," WHERE flg_achat = 'N' and ua.user_id = ".$_SESSION["userConnect"]->user_id . " ORDER BY IFNULL(ua.date_achat, ua.date_ajout) DESC");




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



                    // V�rifie si l'album est cot� par l'utilisateur

                    if (($album->cote != '') & (($album->FLG_TYPE_TOME == 0) | ($defcoffret == 1)))

                    {

                            $tot_prix[$type] += $album->cote;

                            $tot_count[$type]++;

                            $prix_retenu = $album->cote;

                    }

                    // Verifie si l'album est not� par bdovore

                    elseif (($album->PRIX_BDNET != '') & ($album->FLG_TYPE_TOME == 0 | ($defcoffret == 1)))

                    {

                            $tot_prix[$type] += $album->PRIX_BDNET;

                            $tot_count[$type]++;

                            $prix_retenu = $album->PRIX_BDNET;

                    }

                    // Non valoris�

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



                    // stocke le d�tail par mois

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

            // on remplie le block detail par annee



            
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
            $user = new User($_SESSION["userConnect"]->user_id);
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
                    "VAL_COF_TYPE" => $lstCoffret
                ));
                
                $user->update();
            }
            $txtPrixAlbum = $user->VAL_ALB;
            $txtPrixIntegrale = $user->VAL_INT;
            $txtPrixCoffret = $user->VAL_COF;
            $lstCoffret = $user->VAL_COF_TYPE;
            
            
            $this->loadModel("Useralbum");
            
            $o_val = $this->Useralbum->getValorisation($_SESSION["userConnect"]->user_id);
            
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
            $propid = intval(getVal("propid",0));
            
            $lstSource = getVal("lstSource",0);
            $lstFiltre= getVal("lstFiltre","");
            
            if ($lstSource ==2){
		$criteria = "CORRECTION";
		$choix_source = 2;
            }else if ($lstSource==1){
                    $criteria = "EDITION";
                    $choix_source = 1;
            }else{
                    $criteria = "AJOUT";
                    $choix_source = 0;
            }
            
            if ($act == "suppr") {
                // annulation de la proposition courrante : on passe le statut à 98
                if ($lstSource == 1) {
                    
                }
                else {
                    $this->User_album_prop->set_dataPaste(array("ID_PROPOSAL" => $propid));
                    $this->User_album_prop->load();
                    if ($this->User_album_prop->USER_ID == $_SESSION["userConnect"]->user_id ) {
                        $this->User_album_prop->set_dataPaste(array("STATUS"=>98));
                        $this->User_album_prop->update();
                    }
                    else {
                        die("Vous ne pouvez pas supprimer une proposition d'un autre utilisateur !");
                    }
                }
                
            }
            if ($lstFiltre==0) {
		if ($lstSource==1){
			$filtre = " AND (prop_status = 0 OR prop_status = 2 OR prop_status = 3 OR prop_status = 4)";
		}
                else{
			$filtre = " AND (status = 0 OR status = 2 OR status = 3 OR status = 4)";
		}
		$choix_filtre = $lstFiltre;
            } 
            elseif ($lstFiltre!=-1 & $lstFiltre !=""){
		if ($lstSource ==1){
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
            
                // Requete sur les donn�es � afficher
            if ($lstSource ==1){
                // reqûete sur les éditions
                $this->loadModel("Edition");
                
                $dbs_edition = $this->Edition->load("c"," WHERE bd_edition.user_id = ".$_SESSION["userConnect"]->user_id." ".$filtre);
                $this->view->set_var("dbs_edition",$dbs_edition);
            }
            else{
               
                $dbs_prop= $this->User_album_prop->load("c"," WHERE user_id = ".$_SESSION["userConnect"]->user_id. " 
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
    
}
?>

