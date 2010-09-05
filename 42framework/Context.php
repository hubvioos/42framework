<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ContextException extends \Exception { }

class Context
{    
	protected $_url = null;
	
    protected $_ipAddress = '0.0.0.0';
	
	protected $_userAgent = null;
	
	protected $_acceptCharset = null;
	
	protected $_acceptLanguage = null;
	
	protected $_acceptEncoding = null;
	
	protected $_isAjax = false;
	
	protected $_isSecure = false;
	
	protected $_isCli = false;
	
	protected $_historySize = null;

	protected static $_instance = null;
	
	protected function __construct ()
	{
		if (PHP_SAPI === 'cli')
		{
			$this->_isCli = true;
		}
		
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$this->_ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$this->_ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (isset($_SERVER['REMOTE_ADDR']))
		{
			$this->_ipAddress = $_SERVER['REMOTE_ADDR'];
		}
	
		if (!filter_var(Context::$ipAddress, FILTER_VALIDATE_IP))
		{
			$this->_ipAddress = '0.0.0.0';
		}
	
		$this->_userAgent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? null : $_SERVER['HTTP_USER_AGENT'];
	
		$this->_acceptCharset = (!isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? Config::$config['defaultCharset'] : $this->_extractValue(
			$_SERVER['HTTP_ACCEPT_CHARSET']);
	
		$this->_acceptEncoding = (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? null : $this->_extractValue($_SERVER['HTTP_ACCEPT_ENCODING']);
	
		$this->_acceptLanguage = (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? Config::$config['defaultLanguage'] : $this->_extractValue(
			$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	
		$this->_isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
	
		$this->_isAjax = (isset($_SERVER['HTTP_X_ContextED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		
		$this->_historySize = Config::$config['historySize'];
	}
	
	protected function __clone () { }

	public static function getInstance ()
	{
		if (Context::$_instance === null)
		{
			$this->_instance = new Context();
    	}
    	return $this->_instance;
    }

	protected function _extractValue ($str)
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
	
	public function updateHistory ()
	{
		$lastUrl = end(Session::getInstance('history')->session);
		$lastUrl = $lastUrl['url'];
		
		if ($_url != $lastUrl)
		{
			Session::getInstance('history')->session[] = array(
				'url' => $this->_url,
				'ipAddress' => $this->_ipAddress,
				'userAgent' => $this->_userAgent
				);
				
			if (sizeof(Session::getInstance('history')->session) > $this->_historySize)
			{
				array_shift(Session::getInstance('history')->session);
			}
		}
	}
	
	public function getHistory()
	{
		return Session::getInstance('history')->session;
	}
	
	public function getPreviousUrl()
	{
		end(Session::getInstance('history')->session);
		$previous = prev(Session::getInstance('history')->session);
		return $previous['url'];
	}
	
	public function getPreviousIpAddress()
	{
		end(Session::getInstance('history')->session);
		$previous = prev(Session::getInstance('history')->session);
		return $previous['ipAddress'];
	}
	
	public function getPreviousUserAgent()
	{
		end(Session::getInstance('history')->session);
		$previous = prev(Session::getInstance('history')->session);
		return $previous['userAgent'];
	}
	
	public function getUrl ()
	{
		return $this->_url;
	}
	
	public function getIpAddress ()
	{
		return $this->_ipAddress;
	}
	
	public function getUserAgent ()
	{
		return $this->_userAgent;
	}
	
	public function getAcceptCharset ()
	{
		return $this->_acceptCharset;
	}
	
	public function getAcceptLanguage ()
	{
		return $this->_acceptLanguage;
	}
	
	public function getAcceptEncoding ()
	{
		return $this->_acceptEncoding;
	}
	
	public function isSecure ()
	{
		return $this->_isSecure;
	}
	
	public function isAjax ()
	{
		return $this->_isAjax;
	}
	
	public function isCli ()
	{
		return $this->_isCli;
	}
}