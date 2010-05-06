<?php
namespace framework\libs;

class Flash
{    
    protected static $previousFlash = array();
    protected static $instance = null;
    
    protected function __construct() {
    	if(!empty($_SESSION['flash']) && is_array($_SESSION['flash']))
        {
            self::$previousFlash = $_SESSION['flash'];
        }
        
        self::clear();
    }
    
    protected function __clone() {}
    
    public static function getInstance()
    {
    	if(!isset(self::$instance))
        {
        	self::$instance = new self();
        }
        
        return self::$instance;
    }

    public static function get($var)
    {
        return isset(self::$previousFlash[$var]) ? self::$previousFlash[$var] : null;
    }

    public static function set($var, $value)
    {
        $_SESSION['flash'][$var] = $value;
    }

    public static function clear()
    {
        $_SESSION['flash'] = array();
    }
}
?>