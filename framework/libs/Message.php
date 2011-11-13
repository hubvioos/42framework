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

class Message
{	
	protected $_namespace = null;


	public function __construct($namespace)
	{
		$this->_namespace = $namespace;
		
		if (!isset ($_SESSION[$this->_namespace]))
		{
			$_SESSION[$this->_namespace] = array();
		}
	}
	
	public function set ($value, $category = 'notice')
	{
		if (!isset ($_SESSION[$this->_namespace][$category]))
		{
			$_SESSION[$this->_namespace][$category] = array();
		}
		
		$_SESSION[$this->_namespace][$category][] = $value;
	}
	
	/**
	 * @param string $category
	 * @return array
	 */
	public function get ($category = 'notice')
	{
		if (isset($_SESSION[$this->_namespace][$category]))
		{
			return $_SESSION[$this->_namespace][$category];
		}
		return array();
	}
	
	public function getAll()
	{
		return $_SESSION[$this->_namespace];
	}
	
	public function clear ($category = 'notice')
	{
		unset($_SESSION[$this->_namespace][$category]);
	}
	
	public function clearAll ()
	{
		unset($_SESSION[$this->_namespace]);
	}
}