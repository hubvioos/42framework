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
	
	protected $_method = 'GET';
	
	protected $_url = null;
	
    protected static $_current = null;

	protected static $_instance = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($_module, $_action, $_params, $_internal = true)
	{
		$this->_module = $_module;
		$this->_action = $_action;
		$this->_params = $_params;
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
			}
			else
			{
				$this->_url = $_GET['url'];

				$path = Utils\Route::urlToPath($this->_url, Config::$config['defaultModule'], Config::$config['defaultAction']);
				$params = Utils\Route::pathToParams($path);

				// Redirect to root if we use the default module and action.
				if ($this->url != '' 
				    && $params['module'] == Config::$config['defaultModule']
				    && $params['action'] == Config::$config['defaultAction']
				    && empty($params['params'])
				    )
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'], 301, true);
				}
				// Avoid duplicate content of the routes.
				else if ($this->_url != Utils\Route::pathToUrl($path)
					&& $this->_url != '')
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'] . Utils\Route::pathToUrl($path), 301, true);
				}
								
				// Avoid duplicate content with just a "/" after the URL
				if(strrchr($this->url, '/') === '/')
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'] . rtrim($this->url, '/'), 301, true);  
				}
				
        		$this->method = (!isset($_SERVER['REQUEST_METHOD'])) ? 'GET' : $_SERVER['REQUEST_METHOD'];
				
				$context = Context::getInstance();
				
				$context->updateHistory();
				
				if (! Utils\Security::checkHistory($context->getHistory()))
				{
					/* Here ::  Destroy session, set message, regenerate ID */
					Session::destroyAll();
					
					Response::getInstance()->redirect(Config::$config['siteUrl'], 301, true);
				}
				
				Request::$_instance = Request::factory($params['module'], $params['action'], $params['params'], false);
			}
		}
		return Request::$instance;
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
		return Request::$current;
	}
	
	public function execute ()
	{
		$module = Core::loadAction(Request::$current->module, Request::$current->action);
		return $module->execute(Request::$current);
	}
	
	public function isInternal ()
	{
		return $this->_isInternal;
	}
}
