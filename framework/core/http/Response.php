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

namespace framework\core\http;

class Response extends \framework\core\FrameworkObject
{

	protected $_status = 200;
	protected $_content = '';
	protected $_cookies = array();
	protected $_headers = array();
	protected $_encoding = 'UTF-8';
	protected $_contentType = 'text/html';
	protected $_protocol = 'HTTP/1.1';

	/**
	 * Constructor Function
	 */
	public function __construct ($status = 200, $content = '')
	{
		$this->_status = $status;
		$this->_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
		$this->_content = $content;
	}

	/**
	 * @return \Framework\Response
	 */
	public function reset ()
	{
		return $this->resetContent()->resetStatus()->resetCookies()->resetHeaders();
	}

	public function resetContent ()
	{
		$this->_content = '';
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetStatus ()
	{
		$this->_status = 200;
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetHeaders ()
	{
		$this->_headers = array();
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetCookies ()
	{
		$this->_cookies = array();
		return $this;
	}

	public function stopProcess ()
	{
		$this->getComponent('core')->render(true);
	}

	public function getCookies ()
	{
		return $this->_cookies;
	}

	public function getHeaders ()
	{
		return $this->_headers;
	}

	public function getContent ()
	{
		return $this->_content;
	}

	public function getStatus ()
	{
		return $this->_status;
	}

	public function getContentType ()
	{
		return $this->_contentType;
	}

	public function getEncoding ()
	{
		return $this->_encoding;
	}

	public function getProtcol ()
	{
		return $this->_protocol;
	}

	/**
	 * @return \Framework\Response
	 */
	public function setHeader ($name, $value)
	{
		if (!\is_string($name))
		{
			throw new \InvalidArgumentException('Invalid header type.');
		}

		// normalize headers ... not really needed
		for ($tmp = \explode('-', $name), $i = 0; $i < \count($tmp); $i++)
		{
			$tmp[$i] = \ucfirst($tmp[$i]);
		}

		$name = \implode('-', $tmp);
		if ($name == 'Content-Type')
		{
			if (\preg_match('/^(.*);\w*charset\w*=\w*(.*)/', $value, $matches))
			{
				$this->_contentType = $matches[1];
				$this->_encoding = $matches[2];
			}
			else
			{
				$this->_contentType = $value;
			}
		}
		else
		{
			$this->_headers[$name] = $value;
		}

		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function setCookie ($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false, $httponly = true)
	{
		$this->_cookies[$name] = array('value' => $value, 
										'expire' => $expire, 
										'path' => $path, 
										'domain' => $domain, 
										'secure' => $secure, 
										'httponly' => $httponly);
		return $this;
	}

	public function setStatus ($status)
	{
		$this->_status = $status;
		return $this;
	}

	public function setContent ($content)
	{
		$this->_content = $content;
		return $this;
	}

	public function appendContent ($content)
	{
		$this->_content .= $content;
		return $this;
	}

	public function setEncoding ($encoding)
	{
		$this->_encoding = $encoding;
		return $this;
	}

	public function setContentType ($contentType)
	{
		$this->_contentType = $contentType;
		return $this;
	}

	public function setProtocol ($protocol)
	{
		$this->_protocol = $protocol;
		return $this;
	}

	/**
	 * Send HTTP status header
	 */
	protected function sendStatus ()
	{
		$responses = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended'
		);

		$statusText = '';

		if (isset($responses[$this->_status]))
		{
			$statusText = $responses[$this->_status];
		}
		else
		{
			throw new \InvalidArgumentException('Status "' . $this->_status . '" is invalid');
		}

		// Send HTTP Header
		\header($this->_protocol . ' ' . $this->_status . ' ' . $statusText);
	}

	/**
	 * Send all set HTTP headers
	 */
	public function sendHeaders ()
	{
		header('Content-Type: ' . $this->_contentType . '; charset=' . $this->_encoding);

		foreach ($this->_cookies as $cookieName => $cookieProperties)
		{
			\setcookie($cookieName, 
					$cookieProperties['value'], 
					$cookieProperties['expire'], 
					$cookieProperties['path'], 
					$cookieProperties['domain'], 
					$cookieProperties['secure'], 
					$cookieProperties['httponly']);
		}

		// Send all headers
		foreach ($this->_headers as $key => $value)
		{
			if (!\is_null($value))
			{
				\header($key . ': ' . $value);
			}
		}
	}

	/**
	 * Send HTTP body content
	 */
	protected function sendBody ()
	{
		if (\is_resource($this->_content))
		{
			while (!\feof($this->_content))
			{
				echo \fread($this->_content, 8192);
			}
			\fclose($this->_content);
		}
		else
		{
			echo $this->_content;
		}
	}

	/**
	 * Send full HTTP response
	 */
	public function send ()
	{
		if (headers_sent())
		{
			throw new \RuntimeException('Http header was already sent');
		}
		else
		{
			$this->sendStatus();
			$this->sendHeaders();
			$this->sendBody();
		}

		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function redirect ($absoluteUri, $status = 302, $stopProcess = true)
	{
		if ($status < 300 || $status > 399)
		{
			$status = 302;
		}

		$this->setStatus($status)->setHeader('Location', $absoluteUri);

		if ($stopProcess)
		{
			$this->send();
			exit();
		}
		return $this;
	}

	/**
	 * Clear any previously set HTTP redirects
	 */
	public function clearRedirects ()
	{
		if (isset($this->_headers['Location']))
		{
			unset($this->_headers['Location']);
		}
	}

	/**
	 * See if the response has any redirects set
	 * 
	 * @return boolean
	 */
	public function hasRedirects ()
	{
		return isset($this->_headers['Location']);
	}

	public function getHeader ($name)
	{
		return (isset($this->_headers[$name]) == false) ? null : $this->_headers[$name];
	}

}