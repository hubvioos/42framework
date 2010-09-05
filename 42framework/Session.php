<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class SessionException extends \Exception { }

class Session
{
	public $session = null;
	
	protected $_namespace = null;
	
	protected static $_isStarted = false;
	
	protected static $_instance = null;
	
	protected function __construct($namespace)
	{	
		if (!Session::$_isStarted)
		{
			session_start();
			Session::$_isStarted = true;
		}
		
		$this->session = &$_SESSION[$namespace];
		
		$this->_namespace = $namespace;
	}
	
	protected function __clone () { }

	public static function getInstance ($namespace = 'default')
	{
	    if (Session::$_instance[$namespace] === null)
		{
			Session::$_instance[$namespace] = new Session($namespace);
			
		}
		return Session::$_instance[$namespace];
	}
	
	public static function destroyAll ()
	{
		// Delete all data
		session_unset();
		session_destroy();
		Session::$_isStarted = false;
		
		// Delete all instances
		unset(Session::$_instance);
		
		return null;
	}
	
	public function destroy ()
	{	
		// Delete data of the namespace
		unset($_SESSION[$_namespace]);
		
		// Delete instance
		unset(Session::$_instance[$_namespace]);
		
		return null;
	}
	
	
	
	public function set ($var, $value = false)
	{
		if (is_array($var))
		{
			array_merge($this->session, $var);
		}
		else
		{
			$this->session[$var] = $value;
		}
		
		return $this;
	}
	
	/*
	public function delete ($key)
	{
		if (strpos($key, '.'))
		{
			$key = explode('.', $key);
			$size = sizeof($key);
			
			$value = array();
			
			$value[0] = &$this->session;
				
			for ($i = 0; $i < $size; $i++)
			{
				$value[$i+1] = &$value[$i][$key[$i]];
			}
			$value[$i+1] = 1;
		}
		else
		{
			if (isset($this->session[$key]))
			{
				unset($this->session[$key]);
			}
		}
		
		return $this;
	}
	*/
	
	public function get ($key)
	{
		if (strpos($key, '.'))
		{
			$key = explode('.', $key);
			$size = sizeof($key);
			$value = null;
			
			for ($i = 0; $i < $size; $i++)
			{
				if ($i == 0)
				{
					$value = $this->session[$key[0]];
				}
				else
				{
					$value = $value[$key[$i]];
				}
			}
			return $value;
		}
		return isset($this->session[$key]) ? $this->session[$key] : null;
	}
	
	public function getSize ()
	{
		return sizeof($this->session);
	}
}