<?php
/**
 * Copyright (C) 2010 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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

defined('FRAMEWORK_DIR') or die('Invalid script access');

class Response extends \framework\core\FrameworkObject
{
	const SUCCESS = 1;
	const ERROR = 2;
	
	/**
	 * Contains the response
	 * @var mixed
	 */
	protected $response = null;
	
	protected $status = null;
	
	/**
	 * @return \Framework\Response
	 */
	public function clear ()
	{
		$this->set(null);
		return $this;
	}
	
	public function get ()
	{
		return $this->response;
	}
	
	/**
	 * @return \Framework\Response
	 */
	public function set ($value)
	{
		$this->response = $value;
		return $this;
	}

	public function getStatus ()
	{
		return $this->status;
	}

	/**
	 * @return \Framework\Response
	 */
	public function resetStatus ()
	{
		$this->status = null;
		return $this;
	}
	
	/**
	 * @return \Framework\Response
	 */
	public function reset ()
	{
		return $this->clear()->resetStatus();
	}

	/**
	 * @return \Framework\Response
	 */
	protected function setStatus ($status)
	{
		$this->status = $status;
		return $this;
	}
}