<?php



/**

 *

 * @author laurent

 *        

 */

class Bdo_Db_Line

{



    public $insert_id = null;



    public $a_constraint = array();



    public $a_columnConstraint = array();



    public $a_column = array();



    public $a_conditionPk = array();



    public $entite = null;



    public $a_dataColumn = array();



    public $a_dataPaste = array();



    public $Bdo_Db_Select = null;



    public $dbSearch = null;



    public $wherePkQuery = '1';



    public $wherePkQueryNot = '1';



    public $table_name = null;



    public $error = array();



    public $orderSelect = '';

    

    // initialisation

    public function __construct ($table_name, $a_data = array())

    {

        if (is_null($this->table_name)) {

            $this->table_name = $table_name;

        }

        

        $schema = Bdo_Cfg::schema();

        if ($schema->is_table($this->table_name)) {

            $this->schema_name = $schema->schema;

            

            $this->a_column = $schema->dbColumn[$this->table_name];

            $this->setColumn();

            

            $this->setConstraint($schema);

        }

        

        if (is_object($a_data)) $a_data = (array) $a_data;

        if (! empty($a_data)) {

            $this->set_dataPaste($a_data);

        }

    }



    public function load ($type = 'c', $where = null)

    {

        switch ($type) {

            case 'c':

                $query = $this->select();

                break;

            case 'e':

                $query = $this->selectEdit();

                break;

        }

        

        if (is_null($where)) {

            if ($this->wherePk()) {

                $query .= " WHERE " . $this->wherePkQuery;

                if ($this->orderSelect) {

                    $query .= $this->orderSelect;

                }

            }

            else {

                $query .= " WHERE 1=2";

            }

        }

        else {

            $query .= " " . $where;

            if ($this->orderSelect) {

                $query .= $this->orderSelect;

            }

        }
        
        

        $this->dbSelect = new Bdo_Db_Select($query);

        

        // --------------=======================----------------

        $this->dbSelect->exec();

        $this->dbSelect->integreData();

        // --------------=======================----------------

        

        if (1 == $this->dbSelect->nbLineResult) {

            $this->set_data($this->dbSelect->a_dataQuery[0]);

        }

        

        return $this->dbSelect;

    }



    public function set_data ($a_data = array())

    {

        if (is_object($a_data)) $a_data = (array) $a_data;

        foreach ($a_data as $key => $val) {

            $this->$key = $val;

        }

        return true;

    }



    public function _k ($key)

    {

        if (isset($this->$key)) {

            return $this->$key;

        }

        

        if (isset($this->a_dataPaste[$key])) {

            return $this->a_dataPaste[$key];

        }

        

        return null;

    }



    /**

     * Recherche des colonnes de la table

     */

    public function setColumn ()

    {

        foreach ($this->a_column as $column_name => $obj) {

            $lang = substr($column_name, - 3);

            

            if (isset($_SESSION['CFG_A_LANG'][$lang])) {

                $addLang = '(' . $_SESSION['CFG_A_LANG'][$lang]->{'NOM_LANG' . $_SESSION['ID_LANG']} . ')';

                if (! stristr($this->a_column[$column_name]->TITRE_CHAMP, $addLang)) $this->a_column[$column_name]->TITRE_CHAMP .= ' ' . $addLang;

            }

        }

    }



    /**

     * Recherche des constraints de la table

     */

    public function setConstraint ($schema)

    {

        foreach ($schema->dbConstraint[$this->table_name] as $constraint_name => $a_cons) {

            foreach ($a_cons as $column_name => $o_constraint) {

                if (in_array($o_constraint->CONSTRAINT_TYPE, array(

                        'PRIMARY KEY',

                        'UNIQUE'

                ))) {

                    $this->a_constraint[$constraint_name][] = $column_name;

                    $this->a_columnConstraint[$constraint_name][] = $this->a_column[$column_name];

                }

            }

        }

        

        if (! isset($this->a_constraint['PRIMARY'])) {

            $this->error[] = 'Table `' . $this->schema_name . '`.`' . $this->table_name . '` has no primary key : Operation aborted !';

        }

    }



    /**

     * Recherche de la ligne pour update par rapport aux pk présentes

     */

    public function wherePk ()

