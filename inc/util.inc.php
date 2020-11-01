<?php

// charge automatiquement la classe 'Bdo_Name' à partir de 'library/Bdo/Name.php'
// (ou, plus généralement, 'Foo_Name' de 'library/Foo/Name.php')
// grâce à cette fonction, les classes sont chargées automatiquement lorsqu'elles sont appellées
// par exemple le premier appel à Bdo_Cfg déclenche un "require_once library/Bdo/Cfg.php"
function __autoload_classes ($class_name)
{
    //$a_className = explode('_', $class_name);
    //$classFile = implode(DS, $a_className);
    // "__Bdo_Name_" --> "Bdo/Name"
    $classFile = str_replace( '_', DS, trim($class_name, '_') );
    $file = BDO_DIR . 'library' . DS . $classFile . '.php';

    if (is_file($file)) {
        require_once $file;
    } else {
        //echo "Erreur : classe manquante: <b>" . htmlentities($class_name) . "</b><br/>" ;
        return false;
    }
}

// __autoload est 'deprecated', il est recommandé d'utiliser spl_autoload_register() à la place
spl_autoload_register('__autoload_classes');

function htmlPrepaTexte($val)
{
    return htmlspecialchars($val, ENT_QUOTES, "UTF-8");
}

function NlToBrWordWrap($txt,$long=0,$hpt=true)
{
    if (0==$long)
    {
        $long=BDO_WORDWRAP_LENGTH;
    }
    if ($hpt)
        $txt = nl2br(htmlPrepaTexte(wordwrap($txt,$long,"\n",false)));
    else
        $txt = nl2br(wordwrap($txt,$long,"\n",false));
    return $txt;
}

function fr_date($val,$type=false)
{
    if ($type == false)
    {
        switch(strlen($val))
        {
            case 8 :
            case 10 : // cas sans heure
                $type = 'date';
                break;
            case 19 : // cas avec l'heure
                $type = 'datetime';
                break;
            default  :
                {
                    return $val;
                }
        }
    }
    switch ($_SESSION['ID_LANG'])
    {
        case '_FR';
        switch($type)
        {
            case 'date'  : // cas sans heure
                $val = substr($val,0,10);
                $tab_j = explode('-', $val);
                if (3 != count($tab_j)) return $val;
                $date = $tab_j[2].'/'.$tab_j[1].'/'.$tab_j[0];
                break;
            case 'datetime'  : // cas avec l'heure
            case 'timestamp'     : // cas avec l'heure
                $tab_j_h = explode(" ", $val);
                if (2 != count($tab_j_h)) return $val;
                $tab_j = explode('-', $tab_j_h[0]);
                if (3 != count($tab_j)) return $val;
                $date = $tab_j[2].'/'.$tab_j[1].'/'.$tab_j[0].' '.$tab_j_h[1];
                break;
            default  :
                {
                    $date = $val;
                }
        }
        break;
        default  :
            switch($type)
            {
                case 'date'  : // cas sans heure
                    $date = substr($val,0,10);
                    break;
                case 'timestamp'     : // cas avec l'heure
                case 'datetime'  : // cas avec l'heure
                default  :
                    {
                        $date = $val;
                    }
            }

    }

    return $date;
}
function affFormatByteDown($value, $limes = 6, $comma = 0)
{
    $dh           = PMA_pow(10, $comma);
    $li           = PMA_pow(10, $limes);
    $return_value = $value;


    // shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
    if ("_EN" == $_SESSION['ID_LANG'])
        $byteUnits = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB');
    else
        $byteUnits = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo');


    $unit         = $byteUnits[0];

    for ($d = 6, $ex = 15; $d >= 1; $d--, $ex-=3) {
        if (isset($byteUnits[$d]) && $value >= $li * PMA_pow(10, $ex)) {
            // use 1024.0 to avoid integer overflow on 64-bit machines
            $value = round($value / (PMA_pow(1024, $d) / $dh)) /$dh;
            $unit = $byteUnits[$d];
            break 1;
        } // end if
    } // end for

    if ($unit != $byteUnits[0]) {
        $return_value = number_format($value, $comma, '.', ' ');
    } else {
        $return_value = number_format($value, 0, '.', ' ');
    }

    return $return_value.' '.$unit;
}
/**
 * Exponential expression / raise number into power
 *
 * @uses    function_exists()
 * @uses    bcpow()
 * @uses    gmp_pow()
 * @uses    gmp_strval()
 * @uses    pow()
 * @param   number  $base
 * @param   number  $exp
 * @param   string  pow function use, or false for auto-detect
 * @return  mixed  string or float
 */
