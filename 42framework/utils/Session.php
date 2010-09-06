<?php
namespace Framework\Utils;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class SessionException extends \Exception { }

class Session implements \ArrayAccess, \SeekableIterator, \Countable
{
	protected $_session = null;
	
	protected $_position = 0;
	
	protected $_namespace = null;
	
	protected static $_isStarted = false;
	
	protected static $_instance = null;
	
	protected function __construct ($namespace)
	{	
		if (PHP_SAPI !== 'cli')
		{
			Session::start();
			$this->_session = &$_SESSION[$namespace];
		}
		else
		{
			$this->_session = array();
		}
		
		$this->_namespace = $namespace;
	}
	
	protected function __clone () { }

	public static function getInstance ($namespace = 'default')
	{
		if (PHP_SAPI === 'cli')
		{
			return null;
		}
		
	    if (Session::$_instance[$namespace] === null)
		{
			Session::$_instance[$namespace] = new Session($namespace);	
		}
		return Session::$_instance[$namespace];
	}
	
	public static function start ()
	{
		if (!Session::$_isStarted)
		{
			session_start();
			Session::$_isStarted = true;
		}
	}
	
	public static function destroyAll ()
	{
		// Delete all data
		session_unset();
		session_destroy();
		Session::$_isStarted = false;
		
		// Delete all instances
		Session::$_instance = null;
		
		return null;
	}
	
	public function destroy ()
	{	
		// Delete data of the namespace
		unset($_SESSION[$this->_namespace]);
		
		// Delete instance
		Session::$_instance[$this->_namespace] = null;
		
		return null;
	}
	
	public function getNamespace ()
	{
		return $this->_namespace;
	}
	
	public function get()
	{
		return $this->_session;
	}
	
	public function offsetGet ($offset)
	{
		return isset($this->_session[$offset]) ? $this->_session[$offset] : null;
	}
		
	public function offsetSet ($offset, $value)
	{
		if (is_null($offset))
		{
			$this->_session[] = $value;
	    }
		else
		{
			$this->_session[$offset] = $value;
	    }
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