    {

        

        /**

         * a voir ! ne gerer que des dates en version en pas fr(uniquement

         * affichage)

         */

        $a_wherePkQuery = array();

        $a_wherePkQuery_not = array();

        

        foreach ($this->a_constraint['PRIMARY'] as $column_name) {

            if (isset($this->a_dataColumn[$column_name])) {

                $this->a_conditionPk[$column_name] = $this->a_dataColumn[$column_name];

            }

        }

        

        foreach ($this->a_conditionPk as $column_name => $val) {

            if (in_array($this->a_column[$column_name]->DATA_TYPE, array(

                    'date',

                    'datetime',

                    'timestamp'

            ))) {

                $val = PrepaDate($val);

                $a_wherePkQuery[] = "`" . $this->table_name . "`.`" . $column_name . "`=" . $val . "";

                $a_wherePkQuery_not[] = "`" . $this->table_name . "`.`" . $column_name . "`!=" . $val . "";

            }

            else {

                $a_wherePkQuery[] = "`" . $this->table_name . "`.`" . $column_name . "`='" . $val . "'";

                $a_wherePkQuery_not[] = "`" . $this->table_name . "`.`" . $column_name . "`!='" . $val . "'";

            }

        }

        

        $this->wherePkQuery = implode(" \nAND ", $a_wherePkQuery);

        $this->wherePkQueryNot = implode(" \nAND ", $a_wherePkQuery_not);

        

        return $this->wherePkQuery;

    }



    public function add_dataPaste ($key, $val)

    {

        $this->a_dataPaste[$key] = $val;

        $this->setDataColumn();

        return true;

    }



    public function set_dataPaste ($a_data = array())

    {

        foreach ($a_data as $key => $val) {

            $this->a_dataPaste[$key] = $val;

        }

        $this->setDataColumn();

        return true;

    }



    /**

     * Recherche des valeurs renseignees pour chaque colonne de la table

     */

    public function setDataColumn ()

    {

        $this->a_dataColumn = array();

        

        foreach ($this->a_column as $obj) {

            if (isset($this->a_dataPaste[$obj->COLUMN_NAME])) {

                // actions de nettoyage systematiques

                switch ($obj->DATA_TYPE) {

                    // decimal : remplacement virgule par point

                    case 'real':

                    case 'float':

                    case 'decimal':

                        {
                            
                            $this->a_dataPaste[$obj->COLUMN_NAME] = str_replace(',', '.', $this->a_dataPaste[$obj->COLUMN_NAME]);

                        }

                        break;

                }

                

                if ('' != $this->a_dataPaste[$obj->COLUMN_NAME]) {

                    if ('FREE_ID_' == substr($obj->COLUMN_NAME, 0, 8)) {

                        $colNameForFree = substr($obj->COLUMN_NAME, 5);

                        

                        if (1 != $this->a_dataPaste[$colNameForFree]) {

                            $this->a_dataPaste[$obj->COLUMN_NAME] = null;

                        }

                    }

                    $this->a_dataColumn[$obj->COLUMN_NAME] = $this->a_dataPaste[$obj->COLUMN_NAME];

                }

                else {

                    if (in_array($obj->COLUMN_NAME, $this->a_constraint['PRIMARY'])) {

                        // on ne fait rien

                    }

                    else if (empty($obj->EXTRA_CHAMP)) {

                        $this->a_dataColumn[$obj->COLUMN_NAME] = null;

                    }

                    else {

                        $this->a_dataColumn[$obj->COLUMN_NAME] = $obj->EXTRA_CHAMP;

                    }

                }

            }

            // ajout automatique du user modification

            else if (('ID_MODIF_USER' == $obj->COLUMN_NAME) and (! isset($this->a_dataColumn[$obj->COLUMN_NAME]) or empty($this->a_dataColumn[$obj->COLUMN_NAME])) and isset($_SESSION['user']['ID_USER'])) {

                $this->a_dataColumn[$obj->COLUMN_NAME] = $_SESSION['user']->ID_USER;

            }

            // timestamp on update

            else if ($obj->EXTRA == 'on update CURRENT_TIMESTAMP') {

                // on ne fait rien

            }

            else {

                if (in_array($obj->COLUMN_NAME, $this->a_constraint['PRIMARY'])) {

                    /**

                     * pour un update toutes les colonnes de la primary doivent

                     * etre renseignees

                     * pour un insert il faut verifier si la valeur par defaut

                     * est null et l'attribuer sauf pour

                     * le cas de l'autoincrement

                     */

                    if (($obj->EXTRA != 'auto_increment') and is_null($obj->COLUMN_DEFAULT)) {

                        $this->a_dataColumn[$obj->COLUMN_NAME] = null;

                    }

                    // on ne fait rien

                }

                else if (empty($obj->EXTRA_CHAMP)) {

                    // $this->a_dataColumn[$obj->COLUMN_NAME] = null;

                }

                else {

                    $this->a_dataColumn[$obj->COLUMN_NAME] = $obj->EXTRA_CHAMP;

                }

            }

        }

    }



