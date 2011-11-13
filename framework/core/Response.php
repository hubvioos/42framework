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

class Response extends \framework\core\FrameworkObject
{

	const SUCCESS = 1;
	const ERROR = 2;
	const OUTSIDE_ACCESS_FORBIDDEN = 3;

	/**
	 * Contains the response
	 * @var mixed
	 */
	protected $_content = null;
	protected $_status = null;
	protected $_format = null;

	public function getContent ()
	{
		return $this->_content;
	}

	/**
	 * @return \Framework\Response
	 */
	public function setContent ($value)
	{
		$this->_content = $value;
		return $this;
	}

	public function getStatus ()
	{
		return $this->_status;
	}

	/**
	 * @return \Framework\Response
	 */
	public function reset ()
	{
		$this->_content = null;
		$this->_status = null;
		return $this;
	}

	/**
	 * @return \Framework\Response
	 */
	public function setStatus ($status)
	{
		$this->_status = $status;
		return $this;
	}
	
	public function getFormat ()
	{
		return $this->_format;
	}

	/**
	 * @return \Framework\Response
	 */
	public function setFormat ($value)
	{
		$this->_format = $value;
		return $this;
	}

}