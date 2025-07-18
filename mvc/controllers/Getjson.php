<?php

/*
 * @author : Tom
 *
 * Contrôleur pour l'échange de fichier JSON sur les différentes tables de base
 * auteur, Collection, Série, Album etc... pour remplir les listes en mode AJAX
 *
 * Peut servir de base d'une API pour mobile ?
 *
 */

class GetJSON extends Bdo_Controller {

    public function Index() {
        Bdo_Cfg::setVar('debug',false);

        $data = getVal("data", "");
        $compress = getVal("compress", 1);
        if ($compress )  {
            ob_start("ob_gzhandler");
        }
        switch ($data) {
            case "Auteur" :
                $this->Auteur();
                break;
            case "Genre" :
                $this->Genre();
                break;
            case "Edition" :
                $this->Edition();
                break;

            case "Editeur" :
                $this->Editeur();
                break;
            case "Collection":
                $this->Collection();
                break;
            case "Album" :
                $this->Album();
                break;
            case "Serie" :
                $this->Serie();
                break;
            case "Useralbum" :
                $this->Useralbum();
                break;
            case 'Userserie': 
                $this->Userserie();
                break;
            case 'Useractu' :
                $this->Useractu();
                break;
            case "Albummanquant" : 
                $this->Albummanquant();
                break;
            case "Actu":
                $this->Actu();
                break;
            case "Userstat":
                $this->Userstat();
                break;
            case "isExclu":
                $this->isExclu();
                break;
            case "ListExclusion":
                $this->listExclusion();
                break;
            case "ListTomesExclus": 
                $this->listTomesExclus();
                break;
            case "Userpref": 
                $this->Userpref();
                break;
            case "CollectionStat":
                $this->CollectionStat();
                break;
            default :
                break;
        }
    }

