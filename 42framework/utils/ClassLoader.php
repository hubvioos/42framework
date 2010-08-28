<?php namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

use \Framework as F;

class ClassLoaderException extends \Exception { }

class ClassLoader
{
    protected $autoload;
    
    protected static $actionsMap;
    
	public function __construct(Array $autoload = array(), Array $actionsMap = array())
	{
	    if (empty($autoload))
	    {
	    	require FRAMEWORK_DIR.DS.'config'.DS.'autoload.php';
	    }
		$this->autoload = $autoload;
	    self::$actionsMap = $actionsMap;
	}
	
	public function load ($className)
	{
		$className = strtolower($className);
		if (!isset($this->autoload[$className]))
		{
			throw new ClassLoaderException($className.' doesn\'t exist in autoload configuration. Try recompile autoload.');
		}
		require $this->autoload[$className];
	}
	
	public static function getControllerClassName ($module, $action)
	{
		if (empty($module) || empty($action))
		{
			throw new ClassLoaderException('getControllerName : Missing argument.');
		}
		if (!isset(self::$actionsMap[$module][$action]))
		{
			throw new ClassLoaderException('getControllerName : '.$action.' action in '.$module.' module doesn\'t exist in actionsMap. Try recompile it.');
		}
		return self::$actionsMap[$module][$action];
	}
	
	public static function getModelClassName ($module, $model)
	{
		return 'Application\modules\\'.$module.'\models\\'.$model;
	}
}