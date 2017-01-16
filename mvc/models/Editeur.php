<?php

/**
 *
 * @author laurent
 *
 */
class Editeur extends Bdo_Db_Line {

    /**
     */
    public $table_name = 'bd_editeur';

    public $error = '';

    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'ID_EDITEUR' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "
        SELECT
        `bd_editeur`.`ID_EDITEUR` ,
        `bd_editeur`.`NOM` ,
        `bd_editeur`.`URL_SITE`
        FROM `" . $this->table_name . "`
        ";
    }

    public function search ($a_data = array())
    {
        // --------------------------------------------------------------------
        // -------- Champs selectionnés par defaut --------
        if (empty($a_data)) $a_data = $_POST;

        if (! isset($a_data['validSubmitSearch'])) {
            $a_data['ch_NOM'] = "checked";
            $a_data['ch_URL_SITE'] = "checked";
        }

        $dbSearch = new Bdo_Db_Search();

        $dbSearch->select = "
        SELECT
        `bd_editeur`.`ID_EDITEUR` ,
        `bd_editeur`.`NOM` ,
        `bd_editeur`.`URL_SITE`
        ";

        // dans les tables
        $dbSearch->from = "FROM " . $this->table_name . "";
        $dbSearch->where = "WHERE 1";

        // dans l'ordre
        if ($a_data['daff'] == "") $a_data['daff'] = "0";
        if ($a_data['sens_tri'] == "") $a_data['sens_tri'] = "ASC";
        if ($a_data['col_tri'] == "") $a_data['col_tri'] = $this->table_name . ".NOM";

        $dbSearch->groupby = "";

        // --------------=======================----------------

        $dbSearch->infoQuery();

        // --------------=======================----------------

        $dbSearch->integreData($a_data);

        // --------------=======================----------------

        if (isset($_GET['export'])) {
            $dbSearch->execNoLimit();
        } else {
            $dbSearch->exec();
        }

        return $dbSearch;
    }
}
