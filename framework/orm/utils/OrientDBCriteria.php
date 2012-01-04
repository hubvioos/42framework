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
 * Class OrientDBCriteria
 *
 * @author mickael
 */

namespace framework\orm\utils;

class OrientDBCriteria extends \framework\orm\utils\Criteria
{
	const CONTAINS_TEXT = 'containsText';
	const MATCHES = 'matches';

	public function matches ($field, $regex)
	{
		return $this->_addConstraint(self::MATCHES, array($field, $regex));
	}

	public function containsText ($field, $value)
	{
		return $this->_addConstraint(self::CONTAINS_TEXT, array($field, $value));
	}

}