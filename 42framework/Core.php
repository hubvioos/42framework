<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CoreException extends Exception { }

class Core
{
	/**
	 * @var Framework\Core
	 */
	private static $instance = null;
	
	/**
	 * @var Framework\Request
	 */
	protected $request = null;
	
	/**
	 * @var Framework\Response
	 */
	protected $response = null;
	
	/**
	 * Contains models already loaded
	 * 
	 * @var Array
	 */
	protected static $models = array();
	
	/**
	 * Contains modules already loaded
	 * 
	 * @var Array
	 */
	protected static $modules = array();
	
	/**
	 * @param Framework\Request $request
	 * @param Framework\Response $response
	 */
	protected function __construct (Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}
	
	/**
	 * Returns the unique instance of Framework\Core
	 * 
	 * @param Framework\Request $request
	 * @param Framework\Response $response
	 * @return Framework\Core
	 */
	public static function getInstance ($request, $response)
	{
		if (self::$instance === null)
		{
			self::$instance = new self($request, $response);
		}
		return self::$instance;
	}
	
	protected function __clone () { }
	
	/**
	 * Inits the application
	 * 
	 * @param array $config
	 * @return Framework\Core
	 */
	public function init (Array $config = array())
	{
		Config::loadConfig($config);
		return $this;
	}
	
	/**
	 * Load the module $module, if it isn't already loaded
	 * 
	 * @param string $module
	 * @return Framework\Controller
	 */
	public static function loadModule ($module, $controller = 'MainController')
	{
		$module = '\Application\modules\\'.$module.'\controllers\\'.$controller;
		
		if (!isset(self::$modules[$module]))
		{
			self::$modules[$module] = new $module;
		}
		return self::$modules[$module];
	}
	
	/**
	 * Load the model $model, from the module $module, if it isn't already loaded
	 * 
	 * @param string $module
	 * @param string $model
	 * @return Framework\Model
	 */
	public static function loadModel ($module, $model)
	{
		$model = '\Application\modules\\'.$module.'\models\\'.$model;
		
		if (!isset(self::$models[$model]))
		{
			self::$models[$model] = new $model;
		}
		return self::$models[$model];
	}
	
	/**
	 * Main execution method
	 */
	public function execute ()
	{
		$this->response->setBody($this->request->execute());
		
		if ($this->response->getGlobalVar('layout') !== false)
		{
			if ($this->response->getGlobalVar('layout') === null)
			{
				$this->response->setGlobalVar('layout', Config::$config['defaultLayout']);
			}
			$this->response->setGlobalVar('contentForLayout', $this->response->getBody());
			$this->response->setBody(View::factory($this->response->getGlobalVar('layout'), $this->response->getGlobalsVars()));
		}
		return $this;
	}
	
	/**
	 * Returns the response corresponding to the main request
	 * 
	 * @return Framework\Response
	 */
	public function getResponse ()
	{
		return $this->response;
	}
}