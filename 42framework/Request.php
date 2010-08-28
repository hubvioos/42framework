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
	
	protected static $current = null;

	protected static $instance = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à l'instance de Router.
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
			if (Core::getInstance()->isCli())
			{
				$params = Modules\Cli\CliUtils::extractParams();
				Request::$instance = Request::factory('cli', $params['action'], $params['params']);
			}
			else 
			{
				Request::$url = $_GET['url'];
				$params = Route::extractParams(Route::path(Request::$url));
				
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
				
				Request::$acceptCharset = (!isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? Config::$config['defaultCharset'] : $this->extractValue(
					$_SERVER['HTTP_ACCEPT_CHARSET']);
				
				Request::$acceptEncoding = (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? null : $this->extractValue($_SERVER['HTTP_ACCEPT_ENCODING']);
				
				Request::$acceptLanguage = (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? Config::$config['defaultLanguage'] : $this->extractValue(
					$_SERVER['HTTP_ACCEPT_LANGUAGE']);
				
				Request::$isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
				
				Request::$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
			}
		}
		return Request::$instance;
	}

	public static function factory ($module, $action, $params, $internal = true)
	{
		return new Request($module, $action, $params, $internal);
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