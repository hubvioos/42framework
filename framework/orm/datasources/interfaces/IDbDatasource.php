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

namespace framework\orm\datasources\interfaces;

interface IDbDatasource
{
	/**
	 * Execute a request.
	 * @abstract
	 * @return mixed
	 */
	public function exec ($query);
	
	/**
	 * Execute a query to retrieve data.
	 * @abstract
	 * @return mixed
	 */
	public function query ($query);
	
		
	/**
	 * @abstract
	 * @return \framework\orm\utils\Criteria
	 */
	public function getNativeCriteria();
	
	/**
	 * Get the string representation of a Criteria
	 * @abstract
	 * @param \framework\orm\utils\Criteria
	 * @return string 
	 */
	public function criteriaToString(\framework\orm\utils\Criteria $criteria);
	
}