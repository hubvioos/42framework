<?php
/**
 * Copyright (C) 2010 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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
namespace Framework\Libs;
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
			self::start();
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
		if (!isset(self::$_instance[$namespace]) || self::$_instance[$namespace] === null)
		{
			self::$_instance[$namespace] = new self($namespace);	
		}
		return self::$_instance[$namespace];
	}
	
	public static function start ()
	{
		if (!self::$_isStarted)
		{
			session_start();
			self::$_isStarted = true;
		}
	}
	
	public static function destroyAll ()
	{
		// Delete all data
		session_unset();
		session_destroy();
		self::$_isStarted = false;
		
		// Delete all instances
		self::$_instance = null;
		
		return null;
	}
	
	public function destroy ()
	{	
		// Delete data of the namespace
		$_SESSION[$this->_namespace] = null;
		
		// Delete instance
		self::$_instance[$this->_namespace] = null;
		
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