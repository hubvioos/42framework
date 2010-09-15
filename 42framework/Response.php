<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ResponseException extends \Exception { }

class Response
{
	/**
	 * Contains the response
	 * @var mixed (Framework\View or string)
	 */
	protected $body = '';
	
	protected $status = '200 OK';

	protected $cookies = array();

	protected $headers = array();

	protected static $instance = null;
	

	protected function __construct () { }

	protected function __clone () { }

	/**
	 * @return \Framework\Response
	 */
	public static function getInstance ()
	{
		if (self::$instance == null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * @return \Framework\Response
	 */
	public function factory()
	{
		return new Response();
	}
	
	/**
	 * @return \Framework\Response
	 */
	public function clearResponse ()
	{
		$this->setBody('');
		return $this;
	}
	
	public function getBody ()
	{
		return $this->body;
	}
	
	/**
	 * @return \Framework\Response
	 */
	public function setBody ($value)
	{
		$this->body = $value;
		return $this;
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

	/**
	 * @return \Framework\Response
	 */
	public function reset ()
	{
		return $this->resetStatus()->resetCookies()->resetHeaders();
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetStatus ()
	{
		$this->status = '200 OK';
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetHeaders ()
	{
		$this->headers = array();
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetCookies ()
	{
		$this->cookies = array();
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	protected function setStatus ($status)
	{
		$this->status = $status;
		return $this;
	}
	
	public function stopProcess()
	{
		Core::getInstance()->render($this);
	}

	/**
	 * @return \Framework\Response
	 */
	public function setHeader ($name, $value)
	{
		if (!is_string($name))
		{
			throw new ResponseException('Invalid header type.');
		}
		$this->headers[$name] = $value;
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function setCookie ($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null)
	{
		$this->cookies[$name] = array('value' => $value, 'expire' => $expire, 'path' => $path, 'domain' => $domain, 'secure' => $secure);
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
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
				return $this->setStatus('402 Payment Required');
			case 403:
				return $this->setStatus('403 Forbidden');
			case 404:
				return $this->setStatus('404 Not Found');
			case 405:
				return $this->setStatus('405 Method Not Allowed');
			case 406:
				return $this->setStatus('406 Not Acceptable');
			case 407:
				return $this->setStatus('407 Proxy Authentication Required');
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
				return $this->setStatus('414 Request-URI Too Long');
			case 415:
				return $this->setStatus('415 Unsupported Media Type');
			case 416:
				return $this->setStatus('416 Requested Range Not Satisfiable');
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
			case 509:
				return $this->setStatus('509 Bandwidth Limit Exceeded');
			default:
				throw new ResponseException('Status "'.$status.'" is invalid');
		}
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
		
		$this->status($status)->setHeader('Location', $absoluteUri);
		
		if ($stopProcess)
		{
			$this->send();
			exit();
		}
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function send ()
	{
		if (headers_sent())
		{
			throw new ResponseException('Http header was already sent');
		}
		else
		{
			if (isset($_SERVER['SERVER_PROTOCOL']))
			{
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			}
			else
			{
				$protocol = 'HTTP/1.1';
			}
			header($protocol.' '.$this->getStatus());
			
			foreach ($this->getCookies() as $cookieName => $cookieProperties)
			{
				setcookie($cookieName, $cookieProperties['value'], 
									   $cookieProperties['expire'], 
									   $cookieProperties['path'], 
									   $cookieProperties['domain'], 
									   $cookieProperties['secure']);
			}
			
			foreach ($this->getHeaders() as $name => $value)
			{
				header($name.': '.$value);
			}
		}
		return $this;
	}

	public function getHeader ($name)
	{
		return (isset($this->headers[$name]) == false ? null : $this->headers[$name]);
	}
	
	public function __toString()
	{
		return $this->render();
	}
	
	public function render()
	{
		if ($this->body instanceof View)
		{
			return $this->body->render();
		}
		return $this->body;
	}
}