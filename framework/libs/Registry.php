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
	
	public function __get($offset)
	{
		return $this->offsetGet($offset);
	}
	
	public function __set($offset, $value)
	{
		$this->offsetSet($offset, $value);
	}
	
	public function offsetSet($offset, $value)
	{
		if (\is_array($value))
		{
			$value = new self($value);
		}
		
		parent::offsetSet($offset, $value);
	}

	public function get($key, $toArray = true)
	{
		$params = \explode('.', $key);
		$value = $this[$params[0]];
		
		if (\count($params) > 1)
		{
			for ($i = 1; $i < \count($params); $i++)
			{
				$value = $value[$params[$i]];
			}
		}
		
		if($toArray && $value instanceof self)
		{
			$value = $value->toArray();
		}
		
		return $value;
	}
	
	public function set($key, $value)
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
				
				if(!isset($obj[$params[$i]]))
				{
					$obj[$params[$i]] = new self();
				}
				
				$obj = $obj[$params[$i]];
			}
			
			$obj[$params[$size-1]] = $value;
			return;
		}
		
		$this[$params[0]] = $value;
	}
}