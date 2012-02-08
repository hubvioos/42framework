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

namespace framework\libs;

class StaticClassLoader
{
	protected $_computedMap = null;
	protected $_maps = array();
	

	public function __construct()
	{
		
	}
	
	public function getComputedMap()
	{
		return $this->_computedMap;
	}
	
	protected function _computeMap ()
	{
		$computedMap = array();
		
		foreach ($this->_maps as $map)
		{
			$computedMap = \array_merge($map, $computedMap);
		}
		
		$this->_computedMap = $computedMap;
	}
	
	public function register ()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}

	public function unregister ()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}
	
	/**
	 * Registers a new top-level namespace to match.
	 *
	 * @param string $namespace The namespace name to add.
	 * @param string $path The path to the namespace (without the namespace name itself).
	 * @param string $extension The namespace file extension.
	 */
	public function addMap ($name, array $map)
	{
		if (isset($this->_maps[(string) $name]))
		{
			throw new \DomainException('The map ' . $name . ' is already added.');
		}

		$this->_maps[(string) $name] = $map;
		
		$this->_computeMap();
	}

	/**
	 * Removes a registered top-level namespace.
	 *
	 * @param string $namespace The namespace name to remove.
	 */
	public function removeMap ($name)
	{
		if (!isset($this->_maps[(string) $name]))
		{
			throw new \DomainException('The map ' . $name . ' is not available.');
		}
		unset($this->_maps[(string) $name]);
		
		$this->_computeMap();
	}
	
	/**
	 * Load the file containing $className.
	 * 
	 * @param string $className
	 */
	public function loadClass($className)
	{
		$className = \strtolower($className);
		if (!isset($this->_computedMap[$className]))
		{
			return false;
		}
		require $this->_computedMap[$className];
		return true;
	}
}