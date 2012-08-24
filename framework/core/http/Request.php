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
 * 
 * 
 * Inspired by AlloyFramework
 */

namespace framework\core\http;

class Request extends \framework\core\FrameworkObject
{

	protected $_url = null;
	protected $_ipAddress = null;
	protected $_userAgent = null;
	protected $_acceptCharset = null;
	protected $_acceptLanguage = null;
	protected $_acceptEncoding = null;
	protected $_isAjax = null;
	protected $_isSecure = null;
	protected $_isCli = null;
	protected $_method = null;
	protected $_referrer = null;

	/**
	 * @var \Framework\History
	 */
	protected $_history = null;

	public function __construct (\framework\core\http\History $history)
	{
		$this->_history = $history;
	}

	public function updateHistory ()
	{
		if ($this->getUrl(true) != $this->getPreviousUrl())
		{
			$this->_history->update(array(
				'url' => $this->getUrl(true),
				'ipAddress' => $this->getIp(),
				'userAgent' => $this->getUserAgent()
			));
		}
	}

	public function getHistory ()
	{
		return $this->_history->get();
	}

	protected function _getPrevious ($var)
	{
		$previous = $this->_history->getPrevious();

		if ($previous['url'] == $this->getUrl(true))
		{
			$previous = $this->_history->get(1);
		}

		if ($previous !== null && isset($previous[$var]))
		{
			return $previous[$var];
		}

		return null;
	}

	public function getPreviousUrl ()
	{
		return $this->_getPrevious('url');
	}

	public function getPreviousIp ()
	{
		return $this->_getPrevious('ipAddress');
	}

	public function getPreviousUserAgent ()
	{
		return $this->_getPrevious('userAgent');
	}

	public function getUrl ($absolute = false)
	{
		if ($this->_url === null)
		{
			$this->_url = ($this->get('url', false)) ? $this->get('url') : '';
		}
		
		if ($absolute === false)
		{
			return $this->_url;
		}

		return $this->getConfig('siteUrl') . $this->_url;
	}

	/**
	 * Access values contained in the superglobals as public members
	 * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
	 *
	 * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
	 * @param string $key
	 * @return mixed
	 */
	public function get ($key, $default = null, $array = null)
	{
		$value = null;

		switch (true)
		{
			case ($array === null || $array == 'get') && !empty($_GET[$key]):
				$value = $_GET[$key];
				break;

			case ($array === null || $array == 'post') && !empty($_POST[$key]):
				$value = $_POST[$key];
				break;

			case ($array === null || $array == 'cookie') && !empty($_COOKIE[$key]):
				$value = $_COOKIE[$key];
				break;

			case ($array === null || $array == 'server') && !empty($_SERVER[$key]):
				$value = $_SERVER[$key];
				break;

			case ($array === null || $array == 'env') && !empty($_ENV[$key]):
				$value = $_ENV[$key];
				break;

			default:
				$value = $default;
		}

		// Key not found, default is being used
		if ($value === $default)
		{
			// Check for dot-separator (convenience access for nested array values)
			if (\strpos($key, '.') !== false)
			{
				// Get all dot-separated key parts
				$keyParts = \explode('.', $key);

				// Remove first key because we're going to start with it
				$keyFirst = \array_shift($keyParts);

				// Get root value array to begin
				$value = $this->get($keyFirst, $default, $array);

				// Loop over remaining key parts to see if value can be found in resulting array
				foreach ($keyParts as $keyPart)
				{
					if (is_array($value))
					{
						if (!empty($value[$keyPart]))
						{
							$value = $value[$keyPart];
						}
						else
						{
							$value = $default;
						}
					}
				}
			}
		}

		return $value;
	}

	// Automagic companion function
	public function __get ($key)
	{
		return $this->get($key);
	}