    private function Auteur() {
        $id_auteur = getValInteger("id_auteur", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);        
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */

        $this->loadModel("Auteur");
        if ($id_auteur <> 0) {
            $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $id_auteur));
            $this->Auteur->load();
        } else if ($term <> "") {
            
            //$where = " WHERE PSEUDO like '%" . Db_Escape_String($term) . "%' ORDER BY PSEUDO";
            $where = " WHERE MATCH(search_field) AGAINST( '" . Db_Escape_String($term) . "' IN NATURAL LANGUAGE MODE) "
                    . " OR PSEUDO like '%" . Db_Escape_String($term) . "%' "
                    . "ORDER BY CASE WHEN pseudo = '" . Db_Escape_String($term) . "' THEN 1 ELSE 0 END DESC, "
                    . "MATCH(search_field) AGAINST( '" . Db_Escape_String($term) . "') DESC, pseudo LIMIT 0, 10";
            $this->Auteur->load("c", $where);
        } else {
            $this->view->set_var('json',"{}");
        }
        if ($mode == 0 AND isset($this->Auteur->dbSelect->a_dataQuery)) {
            $arr = array();
            foreach ($this->Auteur->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->PSEUDO,
                            'id' => $obj->ID_AUTEUR
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else if (isset($this->Auteur->dbSelect->a_dataQuery)) {
            $this->view->set_var('json', json_encode($this->Auteur->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    private function filterTerms ($term) {
        if (strpos($term, 'hentai')) {
            $term = "";
        }
        if (strpos($term, 'sex')) {
            $term = "";
        }
        if (strpos($term, 'porn')) {
            $term = "";
        }
        if (strpos($term, 'ero')) {
            $term = "";
        }
        if (strpos($term, 'gore')) {
            $term = "";
        }
        if (strpos($term, 'xxx')) {
            $term = "";
        }
        if (strpos($term, 'violence')) {
            $term = "";
        }
        if (strpos($term, 'hardcore')) {
            $term = "";
        }
        if (strpos($term, 'bitch')) {
            $term = "";
        }
        if (strpos($term, 'hardcore')) {
            $term = "";
        }
        if (strpos($term, 'slut')) {
            $term = "";
        }
        if (strpos($term, 'yaoi')) {
            $term = "";
        }
        if (strpos($term, 'ecchi')) {
            $term = "";
        }
        
        return $term;
    }
    private function Album() {
        $ID_TOME = getValInteger('id_tome', 0);
        $id_edition = getValInteger('id_edition', 0);
        $isbn = getVal('ISBN', '');
        $id_serie = getValInteger("id_serie",0);
        $ean = getVal('EAN', '');
        $mode = getValInteger("mode", 0);
        $addfilter = "";
            if ($_SESSION['userConnect']->user_id == 28039) {
                $addfilter .= " AND g.ID_GENRE in (6, 8, 9, 10, 28, 51) ";
                $term = $this->filterTerms($term);
                    
            }
        if ($id_edition or $isbn <> '' or $ean <> '' ) {
            //selection par isbn ou ean

            if ($ean <> '') {
                // selection par ean
                $filter = "bd_edition.ean  ='" . Db_Escape_String($ean) . "'";
            } else if ($isbn <> '') {
                // selection par isbn
                $filter = "bd_edition.isbn = '" . Db_Escape_String($isbn) . "'";
            } else if ($id_edition){
                 $filter = "bd_edition.id_edition = ". $id_edition;
            }

            if ($filter) {
                $this->loadModel('Edition');

                $this->Edition->load("c", "WHERE  " . $filter);

                $this->view->set_var('json', json_encode($this->Edition->dbSelect->a_dataQuery));
            } else {
                $arr = array();
                $arr[] = array("ERROR" => "WRONG ISBN or EAN Code");
                $this->view->set_var('json', json_encode($arr));
            }
        } elseif ($ID_TOME <> 0) {
            // selection par id_tome
            $this->loadModel('Tome');
            $this->Tome->set_dataPaste(array(
                "ID_TOME" => $ID_TOME
            ));
            $this->Tome->load();

            $this->view->set_var('json', json_encode($this->Tome->dbSelect->a_dataQuery));
        } else {
            $term = getVal("term", "");
            $id_serie = getVal("id_serie",0);
            $id_auteur = getValInteger("id_auteur", 0);
            $id_collection = getValInteger("id_collection", 0);
            $where = "";
            
            if ($id_serie) {
                $where = " WHERE s.id_serie = $id_serie "; 
            } else  if ($id_auteur) {
                $where = " WHERE (bd_tome.ID_SCENAR = $id_auteur OR bd_tome.ID_COLOR = $id_auteur OR bd_tome.ID_DESSIN = $id_auteur OR "
                        . " bd_tome.ID_SCENAR_ALT = $id_auteur OR bd_tome.ID_COLOR_ALT = $id_auteur OR bd_tome.ID_DESSIN_ALT = $id_auteur )";
            } else if ($id_collection) {
                $where = " WHERE (c.id_collection = $id_collection) " ;
            } 
            else {
                $where = " WHERE bd_tome.TITRE like '" . Db_Escape_String($term) . "%' ". $addfilter ." limit 0,10";
            }
            
            
           
            $this->loadModel('Tome');
            $this->Tome->load("c", $where);

            if ($mode == 0) {
                foreach ($this->Tome->dbSelect->a_dataQuery as $obj) {

                    $arr[] = (object) array(
                                'label' => $obj->TITRE_TOME,
                                'id' => $obj->ID_TOME
                    );
                }
                $this->view->set_var('json', json_encode($arr));
            } else {
                $this->view->set_var('json', json_encode($this->Tome->dbSelect->a_dataQuery));
            }
        }

        $this->view->layout = "ajax";

        $this->view->render();
    }

    private function Genre() {
        $id_genre = getValInteger("id_genre", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */
        $this->loadModel("Genre");
        if ($id_genre <> 0) {
            $this->Genre->set_dataPaste(array("ID_GENRE" => $id_genre));
            $this->Genre->load();
        } else {
            $where = " WHERE LIBELLE like '%" . Db_Escape_String($term) . "%' ORDER BY LIBELLE";
            $this->Genre->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Genre->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->LIBELLE,
                            'id' => $obj->ID_GENRE
                );
            }
            $this->view->set_var('json', json_encode($arr ?? []));
        } else {
            $this->view->set_var('json', json_encode($this->Genre->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Editeur() {
        $id_editeur = getValInteger("id_editeur", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */
        $this->loadModel("Editeur");
        if ($id_editeur <> 0) {
            $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $id_editeur));
            $this->Editeur->load();
        } else if ($term <> "") {
            //$where = " WHERE NOM like '%" . Db_Escape_String($term) . "%' ORDER BY NOM";
            $where = " WHERE MATCH (NOM) AGAINST ( '" . Db_Escape_String($term) . "'  IN NATURAL LANGUAGE MODE) OR "
                    . " NOM like '%" . Db_Escape_String($term) . "%' "
                    . " ORDER BY MATCH (NOM) AGAINST ( '" . Db_Escape_String($term) . "' ) DESC, NOM";
            
            $this->Editeur->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Editeur->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM,
                            'id' => $obj->ID_EDITEUR
                );
            }
            $this->view->set_var('json', json_encode($arr ?? []));
        } else {
            $this->view->set_var('json', json_encode($this->Editeur->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Collection() {
        $id_editeur = getValInteger("id_editeur", 0);
        $id_collection = getValInteger("id_collection", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);
        /*
         * Mode : 0 pour autocomplete, 1 pour l'ensemble des données
         */
        $this->loadModel("Collection");
        if ($id_collection <> 0) {
            $this->Collection->set_dataPaste(array("ID_COLLECTION" => $id_collection));
            $this->Collection->load();
        } else if ($id_editeur <> 0) {
            /*
             * Sélection de la liste des collection disponible pour cet éditeur
             */
            $where = " WHERE bd_collection.ID_EDITEUR =" . $id_editeur . " ";
            if ($term <> "" AND $term <> "<defaut>")
                $where .= " AND bd_collection.NOM like '%" . Db_Escape_String($term) . "%' ORDER BY bd_collection.NOM ";

            $this->Collection->load("c", $where);
        } else if ($term <> "") {
            $where = " WHERE bd_collection.NOM like '%" . Db_Escape_String($term) . "%'";
            $this->Collection->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Collection->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM,
                            'id' => $obj->ID_COLLECTION
                );
            }
            $this->view->set_var('json', json_encode($arr ?? []));
        } else {
            $this->view->set_var('json', json_encode($this->Collection->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Serie() {
        $id_serie = getValInteger("id_serie", 0);
        $term = getVal("term", "");
        $mode = getValInteger("mode", 0);

        $this->loadModel("Serie");

        if ($id_serie <> 0) {
            $this->Serie->set_dataPaste(array("ID_SERIE" => $id_serie));
            $this->Serie->load();
        } else if ($term <> "") {
            $addfilter = "";
            if ($_SESSION['userConnect']->user_id == 28039) {
                $addfilter .= " AND bd_genre.ID_GENRE in (6, 8, 9, 10, 28, 51) ";
                 $term = $this->filterTerms($term);
                
                
            }
            if ($mode == 2) {
                $termc = str_replace("'", " ", $term);
                $this->Serie->load('c'," WHERE  MATCH (NOM) AGAINST ( '.$termc.' IN NATURAL LANGUAGE MODE)  ".$addfilter ." GROUP BY ID_SERIE ORDER BY (LOG(NBR_USER_ID_SERIE +2) + IF('".$termc."' = NOM, 1000, MATCH (NOM) AGAINST ( '".$termc."' IN NATURAL LANGUAGE MODE))) desc, NOM LIMIT 0,30");
                 
            } 
            if ($mode != 2 || count($this->Serie->dbSelect->a_dataQuery) == 0)
            {
                $this->Serie->load("c", " WHERE bd_serie.nom like '" . Db_Escape_String($term) . "%' ".$addfilter ." group by id_serie");

            }
        }

        if ($mode == 0) {
            foreach ($this->Serie->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM_SERIE,
                            'id' => $obj->ID_SERIE
                );
            }
            $this->view->set_var('json', json_encode($arr ?? []));
        } else {
            $this->view->set_var('json', json_encode($this->Serie->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Useralbum(){
        $id_tome = getValInteger("id_tome",0);
        $id_edition = getValInteger("id_edition",0);
        $mode = getValInteger("mode",0);
        $flg_achat = getVal("flg_achat","N");
        $length = getValInteger("length",10);
        $page = getValInteger("page",1);
        $id_serie = getValInteger("id_serie",0);
        $flg_pret = getVal("flg_pret","");
        $flg_exclu  = getVal("flg_exclu","N");
        $flg_num = getVal("flg_num", "");
        $flg_lu = getVal("flg_lu", "");
        $flg_cadeau = getVal("flg_cadeau", "");
        $term = getVal("term", "");
        $sort = getVal("sort","default");
        $origin = getVal("origin",""); // manga / comics / BD
        $from = getValInteger("from",0);
        $this->loadModel("Useralbum");
        
        if ($length > 30000) $length = 30000;
        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            if ($mode ) {
                $limit = " limit ".($from+($page - 1)*$length).", ".$length;
                $where = " where ua.user_id = ".intval($_SESSION['userConnect']->user_id)." and flg_achat = '". Db_Escape_String($flg_achat)."' ";
                if ($id_serie)  $where.= " AND s.id_serie = ".$id_serie;
                
                if ($origin != "") {
                    $where.= " AND g.ORIGINE = '".Db_Escape_String($origin)."'";
                }
                if ($flg_pret) {
                    $where.= " AND flg_pret = '".Db_Escape_String($flg_pret)."'";
                }
                if ($flg_num) {
                    $where.= " AND FLG_NUM = '".Db_Escape_String($flg_num)."'";
                }
                if ($flg_lu) {
                    $where.= " AND FLG_LU = '".Db_Escape_String($flg_lu)."'";
                }
                if ($flg_cadeau) {
                    $where.= " AND flg_cadeau = '".Db_Escape_String($flg_cadeau)."'";
                }
                if ($term) {
                    $where .= " AND s.NOM like '%".Db_Escape_String($term)."%'";
                }
                if ($sort == "date") {
                    $orderby = " ORDER BY DATE_AJOUT DESC";
                } else {
                    $orderby = " ORDER BY NOM_SERIE";
                }
                
                $dbs_album = $this->Useralbum->load("c",$where.$orderby. $limit);
                $infoalbum["data"] = $dbs_album->a_dataQuery;
                $nbr = Db_CountRow($this->Useralbum->select().$where);
                if ($flg_exclu == "O") {
                    $this->loadModel("Users_exclusions");
                    $where = " where users_exclusions.user_id = ".intval($_SESSION['userConnect']->user_id);
                    if ($id_serie) { 
                        $where.= " AND bd_serie.id_serie = ".$id_serie;
                        
                    }
                    $orderby = " ORDER BY NOM_SERIE";
                    $dbs_exclu = $this->Users_exclusions->load("c",$where.$orderby);
                    $infoalbum["exclu"] = $dbs_exclu->a_dataQuery;
                
                }
                $infoalbum["nbTotal"] = $nbr;
            }
            else {
                

                if ($id_edition) {
                    // selection par edition
                    $this->Useralbum->load("c"," WHERE ua.user_id = ".intval($_SESSION['userConnect']->user_id). " AND ua.id_edition = ".$id_edition);
                    $this->loadModel("Edition");
                    $this->Edition->set_dataPaste(array(
                        "ID_EDITION" => $id_edition
                    ));
                    $dbs_album = $this->Edition->load();
                    $id_serie = $this->Edition->ID_SERIE;
                    $id_tome = $this->Edition->ID_TOME;
                 } 
                 else {
                    // selection par id_tome
                    $this->Useralbum->load("c"," WHERE ua.user_id = ".intval($_SESSION['userConnect']->user_id). " AND bd_tome.id_tome = ".$id_tome);
                    $this->loadModel("Tome");
                    $this->Tome->set_dataPaste(array(
                            "ID_TOME" => $id_tome
                        ));
                    $dbs_album = $this->Tome->load();
                    $id_serie = $this->Tome->ID_SERIE ?? 0;
                 }
                $this->loadModel("Users_exclusions");
                // on check si l'album ou la série est exclue
                $exclu = $this->Users_exclusions->isExclu(intval($_SESSION['userConnect']->user_id), $id_tome, $id_serie);
                $nbserie = $this->Useralbum->isSerieInCollection($id_serie,intval($_SESSION['userConnect']->user_id));
                $infoalbum["data"] = $this->Useralbum->dbSelect->a_dataQuery;
                $infoalbum["nbAlbumSerie"] = $nbserie;
                $infoalbum["exclu"] = (count($this->Useralbum->dbSelect->a_dataQuery) > 0 ? 0 : $exclu);
            }
            $this->view->set_var('json', json_encode($infoalbum));
              

        }

        $this->view->layout = "ajax";
        $this->view->render();


    }
    
    private function Userserie () {
        $length = getValInteger("length",10);
        $page = getValInteger("page",1);
        $from = getValInteger("from",0);
        $term = getVal("term",""); // filtre de recherche 
        $origin = getVal("origin",""); // manga / comics / BD
        $complet = getVal("complet","");
        
        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            $this->loadModel("Useralbum");
            $this->loadModel("Users_exclusions");
            $user_id = intval($_SESSION['userConnect']->user_id);
            $dbs_serie = $this->Useralbum->getUserSerie($user_id, $page, $length,$term,$origin,$auteur="", $liste="", $complet, $from);
            $a_obj = array();
            foreach ($dbs_serie as $serie) {
                $a_obj[] = $serie;
            }
           // $listSerie = $this->Users_exclusions->getListSerieToComplete($user_id,!$flg_achat);
            $stat = $this->Useralbum->getStatistiques($user_id,"album","",$origin,"",$term);
             $nbr = $stat["nbseries"];
            $this->view->set_var('json', json_encode(array (
                "nbserie" => $nbr,
                "data" => $a_obj 
            )));
        }
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    private function Useractu() {
        if (User::minAccesslevel(2)) {
            $this->loadModel("Tome");
            $nb_mois = getValInteger("nb_mois",1);
            $page = getValInteger("page",1);
            $mode = getValInteger("mode",1);
            // creation du filtre par défaut sur les série
            // mode : 1 => serie, 2 => auteur favoris, 3 => coffrets
            $dbs_tome = $this->Tome->getUserActualite($mode, $nb_mois,$page);

            $this->view->set_var('json', json_encode(array(
                "data" => $dbs_tome->a_dataQuery,
                "page" => $page,
                "nb_mois" => $nb_mois,
                "mode" => $mode
                )));
        }
         $this->view->layout = "ajax";
        $this->view->render();
    }
    
    private function Albummanquant () {
        // liste les albums manquants pour une série si id_serie renseigné, renvoie les séries à compléter sinon
         if (User::minAccesslevel(2)) {
             $id_serie = getValInteger("id_serie",0);
             $user_id = intval($_SESSION['userConnect']->user_id);
             $flg_achat = getValInteger("includeAchat",1);
             $length = getValInteger("length",0);
             $page = getValInteger("page",1);
             $from = getValInteger("from",0);
             $mode = getVal("mode","");
             if ($id_serie OR $mode == "all") {
                  $this->loadModel("Useralbum");
                  $this->loadModel("Tome");
                  if ($id_serie == 0) {
                      $order = "ORDER BY en.DTE_PARUTION desc";
                  } else {
                      $order = "";
                  }
                  $nbalbum = $this->Useralbum->isSerieInCollection($id_serie,$_SESSION['userConnect']->user_id);
                  $dbs_tomeComplete  = $this->Tome->getListAlbumToComplete($_SESSION['userConnect']->user_id,$id_serie , !$flg_achat, $page, $length , $order);
                  if ($id_serie) {
                      $nbmanquant =  count($dbs_tomeComplete->a_dataQuery); 
                    
                  } else {
                      $nbmanquant = $this->Tome->getCountAlbumToComplete($user_id);
                  }
                  
                  $this->view->set_var('json', json_encode(array(
                     "nbalbum" => $nbalbum,
                     "nbmanquant"=> $nbmanquant,
                     "data" => $dbs_tomeComplete->a_dataQuery
                      
                  )));
             } else  {
                 // récupérer la liste des séries à compléter
                 $this->loadModel("Users_exclusions"); 
                 $listSerie = $this->Users_exclusions->getListSerieToComplete($user_id, $flg_achat);
                 $this->view->set_var('json', json_encode(array(
                     "data" => $listSerie)));
             } 
             
         }
          $this->view->layout = "ajax";
        $this->view->render();
    }
    
    private function Actu() {
         $this->loadModel('Actus');
         $filter_origine = getVal("origine","BD");
         $mode = getValInteger("mode", 1);
         $nbmois = getValInteger("period", 2);
         $page = getValInteger("page", 1);
         $length = getValInteger("length", 10);
         $limit = " limit ".(($page - 1)*$length).", ".$length; 
        
          $this->loadModel('Edition');
         if ($mode == 1) {
             
         
            // tendances
           $dbs_tome = $this->Edition->load('c', "
               WHERE bd_edition.`DTE_PARUTION`> date_add(now(),INTERVAL - 6 MONTH)
                        and g.origine = '".$filter_origine ."' and bd_edition.PROP_STATUS=1
                   ORDER BY (15.0*bd_edition_stat.NBR_USER_ID_EDITION /(DATEDIFF(now(),DTE_PARUTION) +1))  DESC ".$limit);

        
        } else {
            // parutions 
           if ($nbmois > 0) {
               $filterdate = "bd_edition.`DTE_PARUTION`> date_add(now(),INTERVAL - ". $nbmois ." MONTH) AND bd_edition.`DTE_PARUTION` < now() ";
           } else {
               $filterdate = "bd_edition.`DTE_PARUTION`< date_add(now(),INTERVAL  ". abs($nbmois) ." MONTH) AND bd_edition.`DTE_PARUTION` > now() ";
           }
           $dbs_tome = $this->Edition->load('c', "
               WHERE ". $filterdate ."
                        and g.origine = '".$filter_origine ."' and bd_edition.PROP_STATUS=1
                   ORDER BY DTE_PARUTION  ". (($nbmois > 0 ) ? " DESC " : " ASC ").$limit);

        }
        
        $this->view->set_var('json', json_encode($dbs_tome->a_dataQuery));
        $this->view->layout = "ajax";
        $this->view->render();
         
    }
    
    private function Edition () {
        $id_tome = getValInteger("id_tome",0);
        $this->loadModel('Edition');

        $dbs_edition = $this->Edition->load("c", "where PROP_STATUS not in ('0','99','98') and bd_tome.id_tome =" . $id_tome ." ORDER BY DATE_PARUTION_EDITION");
          $this->view->set_var('json', json_encode($dbs_edition->a_dataQuery));
         $this->view->layout = "ajax";
           $this->view->render();
         
    }
    
    private function Userstat () {
        
        $orig= getVal("origine","");
        if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
        
            $this->loadModel("Useralbum");
        
            $stat = $this->Useralbum->getStatistiques($user_id, $stat="all", $auteur="",$origin=$orig);
            $this->view->set_var('json',json_encode($stat));
        }
       
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    public function isExclu () {
        $id_tome = getValInteger("id_tome");
        $id_serie = getValInteger("id_serie");
        if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
        
            $this->loadModel("Users_exclusions");
        
            $stat = $this->Users_exclusions->isExclu($user_id, $id_tome, $id_serie);
            $this->view->set_var('json',json_encode($stat));
        }
         $this->view->layout = "ajax";
        $this->view->render();
    }
    
    public function listExclusion () {
         if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
        
            $this->loadModel("Users_exclusions");
        
            $stat = $this->Users_exclusions->getListSerieExcluSource($user_id);
            $this->view->set_var('json',json_encode($stat));
        }
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    public function listTomesExclus () {
        $id_serie = getValInteger("id_serie");
        if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
        
            $this->loadModel("Users_exclusions");
        
            $stat = $this->Users_exclusions->getListTomesExclus($user_id, $id_serie);
            $this->view->set_var('json',json_encode($stat));
        }
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    private function Userpref() {
        if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
            $this->loadModel("User");
            $this->User->set_dataPaste(array("user_id" =>$user_id ));
            $user = $this->User->load();
            
            $a_result = array(
            "OpenCollec" => $this->User->OPEN_COLLEC == 'Y',
            "ExplicitContent" => $this->User->EXPLICIT_CONTENT == "1"

            );
          
             $this->view->set_var('json',json_encode($a_result));
        }
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
    private function CollectionStat () {
        // by  = type de comptage genre, auteur, éditeur...
        $by = getVal("by","genre");
         if (User::minAccesslevel(2)) {
            $user_id = intval($_SESSION['userConnect']->user_id);
            $this->loadModel("Useralbum");
            
            switch ($by) {
                case "editeur" :
                     $stat = $this->Useralbum->getStatByEditeur($user_id);
                    break;
                case "auteur": 
                     $stat = $this->Useralbum->getAuteurFavoris($user_id);
                    break;
                case "note":
                    $stat = $this->Useralbum->getStatByNotes($user_id);
                    break;
                case "genre":
                default: 
                     $stat = $this->Useralbum->getStatByGenre($user_id);
                    
            }
           
            
            $this->view->set_var('json',json_encode($stat));
         }
        $this->view->layout = "ajax";
        $this->view->render();
    }
}

?>
