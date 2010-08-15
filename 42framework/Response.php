<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ResponseException extends Exception
{}

class Response
{

	protected $status = null;

	protected $cookies = array();

	protected $headers = array();

	protected static $instance = null;

	protected function __construct ()
	{}

	protected function __clone ()
	{}

	public static function getInstance ()
	{
		if (self::$instance == null)
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public function getStatus ()
	{
		return $this->status;
	}

	public function getCookies ()
	{
		return $this->cookies;
	}

	public function getHeaders ()
	{
		return $this->headers;
	}

	public function statusIsSet ()
	{
		return $this->status !== null;
	}

	public function reset ()
	{
		return $this->resetStatus()->resetCookies()->resetHeaders();
	}

	public function resetStatus ()
	{
		$this->status = null;
		return $this;
	}

	public function resetHeaders ()
	{
		$this->headers = array();
		return $this;
	}

	public function resetCookies ()
	{
		$this->cookies = array();
		return $this;
	}

	protected function setStatus ($status)
	{
		$this->status = $status;
		return $this;
	}

	public function setHeader ($name, $value)
	{
		$this->headers[$name] = $value;
		return $this;
	}

	public function cookie ($name, $value = null, $expire = null, $path = null, 
		$domain = null, $secure = null)
	{
		$this->cookies[$name] = array('value' => $value, 'expire' => $expire, 
			'path' => $path, 'domain' => $domain, 'secure' => $secure);
		
		return $this;
	}

	public function status ($status)
	{
		switch ($status)
		{
			case 100:
				return $this->setStatus('100 Continue');
			
			case 101:
				return $this->setStatus('101 Switching Protocols');
			
			case 102:
				return $this->setStatus('102 Processing');
			
			case 200:
				return $this->setStatus('200 OK');
			
			case 201:
				return $this->setStatus('201 Created');
			
			case 202:
				return $this->setStatus('202 Accepted');
			
			case 203:
				return $this->setStatus('203 Non-Authoriative Information');
			
			case 204:
				return $this->setStatus('204 No Content');
			
			case 205:
				return $this->setStatus('205 Reset Content');
			
			case 206:
				return $this->setStatus('205 Partial Content');
			
			case 207:
				return $this->setStatus('205 Multi-Status');
			
			case 300:
				return $this->setStatus('300 Multiple Choices');
			
			case 301:
				return $this->setStatus('301 Moved Permanently');
			
			case 302:
				return $this->setStatus('302 Found');
			
			case 303:
				return $this->setStatus('303 See Other');
			
			case 304:
				return $this->setStatus('304 Not Modified');
			
			case 305:
				return $this->setStatus('305 Use Proxy');
			
			case 306:
				return $this->setStatus('306 (Unused)');
			
			case 307:
				return $this->setStatus('307 Temporary Redirect');
			
			case 400:
				return $this->setStatus('400 Bad Request');
			
			case 401:
				return $this->setStatus('401 Unauthorized');
			
			case 402:
				return $this->setStatus('402 Payment Granted');
			
			case 403:
				return $this->setStatus('403 Forbidden');
			
			case 404:
				return $this->setStatus('404 File Not Found');
			
			case 405:
				return $this->setStatus('405 Method Not Allowed');
			
			case 406:
				return $this->setStatus('406 Not Acceptable');
			
			case 407:
				return $this->setStatus(
					'407 Proxy Authentification Required');
			
			case 408:
				return $this->setStatus('408 Request Time-out');
			
			case 409:
				return $this->setStatus('409 Conflict');
			
			case 410:
				return $this->setStatus('410 Gone');
			
			case 411:
				return $this->setStatus('411 Length Required');
			
			case 412:
				return $this->setStatus('412 Precondition Failed');
			
			case 413:
				return $this->setStatus('413 Request Entity Too Large');
			
			case 414:
				return $this->setStatus('414 Request-URI Too Large');
			
			case 415:
				return $this->setStatus('415 Unsupported Media Type');
			
			case 416:
				return $this->setStatus(
					'416 Requested Range Not Satisfiable');
			
			case 417:
				return $this->setStatus('417 Expectation Failed');
			
			case 422:
				return $this->setStatus('422 Unprocessable Entity');
			
			case 423:
				return $this->setStatus('423 Locked');
			
			case 424:
				return $this->setStatus('424 Failed Dependency');
			
			case 500:
				return $this->setStatus('500 Internal Server Error');
			
			case 501:
				return $this->setStatus('501 Not Implemented');
			
			case 502:
				return $this->setStatus('502 Bad Gateway');
			
			case 503:
				return $this->setStatus('503 Service Unavailable');
			
			case 504:
				return $this->setStatus('504 Gateway Timeout');
			
			case 505:
				return $this->setStatus('505 HTTP Version Not Supported');
			
			case 507:
				return $this->setStatus('507 Insufficient Storage');
			
			default:
				trigger_error('Status \'' . $status . '\' is invalid', 
					E_USER_ERROR);
		}
	}

	public function location ($absoluteUri, $status = 303)
	{
		return $this->status($status)->setHeader('Location', $absoluteUri);
	}

	public function getLocation ()
	{
		return $this->getHeader('Location');
	}

	public function headerWasSent ()
	{
		return headers_sent();
	}

	public function send ()
	{
		if ($this->headerWasSent() == true)
		{
			trigger_error('Http header was already sent', E_USER_ERROR);
		}
		else
		{
			if ($this->statusIsSet() == true)
			{
				@header('HTTP/1.1 ' . $this->getStatus());
			}
			
			foreach ($this->getCookies() as $cookieName => $cookieProperties)
			{
				@setcookie($cookieName, $cookieProperties['value'], 
					$cookieProperties['expire'], $cookieProperties['path'], 
					$cookieProperties['domain'], 
					$cookieProperties['secure']);
			}
			
			foreach ($this->getHeaders() as $name => $value)
			{
				@header($name . ': ' . $value);
			}
			
			return $this;
		}
	}

	public function getHeader ($name)
	{
		return (isset($this->headers[$name]) == false ? null : $this->headers[$name]);
	}
}