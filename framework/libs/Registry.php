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

class Registry extends \ArrayObject
{
	/**
	 * Constructor.
	 * This is recursive, i.e. every array inside $array will also be stored as an object
	 * @param array $array The array that will be accessed as an object
	 */
	public function __construct(array $array = array())
	{
		foreach($array as $key => $value)
		{
			if (\is_array($value))
			{
				$array[$key] = new self($value);
			}
		}
		
		parent::__construct($array);
	}
	
	/**
	 * Return the object as an array (recursively)
	 * @return array 
	 */
	public function toArray()
	{
		$array = $this->getArrayCopy();
		
		foreach ($array as $key => $value)
		{
			if ($value instanceof self)
			{
				$array[$key] = $value->toArray();
			}
		}
		
		return $array;
	}
	
	/**
	 * Try to get the instance variable named $offset (i.e. $this->$offset)
	 * if it exists. If not, try to get the offset named $offset.
	 * NOTE : the instance variable can _ONLY_ be accessed (thus returned)
	 * if it's been initialized in the constructor
	 * or if it has a default value
	 * @param string $offset
	 * @return mixed 
	 */
	public function __get($offset)
	{
		return (isset($this->$offset)) ? $this->$offset :$this->offsetGet($offset);
	}
	
	/**
	 * Try to set the instance variable named $offset (i.e. $this->$offset)
	 * if it exists. If not, try to set the offset named $offset
	 * NOTE : the instance variable can _ONLY_ be accessed (thus returned)
	 * if it's been initialized in the constructor
	 * or if it has a default value
	 * @param mixed $offset 
	 * @param mixed $value 
	 */
	public function __set($offset, $value)
	{
		if(isset($this->$offset))
		{
			$this->$offset = $value;
		}
		else
		{
			$this->offsetSet($offset, $value);
		}
	}
	
	/**
	 * Assign the value $value to the offset $offset.
	 * If $value is an array, it'll be stored as an ArrayObject
	 * @param mixed $offset
	 * @param mixed $value 
	 */
	public function offsetSet($offset, $value)
	{
		if (\is_array($value))
		{
			$value = new self($value);
		}
		
		parent::offsetSet($offset, $value);
	}

	/**
	 * Get the value of an offset ONLY. 
	 * Instance variable are not accessible with this method, use "$this->$key" instead. 
	 * For nested array, use the dotted notation, i.e. array1.array2.key
	 * @param mixed $key The offset
	 * @param boolean $toArray Whether or not the offset should be returned as an array instead of an object
	 * @return mixed 
	 */
	public function get($key, $toArray = true)
	{
		$value = null;
		
		if($this->offsetExists($key))
		{
			$value = $this[$key];
		}
		else
		{
			$params = \explode('.', $key);
			$size = \count($params);
			
			if($this->offsetExists($params[0]))
			{
				$value = $this->offsetGet($params[0]);

				// deep into the array if dotted notation was used
				if ($size > 1)
				{
					\array_shift($params);
					$newKey = \implode('.', $params);
					
					$value = $value->get($newKey);
				}
			}
		}
		
		if($toArray && $value instanceof self)
		{
			$value = $value->toArray();
		}
			
		return $value;
	}
	
	/**
	 * Set the value of an offset ONLY. 
	 * Instance variable are not accessible with this method, use "$this->$key = $value" instead. 
	 * For nested array, use the dotted notation, i.e. array1.array2.key
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		if($this->offsetExists($key))
		{
			$this->offsetSet($key, $value);
		}
		else
		{
			$params = \explode('.', $key);
			$size = \count($params);

			if ($size > 1)
			{	
				$obj = $this;

				for ($i = 0; $i < $size; $i++)
				{
					if ($i === $size-1)
					{
						break;
					}

					if(!$this->offsetExists($params[$i]))
					{
						$obj->offsetSet($params[$i], new self());
					}

					$obj = $obj->offsetGet($params[$i]);
				}

				$obj->offsetSet($params[$size-1], $value);
				return;
			}

			$this->offsetSet($params[0], $value);
		}
	}
}