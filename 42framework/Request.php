<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RequestException extends \Exception { }

class Request
{
	public $module = null;
	
	public $controller = null;

	public $action = null;

	public $params = array();
	
	public $isInternal = true;
	
	public static $url = null;
	
	public static $ipAddress = '0.0.0.0';
	
	public static $userAgent = null;
	
	public static $acceptCharset = null;
	
	public static $acceptLanguage = null;
	
	public static $acceptEncoding = null;
	
	public static $isSecure = false;
	
	public static $method = 'GET';
	
	public static $isAjax = false;
	
	public static $protocol = 'http';
	
	public static $isCli = false;
	
	protected static $current = null;

	protected static $instance = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($module, $action, $params, $internal = true)
	{
		$this->module = $module;
		$this->controller = Utils\ClassLoader::getController($module, $action);
		$this->action = $action;
		$this->params = $params;
		$this->isInternal = $internal;
		Request::$current = $this;
	}

	protected function __clone () { }

	public static function getInstance ()
	{
		if (Request::$instance === null)
		{
			if (PHP_SAPI === 'cli')
			{
				$params = Modules\Cli\CliUtils::extractParams();
				Request::$instance = Request::factory('cli', $params['action'], $params['params']);
				Request::$isCli = true;
			}
			else 
			{
				Request::$url = $_GET['url'];
				$params = Route::urlToParams(Request::$url);
				
				Request::$instance = Request::factory($params['module'], $params['action'], $params['params'], false);
				
				if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				{
					Request::$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				elseif (isset($_SERVER['HTTP_CLIENT_IP']))
				{
					Request::$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
				}
				elseif (isset($_SERVER['REMOTE_ADDR']))
				{
					Request::$ipAddress = $_SERVER['REMOTE_ADDR'];
				}
				
				if (!filter_var(Request::$ipAddress, FILTER_VALIDATE_IP))
				{
					Request::$ipAddress = '0.0.0.0';
				}
				
				Request::$userAgent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? null : $_SERVER['HTTP_USER_AGENT'];
				
				Request::$method = (!isset($_SERVER['REQUEST_METHOD'])) ? 'GET' : $_SERVER['REQUEST_METHOD'];
				
				Request::$acceptCharset = (!isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? Config::$config['defaultCharset'] : Request::$instance->extractValue(
					$_SERVER['HTTP_ACCEPT_CHARSET']);
				
				Request::$acceptEncoding = (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? null : Request::$instance->extractValue($_SERVER['HTTP_ACCEPT_ENCODING']);
				
				Request::$acceptLanguage = (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? Config::$config['defaultLanguage'] : Request::$instance->extractValue(
					$_SERVER['HTTP_ACCEPT_LANGUAGE']);
				
				Request::$isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
				
				Request::$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
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
				$params = Route::pathToParams(func_get_arg(0));
				$params['internal'] = true;
				break;
			case 2:
				$params = Route::pathToParams(func_get_arg(0));
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
		$module = Core::loadModule(Request::$current->module, Request::$current->controller);
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