	/**
	 * Check to see if a property is set
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset ($key)
	{
		switch (true)
		{
			case isset($_GET[$key]):
				return true;
			case isset($_POST[$key]):
				return true;
			case isset($_COOKIE[$key]):
				return true;
			case isset($_SERVER[$key]):
				return true;
			case isset($_ENV[$key]):
				return true;
			default:
				return false;
		}
	}

	/**
	 * Retrieve a member of the $_GET superglobal
	 *
	 * If no $key is passed, returns the entire $_GET array.
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getFromGet ($key = null, $default = null)
	{
		if ($key === null)
		{
			// Return _GET params without routing param or other params set by Alloy or manually on the request object
			return \array_diff_key($_GET, array('url' => 1));
		}

		return $this->get($key, $default, 'get');
	}

	/**
	 * Retrieve a member of the $_POST superglobal
	 *
	 * If no $key is passed, returns the entire $_POST array.
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getFromPost ($key = null, $default = null)
	{
		if ($key === null)
		{
			return $_POST;
		}

		return $this->get($key, $default, 'post');
	}

	/**
	 * Retrieve a member of the $_COOKIE superglobal
	 *
	 * If no $key is passed, returns the entire $_COOKIE array.
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getFromCookie ($key = null, $default = null)
	{
		if ($key === null)
		{
			return $_COOKIE;
		}

		return $this->get($key, $default, 'cookie');
	}

	/**
	 * Retrieve a member of the $_SERVER superglobal
	 *
	 * If no $key is passed, returns the entire $_SERVER array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getFromServer ($key = null, $default = null)
	{
		if ($key === null)
		{
			return $_SERVER;
		}

		return $this->get($key, $default, 'server');
	}

	/**
	 * Retrieve a member of the $_ENV superglobal
	 *
	 * If no $key is passed, returns the entire $_ENV array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getFromEnv ($key = null, $default = null)
	{
		if ($key === null)
		{
			return $_ENV;
		}

		return $this->get($key, $default, 'env');
	}

	/**
	 * Return the value of the given HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param string $header HTTP header name
	 * @return string|false HTTP header value, or false if not found
	 */
	public function getHeader ($header, $default = false)
	{
		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . \strtoupper(\str_replace('-', '_', $header));
		if ($this->getFromServer($temp, false))
		{
			return $this->getFromServer($temp);
		}

		// This seems to be the only way to get the Authorization header on Apache
		if (\function_exists('apache_request_headers'))
		{
			$headers = \apache_request_headers();
			if (!empty($headers[$header]))
			{
				return $headers[$header];
			}
		}

		return $default;
	}

	/**
	 * Return the method by which the request was made
	 *
	 * @return string
	 */
	public function getMethod ()
	{
		if ($this->_method === null)
		{
			$sm = \strtoupper($this->getFromServer('REQUEST_METHOD', 'GET'));

			// POST + '_method' override to emulate REST behavior in browsers that do not support it
			if ($sm == 'POST' && $this->get('_method'))
			{
				$sm = \strtoupper($this->get('_method'));
			}

			$this->_method = $sm;
		}

		return $this->_method;
	}

	public function getIp ()
	{
		if ($this->_ipAddress === null)
		{
			$ip = false;

			if ($this->getFromServer('HTTP_CLIENT_IP', false))
			{
				$ip = $this->getFromServer('HTTP_CLIENT_IP');
			}

			if ($this->getFromServer('HTTP_X_FORWARDED_FOR', false))
			{
				// Put the IP's into an array which we shall work with shortly.
				$ips = \explode(', ', $this->getFromServer('HTTP_X_FORWARDED_FOR'));
				if ($ip)
				{
					\array_unshift($ips, $ip);
					$ip = false;
				}

				for ($i = 0; $i < \count($ips); $i++)
				{
					if (\filter_var($ips[$i], \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE ^ \FILTER_FLAG_NO_RES_RANGE))
					{
						$ip = $ips[$i];
						break;
					}
				}
			}

			$this->_ipAddress = ($ip ? $ip : ($this->getFromServer('REMOTE_ADDR', false)) ? $this->getFromServer('REMOTE_ADDR') : '0.0.0.0');
		}

		return $this->_ipAddress;
	}

