<?php

/**
 *
 * @author laurent
 *
 */
class Browser extends Bdo_Controller
{

    public $maxRows = 25;

    public $startRow = 0;

    public $pageNum = 0;

    public $pagetitle = "BDovore.com - Bandes dessinées - l'annuaire de toutes les bds par auteur, par série ou par éditeur. BD, Mangas, Comics";

    public $rb_browse = "ser";

    public $keyword = "";

    public $lev_id = "";
    // TODO: $this->lev2_id est utilisé, selon les endroits, comme un id (donc un entier),
    // comme un string, ou comme un mix genre lev2_id=5269#2L5269 (dans les URL).
    // Il doit y avoir une confusion quelque part à régler.
    public $lev2_id = "";

    public $let = "";

    public $url_edit = array(
                "ALBUM" => "admin/editalbum?alb_id=",
                "SERIE" =>  "admin/editserie?serie_id=",
                "GENRE" => "admin/editgenre?genre_id=",
                "COLLECTION" => "admin/editcollection?collec_id=",
                "AUTEUR" => "admin/editauteur?auteur_id=",
                "EDITEUR" => "admin/editediteur?editeur_id="
        );
    

    public $a_searchType = array(
            'ser' => 'Série',
            'aut' => 'Auteur',
            'edit' => 'Editeur'
    );

    /**
     */
    public function __construct ()
    {
        parent::__construct();

        $this->pageNum = getValInteger('pageNum', 0);
        $this->rb_browse = getVal('rb_browse', 'ser');
        $this->lev_id = getValInteger('lev_id',0);//comme ça il est 'clean' pour toutes les utilisations
        $this->lev2_id = getVal('lev2_id');//à vérifier cf. 'TODO' ci-dessus
        $this->let = getVal('let');
        $this->a_idGenre = getValInteger('a_idGenre',null);//NB a_idGenre est habituellement un array

        $this->startRow = $this->pageNum * $this->maxRows;

        if (! isset($this->a_searchType[$this->rb_browse])) $this->rb_browse = "ser";

      
    }

    /**
     */
    public function Index ($rb_browse = 'ser')
    {
        // liste des genres

        $this->loadModel('Genre');
        $search = postVal("search","");
        if ($search <> "") {
            $this->let = $search;
        }
        $dbs_genre = $this->Genre->all();

        $this->view->set_var(array(
                "TYPBROWSE" => $this->rb_browse,
                "a_searchType" => $this->a_searchType,
                "VALUELET" => $this->let,
                "dbs_genre" => $dbs_genre,
                "PAGETITLE" => $this->pagetitle.' '.$this->a_searchType[$this->rb_browse],
                "PAGEKEYWORD" => $this->keyword
        ));
        $this->view->render();
    }

