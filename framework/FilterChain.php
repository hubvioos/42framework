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
namespace framework\filters;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class FilterChain
{
	/**
	 * @var SplObjectStorage
	 */
	protected $_filters = null;
	
	public function __construct ($filters = array())
	{
		$this->_filters = new \SplObjectStorage();
		foreach ($filters as $filter)
		{
			$this->addFilter($filter);
		}
	}
	
	public function addFilter (Filter &$filter)
	{
		$this->_filters->attach($filter);
		$this->_filters->rewind();
	}
	
	public function removeFilter (Filter &$filter)
	{
		$this->_filters->detach($filter);
		$this->_filters->rewind();
	}
	
	public function execute (&$request, &$response)
	{
		if ($this->_filters->valid())
		{
			/* @var $current Filter */
			$current = $this->_filters->current();
			$this->_filters->next();
			$current->execute($request, $response, $this);
		}
	}
}