<?php

/**
 * Copyright (C) 2011 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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

namespace framework\orm\utils;

/**
 * MapException
 */
class MapException extends \Exception
{
	
}

/**
 * Description of Map
 *
 * @author mickael
 */
class Map extends \ArrayObject
{
	/**
	 * Constructor.
	 * @param array $properties An array (containing the property specifications, indexed by the property names) from wich the map should be build. 
	 */
	public function __construct(array $properties = array())
	{
		if(\count($properties) > 0)
		{
			foreach($properties as $property => $spec)
			{
				$this->addPropertyFromArray($property, $spec);
			}
		}
	}
	
	
	/**
	 *
	 * @param string $name The property name
	 * @param array $spec An array representing the property. Accepted fields are storageField, type, value, relation and primary. Other fields will be ignored.
	 */
	public function addPropertyFromArray($name, array $spec)
	{
		if(!\is_string($name) || \is_null($name))
		{
			throw new \framework\orm\utils\MapException('A property name cannot be empty and must be a string.');
		}
		
		$specs = array();
		
		// gonna give that bitch some ternary operators, bitches love ternary operators.
		$specs['storageField'] = (\array_key_exists('storageField', $spec)) ? $spec['storageField'] : $name ;
		$specs['type'] = (\array_key_exists('type', $spec)) ? $spec['type'] : \framework\orm\types\Type::UNKNOWN ;
		$specs['value'] = (\array_key_exists('value', $spec)) ? $spec['value'] : NULL ;
		$specs['relation'] = (\array_key_exists('relation', $spec)) ? $spec['relation'] : NULL ;
		$specs['primary'] = (\array_key_exists('primary', $spec)) ? $spec['primary'] : NULL ;
		
		$this->addProperty($name, $specs['type'], $specs['value'], $specs['storageField'], $specs['primary'], $specs['relation']);
	}
	
	/**
	 * Add a property by specifying every parameter.
	 * @param type $name The property name.
	 * @param type $type The property type.
	 * @param type $value The property value.
	 * @param type $storageField The property storage field's name. The property's name is used by default. If set to NULL, the property won't be stored in the datasource.
	 * @param type $primary Whether or not the property is the primary key. 'false' is used by default.
	 * @param type $relation The type of the relation for this property.
	 */
	public function addProperty($name, $type, $value, $storageField = '', $primary = false, $relation = NULL)
	{
		$this[$name] = array();
		$this[$name]['type'] = $type;
		$this[$name]['value'] = $value;
		$this[$name]['relation'] = $relation;
		
		if($storageField !== NULL)
		{
			if($storageField === '')
			{
				$this[$name]['storageField'] = $name;
			}
			else
			{
				$this[$name]['storageField'] = $storageField;
			}
		}
		
		if($primary)
		{
			$this[$name]['primary'] = true;
		}
	}
	
	/**
	 * Remove a property.
	 * @param type $name The name of the property to remove.
	 */
	public function removeProperty($name)
	{
		if($this->offsetExists($name))
		{
			$this->offsetUnset($name);
		}
	}

	
}

