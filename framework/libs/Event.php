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
/**
 * Class Event
 *
 */

namespace framework\libs;


class Event
{

	/**
	 * The name of the event
	 * @var string
	 */
	protected $_name;

	/**
	 * Array of the parameters
	 * @var array
	 */
	protected $_parameters;


	/**
	 * Get the name of the event
	 * @return string - The name of the event
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get All parameters
	 * @return array - All the parameters
	 */
	public function getParamaters()
	{
		return $this->_parameters;
	}
	
	/**
	 * Set the name of the event
	 * @param string - The name of the event
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * Set All parameters
	 * @param array - All the parameters
	 */
	public function setParamaters($parameters)
	{
		$this->_parameters = $parameters;
	}

	

	
		
}
