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

class ComponentsContainer extends \framework\libs\Registry
{

	/**
	 * Array of all components
	 * Structure of array
	 * 		'Component Name' => array ( 'callable' ,'isUnique' )
	 * @var array
	 */
	//protected $_config = null;

	/**
	 * Contains all number of each instance of each component
	 * @var array
	 */
	protected $_accessCounter = array();

	public function __construct (array $config)
	{
		parent::__construct($config);
		
		$this->_originalConfig = $config;
		
		foreach ($this['components'] as $key => $component)
		{
			if( $component['isUnique']  == true)
			{
				$this['components'][$key] = $this->asUniqueInstance($component['callable']);
			}
			else
			{
				$this['components'][$key] = $component['callable'];
			}
		}
	}

	/**
	 * Get the specified component if it exists
	 * @param string $key - If it is a module component the syntax is MODULE_NAME.COMPONENT_NAME
	 * @return <type>
	 */
	public function getComponent ()
	{
		//Get params
		$arguments = func_get_args();

		//Check if the component name is specified
		if (!isset($arguments[0]))
		{
			throw new \InvalidArgumentException('You have to specify a component name');
		}

		//Extract the component name of the argument array
		$key = array_shift($arguments);
		
		$component = $this->get('components.'.$key);

		//Check if the component exists
		if ($component === null)
		{
			throw new \InvalidArgumentException('Component ' . $key . ' is not defined.');
		}

		//Update the number of instance of the component
		if (isset($this->_accessCounter[$key]))
		{
			$this->_accessCounter[$key]++;
		}
		else
		{
			$this->_accessCounter[$key] = 1;
		}

		//If it's a callable component, we call it !
		if (is_callable($component))
		{
			return $component($this, $arguments);
		}
		//If it's a non collable component, we return it !
		else
		{
			return $component;
		}
	}

	public function __call ($method, $arguments)
	{
		$match = null;

		if (!preg_match('/^get(.+)$/', $method, $match))
		{
			throw new \BadMethodCallException('Call to undefined method : ' . $method);
		}

		$key = \lcfirst($match[1]);

		array_unshift($arguments, $key);

		return call_user_func_array(array($this, 'getComponent'), $arguments);
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

	/**
	 * Get the number of instance of specified component
	 * @param string $name - Alias of the component
	 * @return number - The number of the component instance
	 */
	public function getAccessCounter ($name)
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