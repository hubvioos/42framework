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
 * 
 * 
 * Inspired by Pimple (Copyright (c) 2009 Fabien Potencier) : http://github.com/fabpot/Pimple
 * and by the Symfony Service Container component (Copyright (c) 2008-2009 Fabien Potencier) : http://github.com/fabpot/dependency-injection
 */
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class BaseContainerException extends \Exception { }

class BaseContainer
{
	protected $container = array();
	
	public function __set ($key, $value)
	{
		$this->container[$key] = $value;
	}
	
	public function __get ($key)
	{
		if (!isset($this->container[$key]))
		{
			throw new BaseContainerException($key.' is not defined.');
		}
		return is_callable($this->container[$key]) ? $this->container[$key]($this) : $this->container[$key];
	}
	
	public function __isset ($key)
	{
		return isset($this->container[$key]);
	}
	
	public function __unset ($key)
	{
		unset($this->container[$key]);
	}
	
	public function __call($method, $arguments)
    {
        if (!preg_match('/^get(.+)$/', $method, $match))
        {
            throw new BaseContainerException('Call to undefined method : '.$method);
        }
        $key = lcfirst($match[1]);
        return $this->$key;
    }
}