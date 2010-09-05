<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CoreException extends \Exception { }

class Core
{
	/**
	 * @var Framework\Core
	 */
	protected static $_instance = null;
	
	/**
	 * @var Framework\Request
	 */
	protected $_request = null;
	
	/**
	 * @var Framework\Response
	 */
	protected $_response = null;
	
	/**
	 * Contains models already loaded
	 * 
	 * @var Array
	 */
	protected $_models = array();
	
	
	protected function __construct() { }
	
	/**
	 * Returns the unique instance of Framework\Core
	 * 
	 * @return Framework\Core
	 */
	public static function getInstance ()
	{
		if (Core::$_instance === null)
		{
			Core::$_instance = new Core();
		}
		return Core::$_instance;
	}
	
	protected function __clone () { }
	
	
	public function loadConfig (Array $autoload = array(), Array $config = array())
	{
		// Autoload
		Utils\ClassLoader::init($autoload);
		spl_autoload_register(array('\\Framework\\Utils\\ClassLoader', 'loadClass'));
		
		// Config
		Config::loadConfig($config);
		
		// Routes
		Utils\Route::init(Config::$config['routes']);
		
		return $this;
	}


	public function init (Request $request, Response $response, Context $context)
	{		
		// Timezone
		date_default_timezone_set(Config::$config['defaultTimezone']);
		
		// Views variables
		View::setGlobal('layout', Config::$config['defaultLayout']);
		View::setGlobal('message', Session::getInstance('message'));
		
		$this->_request = $request;
		$this->_response = $response;		
		$this->_context = $context;
		
		return $this;
	}
	
	/**
	 * Load the action $action, from the module $module. Shortcut for ClassLoader::loadController()
	 * 
	 * @param string $module
	 * @param string $action
	 * @return Framework\Controller
	 */
	public static function loadAction($module, $action)
	{
		return Utils\ClassLoader::loadController($module, $action);
	}
	
	/**
	 * Load the model $model, from the module $module. Shortcut for ClassLoader::loadModel()
	 * 
	 * @param string $module
	 * @param string $model
	 * @return Framework\Model
	 */
	public static function loadModel($module, $model)
	{
		return Utils\ClassLoader::loadModel($module, $model);
	}
	
	/**
	 * Main execution method
	 * 
	 * @return Framework\Core
	 */
	public function execute()
	{
		$this->_response->setBody($this->_request->execute());
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
			View::setGlobal('contentForLayout', $this->_response->getBody());
			$this->_response->clearResponse();
			$this->_response->setBody(View::factory(View::getGlobal('layout')));
		}
		
		$this->_response->send();
		echo $this->_response;
		
		if ($this->_response->getStatus() == 200)
		{
			$this->_context->updateHistory(array(
				'url' => $_context->getUrl(),
				'ipAddress' => $_context->getIpAddress(),
				'userAgent' => $_context->getUserAgent()
				));
		}
		
		exit();
	}
	
	/**
	 * Returns the response corresponding to the main request
	 * 
	 * @return Framework\Response
	 */
	public function getResponse()
	{
		return $this->_response;
	}
	
	/**
	 * Returns the main request
	 * 
	 * @return Framework\Request
	 */
	public function getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Returns the context
	 * 
	 * @return Framework\Context
	 */
	public function getContext()
	{
		return $this->_context;
	}
}