	public function getUserAgent ()
	{
		if ($this->_userAgent === null)
		{
			$this->_userAgent = ($this->getFromServer('HTTP_USER_AGENT', false)) ? $this->getFromServer('HTTP_USER_AGENT') : null;
		}

		return $this->_userAgent;
	}

	public function getAcceptCharset ()
	{
		if ($this->_acceptCharset === null)
		{
			$this->_acceptCharset = ($this->getFromServer('HTTP_ACCEPT_CHARSET', false)) ? $this->_extractValue(
							$this->getFromServer('HTTP_ACCEPT_CHARSET')) : $this->getConfig('defaultCharset');
		}

		return $this->_acceptCharset;
	}

	public function getAcceptLanguage ()
	{
		if ($this->_acceptLanguage === null)
		{
			$this->_acceptLanguage = ($this->getFromServer('HTTP_ACCEPT_LANGUAGE', false)) ? $this->_extractValue(
							$this->getFromServer('HTTP_ACCEPT_LANGUAGE')) : $this->getConfig('defaultLanguage');
		}

		return $this->_acceptLanguage;
	}

	public function getAcceptEncoding ()
	{
		if ($this->_acceptEncoding === null)
		{
			$this->_acceptEncoding = ($this->getFromServer('HTTP_ACCEPT_ENCODING', false)) ? $this->_extractValue($this->getFromServer('HTTP_ACCEPT_ENCODING')) : null;
		}

		return $this->_acceptEncoding;
	}

	/**
	 * 	Determine is incoming request is POST
	 *
	 * 	@return boolean
	 */
	public function isPost ()
	{
		return ($this->getMethod() == 'POST');
	}

	/**
	 * 	Determine is incoming request is GET
	 *
	 * 	@return boolean
	 */
	public function isGet ()
	{
		return ($this->getMethod() == 'GET');
	}

	/**
	 * 	Determine is incoming request is PUT
	 *
	 * 	@return boolean
	 */
	public function isPut ()
	{
		return ($this->getMethod() == 'PUT');
	}

	/**
	 * 	Determine is incoming request is DELETE
	 *
	 * 	@return boolean
	 */
	public function isDelete ()
	{
		return ($this->getMethod() == 'DELETE');
	}

	/**
	 * 	Determine is incoming request is HEAD
	 *
	 * 	@return boolean
	 */
	public function isHead ()
	{
		return ($this->getMethod() == 'HEAD');
	}

	public function isSecure ()
	{
		if ($this->_isSecure === null)
		{
			if (($this->getFromServer('HTTPS', null) !== null && \filter_var($this->getFromServer('HTTPS'), \FILTER_VALIDATE_BOOLEAN))
					|| ($this->getFromServer('SERVER_PORT', 80) == 443))
			{
				$this->_isSecure = true;
			}
			else
			{
				$this->_isSecure = false;
			}
		}

		return $this->_isSecure;
	}

