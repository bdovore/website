<?php

/**
 *
 * @author laurent
 *
 */
class Bdo_Security {
    private static $instance;


    public function __construct() {
    }

    public static function _getInstance() {
        if(!isset(self::$instance)) {
            self::$instance = new security();
        }
        return self::$instance;
    }

    public static function is_mail($mail)
    {
        $Syntaxe='#^[\w.\+-]+@[\w.-]+\.[a-zA-Z]{2,5}$#';
        if(preg_match($Syntaxe,$mail))
            return true;
        else
            return false;
    }

    public function stripSlUtf8($data)
    {
        if (get_magic_quotes_gpc())     {
            if (is_array($data)) {
                foreach($data as $key=>$val) {
                    $data[$key] = security::stripSlUtf8($val);
                }
            }
            else {
                $data = stripslashes($data);
                //if (is_utf8($data)) $data = utf8_decode($data);
            }
        }

        return $data;
    }

    public function page() {
        $acl = Bdo_Cfg::getVar('acl');
        $respriv = Bdo_Cfg::getVar('controller').".".Bdo_Cfg::getVar('action');

        if (!$acl->isResourcePrivilege(Bdo_Cfg::getVar('controller'),Bdo_Cfg::getVar('action'))) {
            Bdo_Error::add("Le privil&egrave;ge [".$respriv."] n'existe pas.");
        }

        if (!Bdo_Cfg::user()->isAllowed($respriv)) {
            exit('acc&egrave;s interdit !!');
        }
    }

    public function protectAttack($var_name, $var_data)
    {
        $error = '';
        $a_whiteList = Bdo_Cfg::getVar("a_whiteList");

        if (empty($var_data)) {
            return $var_data;
        }

        if (is_array($var_data)) {
            $corr_var_data = array();
            foreach($var_data as $key=>$val) {
                if (is_numeric($key)) {
                    $real_var_name = ('a_' == substr($var_name,0,2)) ? substr($var_name,2) : $var_name;
                }
                else {
                    $real_var_name = $key;
                }
                $corr_var_data[$key] = self::protectAttack($real_var_name, $val);
            }
            return $corr_var_data;
        }

        /**
         * si la valeur est de type numeric
         * pas de risque d'attaque
         */
        if (is_numeric($var_data)) {
            return $var_data;
        }
        /**
         * valeurs exception
         * pb avec les acl : valeur 'all'
         * voir pour faire autrement
         */
        /*
            if (in_array(strtolower($var_data),array('all','on','off','true','false','null')))  {
        return $var_data;
        }
        */
        /**
         * si c'est une column du schema de base, la protection ce fait a l'insertion
         * sauf pour les cles primaires
         * pour faire simple ... tout ce qui commence par ID_
         *
         */
        if ($column = Bdo_Cfg::getVar('schema')->getColumn($var_name))
        {

            if ('ID_' == substr($column->COLUMN_NAME,0,3)) {
                if (!($error = self::verifData($column,$var_data))) {
                    return $var_data;
                }
            }
            /**
             * quelque soit la colonne si elle contient des caracteres potentiellement dangereux
             *
             * a voir
             */
            /*
                else if (preg_match('#[?+*{}%=<>();]+#',$var_data)) {
            $corr_var_data = $var_data;
            }
            */
            else {
                return $var_data;
            }
        }
        /**
         * controle obligatoire des champs de la white liste sur valeur possible declarees
         *
         */
        if (!$error and isset($a_whiteList[$var_name])
                and !($error = self::verifData($a_whiteList[$var_name],$var_data))) {
            return $var_data;
        }
        /**
         * si on arrive jusqu'ici et que la variable
         * ne contient pas de caracteres potentiellement dangereux
         */
        if (!preg_match('#[?*{}=<>]+#',$var_data)) {
            //      if (!$error and !preg_match('#[?+*{}%=<>();]+#',$var_data)) {
            return $var_data;
        }
        else {
            $error = '<div style="background-color: #FFFFFF;"><font color=red>['.$var_name.'] : valeur passée non-autorisée ! ['.htmlPrepaTexte($var_data).']</font></div>';
        }

        if (!$error) {
            $error = '<div style="background-color: #FFFFFF;"><font color=red>['.$var_name.'] : contôles insuffisants Security Class.</font></div>';
        }
        cfg::quit($error);

        return false;
    }


