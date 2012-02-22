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
	 * @var \framework\libs\ComponentsContainer
	 */
	protected static $_container = null;

	
	public function getConfig($key = null, $toArray = true)
	{
		if ($key === null)
		{
			return self::$_container;
		}
		
		return self::$_container->get($key, $toArray);
	}
	
	public function setConfig($key, $value)
	{
		self::$_container->set($key, $value);
	}
	
	public function getComponent()
	{
		return call_user_func_array(array(self::$_container,'getComponent'),func_get_args());
	}
	
	/**
	 * Get a mapper
	 * @param string $model The model for which we want to get the mapper
	 * @return \framework\orm\mappers\Mapper
	 */
	public function getMapper($model)
	{
		try
		{
			$model = "mapper." . $model;

			return self::$_container->getComponent($model);
		}
		catch (\InvalidArgumentException $e)
		{
			throw new \InvalidArgumentException('Mapper for model <strong>'.$model.'</strong> couldn\'t be found.');
		}
	}
	
	/**
	 * Get a model
	 * @param string $model The model's name
	 * @return \framework\orm\models\IAttachableModel
	 */
	public function getModel($model)
	{
		try
		{
			$model = "model.".$model;

			return self::$_container->getComponent($model);
		}
		catch (\InvalidArgumentException $e)
		{
			throw new \InvalidArgumentException('Model <strong>'.$model.'</strong> couldn\'t be found.');
		}
	}
	
	/**
	 * @param \framework\libs\ComponentsContainer $container
	 */
	public function setContainer(\framework\libs\ComponentsContainer $container)
	{
		self::$_container = $container;
	}
	
	/**
	 * @return \framework\libs\ComponentsContainer
	 */
	public function getContainer()
	{
		return self::$_container;
	}
	
	public function createRequest($params = array(), $state = null)
	{
		return $this->getComponent('request', $this->getComponent('registry', $params), $state);
	}
	
	public function createView(Array $file, $vars = false, $format = null)
	{
		if (isset($file["class"]))
		{
			return $this->getComponent($file["class"], $file["module"], $file["action"], $vars, $format);
		}
		else
		{
			return $this->getComponent('view', $file["module"], $file["action"], $vars, $format);
		}
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
		if ($params === null)
		{
			$params = array();
		}
		elseif (!is_array($params))
		{
			$params = array($params);
		}
		$event = $this->getComponent('event', $name, $params);
		return $this->getComponent('eventManager')->dispatchEvent($event);
	}
	
	public function addPlugin ($name, $plugin)
	{
		if (\is_callable($plugin))
		{
			$this->setConfig('plugin.'.$name, $plugin);
		}
		else
		{
			throw new \InvalidArgumentException('A plugin must be callable !');
		}
		
		return $this;
	}
	
	public function removePlugin ($name)
	{
		unset(self::$_container['plugin'][$name]);
		return $this;
	}
	
	public function __call ($method, $arguments)
	{
		if (isset(self::$_container['plugin'][$method]))
		{
			$plugin = self::$_container['plugin'][$method];
			return \call_user_func_array($plugin, $arguments);
		}
		
		throw new \InvalidArgumentException('Method '.$method.' doesn\'t exist !');
	}
}