function PMA_pow($base, $exp, $use_function = false)
{
    static $pow_function = null;
    if (null == $pow_function) {
        if (function_exists('bcpow')) {
            // BCMath Arbitrary Precision Mathematics Function
            $pow_function = 'bcpow';
        } elseif (function_exists('gmp_pow')) {
            // GMP Function
            $pow_function = 'gmp_pow';
        } else {
            // PHP function
            $pow_function = 'pow';
        }
    }

    if (! $use_function) {
        $use_function = $pow_function;
    }

    switch ($use_function) {
        case 'bcpow' :
            $pow = bcpow($base, $exp);
            break;
        case 'gmp_pow' :
            $pow = gmp_strval(gmp_pow($base, $exp));
            break;
        case 'pow' :
            $base = (float) $base;
            $exp = (int) $exp;
            if ($exp < 0) {
                return false;
            }
            $pow = pow($base, $exp);
            break;
        default:
            $pow = $use_function($base, $exp);
    }

    return $pow;
}

function TransConstante($txt)
{
    $tab_const = array();
    $tab = explode('{{',$txt);
    foreach ($tab as $val)
    {
        if (mb_strpos($val,'}}') !== false)
        {
            $const = mb_substr($val,0,mb_strpos($val,'}}'));
            $tab_const[$const] = $const;
        }
    }
    foreach ($tab_const as $const)
    {
        if (defined($const) and (in_array($const,cfg::getVar('a_constVisible')) or (stristr($const,'LANG_'))))
        {
            if ($const != 'CFG_DATE')
            {
                $val_const = constant($const);
            }
            else
            {
                $val_const = fr_date(constant($const),'date');
            }
            $txt = mb_ereg_replace('{{'.$const.'}}',$val_const,$txt);
        }
        elseif (strpos($const,'#') === 0)
        {
            $varName = mb_substr($const,1);
            $val_const =  $GLOBALS[$varName];
            $txt = mb_ereg_replace('{{'.$const.'}}',$val_const,$txt);

        }
        elseif (strpos($const,'url::') === 0)
        {
            $url = mb_substr($const,5);

            $val_const = '';
            $curl = new CURL();
            $opts = array(
                    CURLOPT_TIMEOUT         =>5,
                    CURLOPT_CONNECTTIMEOUT  =>5,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_FOLLOWLOCATION  => false
            );

            $curl->addSession( $url, $opts );
            $page = $curl->execSingle();

            if ($page = $curl->execSingle())
            {
                if (stripos($page, '<body') !== false) {
                    $content = preg_match('#<body([^>]*)>(.+)</body>#isU',$page,$matches);
                    $val_const = preg_replace(array('#<body([^>]*)>#isU','#</body>#isU'),'',$matches[0]);
                }
                else {
                    $val_const = $page;
                }
            }
            else {
                $val_const = 'erreur : page ['.url.'] non-trouvée !';
            }
            $curl->clear();

            $txt = mb_ereg_replace('{{'.$const.'}}',$val_const,$txt);
        }
    }
    return $txt;
}

