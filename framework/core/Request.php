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

class Request extends \framework\core\FrameworkObject
{
	protected $_params = array();
	
	protected $_state = null;
	
	const DEFAULT_STATE = -1;
	const CLI_STATE = -50;
	const FIRST_REQUEST = -100;

	public function __construct (\framework\Libs\Registry $params, $state = self::DEFAULT_STATE)
	{
		$this->_params = $params;
		$this->_state = $state;
	}
	
	public function execute ()
	{
		return $this->getComponent('dispatcher')->dispatch($this);
	}
	
	/**
	 * @return the $_module
	 */
	public function getModule ()
	{
		return $this->_params['module'];
	}

	public function getMethod ()
	{
		return $this->_params['method'];
	}

	/**
	 * @return the $_action
	 */
	public function getAction ()
	{
		return $this->_params['action'];
	}
	
	public function getFormat ()
	{
		return $this->_params['format'];
	}
	
	public function setFormat ($value)
	{
		$this->_params['format'] = $value;
		return $this;
	}

	/**
	 * @return the $_params
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	public function getState ()
	{
		return $this->_state;
	}
	
	public function setState ($state)
	{
		$this->_state = $state;
		return $this;
	}


	public function __get ($key)
	{
		return $this->get($key, null);
	}
	
	public function __set ($key, $value)
	{
		return $this->set($key, $value);
	}

	public function get ($key, $default = null)
	{
		$value = $this->_params->get($key, true);

		if ($value === null)
		{
			$value = $default;
		}

		return $value;
	}

	public function set ($key, $value)
	{
		$this->_params->set($key, $value);
		return $this;
	}
}
