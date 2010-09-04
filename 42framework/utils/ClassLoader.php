<?php
namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ClassLoaderException extends \Exception { }

class ClassLoader
{
	protected static $_autoload;
	
	protected static $_models = array();

	public static function init(Array $autoload = array())
	{
		if (empty($autoload))
		{
			require FRAMEWORK_DIR . DS . 'config' . DS . 'autoload.php';
		}
		ClassLoader::$_autoload = $autoload;
	}

	/**
	 * Load the file containing $className.
	 * 
	 * @param string $className
	 */
	public static function loadClass($className)
	{
		$className = strtolower($className);
		if (!isset(ClassLoader::$_autoload[$className]))
		{
			throw new ClassLoaderException(__METHOD__.' : '.$className.' doesn\'t exist in autoload configuration. Try recompile autoload.');
		}
		require ClassLoader::$_autoload[$className];
	}

	/**
	 * Load the action $action, from the module $module.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return Framework\Controller
	 */
	public static function loadController($module, $action)
	{
		if (empty($module) || empty($action))
		{
			throw new ClassLoaderException(__METHOD__.' : Missing argument.');
		}
		
		$controller = 'Application\\modules\\'.$module.'\\controllers\\'.$action;
		return new $controller;
	}

	/**
	 * Load the model $model, from the module $module, if it isn't already loaded
	 * 
	 * @param string $module
	 * @param string $model
	 * @return Framework\Model
	 */
	public static function loadModel($module, $model)
	{
		if (empty($module) || empty($model))
		{
			throw new ClassLoaderException(__METHOD__.' : Missing argument.');
		}
		
		$model = 'Application\\modules\\'.$module.'\\models\\'.$model;
		if (!isset(ClassLoader::$_models[$model]))
		{
			ClassLoader::$_models[$model] = new $model;
		}
		return ClassLoader::$_models[$model];
	}
}