function protectAttack ($GLOBALVAR)
{
    // mots interdits
    $a_motInterdit = array(
            'SELECT ',
            'INSERT ',
            'DROP ',
            'UPDATE ',
            '=',
            '<',
            '>',
            '/*',
            '*/'
    );
    // exceptions
    $a_motExcept = array(
            '<N/A>',
            '<indéterminé>',
            '<n&b>',
            '<Bichromie>',
            '<Quadrichromie>',
            '<Aucun>'
    );

    // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
    $toAlert = "laurent.mignot@gmail.com";
    $subjectAlert = "BDovore Alerte";

    $headersAlert = 'MIME-Version: 1.0' . "\r\n";
    $headersAlert .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

    if (! isset($_SESSION["UserLevel"]) or ($_SESSION["UserLevel"] > 1)) {
        if (issetNotEmpty(${$GLOBALVAR})) {
            foreach (${$GLOBALVAR} as $key => $val) {
                if (strlen($val) > 15) {
                    foreach ($a_motInterdit as $mot) {
                        if ((stristr($val, $mot)) and ! in_array($val, $a_motExcept)) {

                            ob_start();
                            echo "\n------" . $GLOBALVAR . "------\n\n";
                            var_dump(${$GLOBALVAR});
                            echo "\n\n------_SERVER------\n";
                            var_dump($_SERVER);
                            $dump = ob_get_clean();
                            echo "";

                            $message = nl2br(
                                    '<pre>Mot ou caractère interdit rencontré : [' . $mot . '] dans [' .
                                             htmlspecialchars($val, ENT_QUOTES) . ']' . "\n" . date('d/m/Y H:i:s') . "\n" .
                                             htmlspecialchars($dump, ENT_QUOTES) . "</pre>");

                            mail($toAlert, $subjectAlert, $message, $headersAlert);
                            exit(
                                    'Mot ou caractère interdit rencontré : [' . $mot . '] dans [' . htmlspecialchars($val, ENT_QUOTES) . ']
                        <br />un mail a été envoyé aux administrateurs pour étudier votre requête.<br />
                        Utilisez le forum pour signaler un problème persistant.
                        <br /><br />Utilisez la fonction "Page précédente" de votre navigateur.');
                        }
                    }
                }
            }
        }
    }
}

function stripSlUtf8 ($data)
{
    if (get_magic_quotes_gpc()) {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = stripSlUtf8($val);
            }
        }
        else {
            $data = stripslashes($data);
            if (is_utf8($data)) $data = utf8_decode($data);
        }
    }

    return $data;
}

/**
 * Add slashes before "'" and "\" characters so a value containing them can
 * be used in a sql comparison.
 *
 * @uses str_replace()
 * @param
 *            string the string to slash
 * @param
 *            boolean whether the string will be used in a 'LIKE' clause
 *            (it then requires two more escaped sequences) or not
 * @param
 *            boolean whether to treat cr/lfs as escape-worthy entities
 *            (converts \n to \\n, \r to \\r)
 *
 * @param
 *            boolean whether this function is used as part of the
 *            "Create PHP code" dialog
 *
 * @return string the slashed string
 *
 * @access public
 */
function PMA_sqlAddslashes ($a_string = '', $is_like = false, $crlf = false, $php_code = false)
{
    if ($is_like) {
        $a_string = str_replace('\\', '\\\\\\\\', $a_string);
    }
    else {
        $a_string = str_replace('\\', '\\\\', $a_string);
    }

    if ($crlf) {
        $a_string = str_replace("\n", '\n', $a_string);
        $a_string = str_replace("\r", '\r', $a_string);
        $a_string = str_replace("\t", '\t', $a_string);
    }

    if ($php_code) {
        $a_string = str_replace('\'', '\\\'', $a_string);
    }
    else {
        $a_string = str_replace('\'', '\'\'', $a_string);
    }

    return $a_string;
} // end of the 'PMA_sqlAddslashes()' function
function issetNotEmpty (&$var)
{
    if (isset($var) and ! empty($var)) {
        return true;
    }
    return false;
}

function notIssetOrEmpty (&$var)
{
    if (! isset($var) or empty($var)) {
        return true;
    }
    return false;
}

function getVal ($nomvar, $default = '')
{
    $val = isset($_GET[$nomvar]) ? $_GET[$nomvar] : $default;

    // Si les Magic Quotes sont activées, retirer les "\" en trop avant de passer à la moulinette
    // NB: les Magic Quotes n'existent plus pour PHP >= 5.4.0
    /*if (get_magic_quotes_gpc() ) {
        if (is_array($val))
            return array_map('stripslashes',$val);//NB: non-recursif --> suppose que $_GET['nom'] est un array simple
        else
            return stripslashes($val);
    } else {
        return $val;
    }
     * 
     */
    return $val;
}

function getValInteger ($nomvar, $default = 0)
{
    $val = isset($_GET[$nomvar]) ? $_GET[$nomvar] : $default;

    if (is_array($val))
        return array_map('intval',$val); //NB: non recursif --> suppose que $_GET['nom'] est un array simple
    else
        return intval($val);
}

