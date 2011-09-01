<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
 * 
 * 42framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * 42framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

namespace framework\core;

class HttpRequest extends \framework\core\FrameworkObject
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
	
	protected $_request = null;
	
	/**
	 * @var \Framework\History
	 */
	protected $_history = null;
	
	public function __construct (\framework\libs\History $history)
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
	
		$this->_acceptCharset = (!isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? $this->getConfig('defaultCharset') : $this->_extractValue(
			$_SERVER['HTTP_ACCEPT_CHARSET']);
	
		$this->_acceptEncoding = (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? null : $this->_extractValue($_SERVER['HTTP_ACCEPT_ENCODING']);
	
		$this->_acceptLanguage = (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $this->getConfig('defaultLanguage') : $this->_extractValue(
			$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	
		$this->_isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
	
		$this->_isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		
		$this->_url = (!isset($_GET['url'])) ? null : $_GET['url'];
				
		$this->_history = $history;
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
	
	public function updateHistory (Array $values = array())
	{
		if ($this->getUrl(true) != $this->getPreviousUrl())
		{
			return $this->_history->update($values);
		}
	}
	
	public function getHistory ()
	{
		return $this->_history->get();
	}

	public function getPreviousUrl ()
	{
		$previous = $this->_history->getPrevious();
		if ($previous !== null)
		{
			return (isset($previous['url'])) ? $previous['url'] : null;
		}
		
		return null;
	}

	public function getPreviousIpAddress ()
	{
		$previous = $this->_history->getPrevious();
		if ($previous !== null)
		{
			return (isset($previous['ipAddress'])) ? $previous['ipAddress'] : null;
		}
		
		return null;
	}

	public function getPreviousUserAgent ()
	{
		$previous = $this->_history->getPrevious();
		if ($previous !== null)
		{
			return (isset($previous['userAgent'])) ? $previous['userAgent'] : null;
		}
		
		return null;
	}
	
	public function getUrl ($absolute = false)
	{
		if ($absolute === false)
		{
			return $this->_url;
		}
		return $this->getConfig('siteUrl') . $this->_url;
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
	
	public function getRequest()
	{
		return $this->_request;
	}
	
	public function setRequest(Request $request)
	{
		$this->_request = $request;
	}
	
	public function isCli()
	{
		return $this->_isCli;
	}
	
	public function setCli($isCli)
	{
		$this->_isCli = $isCli;
	}
}