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

class Session extends \framework\libs\Registry
{
	protected $_namespace = null;
	
	protected static $_isStarted = false;
	
	public function __construct ($namespace = 'default')
	{	
		$this->init();
			
		if (!isset($_SESSION[$namespace]) || !is_array($_SESSION[$namespace]))
		{
			$_SESSION[$namespace] = array();
		}
		$this->_namespace = $namespace;
		parent::__construct($_SESSION[$namespace]);
	}
	
	public function init ()
	{
		if (!self::$_isStarted)
		{
			session_start();
			self::$_isStarted = true;
		}
		
		return $this;
	}

	public function destroy ()
	{	
		$_SESSION[$this->_namespace] = null;
	}
	
	public function destroyAll ()
	{
		session_unset();
		session_destroy();
		self::$_isStarted = false;
		
		return $this;
	}
	
	public function getNamespace ()
	{
		return $this->_namespace;
	}
	
	public function save()
	{
		$_SESSION[$this->_namespace] = $this->toArray();
	}
}