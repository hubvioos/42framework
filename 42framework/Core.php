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
	
	
	/**
	 * @param array $autoload
	 * @param array $config
	 * @return \Framework\Core
	 */
	public function init (Array $autoload = array(), Array $config = array())
	{
		// Autoload
		Utils\ClassLoader::init($autoload, FRAMEWORK_DIR.DS.'config'.DS.'autoload.php');
		
		// Config
		Utils\Config::init($config, FRAMEWORK_DIR.DS.'config'.DS.'config.php');
		
		ErrorHandler::getInstance()
			->start(Utils\Config::$config['errorReporting'], Utils\Config::$config['displayErrors'])
			->attach(new ErrorHandlerListeners\Html());
		
		// Routes
		Utils\Route::init(Utils\Config::$config['routes']);
		
		return $this;
	}


	public function bootstrap (Context $context, Response $response)
	{		
		if (PHP_SAPI === 'cli')
		{
			$params = \Application\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';
			
			$state = Request::CLI_STATE;
		}
		else
		{
			$url = $context->getUrl();
			
			$path = Utils\Route::urlToPath($url, Utils\Config::$config['defaultModule'], Utils\Config::$config['defaultAction']);
			$params = Utils\Route::pathToParams($path);
			
			$state = Request::FIRST_REQUEST;
			
			// Views variables
			View::setGlobal('layout', Utils\Config::$config['defaultLayout']);
			View::setGlobal('message', Utils\Session::getInstance('message'));
			
			if (!Utils\ClassLoader::canLoadClass('Application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				Request::factory('errors','error404',array(),Request::FIRST_REQUEST)->execute();
			}
			
			$this->duplicateContentPolicy($url, $path, $params);
			$this->requestSecurityPolicy($context);
		}
		// Timezone
		date_default_timezone_set(Utils\Config::$config['defaultTimezone']);
		
		$this->_context = $context;
		$this->_request = Request::factory($params['module'], $params['action'], $params['params'], $state);
		$this->_response = $response;
		
		return $this;
	}
	
	public function duplicateContentPolicy ($url, $path, $params)
	{
		// Redirect to root if we use the default module and action.
		if ($url != '' 
		    && $params['module'] == Utils\Config::$config['defaultModule']
		    && $params['action'] == Utils\Config::$config['defaultAction']
		    && empty($params['params'])
		    )
		{
		    Response::getInstance()->redirect(Utils\Config::$config['siteUrl'], 301, true);
		}
		// Avoid duplicate content of the routes.
		else if ($url != Utils\Route::pathToUrl($path)
			&& $url != '')
		{
		    Response::getInstance()->redirect(Utils\Config::$config['siteUrl'] . Utils\Route::pathToUrl($path), 301, true);
		}
						
		// Avoid duplicate content with just a "/" after the URL
		if(strrchr($url, '/') === '/')
		{
		    Response::getInstance()->redirect(Utils\Config::$config['siteUrl'] . rtrim($url, '/'), 301, true);  
		}
	}
	
	/**
	 * @param \Framework\Context $context
	 */
	public function requestSecurityPolicy ($context)
	{
		$previousIpAddress = $context->getPreviousIpAddress();
		$previousUserAgent = $context->getPreviousUserAgent();
					
		if ($previousIpAddress !== null 
			&& $previousIpAddress != $context->getIpAddress()
			&& $previousUserAgent !== null
			&& $previousUserAgent != $context->getUserAgent()
			)
		{
			Utils\Session::destroyAll();
			
			Utils\Message::add(Utils\Session::getInstance('message'),'warning',
				'It seems that your session has been stolen, we destroyed it for security reasons. Check your environment security.');
			
			Response::getInstance()->redirect(Utils\Config::$config['siteUrl'], 301, true);
		}
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
			$this->_response = $response;
		}
		
		if (View::getGlobal('layout') !== false)
		{
			if (View::getGlobal('layout') === null)
			{
				View::setGlobal('layout', Utils\Config::$config['defaultLayout']);
			}
			View::setGlobal('contentForLayout', $this->_response->getBody());
			$this->_response->clearResponse();
			$this->_response->setBody(View::factory(Utils\Config::$config['defaultModule'], View::getGlobal('layout')));
		}
		
		$this->_response->send();
		echo $this->_response;
		
		if ($this->_response->getStatus() == 200)
		{
			$this->_context->updateHistory(array(
				'url' => $this->_context->getUrl(),
				'ipAddress' => $this->_context->getIpAddress(),
				'userAgent' => $this->_context->getUserAgent()
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