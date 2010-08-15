<?php namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

use \Framework as F;

class ClassLoaderException extends Exception { }

class ClassLoader
{
    private $autoload;
    
	public function __construct(Array $autoload = array())
	{
	    $this->autoload = $autoload;
	}
	
	public function load ($className)
	{
		require_once $this->autoload[$className];
	}
}