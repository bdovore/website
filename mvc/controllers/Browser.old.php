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

    public $pagetitle = "BDovore.com - Bandes dessinées ";

    public $rb_browse = "ser";
    
    public $keyword = "";
    
    public $lev_id = "";

    public $lev2_id = "";

    public $let = "";

    public $a_searchType = array(
            'ser' => 'Par Série',
            'aut' => 'Par Auteur',
            'genr' => 'Par Genre',
            'edit' => 'Par Editeur'
    );

    /**
     */
    public function __construct ()
    {
        parent::__construct();
        
        $this->pageNum = getVal('pageNum', '0');
        $this->rb_browse = getVal('rb_browse', 'ser');
        $this->lev_id = getVal('lev_id');
        $this->lev2_id = getVal('lev2_id');
        $this->let = getVal('let');
        
        $this->startRow = $this->pageNum * $this->maxRows;
        
        if (! isset($this->a_searchType[$this->rb_browse])) $this->rb_browse = "ser";
        
        // URL des pages d'edition
        $url_edit = array(
                "ALBUM" => BDO_URL . "admin/albums?alb_id=%d",
                "SERIE" => BDO_URL . "admin/series?serie_id=%d",
                "GENRE" => BDO_URL . "admin/genres?genre_id=%d",
                "COLLECTION" => BDO_URL . "admin/collections?collec_id=%d",
                "AUTEUR" => BDO_URL . "admin/auteurs?auteur_id=%d",
                "EDITEUR" => BDO_URL . "admin/editeurs?editeur_id=%d"
        );
    }

    /**
     */
    public function Index ($rb_browse = 'ser')
    {
        $this->view->set_var(array(
                "TYPBROWSE" => $this->rb_browse,
                "a_searchType" => $this->a_searchType,
                "VALUELET" => $this->let,
                "PAGETITLE" => $this->pagetitle.' '.$this->a_searchType[$this->rb_browse],
                "PAGEKEYWORD" => $this->keyword
        ));
        $this->view->render();
    }

    /**
     */
    public function Search ()
    {

        if ($this->rb_browse == 'ser' || ! $this->rb_browse) {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_serie ";
            $query_select = "SELECT distinct bd_serie.id_serie id, bd_serie.nom name,
               bd_serie.FLG_FINI as FLG_FINI_SERIE	FROM bd_serie ";
            if ($this->let) {
                $query_where = "WHERE nom like '" . PMA_sqlAddslashes($this->let, true) . "%' ";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY nom ASC ";
            
            // URL album
            $act_url = ' onclick="window.open(\'' . BDO_URL . 'membres/album?id_tome=%d\',\'Album\',\'width=500,height=400,scrollbars=1\');return false;"';
            $url_alb = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["SERIE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'aut') {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_auteur ";
            $query_select = "SELECT ID_AUTEUR id, PSEUDO name FROM bd_auteur ";
            if ($this->let) {
                $query_where = "WHERE pseudo like '" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY PSEUDO ASC ";
            
            // URL album
            $act_url = ' onclick="window.open(' . "'" . BDO_URL . "membres/album?id_tome=%d','Album','width=500,height=400,scrollbars=1')" . '"';
            $url_alb = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["AUTEUR"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'edit') {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_editeur ";
            $query_select = "SELECT ID_EDITEUR id, NOM name FROM bd_editeur ";
            if ($this->let) {
                $query_where = "WHERE NOM like '" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY NOM ASC ";
            
            // URL affichage
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["EDITEUR"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        elseif ($this->rb_browse == 'genr') {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_genre ";
            $query_select = "SELECT ID_GENRE id, LIBELLE name FROM bd_genre ";
            if ($this->let) {
                $query_where = "WHERE LIBELLE like '" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY LIBELLE ASC ";
            
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["GENRE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        // echo $query_limit;
        $query_limit = $query_select . $query_where . $query_order . " LIMIT " . Db_Escape_String($this->startRow) . "," . Db_Escape_String($this->maxRows);
        
        if (isset($_GET['totalRows'])) {
            $totalRows = $_GET['totalRows'];
        }
        else {
            $all = Db_query($query_selectCount . $query_where);
            if ($row = Db_fetch_array($all)) {
                $totalRows = $row['nbr'];
            }
        }
        $totalPages = ceil($totalRows / $this->maxRows) - 1;
        
        $RecAuteur = Db_query($query_limit);
        
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
        }
        if ($this->rb_browse == 'aut') {
            $this->view->set_var("AUTCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "aut");
        }
        if ($this->rb_browse == 'genr') {
            $this->view->set_var("GENRCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "genr");
        }
        if ($this->rb_browse == 'edit') {
            $this->view->set_var("EDITCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "edit");
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
        if ($this->rb_browse != "ser") {
            while ($row = Db_fetch_array($RecAuteur)) {
                $a_row[] = array(
                        "WSPACER" => "0px",
                        "HSPACER" => "0px",
                        "IMGNAVIG" => "aro_3_1.gif",
                        "URLEVEL" => BDO_URL . "browser/xhr" . "?lev_id=" . $row['id'] . $queryString,
                        "NAMELEVEL" => htmlspecialchars($row['name']),
                        "ACTLEVEL" => "",
                        "LEVSIGN" => "1L" . $row['id'],
                        "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev1, $row['id']) : '')
                );
                $this->keyword .= htmlspecialchars($row['name']) . ",";
                // //$t->parse("DBlock", "DataBlock", true);
            }
        }
        else {
            // browse par série : seulement 2 niveaux
            while ($row = Db_fetch_array($RecAuteur)) {
                $a_row[] = array(
                        "WSPACER" => "0px",
                        "HSPACER" => "0px",
                        "IMGNAVIG" => "aro_3_1.gif",
                        "URLEVEL" => BDO_URL . "browser/xhr_level2" . "?lev_id=" . $row['id'] . $queryString,
                        "NAMELEVEL" => htmlspecialchars(stripslashes($row['name'])),
                        "ACTLEVEL" => "",
                        "LEVSIGN" => "1L" . $row['id'],
                        "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev1, $row['id']) : '')
                );
                $this->keyword .= htmlspecialchars($row['name']) . ",";
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
                "TITRESEARCH" => "<b>Commence par '<i>" . htmlspecialchars($this->let) . "</i>...'</b>"
        ));
        $this->view->layout = "ajax";
        $this->view->render();
    }

    /**
     */
    public function Search2 ()
    {

        if ($this->rb_browse == 'ser' || ! $this->rb_browse) {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_serie ";
            $query_select = "SELECT distinct bd_serie.id_serie id, bd_serie.nom name,
               bd_serie.FLG_FINI as FLG_FINI_SERIE	FROM bd_serie ";
            if ($this->let) {
                $query_where = "WHERE nom  NOT like '" . PMA_sqlAddslashes($this->let, true) . "%' AND nom Like'%" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY nom ASC ";
            
            // URL album
            $act_url = ' onclick="window.open(\'' . BDO_URL . 'membres/album?id_tome=%d\',\'Album\',\'width=500,height=400,scrollbars=1\');return false;"';
            $url_alb = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["SERIE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'aut') {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_auteur ";
            $query_select = "SELECT ID_AUTEUR id, PSEUDO name FROM bd_auteur ";
            if ($this->let) {
                $query_where = "WHERE pseudo NOT like '" . PMA_sqlAddslashes($this->let, true) . "%' AND pseudo Like'%" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY PSEUDO ASC ";
            
            // URL album
            $act_url = ' onclick="window.open(' . "'" . BDO_URL . "membres/album?id_tome=%d','Album','width=500,height=400,scrollbars=1')" . '"';
            $url_alb = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["AUTEUR"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'edit') {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_editeur ";
            $query_select = "SELECT ID_EDITEUR id, NOM name FROM bd_editeur ";
            if ($this->let) {
                $query_where = "WHERE NOM NOT like '" . PMA_sqlAddslashes($this->let, true) . "%' AND NOM Like'%" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY NOM ASC ";
            
            // URL affichage
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["EDITEUR"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        elseif ($this->rb_browse == 'genr') {
            $query_selectCount = "SELECT count(1) as nbr FROM bd_genre ";
            $query_select = "SELECT ID_GENRE id, LIBELLE name FROM bd_genre ";
            if ($this->let) {
                $query_where = "WHERE LIBELLE NOT like '" . PMA_sqlAddslashes($this->let, true) . "%' AND LIBELLE Like'%" . PMA_sqlAddslashes($this->let, true) . "%'";
            }
            else {
                $query_where = "";
            }
            $query_order = " ORDER BY LIBELLE ASC ";
            
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev1 = "<a href='" . $url_edit["GENRE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        // echo $query_limit;
        $query_limit = $query_select . $query_where . $query_order . " LIMIT " . Db_Escape_String($this->startRow) . "," . Db_Escape_String($this->maxRows);
        
        if (isset($_GET['totalRows'])) {
            $totalRows = $_GET['totalRows'];
        }
        else {
            $all = Db_query($query_selectCount . $query_where);
            if ($row = Db_fetch_array($all)) {
                $totalRows = $row['nbr'];
            }
        }
        $totalPages = ceil($totalRows / $this->maxRows) - 1;
        
        $RecAuteur = Db_query($query_limit);
        
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
        }
        if ($this->rb_browse == 'aut') {
            $this->view->set_var("AUTCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "aut");
        }
        if ($this->rb_browse == 'genr') {
            $this->view->set_var("GENRCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "genr");
        }
        if ($this->rb_browse == 'edit') {
            $this->view->set_var("EDITCHECK", "checked");
            $this->view->set_var("TYPBROWSE", "edit");
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
        if ($this->rb_browse != "ser") {
            while ($row = Db_fetch_array($RecAuteur)) {
                $a_row[] = array(
                        "WSPACER" => "0px",
                        "HSPACER" => "0px",
                        "IMGNAVIG" => "aro_3_1.gif",
                        "URLEVEL" => BDO_URL . "browser/xhr" . "?lev_id=" . $row['id'] . $queryString,
                        "NAMELEVEL" => htmlspecialchars($row['name']),
                        "ACTLEVEL" => "",
                        "LEVSIGN" => "1L" . $row['id'],
                        "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev1, $row['id']) : '')
                );
                $this->keyword .= htmlspecialchars($row['name']) . ",";
                // //$t->parse("DBlock", "DataBlock", true);
            }
        }
        else {
            // browse par série : seulement 2 niveaux
            while ($row = Db_fetch_array($RecAuteur)) {
                $a_row[] = array(
                        "WSPACER" => "0px",
                        "HSPACER" => "0px",
                        "IMGNAVIG" => "aro_3_1.gif",
                        "URLEVEL" => BDO_URL . "browser/xhr_level2" . "?lev_id=" . $row['id'] . $queryString,
                        "NAMELEVEL" => htmlspecialchars(stripslashes($row['name'])),
                        "ACTLEVEL" => "",
                        "LEVSIGN" => "1L" . $row['id'],
                        "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev1, $row['id']) : '')
                );
                $this->keyword .= htmlspecialchars($row['name']) . ",";
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
            
            if ($this->pageNum > 0) $this->view->set_var("URLPREVPAGE", '<a href="javascript:document.browser.pageNum.value=\'' . max(0, $this->pageNum - 1) . '\';go2()">Pr&eacute;c&eacute;dent</a>');
            else
                $this->view->set_var("URLPREVPAGE", '');
            
            if ($this->pageNum < $totalPages) $this->view->set_var("URLNEXTPAGE", '<a href="javascript:document.browser.pageNum.value=\'' . min($totalPages, $this->pageNum + 1) . '\';go2()">Suivant</a>');
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
                "TITRESEARCH" => "Contient '...<i>" . htmlspecialchars($this->let) . "<i>...'</b>"
        ));
        $this->view->layout = "ajax";
        $this->view->render();
    }

    public function Xhr ()
    {
        if ($this->rb_browse == 'aut') {
            
            $query_niv2 = "
        	select SQL_CALC_FOUND_ROWS distinct
        		bd_serie.id_serie id,
        		bd_serie.nom name,
               bd_serie.FLG_FINI as FLG_FINI_SERIE
        	from
        		bd_serie
        		INNER JOIN bd_tome USING(id_serie)
        	where
        		bd_tome.id_dessin = " . Db_Escape_String($this->lev_id) . "
        		or bd_tome.id_scenar = " . Db_Escape_String($this->lev_id) . "
        		or bd_tome.id_color = " . Db_Escape_String($this->lev_id) . "
        		or bd_tome.id_scenar_alt = " . Db_Escape_String($this->lev_id) . "
        		or bd_tome.id_dessin_alt = " . Db_Escape_String($this->lev_id) . "
        		or bd_tome.id_color_alt = " . Db_Escape_String($this->lev_id) . "
        	order by name ";
            
            // URL album
            $act_url = ' onclick="window.open(' . "'./membres/album?id_tome=%d','Album','width=500,height=400,scrollbars=1')" . '"';
            $url_alb = "";
            // URL d'edition
            $edit_lev2 = "<a href='" . $url_edit["SERIE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'edit') {
            
            $query_niv2 = "
        	select SQL_CALC_FOUND_ROWS
        		id_collection id,
        		nom name
        	from
        		bd_collection
        	where
        		id_editeur =" . Db_Escape_String($this->lev_id) . "
        	order by name
        	";
            
            // URL affichage
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev2 = "<a href='" . $url_edit["COLLECTION"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        elseif ($this->rb_browse == 'genr') {
            
            $query_niv2 = "
        	select SQL_CALC_FOUND_ROWS distinct
        		Upper(left(nom,1)) id,
        		Upper(left(nom,1)) name,
               bd_serie.FLG_FINI as FLG_FINI_SERIE
        	from
        		bd_serie
        	where
        		id_genre =" . Db_Escape_String($this->lev_id) . "
        	order by name
        	";
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev2 = "";
        }
        
        $recLev2 = Db_query($query_niv2 . " LIMIT " . Db_Escape_String($this->startRow) . "," . Db_Escape_String($this->maxRows));
        
        $resCount = Db_query('SELECT FOUND_ROWS() as nb');
        $rowCount = Db_fetch_array($resCount);
        $totalRows = $rowCount['nb'];
        
        $a_row = array();
        while ($row_niv2 = Db_fetch_array($recLev2)) {
            $a_row[] = array(
                    "WSPACER" => "16px",
                    "HSPACER" => "1px",
                    "IMGNAVIG" => "aro_3_1.gif",
                    "URLEVEL" => "browser/xhr_level2" . "?rb_browse=" . $this->rb_browse . "&lev_id=" . $this->lev_id . "&lev2_id=" . $row_niv2['id'],
                    "NAMELEVEL" => htmlspecialchars(stripslashes($row_niv2['name'])),
                    "ACTLEVEL" => "",
                    "LEVSIGN" => "1L" . $this->lev_id . "_2L" . $row_niv2['id'],
                    "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev2, $row_niv2['id']) : '')
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
            else
                $this->view->set_var("URLPREVPAGE", '');
            
            if ($this->pageNum < $totalPages) {
                $jvs = '$.get("'.BDO_URL.'browser/xhr?rb_browse=' . $this->rb_browse . '&lev_id=' . $this->lev_id . '&pageNum=' . min($totalPages, $this->pageNum + 1) . '",
                         function(data) {$("#onglet_div_xhr_1L' . $this->lev_id . '").html(data);});';
                $url = '<a href="#" onClick=\''.$jvs.';return false\'>Suivant</a>';
                $this->view->set_var("URLNEXTPAGE", $url);
            }
            else
                $this->view->set_var("URLNEXTPAGE", '');            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            $this->view->set_var("URLSITE", BDO_URL);
            $this->view->set_var("URLSITEIMAGE", BDO_URL_IMAGE);
            $this->view->set_var("TOTALROW", (empty($totalRows) ? 'Aucune ligne de résultat !' : $totalrowhtml));
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
        
        if ($this->rb_browse == 'ser') {
            
            $query_album = "
            select SQL_CALC_FOUND_ROWS
            bd_tome.id_tome id,
            bd_tome.ID_TOME,
            (CASE
                WHEN bd_tome.flg_int = 'O'
                THEN 'Intégrale '
                WHEN bd_tome.flg_type =1
                THEN 'Coffret '
                WHEN bd_serie.flg_fini =2
                THEN 'One shot'
                ELSE IFNULL( concat( 'Tome ', bd_tome.num_tome ) , 'HS' )
                END
                ) AS typeTome ,
                bd_tome.titre as titre,
                bd_tome.TITRE as TITRE_TOME,
                bd_tome.NUM_TOME,
                bd_serie.NOM as NOM_SERIE,
               bd_serie.FLG_FINI as FLG_FINI_SERIE,
                bd_edition.IMG_COUV
            from
            bd_tome 
            INNER JOIN bd_serie USING(id_serie)
        	INNER JOIN bd_edition USING(ID_EDITION)
        	where
            bd_tome.id_serie = $this->lev_id
            order by num_tome, TITRE
            ";
            
            // URL album
            $act_url = ' onclick="window.open(' . "'./membres/album?id_tome=%d','Album','width=550,height=600,scrollbars=1')" . ';return false;"';
            $act_url = ' rel="shadowbox;player=iframe;height=600;width=580;"';
            $url_alb = "#";
            $url_alb = BDO_URL . 'membres/album.php?id_tome=%d';
            // URL d'edition
            $edit_lev3 = "<a href='" . $url_edit["ALBUM"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'aut') {
            
            $query_album = "
            select SQL_CALC_FOUND_ROWS
        		bd_tome.id_tome id,
            bd_tome.ID_TOME,
                    (CASE
                WHEN bd_tome.flg_int = 'O'
                THEN 'Intégrale '
                WHEN bd_tome.flg_type =1
                THEN 'Coffret '
                WHEN bd_serie.flg_fini =2
                THEN 'One shot'
                ELSE IFNULL( concat( 'Tome ', bd_tome.num_tome ) , 'HS' )
                END
                ) AS typeTome ,
                bd_tome.titre as titre,
                bd_tome.TITRE as TITRE_TOME,
                bd_tome.NUM_TOME,
                bd_serie.NOM as NOM_SERIE,
               bd_serie.FLG_FINI as FLG_FINI_SERIE,
                bd_genre.LIBELLE as NOM_GENRE,
                bd_edition.IMG_COUV
        	from
        		bd_tome
        		INNER JOIN bd_serie USING(id_serie)
        		INNER JOIN bd_edition USING(ID_EDITION)
        	INNER JOIN bd_genre ON bd_genre.ID_GENRE=bd_serie.ID_GENRE
                    where
        		bd_tome.id_serie =" . $this->lev2_id . "
                		and	(
        			bd_tome.id_dessin = " . Db_Escape_String($this->lev_id) . "
                			or bd_tome.id_scenar = " . Db_Escape_String($this->lev_id) . "
                			or bd_tome.id_color = " . Db_Escape_String($this->lev_id) . "
                			or bd_tome.id_scenar_alt = " . Db_Escape_String($this->lev_id) . "
                			or bd_tome.id_dessin_alt = " . Db_Escape_String($this->lev_id) . "
                			or bd_tome.id_color_alt = " . Db_Escape_String($this->lev_id) . "
                			)
                			order by num_tome, titre
            ";
            
            // URL album
            $act_url = ' onclick="window.open(' . "'./membres/album?id_tome=%d','Album','width=550,height=600,scrollbars=1')" . ';return false;"';
            $url_alb = "#";
            // URL d'edition
            $edit_lev3 = "<a href='" . $url_edit["ALBUM"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0 /></a>";
        }
        elseif ($this->rb_browse == 'edit') {
            
            $query_album = "
        	select SQL_CALC_FOUND_ROWS distinct
        		bd_serie.id_serie id,
                    bd_serie.ID_SERIE,
        		bd_serie.nom as titre,
                    bd_serie.NOM as NOM_SERIE,
               bd_serie.FLG_FINI as FLG_FINI_SERIE
        	from
        	bd_edition
        		INNER JOIN bd_tome USING(id_tome)
        		INNER JOIN bd_serie USING(id_serie)
        	where
        		bd_edition.id_editeur=" . Db_Escape_String($this->lev_id) . "
        		and bd_edition.id_collection=" . Db_Escape_String($this->lev2_id) . "
        	order by bd_serie.nom
        	";
            
            // URL affichage
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev3 = "<a href='" . $url_edit["SERIE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        elseif ($this->rb_browse == 'genr') {
            
            $query_album = "
        	select SQL_CALC_FOUND_ROWS
        		distinct
        		bd_serie.id_serie id,
                    bd_serie.ID_SERIE,
        		bd_serie.nom as titre,
                    bd_serie.NOM as NOM_SERIE,
               bd_serie.FLG_FINI as FLG_FINI_SERIE
          	from
                bd_serie
            where
                bd_serie.id_genre=" . Db_Escape_String($this->lev_id) . "
        		and bd_serie.nom like '" . $this->lev2_id . "%'
            order by titre
        	";
            $url_alb = "serie?id_serie=%d";
            $act_url = "";
            // URL d'edition
            $edit_lev3 = "<a href='" . $url_edit["SERIE"] . "' target =_blank><img src='" . BDO_URL_IMAGE . "edit.gif' border=0></a>";
        }
        
        if ($this->rb_browse != "ser") {
            $recAlbum = Db_query($query_album . " LIMIT " . Db_Escape_String($this->startRow) . "," . Db_Escape_String($this->maxRows));
            
            $resCount = Db_query('SELECT FOUND_ROWS() as nb');
            $rowCount = Db_fetch_array($resCount);
            $totalRows = $rowCount['nb'];
            
            $a_row = array();
            while ($rowAlbum = Db_fetch_array($recAlbum)) {
                $a_row[] = array_merge($rowAlbum,array(
                        "WSPACER" => "1px",
                        "HSPACER" => "1px",
                        "IMGNAVIG" => "spacer.gif",
                        "URLEVEL" => sprintf($url_alb, $rowAlbum['id']),
                        "NAMELEVEL" => $rowAlbum['typeTome'].': '.$rowAlbum['TITRE'],
                        "ACTLEVEL" => sprintf($act_url, $rowAlbum['id']),
                        "LEVSIGN" => "A" . $rowAlbum['id'],
                        "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev3, $rowAlbum['id']) : '')
                ));
            }
            $this->view->set_var("a_row", $a_row);
        }
        else {
            // browse par série : seulement 2 niveaux
            // aJOUT DE LA PREmière ligne Fiche Série
            $this->view->set_var(
                    array(
                            "FICHESERIE" => $this->lev_id,
                    ));
            
            $recAlbum = Db_query($query_album . " LIMIT " . Db_Escape_String($this->startRow) . "," . Db_Escape_String($this->maxRows));
            
            $resCount = Db_query('SELECT FOUND_ROWS() as nb');
            $rowCount = Db_fetch_array($resCount);
            $totalRows = $rowCount['nb'];
            
            $a_row = array();
            while ($rowAlbum = Db_fetch_array($recAlbum)) {
                $a_row[] = array_merge($rowAlbum,array(
                        "WSPACER" => "10px",
                        "HSPACER" => "1px",
                        "IMGNAVIG" => "spacer.gif",
                        "URLEVEL" => sprintf($url_alb, $rowAlbum['id']),
                        "NAMELEVEL" => $rowAlbum['typeTome'].': '.$rowAlbum['titre'],
                        "ACTLEVEL" => sprintf($act_url, $rowAlbum['id']),
                        "LEVSIGN" => "A" . $rowAlbum['id'],
                        "URLEDIT" => (Bdo_Cfg::user()->minAccessLevel(1) ? sprintf($edit_lev3, $rowAlbum['id']) : '')
                ));
            }
            $this->view->set_var("a_row", $a_row);
        }
        
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

