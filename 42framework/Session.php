<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class SessionException extends \Exception { }

class Session implements ArrayAccess, SeekableIterator, Countable
{
	protected $_session = null;
	
	protected $_position = 0;
	
	protected $_namespace = null;
	
	protected static $_isStarted = false;
	
	protected static $_instance = null;
	
	protected function __construct ($namespace)
	{	
		if (!Session::$_isStarted && !Request::isCli())
		{
			Session::init();
		}
		
		$this->_session = &$_SESSION[$namespace];
		
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
	
	public static function init ()
	{
		session_start();
		Session::$_isStarted = true;
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
		unset($_SESSION[$this->_namespace]);
		
		// Delete instance
		unset(Session::$_instance[$this->_namespace]);
		
		return null;
	}
	
	public function getNamespace ()
	{
		return $this->_namespace;
	}
	
	public function offsetGet ($offset)
	{
		return isset($this->_session[$offset]) ? $this->_session[$offset] : null;
	}
		
	public function offsetSet ($offset, $value)
	{
		$this->_session[$offset] = $value;
	}
	
	public function offsetUnset ($offset)
	{
		unset ($_SESSION[$this->_namespace][$offset]);
		unset ($this->_session[$offset]);
	}
	
	public function offsetExists ($offset)
	{
		return isset($this->_session[$offset]);
	}
	
	public function current ()
	{
		return $this->_session[$this->_position];
	}
	
	public function key ()
	{
		return $this->_position;
	}
	
	public function next ()
	{
		$this->_position ++;
	}
	
	public function rewind ()
	{
		$this->_position = 0;
	}
	
	public function valid ()
	{
		return $this->offsetExists($this->_position);
	}
	
	public function seek ($position)
	{
		if ($this->offsetExists($position))
		{
			$this->_position = $position;
		}
	}
	
	public function count ()
	{
		return sizeof($this->_session);
	}
}