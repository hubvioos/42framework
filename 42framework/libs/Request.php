<?php
namespace framework\libs;

class Request
{
	protected $Response = null;
	protected $request = null;
	protected $module = null;
    protected $action = null;
    protected $params = array();
    protected $context = array(
    	'ipAddress' => null,
    	'userAgent' => null,
    	'acceptCharset' => array(),
    	'acceptLanguage' => array(),
    	'acceptEncoding' => array(),
    	'isSecure' => false,
    	'requestMethod' => null
    );
    protected $historySize = 6;
    protected static $instance = null;
	
	protected function __construct($historySize = 6)
	{
		$this->request = $_GET['url'];
		$routedRequest = Router::routeToApp($this->request, true);
		
		$class = '\app\modules\\'.$routedRequest['module'];
    	
    	if(class_exists($class))
    	{	
    		$this->module = new $class();
    		
    		if(method_exists($this->module, $routedRequest['action']))
    		{
    			if(!empty($routedRequest['params']))
    			{
    				$this->params = $routedRequest['params'];
    			}
    			else
    			{
    				$this->params = array();
    			}
    			
    			$this->action = $routedRequest['action'];
    			$this->module->checkParams($this->action, $this->params);
    		}
    		else
    		{
    			if(Registry::get('envMode') === 'dev')
    			{
    				throw new Exception('La méthode n\'existe pas !');
    			}
    			
    			$module = '\app\modules\\'.Registry::get('defaultModule');
    			$this->module = new $module();
    			$this->action = 'error404';
    			$this->params = array($routedRequest);
    		}
    	}
    	else
    	{
    		if(Registry::get('envMode') === 'dev')
    		{
    			throw new Exception('La classe n\'existe pas !');
    		}
    		
    		$module = '\app\modules\\'.Registry::get('defaultModule');
    		$this->module = new $module();
    		$this->action = 'error404';
    		$this->params = array($routedRequest);
    	}
    	
    	$this->determineContext();
    	
    	$this->historySize = $historySize;
    	
    	if(!isset($_SESSION['history']))
    	{
    		$_SESSION['history'] = array();
    	}
    	
    	$this->updateHistory();
	}
	
	protected function __clone() { }
	
	public static function getInstance($historySize = 6) {
		if(!isset(self::$instance))
        {
        	self::$instance = new self($historySize);
        }
        
        return self::$instance;
	}
	
	public function __get($var) {
		return $this->$var;
	}
	
	public function reload()
	{
		Response::getInstance()->status(303)->location(APP_BASE_URL.$this->getLastUrl())->send();
		return $this;
	}
	
	public function back()
	{
		Response::getInstance()->status(303)->location(APP_BASE_URL.$this->getPreviousUrl())->send();
		return $this;
	}
	
	protected function updateHistory()
	{
		if($this->context['requestMethod'] === 'GET')
		{
			if($this->request === '')
			{
				$this->request = '/';
			}
			
			if(sizeof($_SESSION['history']) >= 0 || $this->request !== $this->getLastUrl())
			{
				$_SESSION['history'][] = array('url' => $this->request, 'ipAddress' => $this->context['ipAddress']);
				
				if(sizeof($_SESSION['history']) > $this->historySize)
				{
					array_shift($_SESSION['history']);
				}
			}
		}
	}
	
	public function getHistory()
	{
		if(isset($_SESSION['history']))
		{
			return $_SESSION['history'];
		}
		
		return array();
	}
	
	public function getPreviousUrl()
	{
		end($_SESSION['history']);
		$url = prev($_SESSION['history']);
		return $url['url'];
	}
	
	public function getPreviousIpAddress()
	{
		end($_SESSION['history']);
		$url = prev($_SESSION['history']);
		return $url['ipAddress'];
	}

	public function getLastUrl()
	{
		$url = end($_SESSION['history']);
		return $url['url'];
	}
	
	public function getLastIpAddress()
	{
		$url = end($_SESSION['history']);
		return $url['ipAddress'];
	}
	
	public function determineContext() {
		$this->context['ipAddress'] = $this->getIpAddress();
		$this->context['userAgent'] = $this->getUserAgent();
		$this->context['acceptCharset'] = $this->getAcceptCharset();
		$this->context['acceptLanguage'] = $this->getAcceptLanguage();
		$this->context['acceptEncoding'] = $this->getAcceptEncoding();
		$this->context['isSecure'] = $this->isSecure();
		$this->context['requestMethod'] = $this->getRequestMethod();
	}
	
	public function getIpAddress()
	{
		if ($this->context['ipAddress'] !== null)
		{
			return $this->context['ipAddress'];
		}
		
		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (isset($_SERVER['REMOTE_ADDR']))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($ip === null)
		{
			$ip = '0.0.0.0';
			return $ip;
		}

		if (!$this->isValidIp($ip))
		{
			$ip = '0.0.0.0';
		}

		return $ip;
	}
	
	public function isValidIp($ip)
	{
		if(filter_var($ip, FILTER_VALIDATE_IP))
		{
			return true;
		}
		
		return false;
	}

	public function getUserAgent()
	{
		if ($this->context['userAgent'] !== null)
		{
			return $this->context['userAgent'];
		}

		return (!isset($_SERVER['HTTP_USER_AGENT'])) ? null : $_SERVER['HTTP_USER_AGENT'];
	}
	
	public function getRequestMethod()
	{
		if ($this->context['requestMethod'] !== null)
		{
			return $this->context['requestMethod'];
		}

		return (!isset($_SERVER['REQUEST_METHOD'])) ? null : $_SERVER['REQUEST_METHOD'];
	}
	
	public function getAcceptCharset()
	{
		if ($this->context['acceptCharset'] !== null)
		{
			return $this->context['acceptCharset'];
		}

		return (!isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? Registry::get('defaultCharset') : $this->extractValue($_SERVER['HTTP_ACCEPT_CHARSET']);
	}
	
	public function getAcceptEncoding()
	{
		if ($this->context['acceptEncoding'] !== null)
		{
			return $this->context['acceptEncoding'];
		}

		return (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? null : $this->extractValue($_SERVER['HTTP_ACCEPT_ENCODING']);
	}
	
	public function getAcceptLanguage()
	{
		if ($this->context['acceptLanguage'] !== null)
		{
			return $this->context['acceptLanguage'];
		}

		return (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? Registry::get('defaultLanguage') : $this->extractValue($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	}
	
	public function isSecure()
	{
		if ($this->context['isSecure'] !== null)
		{
			return $this->context['isSecure'];
		}

		return (!isset($_SERVER['HTTPS'])) ? false : true;
	}
	
	protected function extractValue($str) {
		$arr = array();

		if (sizeof($str) > 0)
		{
			foreach (explode(',', $str) as $v)
			{
				if (preg_match('#^\s*([^;]+)(?:;q=([0-9]+\.[0-9]+))?$#', $v, $match))
				{
					$arr[] = $match[1];
				}
			}
		}

		return $arr;
	}
}
?>