	public function isAjax ()
	{
		if ($this->_isAjax === null)
		{
			$this->_isAjax = ($this->getFromServer('HTTP_X_REQUESTED_WITH', false) AND \strtolower($this->getFromServer('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
		}

		return $this->_isAjax;
	}

	public function isCli ()
	{
		if ($this->_isCli === null)
		{
			$this->_isCli = (\PHP_SAPI === 'cli') ? true : false;
		}

		return $this->_isCli;
	}

	/**
	 * Is the request coming from a mobile device?
	 *
	 * Works with iPhone, Android, Windows Mobile, Windows Phone 7, Symbian, and other mobile browsers
	 *
	 * @return boolean
	 */
	public function isMobile ()
	{
		$op = \strtolower($this->getFromServer('HTTP_X_OPERAMINI_PHONE'));
		$ua = \strtolower($this->getFromServer('HTTP_USER_AGENT'));
		$ac = \strtolower($this->getFromServer('HTTP_ACCEPT'));

		return (
				\strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
				|| \strpos($ac, 'text/vnd.wap.wml') !== false
				|| $op != ''
				|| \strpos($ua, 'iphone') !== false
				|| \strpos($ua, 'android') !== false
				|| \strpos($ua, 'iemobile') !== false
				|| \strpos($ua, 'kindle') !== false
				|| \strpos($ua, 'sony') !== false
				|| \strpos($ua, 'symbian') !== false
				|| \strpos($ua, 'nokia') !== false
				|| \strpos($ua, 'samsung') !== false
				|| \strpos($ua, 'mobile') !== false
				|| \strpos($ua, 'windows ce') !== false
				|| \strpos($ua, 'epoc') !== false
				|| \strpos($ua, 'opera mini') !== false
				|| \strpos($ua, 'nitro') !== false
				|| \strpos($ua, 'j2me') !== false
				|| \strpos($ua, 'midp-') !== false
				|| \strpos($ua, 'cldc-') !== false
				|| \strpos($ua, 'netfront') !== false
				|| \strpos($ua, 'mot') !== false
				|| \strpos($ua, 'up.browser') !== false
				|| \strpos($ua, 'up.link') !== false
				|| \strpos($ua, 'audiovox') !== false
				|| \strpos($ua, 'blackberry') !== false
				|| \strpos($ua, 'ericsson,') !== false
				|| \strpos($ua, 'panasonic') !== false
				|| \strpos($ua, 'philips') !== false
				|| \strpos($ua, 'sanyo') !== false
				|| \strpos($ua, 'sharp') !== false
				|| \strpos($ua, 'sie-') !== false
				|| \strpos($ua, 'portalmmm') !== false
				|| \strpos($ua, 'blazer') !== false
				|| \strpos($ua, 'avantgo') !== false
				|| \strpos($ua, 'danger') !== false
				|| \strpos($ua, 'palm') !== false
				|| \strpos($ua, 'series60') !== false
				|| \strpos($ua, 'palmsource') !== false
				|| \strpos($ua, 'pocketpc') !== false
				|| \strpos($ua, 'smartphone') !== false
				|| \strpos($ua, 'rover') !== false
				|| \strpos($ua, 'ipaq') !== false
				|| \strpos($ua, 'au-mic,') !== false
				|| \strpos($ua, 'alcatel') !== false
				|| \strpos($ua, 'ericy') !== false
				|| \strpos($ua, 'up.link') !== false
				|| \strpos($ua, 'vodafone/') !== false
				|| \strpos($ua, 'wap1.') !== false
				|| \strpos($ua, 'wap2.') !== false
				);
	}

	/**
	 * Is the request coming from a bot or spider?
	 *
	 * Works with Googlebot, MSN, Yahoo, possibly others.
	 *
	 * @return boolean
	 */
	public function isBot ()
	{
		$ua = \strtolower($this->getFromServer('HTTP_USER_AGENT'));
		return (
				false !== \strpos($ua, 'googlebot') 
				|| \strpos($ua, 'msnbot' !== false) 
				|| \strpos($ua, 'yahoo!' !== false) 
				|| \strpos($ua, 'slurp' !== false) 
				|| \strpos($ua, 'bot' !== false) 
				|| \strpos($ua, 'spider' !== false)
				);
	}

	/**
	 * Return's the referrer
	 *
	 * @return  string
	 */
	public function getReferrer ($default = '')
	{
		if ($this->_referrer === null)
		{
			$this->_referrer = $this->getFromServer('HTTP_REFERER', $default);
		}

		return $this->_referrer;
	}

	protected function _extractValue ($str)
	{
		$arr = array();

		if (\sizeof($str) > 0)
		{
			foreach (\explode(',', $str) as $v)
			{
				if (\preg_match('#^\s*([^;]+)(?:;q=([0-9]+\.[0-9]+))?$#', $v, $match))
				{
					$arr[] = $match[1];
				}
			}
		}
		return $arr;
	}

}