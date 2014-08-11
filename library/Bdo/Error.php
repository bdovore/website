<?php

/**
 *
 * @author laurent
 *        
 */
class Bdo_Error
{
public $a_error=array();
    private static $instance;

    public function __construct ()
    {}

    public static function getInstance ()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __toString ()
    {
        Bdo_Error::getInstance();
        var_dump_pre(self::$instance);
    }

    public static function flush ()
    {
        Bdo_Error::getInstance();
        return self::$instance->a_error;
    }

    public static function add ($value)
    {
        Bdo_Error::getInstance();
        
        if (empty($value)) return true;
        
        if (is_array($value)) {
            self::$instance->a_error = array_merge(self::$instance->a_error, $value);
        }
        else {
            self::$instance->a_error[] = $value;
        }
    }

    public static function notEmpty ()
    {
        Bdo_Error::getInstance();
        
        if (self::$instance->a_error) {
            return true;
        }
        else {
            return false;
        }
    }
}