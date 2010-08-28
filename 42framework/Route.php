<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RouteException extends \Exception { }

class Route
{
	protected $instance = null;
	protected $routes = array();
	
	protected function __construct ($route = array())
	{
		
	}
	
	public static function getInstance ($route)
	{
		if (self::$instance === null)
		{
			self::$instance = new self($route);
		}
		return self::$instance;
	}
	
	public function extractParams($path)
	{
		
	}
	
	public function path($url)
	{
		
	}
	
	public function url($params)
	{
		
	}
}