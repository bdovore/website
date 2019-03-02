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
            $where = " WHERE PSEUDO like '%" . Db_Escape_String($term) . "%'";
            $this->Auteur->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Auteur->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->PSEUDO,
                            'id' => $obj->ID_AUTEUR
                );
            }
            $this->view->set_var('json', json_encode($arr));
        } else {
            $this->view->set_var('json', json_encode($this->Auteur->dbSelect->a_dataQuery));
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    private function Album() {
        $ID_TOME = getValInteger('id_tome', 0);
        $id_edition = getValInteger('id_edition', 0);
        $isbn = getVal('ISBN', '');
        $id_serie = getValInteger("id_serie",0);
        $ean = getVal('EAN', '');
        $mode = getValInteger("mode", 0);
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

                $this->Edition->load(c, "WHERE  " . $filter);

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
            $where = "";
            if ($id_serie) {
                $where = " WHERE s.id_serie = $id_serie"; 
            } else {
                $where = " WHERE bd_tome.TITRE like '" . Db_Escape_String($term) . "%' limit 0,10";
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
            $where = " WHERE LIBELLE like '%" . Db_Escape_String($term) . "%'";
            $this->Genre->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Genre->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->LIBELLE,
                            'id' => $obj->ID_GENRE
                );
            }
            $this->view->set_var('json', json_encode($arr));
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
            $where = " WHERE NOM like '%" . Db_Escape_String($term) . "%'";

            $this->Editeur->load("c", $where);
        }
        if ($mode == 0) {
            foreach ($this->Editeur->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM,
                            'id' => $obj->ID_EDITEUR
                );
            }
            $this->view->set_var('json', json_encode($arr));
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
            if ($term <> "")
                $where .= " AND bd_collection.NOM like '%" . Db_Escape_String($term) . "%'";

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
            $this->view->set_var('json', json_encode($arr));
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
            $this->Serie->load("c", " WHERE bd_serie.nom like '" . Db_Escape_String($term) . "%' group by id_serie");
        }

        if ($mode == 0) {
            foreach ($this->Serie->dbSelect->a_dataQuery as $obj) {

                $arr[] = (object) array(
                            'label' => $obj->NOM_SERIE,
                            'id' => $obj->ID_SERIE
                );
            }
            $this->view->set_var('json', json_encode($arr));
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
        $this->loadModel("Useralbum");
        
        if ($length > 100) $length = 100;
        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            if ($mode ) {
                $limit = " limit ".(($page - 1)*$length).", ".$length;
                $where = " where ua.user_id = ".intval($_SESSION['userConnect']->user_id)." and flg_achat = '". Db_Escape_String($flg_achat)."' ";
                if ($id_serie)  $where.= " AND s.id_serie = ".$id_serie;
                if ($flg_pret) {
                    $where.= " AND flg_pret = '".Db_Escape_String($flg_pret)."'";
                }
                $orderby = " ORDER BY NOM_SERIE";
                $dbs_album = $this->Useralbum->load("c",$where.$orderby. $limit);
                $infoalbum["data"] = $dbs_album->a_dataQuery;
                $nbr = Db_CountRow($this->Useralbum->select().$where);
                
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
                    $id_serie = $this->Tome->ID_SERIE;
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
        $term = getVal("term",""); // filtre de recherche 
        $origin = getVal("origin",""); // manga / comics / BD
        
        if (Bdo_Cfg::user()->minAccesslevel(2)) {
            $this->loadModel("Useralbum");
            $this->loadModel("Users_exclusions");
            $user_id = intval($_SESSION['userConnect']->user_id);
            $dbs_serie = $this->Useralbum->getUserSerie($user_id, $page, $length,$term,$origin,$auteur);
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
             if ($id_serie) {
                  $this->loadModel("Useralbum");
                  $this->loadModel("Tome");
                  $nbalbum = $this->Useralbum->isSerieInCollection($id_serie,$_SESSION['userConnect']->user_id);
                  $dbs_tomeComplete  = $this->Tome->getListAlbumToComplete($_SESSION['userConnect']->user_id,$id_serie , !$flg_achat);
                  $nbmanquant =  count($dbs_tomeComplete->a_dataQuery);
                  $this->view->set_var('json', json_encode(array(
                     "nbalbum" => $nbalbum,
                     "nbmanquant"=> $nbmanquant,
                     "data" => $dbs_tomeComplete->a_dataQuery
                      
                  )));
             } else {
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

}

?>