    public function ctrlDataPost ()

    {
        
        foreach ($this->a_column as $column_name => $obj) {

            if (! isset($obj->EXTRA_CHAMP)) $obj->EXTRA_CHAMP = '';

            

            if (array_key_exists($column_name, $this->a_dataColumn) and (is_null($this->entite) or ($this->entite->{$column_name} != $this->a_dataColumn[$column_name]))) {

              
                if (is_null($this->a_dataColumn[$column_name])) {

                    if ('YES' == $obj->IS_NULLABLE) $this->a_updateColumn[$column_name] = "NULL";

                    else

                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR3;

                }

                else {

                    // vérification par type
                    
                    switch ($obj->DATA_TYPE) {

                        case 'timestamp':

                        case 'date':

                        case 'datetime':

                            {

                                if (empty($obj->EXTRA_CHAMP)) {

                                    if (! ($this->a_updateColumn[$column_name] = PrepaDate($this->a_dataColumn[$column_name]))) {

                                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR4;

                                    }

                                }

                                else {

                                    $this->a_updateColumn[$column_name] = $obj->EXTRA_CHAMP;

                                }

                                break;

                            }

                        case 'enum':

                            {

                                if (in_array($this->a_dataColumn[$column_name], $obj->TAB_CHECK_VALUE)) {

                                    $this->a_updateColumn[$column_name] = "'" . $this->a_dataColumn[$column_name] . "'";

                                }

                                else {

                                    $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] = [ ' . $this->a_dataColumn[$column_name] . ' ] : ' . LANG_INSERTERROR5;

                                }

                                break;

                            }

                        case 'set':

                            {

                                $a_data_set_update = array();

                                foreach ($this->a_dataColumn[$column_name] as $data_set) {

                                    if (in_array($data_set, $obj->TAB_CHECK_VALUE)) {

                                        $a_data_set_update[] = $data_set;

                                        $this->a_updateColumn[$column_name] = "'" . $this->a_dataColumn[$column_name] . "'";

                                    }

                                    else {

                                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] = [ ' . $this->a_dataColumn[$column_name] . ' ] : ' . LANG_INSERTERROR5;

                                    }

                                }

                                if ($a_data_set_update) {

                                    $this->a_updateColumn[$column_name] = "'" . implode(',', $a_data_set_update) . "'";

                                }

                                break;

                            }
                        
                        case 'decimal':

                            {

                                if (is_numeric($this->a_dataColumn[$column_name])) {

                                    $retourVerifMaxNumValue = Bdo_Security::verifDecimal($obj, $this->a_dataColumn[$column_name]);

                                    if ($retourVerifMaxNumValue === true) {

                                        $this->a_updateColumn[$column_name] = "'" . $this->a_dataColumn[$column_name] . "'";

                                    }

                                    else {

                                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . $retourVerifMaxNumValue;

                                    }

                                    unset($retourVerifMaxNumValue);

                                }

                                else {

                                    $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR6;

                                }

                                

                                break;

                            }
                        case 'double':
                        case 'float':
                            $this->a_updateColumn[$column_name] = "'" . $this->a_dataColumn[$column_name] . "'";
                            break;
                        case 'real':
                            $this->a_updateColumn[$column_name] = "'" . $this->a_dataColumn[$column_name] . "'";
                            break;
                        case 'int':

                        case 'tinyint':

                        case 'bigint':

                        case 'smallint':

                        case 'mediumint':

                            {

                                if (is_numeric($this->a_dataColumn[$column_name])) {

                                    $retourVerifMaxNumValue = Bdo_Security::verifInteger($obj, $this->a_dataColumn[$column_name]);

                                    if ($retourVerifMaxNumValue === true) {

                                        $this->a_updateColumn[$column_name] = "'" . $this->a_dataColumn[$column_name] . "'";

                                    }

                                    else {

                                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . $retourVerifMaxNumValue;

                                    }

                                    unset($retourVerifMaxNumValue);

                                }

                                else {

                                    $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR6;

                                }

                                

                                break;

                            }

                        case 'time':

                            if (empty($obj->EXTRA_CHAMP)) {

                                if (! ($this->a_updateColumn[$column_name] = PrepaTime($this->a_dataColumn[$column_name]))) {

                                    $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR7;

                                }

                            }

                            else {

                                $this->a_updateColumn[$column_name] = $obj->EXTRA_CHAMP;

                            }

                            break;

                        case 'char':

                        case 'varchar':

                        case 'tinytext':

                        case 'text':

                        case 'mediumtext':

                        case 'longtext':

                            {

                                switch ($obj->EXTRA_CHAMP) {

                                    case 'password': // modification d'un mot

                                                      // de passe

                                        {

                                            if (isset($this->a_dataPaste['SALT_USER'])) $salt_password = $this->a_dataPaste['SALT_USER'];

                                            else

                                                $salt_password = CFG_SALT_PASSWORD;

                                            

                                            if (is_null($this->entite)) {

                                                if (isset($this->a_dataPaste[$column_name . '_OLD']) and isset($this->a_dataPaste[$column_name])) {

                                                    

                                                    if (! empty($this->a_dataPaste[$column_name . '_OLD']) and ($this->a_dataPaste[$column_name . '_OLD'] == $this->a_dataPaste[$column_name])) {

                                                        $this->a_updateColumn[$column_name] = "'" . sha1($salt_password . $this->a_dataPaste[$column_name]) . "'";

                                                    }

                                                    else {

                                                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR12;

                                                    }

                                                }

                                                

                                                else if (! empty($this->a_dataPaste[$column_name])) {

                                                    $mdp_user_clair = $this->a_dataPaste[$column_name];

                                                    $this->a_updateColumn[$column_name] = "'" . sha1($salt_password . $this->a_dataPaste[$column_name]) . "'";

                                                }

                                                else {

                                                    $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR8;

                                                }

                                            }

                                            else if (issetNotEmpty($this->a_dataPaste[$column_name . '_OLD'])) {

                                                if ($this->entite->{$column_name} == sha1($this->entite->SALT_USER . $this->a_dataPaste[$column_name . '_OLD'])) {

                                                    if (! empty($this->a_dataPaste[$column_name])) {

                                                        $this->a_updateColumn[$column_name] = "'" . sha1($salt_password . $this->a_dataPaste[$column_name]) . "'";

                                                    }

                                                    else {

                                                        $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR12;

                                                    }

                                                }

                                                else {

                                                    $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR9;

                                                }

                                            }

                                            else if (! isset($this->a_dataPaste[$column_name . '_OLD']) and isset($this->a_dataPaste[$column_name])) {

                                                if (! empty($this->a_dataPaste[$column_name])) {

                                                    $this->a_updateColumn[$column_name] = "'" . sha1($salt_password . $this->a_dataPaste[$column_name]) . "'";

                                                }

                                                else {

                                                    // le mot de passe existe et

                                                // ne doit pas etre change : pas

                                                // d'erreur

                                                }

                                            }

                                            break;

                                        }

                                    default:

                                        {

                                            if (stristr($obj->COLUMN_NAME, 'EMAIL')) {

                                                if (! Bdo_Security::is_mail($this->a_dataColumn[$column_name])) {

                                                    $this->error[] = "[ " . $obj->TITRE_CHAMP . " ] : " . LANG_INSERTERROR10;

                                                }

                                            }

                                            if ($obj->CHARACTER_MAXIMUM_LENGTH >= mb_strlen($this->a_dataColumn[$column_name])) {

                                                $this->a_updateColumn[$column_name] = "'" . Db_Escape_String($this->a_dataColumn[$column_name]) . "'";
                                                   
                                            }

                                            else {

                                                $this->error[] = '[ ' . $obj->TITRE_CHAMP . ' ] : ' . LANG_INSERTERROR11;

                                            }

                                        }

                                }

                                break;

                            }

                        case 'blob':

                            {

                                if (strpos($column_name, 'CRYPT_') === 0) {

                                    $this->a_updateColumn[$column_name] = "AES_ENCRYPT('" . Db_Escape_String($this->a_dataColumn[$column_name]) . "','" . CFG_CRYPT_PWD . "')";

                                }

                            }

                    }

                }

            }

        }

    }



    public function delete ()

    {

        $this->wherePk();

        if (empty($this->error)) $this->controlPk();

        

        if (! is_null($this->entite)) {

            $delete_query = "

			DELETE FROM `" . $this->schema_name . "`.`" . $this->table_name . "` 

			WHERE " . $this->wherePkQuery . " LIMIT 1";

            

            $typeQuery = "DELETE";

            if (Db_query($delete_query)) {

                // mettre un log ici

            }

            else {

                $this->error[] = "execute 'delete' query failed";

            }

        }

        else {

            $this->error[] = LANG_DATAFALSE;

        }

    }



    /**

     * Recherche de la ligne pour update ou delete par rapport aux pk présentes

     */

    public function controlPk ()

    {

        // toutes les colonnes de la contrainte doivent avoir une valeur

        if (count($this->a_constraint['PRIMARY']) == count($this->a_conditionPk)) {

            // recherche de la ligne à modifier

            $resultat = Db_query("

				SELECT * FROM `" . $this->schema_name . "`.`" . $this->table_name . "` 

				WHERE " . $this->wherePkQuery . " LIMIT 1");

            

            if ($obj = Db_fetch_object($resultat)) {

                $this->entite = $obj;

            }

            else {

                $this->entite = null;

            }

            Db_free_result($resultat);

        }

        else {
           
            $this->entite = null;

        }

    }



    /**

     * controle de uk

     */

    public function controlUk ()

    {

        foreach ($this->a_constraint as $cons_name => $a_cons_column) {

            if ($cons_name != 'PRIMARY') {

                $a_conditionUk = array();

                

                foreach ($a_cons_column as $column_name) {

                    

                    if (isset($this->a_dataColumn[$column_name])) {

                        $a_conditionUk[] = "`" . $column_name . "`='" . Db_Escape_String($this->a_dataColumn[$column_name]) . "'";

                    }

                }

                

                // toutes les colonnes de la contrainte doivent avoir une valeur

                if (count($a_cons_column) == count($a_conditionUk)) {

                    $whereUkQuery = implode(' AND ', $a_conditionUk);

                    // compte le nombre de ligne avec la même valeur transmise

                    // pour la uk en dehors de la pk

                    

                    if (0 < Db_CountRow("

				SELECT * FROM `" . $this->schema_name . "`.`" . $this->table_name . "` 

				WHERE " . $whereUkQuery . " 

				AND " . $this->wherePkQueryNot)) {

                        $error_uk = "[";

                        foreach ($a_cons_column as $column_name) {

                            $error_uk .= " " . $this->a_column[$column_name]->TITRE_CHAMP . " ";

                        }

                        $error_uk .= "] : " . LANG_INSERTERROR2;

                        $this->error[] = $error_uk;

                    }

                }

            }

        }

    }



    public function insert ()

    {

        return $this->update();

    }



    public function update ()

    {

        $this->wherePk();

        

        if (empty($this->error)) $this->controlPk();

        if (empty($this->error)) $this->controlUk();

        if (empty($this->error)) $this->ctrlDataPost();

        

        if (empty($this->error)) {

            // ----------------- cas UPDATE ------------------------
            
            if (! is_null($this->entite) and (0 < count($this->a_updateColumn))) {

                $typeQuery = "UPDATE";

                $tab = array();
              
                foreach ($this->a_updateColumn as $key => $val) {

                    $tab[] = "`" . $key . "`=" . $val;

                }

                

                $update_query = "

					UPDATE `" . $this->schema_name . "`.`" . $this->table_name . "` 

					SET " . implode(",\n", $tab) . " 

					WHERE " . $this->wherePkQuery;

            }

            // ----------------- cas INSERT ------------------------

            else if (is_null($this->entite)) {

                $typeQuery = "INSERT";

                $tabKey = array();

                $tabVal = array();

                foreach ($this->a_updateColumn as $key => $val) {

                    $tabKey[] = "`" . $key . "`";

                    $tabVal[] = $val;

                }

                

                $update_query = "INSERT INTO `" . $this->schema_name . "`.`" . $this->table_name . "` ( " . implode(",\n", $tabKey) . "

					) VALUES ( " . implode(",\n", $tabVal) . "

					)";

            }

            // var_dump_pre($update_query);

            // Bdo_Cfg::quit();

            if (isset($update_query)) {

                if (Db_query($update_query)) {

                    if ((1 == count($this->a_constraint['PRIMARY'])) and ('auto_increment' == $this->a_columnConstraint['PRIMARY'][0]->EXTRA) and is_null($this->entite)) {

                        $this->insert_id = Db_insert_id();

                        $this->a_dataPaste[$this->a_constraint['PRIMARY'][0]] = $this->insert_id;

                        $this->a_dataColumn[$this->a_constraint['PRIMARY'][0]] = $this->insert_id;

                        $this->controlPk();

                    }

                    $this->affected_rows = Db_affected_rows();

                    

                    $this->set_data($this->a_dataColumn);

                }

            }

            else if (0 < count($this->a_updateColumn)) {

                $this->error[] = "execute 'insert' query failed";

            }

        }

    }



    public function priColUrl ()

    {

        $pri_col = '';

        $a_pri_col = array();

        foreach ($this->a_constraint['PRIMARY'] as $column_name) {

            if (isset($this->a_dataPaste[$column_name]) and ! empty($this->a_dataPaste[$column_name])) {

                $a_pri_col[] = $column_name . '=' . $this->a_dataPaste[$column_name];

            }

        }

        return implode('&', $a_pri_col);

    }

}



?>