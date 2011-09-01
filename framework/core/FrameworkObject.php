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
 */
namespace framework\core;

/**
 * @var $app \framework\core\Application
 */
abstract class FrameworkObject
{
	/**
	 * @var \framework\core\ComponentsContainer
	 */
	protected static $_container = null;

	
	public function getConfig($key = null, $toArray = true)
	{
		if ($key === null)
		{
			return self::$_container->config;
		}
		
		return self::$_container->config->get($key, $toArray);
	}
	
	public function setConfig($key, $value)
	{
		self::$_container->config->set($key, $value);
	}
	
	public function getComponent()
	{
		return call_user_func_array(array(self::$_container,'get'),func_get_args());
	}
		
	/**
	 * @param \framework\core\ComponentsContainer $container
	 */
	public function setContainer(\framework\core\ComponentsContainer $container)
	{
		self::$_container = $container;
	}
	
	/**
	 * @return \framework\core\ComponentsContainer
	 */
	public function getContainer()
	{
		return self::$_container;
	}
	
	public function createRequest($module, $action, $params = array(), $state = null)
	{
		return $this->getComponent('request', $module, $action, $params, $state);
	}
	
	public function createView($module, $action, $vars = false)
	{
		return $this->getComponent('view', $module, $action, $vars);
	}
	
		
	/**
	 * Set a global variable for the view. Shortcut for View::setGlobal()
	 * 
	 * @param mixed $var
	 * @param mixed $value
	 */
	public function viewSetGlobal($var, $value)
	{
		/* @var $view View */
		\framework\core\View::setGlobal($var, $value);
		return $this;
	}
	
	/**
	 * Get a global variable from the view. Shortcut for View::getGlobal()
	 * 
	 * @param mixed $var
	 */
	public function viewGetGlobal($var)
	{
		/* @var $view View */
		return \framework\core\View::getGlobal($var);
	}
	
	public function raiseEvent($name, $params = null)
	{
		return $this->getComponent('eventManager')->dispatchEvent($name, $params);
	}
}