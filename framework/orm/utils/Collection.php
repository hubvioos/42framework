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
 * CollectionException 
 */
class CollectionException extends \Exception
{
	
}

/**
 * Description of Collection
 *
 * @author mickael
 */
class Collection extends \ArrayObject
{
	const SORT_ASC = 'asc';
	const SORT_DESC = 'desc';
	
	public function __construct ($array = array())
	{
		if(\count($array) > 0)
		{
			foreach ($array as $element)
			{
				$this[] = $element;
			}
		}
	}
	
	public function add($element)
	{
		$this[] = $element;
		return $this;
	}
	
	public function sort($property, $order = self::SORT_ASC)
	{
		// nope !
	}
	
	public function asArray()
	{
		return $this->getArrayCopy();
	}
	
	/**
	 * Merge with an array or another Collection
	 * @param array|\framework\orm\utils\Collection $collection
	 * @return \framework\orm\utils\Collection $this
	 * @throws \framework\orm\utils\CollectionException 
	 */
	public function merge($collection)
	{
		if(!\is_array($collection) && !($collection instanceof self))
		{
			throw new \framework\orm\utils\CollectionException('A Collection can only be merged with an array or another Collection');
		}
		
		foreach ($collection as $index => $element)
		{
			$this[$index] = $element;
		}
		
		return $this;
	}
	
	/**
	 * Find whether the Collection is empty or not.
	 * @return bool
	 */
	public function isEmpty()
	{
		return \count($this) == 0;
	}
}