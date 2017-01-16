<?php

/**
 *
 * @author laurent
 *
 */
class Bdo_Cfg
{

    private static $instance;

    public function __construct ()
    {}

    public static function getInstance ()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __toString ()
    {
        Bdo_Cfg::getInstance();
        var_dump_pre(self::$instance);
    }

    public static function setVar ($var, $val)
    {
        Bdo_Cfg::getInstance();
        self::$instance->$var = $val;
    }

    public static function getVar ($var)
    {
        Bdo_Cfg::getInstance();
        if (isset(self::$instance->$var)) {
            return self::$instance->$var;
        }
        else {
            return null;
        }
    }

    public static function quit ($msg = '')
    {
        Bdo_Cfg::getInstance();

        if (isset(self::$instance->connexion)) self::$instance->connexion->close();

        if ($msg) {
            header('Content-Type: text/html; charset=UTF-8');
            exit($msg);
        }
        else {
            exit();
        }
    }

    public static function Db_connect ()
    {
        Bdo_Cfg::getInstance();

        if (self::$instance->connexion) Db_close();

        self::$instance->a_connect_vars = array(
                'login' => BDO_DB_USER,
                'password' => BDO_DB_PWD,
                'sid' => BDO_DB_SID,
                'server' => BDO_DB_HOST
        );

        if ($connexion = Db_connect(self::$instance->a_connect_vars)) {
            self::$instance->connexion = $connexion;
            $connexion->query("SET NAMES 'utf8'");
        }
        else {
            $to = 'thanaos@bdovore.com';
            $from = CFG_MAIL_AUTO_NAME . '<' . CFG_MAIL_AUTO_EMAIL . '>';

            $headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n";
            ;

            $msg = 'Alerte serveur MySQL ' . CFG_DB_HOST . ' - connexion impossible - ' . date('Y-m-d H:i') . print_r($_SERVER, true);

            mail($to, CFG_NAME . ' : ALERTE CONNEXION MySQL - ' . CFG_DB_HOST, $msg, $headers);

            Bdo_Cfg::quit('cfg : connection database error.');
        }
        return true;
    }

    public static function user ()
    {
        Bdo_Cfg::getInstance();
        if (isset(self::$instance->user)) {
            return self::$instance->user;
        }
        else {
            return false;
        }
    }

    public static function schema ()
    {
        Bdo_Cfg::getInstance();
        if (isset(self::$instance->schema)) {
            return self::$instance->schema;
        }
        else {
            return false;
        }
    }

    public static function debug ()
    {
        Bdo_Cfg::getInstance();
        if (isset(self::$instance->debug)) {
            return self::$instance->debug;
        }
        else {
            return false;
        }
    }

    public static function log ($msg = null, $type = null)
    {
        if (! defined('CFG_LOGFILE_ACTIVE') or ! CFG_LOGFILE_ACTIVE or ! defined('CFG_LOGFILE_FILENAME') or ! CFG_LOGFILE_FILENAME) {
            return false;
        }

        $line = date('Ymd H:i:s') . ' => ';

        if ($msg) {
            $line .= (string) $msg;

            if ($type) {
                $line .= ' => ';
            }
        }

        switch ($type) {
            case 'soap':
                {
                    $line .= $_SERVER['REMOTE_ADDR'] . " - " . $_SERVER['REQUEST_URI'] . " - " . $_SERVER['HTTP_SOAPACTION'];
                    break;
                }
            case 'apache':
                {
                    $line .= $_SERVER['REMOTE_ADDR'] . " - " . $_SERVER['REQUEST_URI'];
                    break;
                }
            default:
                {
                    break;
                }
        }
        if (file_put_contents(CFG_LOGFILE_FILENAME, $line . "\n", FILE_APPEND)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * affichage de variable
     *
     * @param unknown_type $var
     * @param string $mode
     * @param string $var_name
     */

    public static function pre ($var,$var_name=null)
    {
        echo "<pre style='background-color:#ffffff'>---------------";
        if ($var_name) echo 'Variable : '.$var_name.' -> '."\n";
        print_r($var);
        echo "</pre>";

    }
}
