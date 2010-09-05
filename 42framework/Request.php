<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RequestException extends \Exception { }

class Request
{
	protected $_module = null;

	protected $_action = null;

	protected $_params = array();
	
	protected $_isInternal = true;
	
	protected $_isCli = false;
	
	protected $_method = 'GET';
	
    protected static $_current = null;

	protected static $_instance = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($_module, $_action, $_params, $_internal = true, $_method = 'GET')
	{
		$this->_module = $_module;
		$this->_action = $_action;
		$this->_params = $_params;
		$this->_method = $_method;
		$this->_isInternal = $_internal;
		Request::$_current = $this;
	}

	protected function __clone () { }

	public static function getInstance ()
	{
		if (Request::$_instance === null)
		{
			if (PHP_SAPI === 'cli')
			{
				$params = \Application\modules\cli\CliUtils::extractParams();
				Request::$_instance = Request::factory('cli', $params['action'], $params['params']);
				$this->_isCli = true;
			}
			else
			{
				$context = Context::getInstance();
				$url = $context->getUrl();
				
				$path = Utils\Route::urlToPath($url, Config::$config['defaultModule'], Config::$config['defaultAction']);
				$params = Utils\Route::pathToParams($path);
				
				// Redirect to root if we use the default module and action.
				if ($url != '' 
				    && $params['module'] == Config::$config['defaultModule']
				    && $params['action'] == Config::$config['defaultAction']
				    && empty($params['params'])
				    )
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'], 301, true);
				}
				// Avoid duplicate content of the routes.
				else if ($url != Utils\Route::pathToUrl($path)
					&& $url != '')
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'] . Utils\Route::pathToUrl($path), 301, true);
				}
								
				// Avoid duplicate content with just a "/" after the URL
				if(strrchr($url, '/') === '/')
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'] . rtrim($url, '/'), 301, true);  
				}
				
				$previousIpAddress = $context->getPreviousIpAddress();
				$previousUserAgent = $context->getPreviousUserAgent();
							
				if ($previousIpAddress !== null 
					&& $previousIpAddress != $context->getIpAddress()
					&& $previousUserAgent !== null
					&& $previousUserAgent != $context->getUserAgent()
					)
				{
					Utils\Session::destroyAll();
					
					Utils\Message::add(Session::getInstance('message'),'warning',
						'It seems that your session has been stolen, we destroyed it for security reasons. Check your environment security.');
					
					Response::getInstance()->redirect(Config::$config['siteUrl'], 301, true);
				}
				
				Request::$_instance = Request::factory($params['module'], $params['action'], $params['params'], false);
			}
		}
		return Request::$_instance;
	}

	public static function factory ()
	{
		$params = array();
		switch (func_num_args())
		{
			case 1:
				$params = Utils\Route::pathToParams(func_get_arg(0));
				$params['internal'] = true;
				break;
			case 2:
				$params = Utils\Route::pathToParams(func_get_arg(0));
				$params['internal'] = func_get_arg(1);
				break;
			case 3:
				list($params['module'], $params['action'], $params['params']) = func_get_args();
				$params['internal'] = true;
				break;
			case 4:
				list($params['module'], $params['action'], $params['params'], $params['internal']) = func_get_args();
				break;
			default:
				throw new RequestException('Request::factory : invalid arguments');
		}
		return new Request($params['module'], $params['action'], $params['params'], $params['internal']);
	}
	
	public function getCurrent ()
	{
		return Request::$_current;
	}
	
	public function execute ()
	{
		$module = Core::loadAction(Request::$_current->_module, Request::$_current->_action);
		return $module->execute(Request::$_current);
	}
	
	/**
	 * @return the $_module
	 */
	public function getModule ()
	{
		return $this->_module;
	}

	/**
	 * @return the $_action
	 */
	public function getAction ()
	{
		return $this->_action;
	}

	/**
	 * @return the $_params
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	/**
	 * @return the $_method
	 */
	public function getMethod ()
	{
		return $this->_method;
	}

	public function isInternal ()
	{
		return $this->_isInternal;
	}

	public function isCli ()
	{
		return $this->_isCli;
	}
}
