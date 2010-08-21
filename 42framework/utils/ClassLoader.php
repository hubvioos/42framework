<?php namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

use \Framework as F;

class ClassLoaderException extends Exception { }

class ClassLoader
{
    protected $autoload;
    
    protected static $actionsMap;
    
	public function __construct(Array $autoload = array(), Array $actionsMap = array())
	{
	    $this->autoload = $autoload;
	    self::$actionsMap = $actionsMap;
	}
	
	public function load ($className)
	{
		if (!isset($this->autoload[$className]))
		{
			throw new ClassLoaderException($className.' doesn\'t exist in autoload configuration. Try recompile autoload.');
		}
		require $this->autoload[$className];
	}
	
	public static function getController ($module, $action)
	{
		if (!isset(self::$actionsMap[$module][$action]))
		{
			throw new ClassLoaderException('Controller for '.$action.'action in '.$module.' module is not set. Try recompile actionsMap.');
		}
		return self::$actionsMap[$module][$action];
	}
}