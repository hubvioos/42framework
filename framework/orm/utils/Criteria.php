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

/**
 * Description of Criteria
 *
 * @author mickael
 */
class Criteria
{
	
	const EQUALS = 'equals';
	const GREATER_THAN = 'greaterThan';
	const LESS_THAN = 'lessThan';
	const NOT_EQUALS = 'notEquals';
	const GREATER_THAN_OR_EQUAL = 'greaterThanOrEqual';
	const LESS_THAN_OR_EQUAL = 'lessThanOrEqual';
	
	const IN = 'in';
	const NOT_IN = 'notIn';
	
	const IS_NULL = 'isNull';
	const IS_NOT_NULL = 'isNotNull';
	
	const LIKE = 'like';
	const NOT_LIKE = 'notLike';
	
	const LIMIT = 'limit';
	
	const CRITERIA = 'criteria';
	
	const ASSOCIATION_OR = 'or';
	const ASSOCIATION_AND = 'and';
	
	protected $constraints = array();
	
	public function __construct ()
	{
		$this->constraints[self::CRITERIA] = array();
	}
	
	protected function _addConstraint($operator, $params)
	{
		$this->constraints[$operator] = $params;

		return $this;
	}

	
	public function criteria(\framework\orm\utils\Criteria $criteria, $association = self::ASSOCIATION_OR)
	{
		$this->constraints[self::CRITERIA] = \array_merge($this->constraints[self::CRITERIA], array($criteria, $association));
		return $this;
	}
	
	public function andCriteria(\framework\orm\utils\Criteria $criteria)
	{
		return $this->criteria($criteria, self::ASSOCIATION_AND);
	}
	
	public function orCriteria(\framework\orm\utils\Criteria $criteria)
	{
		return $this->criteria($criteria, self::ASSOCIATION_OR);
	}
	
	
	public function equals($field, $value)
	{
		return $this->_addConstraint(self::EQUALS, array($field, $value));
	}
	
	public function greaterThan($field, $value)
	{
		return $this->_addConstraint(self::GREATER_THAN, array($field, $value));
	}
	
	public function lessThan($field, $value)
	{
		return $this->_addConstraint(self::LESS_THAN, array($field, $value));
	}
	
	public function greaterThanOrEqual($field, $value)
	{
		return $this->_addConstraint(self::GREATER_THAN_OR_EQUAL, array($field, $value));
	}
	
	public function lessThanOrEqual($field, $value)
	{
		return $this->_addConstraint(self::LESS_THAN_OR_EQUAL, array($field, $value));
	}

	public function notEquals($field, $value)
	{
		return $this->_addConstraint(self::NOT_EQUALS, array($field, $value));
	}
	
	
	
	
	public function in($field, array $values)
	{
		return $this->_addConstraint(self::IN, array($field, $values));
	}

	public function notIn($field, array $values)
	{
		return $this->_addConstraint(self::NOT_IN, array($field, $values));
	}
	
	
	
	
	public function isNull($field)
	{
		return $this->_addConstraint(self::IS_NULL, $field);
	}

	public function isNotNull($field)
	{
		return $this->_addConstraint(self::IS_NOT_NULL, $field);
	}

	
	
	
	public function like($field, $value)
	{
		return $this->_addConstraint(self::LIKE, array($field, $value));
	}

	public function notLike($field, $value)
	{
		return $this->_addConstraint(self::NOT_LIKE, array($field, $value));
	}	
	
	
	
	
	public function limit($count, $start = 0)
	{
		return $this->_addConstraint(self::LIMIT, array($start, $count));
	}
	
	
	
	public function getConstraints()
	{		
		return $this->constraints;
	}
	
}