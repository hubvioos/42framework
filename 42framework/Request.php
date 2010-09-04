<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RequestException extends \Exception { }

class Request
{
	public $module = null;

	public $action = null;

	public $params = array();
	
	public $isInternal = true;
	
	public static $method = 'GET';
	
    protected static $current = null;

	protected static $instance = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($module, $action, $params, $internal = true)
	{
		$this->module = $module;
		$this->action = $action;
		$this->params = $params;
		$this->isInternal = $internal;
		Request::$current = $this;
	}

	protected function __clone () { }

	public static function getInstance ()
	{
	    $path = null;
	    
		if (Request::$instance === null)
		{
			if (PHP_SAPI === 'cli')
			{
				$params = \Application\modules\cli\CliUtils::extractParams();
				Request::$instance = Request::factory('cli', $params['action'], $params['params']);
				Request::$isCli = true;
			}
			else 
			{
				Request::$url = $_GET['url'];

				$path = Utils\Route::urlToPath(Request::$url, Config::$config['defaultModule'], Config::$config['defaultAction']);
				$params = Utils\Route::pathToParams($path);

				// Redirect to root if we use the default module and action.
				if (Request::$url != '' 
				    && $params['module'] == Config::$config['defaultModule']
				    && $params['action'] == Config::$config['defaultAction']
				    && empty($params['params'])
				    )
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'], 301, true);
				}
				// Avoid duplicate content of the routes.
				else if (Request::$url != Utils\Route::pathToUrl($path)
					&& Request::$url != '')
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'] . Utils\Route::pathToUrl($path), 301, true);
				}
								
				// Avoid duplicate content with just a "/" after the URL
				if(strrchr(Request::$url, '/') === '/')
				{
				    Response::getInstance()->redirect(Config::$config['siteUrl'] . rtrim(Request::$url, '/'), 301, true);  
				}
	
				Request::$instance = Request::factory($params['module'], $params['action'], $params['params'], false);
				
        		Request::$method = (!isset($_SERVER['REQUEST_METHOD'])) ? 'GET' : $_SERVER['REQUEST_METHOD'];
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
		$module = Core::loadModule(Request::$current->module, Request::$current->action);
		return $module->execute(Request::$current);
	}
	
	public function isCli ()
	{
		return Request::$isCli;
	}

	protected function extractValue ($str)
	{
		$arr = array();
		
		if (sizeof($str) > 0)
		{
			foreach (explode(',', $str) as $v)
			{
				if (preg_match('#^\s*([^;]+)(?:;q=([0-9]+\.[0-9]+))?$#', $v, 
					$match))
				{
					$arr[] = $match[1];
				}
			}
		}
		return $arr;
	}
}