    public function verifData ($column,$data)
    {
        $error = "";

        //le type doit correspondre avec la valeur
        // vérification par type
        switch ($column->DATA_TYPE)
        {
            case 'timestamp' :
            case 'date' :
            case 'datetime' :
                {
                    if (!($corr_data = Verif_DateHeure($data))) {
                        $error = '[ '.$column->COLUMN_NAME.' ] : '.LANG_INSERTERROR4;
                    }
                    break;
                }
            case 'enum' :
                {
                    if (in_array($data,$column->TAB_CHECK_VALUE)) {
                        $corr_data = $data;
                    }
                    else {
                        $error = '[ '.$column->COLUMN_NAME.' ] = [ '.$data.' ] : '.LANG_INSERTERROR5;
                    }
                    break;
                }
            case 'decimal' :
                {
                    if (is_numeric($data)) {
                        $retourVerifMaxNumValue = $this->verifDecimal($column,$data);
                        if ( $retourVerifMaxNumValue === true) {
                            $corr_data = "'".$data."'";
                        }
                        else {
                            $error = '[ '.$column->COLUMN_NAME.' ] : '.$retourVerifMaxNumValue;
                        }
                        unset($retourVerifMaxNumValue);
                    }
                    else {
                        $error = '[ '.$column->COLUMN_NAME.' ] : '.LANG_INSERTERROR6;
                    }

                    break;
                }
            case 'float' :
            case 'real' :
            case 'int' :
            case 'tinyint' :
            case 'bigint' :
            case 'smallint' :
            case 'mediumint' :
                {
                    if (is_numeric($data)) {
                        $retourVerifMaxNumValue = self::verifInteger($column,$data);
                        if ( $retourVerifMaxNumValue === true) {
                            $corr_data = $data;
                        }
                        else {
                            $error = '[ '.$column->COLUMN_NAME.' ] : '.$retourVerifMaxNumValue;
                        }
                        unset($retourVerifMaxNumValue);
                    }
                    else {
                        $error = '[ '.$column->COLUMN_NAME.' ] : '.LANG_INSERTERROR6;
                    }

                    break;
                }
            case 'time' :
                if (!($corr_data = Verif_Time($data))) {
                    $error = '[ '.$column->COLUMN_NAME.' ] : '.LANG_INSERTERROR7;
                }
                break;
            case 'char' :
            case 'varchar' :
            case 'tinytext' :
            case 'text' :
            case 'mediumtext' :
            case 'longtext' :

                {
                    if (stristr($column->COLUMN_NAME,'EMAIL')) {
                        if (!self::is_mail($data)) {
                            $error = "[ ".$column->COLUMN_NAME." ] : ".LANG_INSERTERROR10;
                        }
                    }
                    if ($column->CHARACTER_MAXIMUM_LENGTH >= mb_strlen($data)) {
                        $corr_data = "'".Db_Escape_String($data)."'";
                    }
                    else {
                        $error = '[ '.$column->COLUMN_NAME.' ] : '.LANG_INSERTERROR11;
                    }

                    break;
                }
        }
        return $error;
    }


    public static function verifDecimal($column,$data)
    {
        $dataeurs = preg_replace("#decimal|\(|\)| unsigned#i", "$1", $column->COLUMN_TYPE);
        list($tot,$dec)= explode(",", $dataeurs);
        $unit = $tot - $dec;

        $max = str_pad('',$unit,'9').'.'.str_pad('',$dec,'9');
        if (stripos($column->COLUMN_TYPE,'unsigned'))
        {
            $sign = '[\+]?';
            $min=0;
        }
        else
        {
            $sign = '[\-+]?';
            $min='-'.$max;
        }

        if (preg_match( '/^'.$sign.'[0-9]{0,'.$unit.'}(\.[0-9]{1,'.$dec.'})?$/', $data))
        {
            return true;

        }
        else
        {
            return $min.' <= n <= '.$max;
        }
    }

    public static function verifInteger($column,$data)
    {
        $base = 256;

        switch(strtolower($column->DATA_TYPE))
        {
            case 'tinyint' : $expo = 1; break;
            case 'smallint' : $expo = 2; break;
            case 'mediumint' : $expo = 3; break;
            case 'int' : $expo = 4; break;
            case 'bigint' : $expo = 8; break;
        }

        if (stripos($column->COLUMN_TYPE,'unsigned'))
        {
            $min = 0;
            $max = (pow($base,$expo))-1;
        }
        else
        {
            $min = -(pow($base,$expo)/2);
            $max = (pow($base,$expo)/2)-1;
        }

        if (($min <= $data) and ($max >=$data)) {
            return true;

        }
        else {
            return $min.' <= n <= '.$max;
        }
    }
}

?>