    /**
     */
    public function Search ()
    {
        $query_where = "";
        if (strlen($this->let) > 3) {
            $pre_filtre = '%';
        } else 
        {
            $pre_filtre = "";
        }
          $query_where = " WHERE 1 ";
        if ($this->rb_browse == 'ser' || ! $this->rb_browse) {
            $this->loadModel("Serie");
            if ($this->let) {
                $query_where .= " AND nom like '" .$pre_filtre. PMA_sqlAddslashes($this->let, true) . "%' ";
            }
            if ($this->a_idGenre) {
                $query_where .= " AND ID_GENRE IN (".implode(',',$this->a_idGenre). ")";
            }
            $query_order = " ORDER BY nom ASC ";
            $this->Serie->calcFoundRow = true;
            $this->Serie->selectType = "browse";
            $dbsData = $this->Serie->load("c", $query_where. " GROUP BY ID_SERIE ". $query_order ." LIMIT " . intval($this->startRow) . "," . intval($this->maxRows));
        }
        elseif ($this->rb_browse == 'aut') {
            $this->loadModel("Auteur");
            if ($this->let) {
                $query_where .= " AND pseudo like '".$pre_filtre. PMA_sqlAddslashes($this->let, true) . "%'";
            }

            if ($this->a_idGenre) {

                $query_where .= " AND ID_AUTEUR IN (
                        SELECT DISTINCT bd_tome.ID_SCENAR as ID_AUTEUR FROM bd_tome INNER JOIN bd_serie USING(ID_SERIE) WHERE bd_serie.ID_GENRE IN (".implode(',',$this->a_idGenre). ")
                        UNION
                        SELECT DISTINCT bd_tome.ID_DESSIN as ID_AUTEUR FROM bd_tome INNER JOIN bd_serie USING(ID_SERIE) WHERE bd_serie.ID_GENRE IN (".implode(',',$this->a_idGenre). ")
                        UNION
                        SELECT DISTINCT bd_tome.ID_COLOR as ID_AUTEUR FROM bd_tome INNER JOIN bd_serie USING(ID_SERIE) WHERE bd_serie.ID_GENRE IN (".implode(',',$this->a_idGenre). ")
                                )";

            }
            $query_order = " ORDER BY PSEUDO ASC ";
             $this->Auteur->calcFoundRow = true;
            $dbsData = $this->Auteur->load("c", $query_where. $query_order." LIMIT " . intval($this->startRow) . "," . intval($this->maxRows));
        }
        elseif ($this->rb_browse == 'edit') {
           $this->loadModel("Editeur");
            if ($this->let) {
                $query_where .= " AND NOM like '".$pre_filtre. PMA_sqlAddslashes($this->let, true) . "%'";
            }
            if ($this->a_idGenre) {

                $query_where .= " AND ID_EDITEUR IN (

                        SELECT DISTINCT ID_EDITEUR FROM bd_edition_stat WHERE ID_GENRE IN (".implode(',',$this->a_idGenre). ")

                                )";

            }
            $query_order = " ORDER BY NOM ASC ";
             $this->Editeur->calcFoundRow = true;
            $dbsData = $this->Editeur->load("c", $query_where. $query_order." LIMIT " . intval($this->startRow) . "," . intval($this->maxRows));
      
        }

        $totalRows = getValInteger('totalRows',-1);

        if ($totalRows < 0) {
            $totalRows = $dbsData->nbLineTotal;
        }

        $totalPages = ceil($totalRows / $this->maxRows) - 1;


        // on déclare les blocks contenus sur la feuille
        // //$t->set_block('tpBody', 'DataBlock', 'DBlock');

        $queryString = "";
        if (! empty($_SERVER['QUERY_STRING'])) {
            $params = explode("&", $_SERVER['QUERY_STRING']);
            $newParams = array();
            foreach ($params as $param) {
                if (stristr($param, "pageNum") == false && stristr($param, "totalRows") == false && stristr($param, "lev_id") == false && stristr($param, "lev2_id") == false && stristr($param, "rb_browse") == false) {
                    array_push($newParams, $param);
                }
            }
            if (count($newParams) != 0) {
                $queryString = "&" . implode("&", $newParams);
            }
        }
        $queryString = sprintf("&totalRows=%d%s", $totalRows, $queryString);
        
       
        if (! $this->rb_browse or $this->rb_browse == 'ser') {
            $this->view->set_var("TYPBROWSE", "ser");
            $url_edit = BDO_URL.$this->url_edit["SERIE"];

        }
        if ($this->rb_browse == 'aut') {
            $this->view->set_var("AUTCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "aut");
            $url_edit = BDO_URL.$this->url_edit["AUTEUR"];
        }
        if ($this->rb_browse == 'genr') {
            $this->view->set_var("GENRCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "genr");
        }
        if ($this->rb_browse == 'edit') {
            $this->view->set_var("EDITCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "edit");
             $url_edit = BDO_URL.$this->url_edit["EDITEUR"];
        }

        $query_string = "";
        if ($this->rb_browse) {
            $query_string .= "rb_browse=" . $this->rb_browse;
        }
        else {
            $this->rb_browse = "ser";
            $query_string .= "rb_browse=" . $this->rb_browse;
        }
        $queryString .= "&" . $query_string;

        $a_row = array();
        $img_edit = "edit.gif";
        if ($this->rb_browse != "ser") {
            foreach($dbsData->a_dataQuery as $data) {
                if ($this->rb_browse == "aut") {
                    // class pour le bouton d'édition en fonction de présence d'une bio ou non
                    if (strlen( $row["COMMENT"] ?? "") > 0 ) {
                        $img_edit ="aut_edit.jpg";
                    }  else {
                        $img_edit = "edit.gif";
                    }
                    $id = $data->ID_AUTEUR;
                    $nom = $data->PSEUDO;
                } else {
                    $id = $data->ID_EDITEUR;
                    $nom = $data->NOM;
                } 
                
                $a_row[] = array(
                        "WSPACER" => "0px",
                        "HSPACER" => "0px",
                        "IMGNAVIG" => "aro_3_1.gif",
                        "URLEVEL" => BDO_URL . "browser/xhr" . "?lev_id=" . $id . $queryString,
                        "NAMELEVEL" => htmlspecialchars($nom),
                        "ACTLEVEL" => "",
                        "LEVSIGN" => "1L" . $id,
                        "URLEDIT" => (User::minAccesslevel(1)) ? "<a href='".$url_edit.$id."'".' class="fancybox fancybox.iframe {width:700,height:600} " ><img src="' . BDO_URL_IMAGE . $img_edit.'" border=0></a>' : ""
                );
                $this->keyword .= htmlspecialchars($nom) . ",";
               
            }
        }
        else {
            // browse par série : seulement 2 niveaux
            foreach($dbsData->a_dataQuery as $data) {
                $id = $data->ID_SERIE;
                    $nom = $data->NOM_SERIE;
                $a_row[] = array(
                        "WSPACER" => "0px",
                        "HSPACER" => "0px",
                        "IMGNAVIG" => "aro_3_1.gif",
                        "URLEVEL" => BDO_URL . "browser/xhr_level2" . "?lev_id=" . $id . $queryString,
                        "NAMELEVEL" => htmlspecialchars(stripslashes($nom)),
                        "ACTLEVEL" => "",
                        "LEVSIGN" => "1L" .$id,
                   "URLEDIT" => (User::minAccesslevel(1)) ? "<a href='".$url_edit.$id."'".' class="fancybox fancybox.iframe {width:700,height:600}" ><img src="' . BDO_URL_IMAGE . 'edit.gif" border=0></a>' : ""
                );
                $this->keyword .= htmlspecialchars($nom) . ",";
            }
        }
        $this->view->set_var(array(
                "a_row" => $a_row
        ));
        if (! empty($totalRows) and ($totalRows <= $this->maxRows)) {
            $this->view->set_var(array(
                    "URLPREVPAGE" => '',
                    "URLNEXTPAGE" => '',
                    "TOTALROW" => ''
            ));
        }
        else if (! empty($totalRows)) {
            $totalrow = 'lignes de ' . ($this->startRow + 1) . ' à ';
            $totalrow .= ($this->startRow + $this->maxRows > $totalRows) ? $totalRows : ($this->startRow + $this->maxRows);
            $totalrow .= ' ( sur ' . $totalRows . ' lignes )';

            if ($this->pageNum > 0) $this->view->set_var("URLPREVPAGE", '<a href="javascript:document.browser.pageNum.value=\'' . max(0, $this->pageNum - 1) . '\';go1()">Pr&eacute;c&eacute;dent</a>');
            else
                $this->view->set_var("URLPREVPAGE", '');

            if ($this->pageNum < $totalPages) $this->view->set_var("URLNEXTPAGE", '<a href="javascript:document.browser.pageNum.value=\'' . min($totalPages, $this->pageNum + 1) . '\';go1()">Suivant</a>');
            else
                $this->view->set_var("URLNEXTPAGE", '');
            $this->view->set_var("TOTALROW", (empty($totalRows) ? 'Aucune ligne de résultat !' : $totalrow));
        }
        else {
            $this->view->set_var("URLPREVPAGE", '');
            $this->view->set_var("URLNEXTPAGE", '');
            $this->view->set_var("TOTALROW", 'Aucune ligne de résultat !');
        }

        $this->view->set_var(array(
                "TITRESEARCH" => "<b>Résultat pour '<i>" . htmlspecialchars($this->let) . "</i>...'</b>"
        ));
        $this->view->layout = "ajax";
        $this->view->render();
    }

   

    public function Xhr ()
    {
        if ($this->rb_browse == 'aut') {
            $this->loadModel("Serie");
            $where = " WHERE
            
                (bd_tome.id_dessin = " . $this->lev_id . "
                or bd_tome.id_scenar = " . $this->lev_id . "
                or bd_tome.id_color = " . $this->lev_id . "
                or bd_tome.id_scenar_alt = " . $this->lev_id . "
                or bd_tome.id_dessin_alt = " . $this->lev_id . "
                or bd_tome.id_color_alt = " . $this->lev_id . ")
                ".($this->a_idGenre ? " AND bd_serie.ID_GENRE IN (".implode(',',$this->a_idGenre). ")" : "")."
            group by ID_SERIE order by NOM_SERIE  ";
            $this->Serie->calcFoundRow = true;
            $dbsData = $this->Serie->load("c",$where);

        }
        elseif ($this->rb_browse == 'edit') {
             $this->loadModel("Collection"); 
            $where = " WHERE bd_editeur.id_editeur =" . $this->lev_id . "             
            order by `bd_collection`.`NOM`
            ";
             $this->Collection->calcFoundRow = true;
             $dbsData = $this->Collection->load("c",$where);
        }
      
        $totalRows = $dbsData->nbLineTotal;

        $a_row = array();
        foreach ($dbsData->a_dataQuery as $row) {
            if ($this->rb_browse == "aut") {
                $id = $row->ID_SERIE;
                $name = $row->NOM_SERIE;
                        
            } else {
                $id = $row->ID_COLLECTION;
                $name = $row->NOM;
            }
            $a_row[] = array(
                    "WSPACER" => "16px",
                    "HSPACER" => "1px",
                    "IMGNAVIG" => "aro_3_1.gif",
                    "URLEVEL" => "browser/xhr_level2" . "?rb_browse=" . $this->rb_browse . "&lev_id=" . $this->lev_id . "&lev2_id=" . $id,
                    "NAMELEVEL" => htmlspecialchars(stripslashes($name)),
                    "ACTLEVEL" => "",
                    "LEVSIGN" => "1L" . $this->lev_id . "_2L" . $id,
            );
        }
        $this->view->set_var("a_row", $a_row);

        if (! empty($totalRows) and ($totalRows <= $this->maxRows)) {
            $this->view->set_var("URLPREVPAGE", '');
            $this->view->set_var("URLNEXTPAGE", '');
            $this->view->set_var("TOTALROW", '');
        }
        else if (! empty($totalRows)) {
            $totalrowhtml = 'lignes de ' . ($this->startRow + 1) . ' à ';
            $totalrowhtml .= ($this->startRow + $this->maxRows > $totalRows) ? $totalRows : ($this->startRow + $this->maxRows);
            $totalrowhtml .= ' ( sur ' . $totalRows . ' lignes )';

            $totalPages = ceil($totalRows / $this->maxRows) - 1;
            if ($this->pageNum > 0) {
                $jvs = '$.get("'.BDO_URL.'browser/xhr?rb_browse=' . $this->rb_browse . '&lev_id=' . $this->lev_id . '&pageNum=' . max(0, $this->pageNum - 1) . '",

                         function(data) {$("#onglet_div_xhr_1L' . $this->lev_id . '").html(data);});';
                $url = '<a href="#" onClick=\''.$jvs.';return false\'>Pr&eacute;c&eacute;dent</a>';

                $this->view->set_var("URLPREVPAGE", $url);

            }
            else {

                $this->view->set_var("URLPREVPAGE", '');
            }
             if ($this->pageNum < $totalPages) {

                $jvs = '$.get("'.BDO_URL.'browser/xhr?rb_browse=' . $this->rb_browse . '&lev_id=' . $this->lev_id . '&pageNum=' . min($totalPages, $this->pageNum + 1) . '",

                         function(data) {$("#onglet_div_xhr_1L' . $this->lev_id . '").html(data);});';

                $url = '<a href="#" onClick=\''.$jvs.';return false\'>Suivant</a>';

                $this->view->set_var("URLNEXTPAGE", $url);

            }

            else {

             $this->view->set_var("URLNEXTPAGE", '');


            $this->view->set_var("URLSITE", BDO_URL);
            $this->view->set_var("URLSITEIMAGE", BDO_URL_IMAGE);
            $this->view->set_var("URLSITEFORUM", BDO_URL_FORUM);
            $this->view->set_var("TOTALROW", (empty($totalRows) ? 'Aucune ligne de résultat !' : $totalrowhtml));
            }
        }
        else {
            $this->view->set_var("URLPREVPAGE", '');
            $this->view->set_var("URLNEXTPAGE", '');
            $this->view->set_var("TOTALROW", 'Aucune ligne de résultat !');
        }

        $this->view->layout = "ajax";
        $this->view->render();
    }

    public function Xhr_Level2 ()
    {
        // defintion des variables
        $a_serie = explode('#', $this->lev2_id);
        $this->lev2_id = $a_serie[0];
       if ($this->rb_browse == 'edit') {
           $this->loadModel("Serie");
            $where = "
            where
                bd_edition.id_editeur=" . $this->lev_id . "
                and bd_edition.id_collection=" . intval($this->lev2_id) . "
                ".($this->a_idGenre ? " AND bd_serie.ID_GENRE IN (".implode(',',$this->a_idGenre). ")" : "")."
            group by bd_serie.ID_SERIE
            order by bd_serie.nom
            ";
            $this->Serie->calcFoundRow = true;
            $dbsData = $this->Serie->load("c",$where . " LIMIT " . intval($this->startRow) . "," . intval($this->maxRows) );
          
        }        
        else {
            // browse par série ou auteur : seulement 2 niveaux
            // aJOUT DE LA PREmière ligne Fiche Série
            $this->view->set_var(
                    array(
                            "FICHESERIE" => $this->lev_id,
                    ));
            $this->loadModel("Tome");
            $this->Tome->calcFoundRow = true;
            $where = "
            where
            bd_tome.id_serie = ". ($this->rb_browse == "aut" ?$this->lev2_id : $this->lev_id)."
            order by num_tome, TITRE
            ";
            $dbsData = $this->Tome->load("c",$where . " LIMIT " . intval($this->startRow) . "," . intval($this->maxRows));
           

           
        }
         $totalRows = $dbsData->nbLineTotal;
         $a_row = array();
            foreach($dbsData->a_dataQuery as $row) {
                $a_row[] = $row;
            }
            $this->view->set_var("a_row", $a_row);

        if (! empty($totalRows) and ($totalRows <= $this->maxRows)) {
            $this->view->set_var("URLPREVPAGE", '');
            $this->view->set_var("URLNEXTPAGE", '');
            $this->view->set_var("TOTALROW", '');
        }
        else if (! empty($totalRows)) {
            $totalrowhtml = 'lignes de ' . ($this->startRow + 1) . ' à ';
            $totalrowhtml .= ($this->startRow + $this->maxRows > $totalRows) ? $totalRows : ($this->startRow + $this->maxRows);
            $totalrowhtml .= ' ( sur ' . $totalRows . ' lignes )';

            $totalPages = ceil($totalRows / $this->maxRows) - 1;

            if ($this->pageNum > 0) {

                $jvs = '$.get("'.BDO_URL.'browser/xhr_level2?rb_browse=' . $this->rb_browse . '&lev_id=' . $this->lev_id . '&lev2_id=' . $this->lev2_id . '&pageNum=' . max(0, $this->pageNum - 1) .'",
                         function(data) {$("#onglet_div_xhr_1L' . $this->lev_id . (($this->rb_browse != 'ser') ? ('_2L' . $this->lev2_id) : '') . '").html(data);});';
                $url = '<a href="#" onClick=\''.$jvs.';return false\'>Pr&eacute;c&eacute;dent</a>';
                $this->view->set_var("URLPREVPAGE", $url);
            }
            else
                $this->view->set_var("URLPREVPAGE", '');

            if ($this->pageNum < $totalPages) {
                $jvs = '$.get("'.BDO_URL.'browser/xhr_level2?rb_browse=' . $this->rb_browse . '&lev_id=' . $this->lev_id . '&lev2_id=' . $this->lev2_id . '&pageNum=' . min($totalPages, $this->pageNum + 1) .'",

                         function(data) {$("#onglet_div_xhr_1L' . $this->lev_id . (($this->rb_browse != 'ser') ? ('_2L' . $this->lev2_id) : '') . '").html(data);});';

                $url = '<a href="#" onClick=\''.$jvs.';return false\'>Suivant</a>';

                $this->view->set_var("URLNEXTPAGE", $url);
            }
            else
                $this->view->set_var("URLNEXTPAGE", '');

            $this->view->set_var("TOTALROW", $totalrowhtml);
        }
        else {
            $this->view->set_var("URLPREVPAGE", '');
            $this->view->set_var("URLNEXTPAGE", '');
            $this->view->set_var("TOTALROW", 'Aucune ligne de résultat !');
        }
        $this->view->layout = "ajax";
        $this->view->render();
    }
    
   
}
