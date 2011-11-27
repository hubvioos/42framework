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
	
	const IN = 'in';
	const NOT_IN = 'notIn';
	
	const IS_NULL = 'isNull';
	const IS_NOT_NULL = 'isNotNull';
	
	const LIKE = 'like';
	const NOT_LIKE = 'notLike';
	
	const LIMIT = 'limit';
	
	
	protected $constraints = array();
	
	
	
	public function __construct ()
	{
		
	}
	
	protected function _addCriterion($operator, array $args)
	{
		$this->constraints[] = array_merge(array($operator), $args);

		return $this;
	}

	public function criteria(\Gacela\Criteria $criteria, $or = false)
	{
		return $this->_addCriterion($criteria, array($or));
	}


	
	public function lessThan($field, $value)
	{
		return $this->_addCriterion(self::LESS_THAN, func_get_args());
	}
	
	public function equals($field, $value)
	{
		return $this->_addCriterion(self::EQUALS, func_get_args());
	}
	
	public function greaterThan($field, $value)
	{
		return $this->_addCriterion(self::GREATER_THAN, func_get_args());
	}

	public function notEquals($field, $value)
	{
		return $this->_addCriterion(self::NOT_EQUALS, func_get_args());
	}
	
	
	
	
	public function in($field, array $values)
	{
		return $this->_addCriterion(self::IN, func_get_args());
	}

	public function notIn($field, array $values)
	{
		return $this->_addCriterion(self::NOT_IN, func_get_args());
	}
	
	
	
	
	public function isNull($field)
	{
		return $this->_addCriterion(self::IS_NULL, func_get_args());
	}

	public function isNotNull($field)
	{
		return $this->_addCriterion(self::IS_NOT_NULL, func_get_args());
	}

	
	
	
	public function like($field, $value)
	{
		return $this->_addCriterion(self::LIKE, func_get_args());
	}

	public function notLike($field, $value)
	{
		return $this->_addCriterion(self::NOT_LIKE, func_get_args());
	}	
	
	
	
	
	public function limit($start, $count)
	{
		return $this->_addCriterion(self::LIMIT, func_get_args());
	}
	
	
	
	public function get()
	{
		return $this->constraints;
	}
	
}