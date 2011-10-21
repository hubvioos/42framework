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
 * 
 * 
 * Inspired by Pimple (Copyright (c) 2009 Fabien Potencier) : http://github.com/fabpot/Pimple
 * and by the Symfony Service Container component (Copyright (c) 2008-2009 Fabien Potencier) : http://github.com/fabpot/dependency-injection
 */

namespace framework\libs;

class BaseContainer
{
	protected $_container = array();
	protected $_accessCounter = array();
	
	public function __set ($key, $value)
	{
		$this->_container[$key] = $value;
	}
	
	public function __get ($key)
	{
		return $this->get($key);
	}
	
	public function __isset ($key)
	{
		return isset($this->_container[$key]);
	}
	
	public function __unset ($key)
	{
		unset($this->_container[$key]);
	}
	
	public function __call ($method, $arguments)
	{
		$match = null;
		
		if (!preg_match('/^get(.+)$/', $method, $match))
		{
			throw new \BadMethodCallException('Call to undefined method : ' . $method);
		}
		
		$key = \lcfirst($match[1]);
		
		array_unshift($arguments,$key);
		
		return call_user_func_array(array($this,'get'),$arguments);
	}
	
	public function get()
	{
		$arguments = func_get_args();
		
		if (!isset($arguments[0]))
		{
			throw new \InvalidArgumentException('You have to specify a component name');	
		}
		
		$key = array_shift($arguments);
		
		if (!isset($this->_container[$key]))
		{
			throw new \InvalidArgumentException($key . ' is not defined.');
		}
		
		if (isset($this->_accessCounter[$key]))
		{
			$this->_accessCounter[$key]++;
		}
		else
		{
			$this->_accessCounter[$key] = 1;
		}
		
		if (is_callable($this->_container[$key]))
		{
			return $this->_container[$key]($this, $arguments);
		}
		else
		{
			return $this->_container[$key];
		}
	}
	
	public function asUniqueInstance ($callable)
	{
		return function ($c, $arguments) use ($callable)
		{
			static $object = null;
			
			if ($object === null)
			{
				$object = $callable($c, $arguments);
			}
			
			return $object;
		};
	}
	
	public function getAccessCounter($name)
	{
		if (isset($this->_accessCounter[$name]))
		{
			return $this->_accessCounter[$name];
		}
		else
		{
			return 0;
		}
	}
}