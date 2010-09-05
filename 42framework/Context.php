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
	
	/**
	 * @var \Framework\History
	 */
	protected $_history = null;
	
	protected $_historySize = null;
	
	protected static $_isInit = null;

	protected static $_instance = null;
	
	protected function __construct ($history)
	{		
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
	
		if (!filter_var($this->_ipAddress, FILTER_VALIDATE_IP))
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
		
		$this->_url = $_GET['url'];
		
		$this->_history = $history;
	}
	
	protected function __clone () { }

	/**
	 * @return \Framework\Context
	 */
	public static function getInstance (History $history = null)
	{
		if (Context::$_instance === null)
		{
			if ($history === null)
			{
				throw new ContextException('Invalid params');
			}
			Context::$_instance = new Context($history);
    	}
    	return Context::$_instance;
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
	
	public function getHistory ()
	{
		return $this->_history->get();
	}
	
	/**
	 * @return \Framework\History
	 */
	public function getHistoryInstance()
	{
		return $this->_history;
	}

	public function getPreviousUrl ()
	{
		$previous = $this->_history->getPrevious();
		return $previous['url'];
	}

	public function getPreviousIpAddress ()
	{
		$previous = $this->_history->getPrevious();
		return $previous['ipAddress'];
	}

	public function getPreviousUserAgent ()
	{
		$previous = $this->_history->getPrevious();
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
}