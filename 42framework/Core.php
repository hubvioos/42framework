<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CoreException extends \Exception { }

class Core
{
	/**
	 * @var Framework\Core
	 */
	protected static $instance = null;
	
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
	protected $models = array();
	
	/**
	 * Contains modules already loaded
	 * 
	 * @var Array
	 */
	protected $modules = array();
	
	
	protected function __construct() { }
	
	/**
	 * Returns the unique instance of Framework\Core
	 * 
	 * @return Framework\Core
	 */
	public static function getInstance ()
	{
		if (self::$instance === null)
		{
			self::$instance = new Core();
		}
		return self::$instance;
	}
	
	protected function __clone () { }
	
	/**
	 * Inits the application
	 * 
	 * @param array $config
	 * @param Framework\Request $request
	 * @param Framework\Response $response
	 * @return Framework\Core
	 */
	public function init (Array $config = array())
	{
		Config::loadConfig($config);
		Utils\Route::init(Config::$config['routes']);
		View::setGlobal('layout', Config::$config['defaultLayout']);
		return $this;
	}
	
	/**
	 * Load the main request in the Core instance
	 * @param Framework\Request $request
	 * @return Framework\Core
	 */
	public function setRequest(Request $request)
	{
		if (!($request instanceof \Framework\Request))
		{
			throw new CoreException('setRequest : Param given is not a valid Request.');
		}
		$this->request = $request;
		return $this;
	}
	
	/**
	 * Load the main response in the Core instance
	 * @param Framework\Response $response
	 * @return Framework\Core
	 */
	public function setResponse(Response $response)
	{
		if (!($response instanceof \Framework\Response))
		{
			throw new CoreException('setResponse : Param given is not a valid Response.');
		}
		$this->response = $response;
		return $this;
	}
	
	/**
	 * Load the module $module, if it isn't already loaded
	 * 
	 * @param string $module
	 * @return Framework\Controller
	 */
	public static function loadModule ($module, $action)
	{
		$module = Utils\ClassLoader::getControllerClassName($module, $action);
		
		if (!isset(self::$instance->modules[$module]))
		{
			self::$instance->modules[$module] = new $module;
		}
		return self::$instance->modules[$module];
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
		$model = Utils\ClassLoader::getModelClassName($module, $model);
		
		if (!isset(self::$instance->models[$model]))
		{
			self::$instance->models[$model] = new $model;
		}
		return self::$instance->models[$model];
	}
	
	/**
	 * Main execution method
	 * 
	 * @return Framework\Core
	 */
	public function execute ()
	{
		$this->response->setBody($this->request->execute());
		return $this;
	}
	
	/**
	 * Render the request (send headers and display the response)
	 * 
	 * @param Framework\Response $response (optional)
	 */
	public function render($response = null)
	{
		if ($response !== null)
		{
			$this->setResponse($response);
		}
		
		if (View::getGlobal('layout') !== false)
		{
			if (View::getGlobal('layout') === null)
			{
				View::setGlobal('layout', Config::$config['defaultLayout']);
			}
			View::setGlobal('contentForLayout', $this->response->getBody());
			$this->response->clearResponse();
			$this->response->setBody(View::factory(View::getGlobal('layout')));
		}
		
		$this->response->send();
		echo $this->response;
		
		exit();
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
	
	/**
	 * Returns the main request
	 * 
	 * @return Framework\Request
	 */
	public function getRequest ()
	{
		return $this->request;
	}
}