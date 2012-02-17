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

namespace framework\orm\utils;

class MySQLCriteriaException extends \Exception 
{
	
}


/**
 * Class MySQLCriteria
 *
 * @author mickael
 */

class MySQLCriteria extends \framework\orm\utils\Criteria
{
	const BETWEEN = 'between';

	public function between ($field, array $boundaries)
	{
		if(\count($boundaries) != 2)
		{
			throw new \framework\orm\utils\MySQLCriteriaException('BETWEEN operator expects 2 boundaries');
		}
		
		return $this->_addConstraint(self::BETWEEN, array($field, $boundaries));
	}
}