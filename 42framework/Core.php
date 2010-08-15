<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CoreException extends Exception { }

class Core
{
	private static $instance = null;
	protected $request = null;
	protected $response = null;
	protected static $models = array();
	protected static $modules = array();
	
	protected function __construct (Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}
	
	/**
	 * @param Framework\Request $request
	 * @param Framework\Response $response
	 * @return Framework\Core
	 */
	public static function getInstance ($request, $response)
	{
		if (self::$instance === null)
		{
			self::$instance = new self($request, $response);
		}
		return self::$instance;
	}
	
	protected function __clone () { }
	
	/**
	 * @param array $config
	 * @return Framework\Core
	 */
	public function init (Array $config = array())
	{
		Config::loadConfig($config);
		return $this;
	}
	
	public static function loadModule ($module)
	{
		
	}
	
	public static function loadModel ($module, $model)
	{
		
	}
	
	public function execute ()
	{
		$this->response = $this->request->execute();
	}
	
	/**
	 * @return Framework\Response
	 */
	public function getResponse ()
	{
		return $this->response;
	}
}