function postVal ($nomvar, $default = '')
{
    $val = isset($_POST[$nomvar]) ? $_POST[$nomvar] : $default;

    // Si les Magic Quotes sont activées, retirer les "\" en trop avant de passer à la moulinette
    // NB: les Magic Quotes n'existent plus pour PHP >= 5.4.0
    if (get_magic_quotes_gpc()) {
        if (is_array($val))
            return array_map('stripslashes',$val);//NB: non-recursif --> suppose que $_POST['nom'] est un array simple
        else
            return stripslashes($val);
    } else {
        return $val;
    }
}

function postValInteger ($nomvar, $default = 0)
{
    $val = isset($_POST[$nomvar]) ? $_POST[$nomvar] : $default;

    if (is_array($val))
        return array_map('intval',$val); //NB: non recursif --> suppose que $_POST['nom'] est un array simple
    else
        return intval($val);
}

function var_dump_pre ($var)
{
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

function echo_pre ($var)
{
    echo "<pre>";
    echo $var;
    echo "</pre>";
}


function tabOfFetchObj ($resultat, $columnKey = '')
{
    $tab_obj = array();
    while ($obj = Db_fetch_object($resultat)) {
        if (empty($columnKey)) $tab_obj[] = $obj;
        else
            $tab_obj[$obj->$columnKey][] = $obj;
    }
    Db_free_result($resultat);
    return $tab_obj;
}

function firstObjectArray ($data)
{
    if (is_array($data)) {
        return firstObjectArray(current($data));
    }
    else if (is_object($data)) {
        return $data;
    }
    else {
        return false;
    }
}

function tableOfFetchObj ($data, $a_onlyCol = array(), $specialChar = true)
{
    if (is_array($data)) {
        if (0 == count($data)) return false;
        $tab_objVar = get_object_vars(firstObjectArray($data));
        foreach ($tab_objVar as $key => $val) {
            if (! stristr($key, 'bgcolor_')) {
                $tab_col[]->name = $key;
            }
        }
        $tab_obj = $data;
    }
    else {
        $tab_col = mysqli_fetch_fields($data);
        $tab_obj = array();
        while ($obj = Db_fetch_object($data)) {
            $tab_obj[] = $obj;
        }
        Db_free_result($data);
    }
    if (! empty($a_onlyCol)) {
        foreach ($tab_col as $key => $val) {
            if (! in_array($val->name, $a_onlyCol)) {
                unset($tab_col[$key]);
            }
        }
    }

    echo '<table border=1 cellpadding=2 cellspacing=1 class=scp>
    <tr>';
    foreach ($tab_col as $val) {
        echo "<td valign=top class='entete_admin'>" . $val->name . "</td>";
    }
    echo "</tr>";
    $l = 0;
    foreach ($tab_obj as $key => $obj) {
        if (is_array($obj)) {
            echo '<tr>';
            echo "<td  class='data_admin' colspan=" . count($tab_col) . " valign=top>" . $key;
            tableOfFetchObj($obj);
            echo "</td>";
        }
        else {
            echo '<tr>';

            foreach ($tab_col as $key => $val) {
                echo "<td  class='data_admin' valign=top>";
                if (isset($obj->{$val->name})) {
                    if ('voir' == $val->name) echo $obj->{$val->name};
                    else
                        echo ($specialChar ? htmlspecialchars($obj->{$val->name}) : $obj->{$val->name});
                }
                else {
                    echo '&nbsp;';
                }
                echo "</td>";
            }
        }
        echo "</tr>";
    }

    echo '</table>';
    return $tab_obj;
}

function zeroFill ($Num, $lentgh = 2)
{
    return str_pad($Num, $lentgh, '0', STR_PAD_LEFT);
}

function Verif_Time ($Time)
{
    $Tableau = array();
    $timeOk = '';

    $Tableau = explode(':', $Time);
    if (1 > count($Tableau)) {
        $Tableau = explode('-', $Date);
        if (1 > count($Tableau)) {
            return false;
        }
    }

    if (is_numeric($Tableau[0]) and ($Tableau[0] < 24)) {
        $Heure = zeroFill($Tableau[0]);
    }
    else {
        return false;
    }

    if (is_numeric($Tableau[1]) and ($Tableau[1] < 60)) {
        $Minute = zeroFill($Tableau[1]);
    }
    else {
        return false;
    }

    if (3 == count($Tableau)) {
        if (is_numeric($Tableau[2]) and ($Tableau[2] < 60)) {
            $Seconde = zeroFill($Tableau[2]);
        }
        else {
            $Seconde = '00';
        }
    }

    $timeOk = $Heure . ':' . $Minute;

    if (isset($Seconde)) $timeOk .= ':' . $Seconde;

    return $timeOk;
}

/**
 * explodeDate
 *
 * explose une date en fonction du separateur et retourne un tableau avec les
 * elements de date dans l'ordre array(aaaa,mm,dd)
 *
 * @param string $date
 * @return array
 */
function explodeDate ($s_date)
{
    $a_date = explode('-', $s_date);

    if (2 > count($a_date)) {
        $a_date = explode('/', $s_date);
        if (2 > count($a_date)) {
            return false;
        }
        $a_temp[2] = $a_date[0];
        $a_temp[1] = $a_date[1];
        $a_temp[0] = $a_date[2];
        $a_date = $a_temp;
    }

    return $a_date;
}

function Verif_Date ($Date)
{
    $Tableau = explodeDate($Date);

    if (is_numeric($Tableau[2]) ) {
        $Jour = zeroFill($Tableau[2]);

    }
    else {
        return false;
    }

    if (is_numeric($Tableau[1]) ) {
        $Mois = zeroFill($Tableau[1]);
    }
    else {
        return false;
    }

    if (2 == count($Tableau)) {
        if (($Mois + 0) <= date('n')) $Tableau[0] = date('Y') + 1;
        else
            $Tableau[0] = date('Y');
    }

    if (is_numeric($Tableau[0])) {
        if (strlen($Tableau[0]) <= 2) {
            $Tableau[0] = zeroFill($Tableau[0]);

            if (49 > $Tableau[0]) $Annee = '20' . $Tableau[0];
            else
                $Annee = '19' . $Tableau[0];
        }
        elseif (strlen($Tableau[0]) == 4) {
            $Annee = $Tableau[0];
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }

    $Date_SQL = $Jour . '/' . $Mois . '/' . $Annee;

    if (checkdate($Mois, $Jour, $Annee) || ($Mois==0 and $Jour==0)) {
        return $Date_SQL;
    }
    else {
        return false;
    }
}

function VerifierAdresseMail ($adresse)
{
    $Syntaxe = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,5}$#';
    if (preg_match($Syntaxe, $adresse)) return true;
    else
        return false;
}

function Verif_DateHeure ($val)
{
    if (stristr($val, ' '))     // cas date heure
    {
        $tab = explode(' ', $val);
        $dateVal = Verif_Date($tab[0]);
        $timeVal = Verif_Time($tab[1]);

        if ($dateVal and $timeVal) {
            return $dateVal . ' ' . $timeVal;
        }
    }
    else if (stristr($val, ':') === true)     // cas heure seule
    {
        return Verif_Time($val);
    }
    else     // cas date seule
    {
        return Verif_Date($val);
    }

    return false;
}

function PrepaDate ($val)
{
    if ($val = Verif_DateHeure($val)) {
        switch (strlen($val)) {
            case 8:
                $to_date = "STR_TO_DATE('" . $val . "', '%d/%m/%y')";
                break;
            case 10:
                $to_date = "STR_TO_DATE('" . $val . "', '%d/%m/%Y')";
                break;
            case 16:
                $to_date = "STR_TO_DATE('" . $val . "', '%d/%m/%Y %H:%i')";
                break;
            case 19:
                $to_date = "STR_TO_DATE('" . $val . "', '%d/%m/%Y %H:%i:%s')";
                break;
            default:
                {
                    return false;
                }
        }
        return $to_date;
    }
    else {
        return false;
    }
}

function PrepaTime ($val)
{
    if ($val = Verif_Time($val)) {
        return "TIME_FORMAT('" . $val . "', '%H:%i:%s')";
    }
    else {
        return false;
    }
}

function TimestampDate ($date)
{
    if (Verif_DateHeure($date)) {
        $a_date = explodeDate($date);
    }
    else {
        return false;
    }

    return mktime((isset($a_date[3]) ? $a_date[3] : 0), (isset($a_date[4]) ? $a_date[4] : 0), (isset($a_date[5]) ? $a_date[5] : 0),
            (isset($a_date[1]) ? $a_date[1] : 0), (isset($a_date[2]) ? $a_date[2] : 0), (isset($a_date[0]) ? $a_date[0] : 0));
}

function translate_date ($val, $type = false)
{
    if ($a_date = explodeDate($val)) {
        switch ($_SESSION['ID_LANG']) {
            case 'fr':
                {
                    $s_date = (isset($a_date[2]) ? $a_date[2] : '') . ((isset($a_date[1]) and isset($a_date[2])) ? '/' : '') .
                             (isset($a_date[1]) ? $a_date[1] : '') . ((isset($a_date[0]) and isset($a_date[1])) ? '/' : '') .
                             (isset($a_date[0]) ? $a_date[0] : '') .
                             (((isset($a_date[0]) or isset($a_date[1]) or isset($a_date[2])) and
                             (isset($a_date[3]) or isset($a_date[4]) or isset($a_date[5]))) ? ' ' : '') .
                             (isset($a_date[3]) ? $a_date[3] : '') . ((isset($a_date[3]) and isset($a_date[4])) ? ':' : '') .
                             (isset($a_date[4]) ? $a_date[4] : '') . ((isset($a_date[4]) and isset($a_date[5])) ? ':' : '') .
                             (isset($a_date[5]) ? $a_date[5] : '');
                    break;
                }

            default:
                {
                    $s_date = (isset($a_date[0]) ? $a_date[0] : '') . ((isset($a_date[1]) and isset($a_date[0])) ? '-' : '') .
                             (isset($a_date[1]) ? $a_date[1] : '') . ((isset($a_date[2]) and isset($a_date[1])) ? '-' : '') .
                             (isset($a_date[2]) ? $a_date[2] : '') .
                             (((isset($a_date[0]) or isset($a_date[1]) or isset($a_date[2])) and
                             (isset($a_date[3]) or isset($a_date[4]) or isset($a_date[5]))) ? ' ' : '') .
                             (isset($a_date[3]) ? $a_date[3] : '') . ((isset($a_date[3]) and isset($a_date[4])) ? ':' : '') .
                             (isset($a_date[4]) ? $a_date[4] : '') . ((isset($a_date[4]) and isset($a_date[5])) ? ':' : '') .
                             (isset($a_date[5]) ? $a_date[5] : '');
                }
        }
        return $s_date;
    }
    return false;
}

function completeDate ($date)
{
    if(substr($date,-2) === "-0" ) {
        $date = substr($date,0,strlen($date)-2)."-00";
    }
    if (stristr($date, '-00')) {
        $date = str_replace('-00', '-01', $date);
    }
    if (stristr($date, '00/')) {
        $date = str_replace('00/', '01/', $date);
    }

    if (preg_match('#^[0-9]{4}\-[0-9]{2}$#', $date)) {
        $date .= '-01';
    }
    if (preg_match('#^[0-9]{2}\/[0-9]{4}$#', $date)) {
        $date = '01/' . $date;
    }
    if (preg_match('#^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$#', $date)) {
        $date = date("Y-m-d", TimestampDate($date));
    }

    return $date;
}
// vérification de date valide
function validateDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
function detectUTF8 ($str)
{
    $c = 0;
    $b = 0;
    $bits = 0;
    $len = strlen($str);
    for ($i = 0; $i < $len; $i ++) {
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c >= 254)) return false;
            elseif ($c >= 252) $bits = 6;
            elseif ($c >= 248) $bits = 5;
            elseif ($c >= 240) $bits = 4;
            elseif ($c >= 224) $bits = 3;
            elseif ($c >= 192) $bits = 2;
            else
                return false;
            if (($i + $bits) > $len) return false;
            while ($bits > 1) {
                $i ++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                $bits --;
            }
        }
    }
    return true;
}

function is_utf8 ($string)
{
    if (is_string($string)) {
        return preg_match(
                '%(?:
[\\xC2-\\xDF][\\x80-\\xBF] # non-overlong 2-byte
|\\xE0[\\xA0-\\xBF][\\x80-\\xBF] # excluding overlongs
|[\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2} # straight 3-byte
|\\xED[\\x80-\\x9F][\\x80-\\xBF] # excluding surrogates
|\\xF0[\\x90-\\xBF][\\x80-\\xBF]{2} # planes 1-3
|[\\xF1-\\xF3][\\x80-\\xBF]{3} # planes 4-15
|\\xF4[\\x80-\\x8F][\\x80-\\xBF]{2} # plane 16
)+%xs', $string);
    }
    else {
        echo "erreur sur detection utf8 : [" . $string . "]";
    }
    return false;
}

