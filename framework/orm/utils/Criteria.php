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

class CriteriaException extends \Exception
{
	
}

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

	/**
	 * Construct a Criteria from another criteria, building a logical operation between them.
	 * @param \framework\orm\utils\Criteria $criteria
	 * @param type $association 
	 */
	public function __construct (\framework\orm\utils\Criteria $criteria = null, $association = self::ASSOCIATION_OR)
	{
		if ($criteria !== null)
		{
			$this->criteria($criteria, $association);
		}
	}

	/**
	 * Add a constraint
	 * @param type $operator
	 * @param type $params
	 * @return \framework\orm\utils\Criteria $this  
	 */
	protected function _addConstraint ($operator, $params)
	{
		$this->constraints[] = array($operator, $params);

		return $this;
	}

	/**
	 * Add a logical (AND or OR) between this Criteria ans adnother.
	 * @param \framework\orm\utils\Criteria $criteria
	 * @param type $association
	 * @throws \framework\orm\utils\CriteriaException
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function criteria (\framework\orm\utils\Criteria $criteria, $association = self::ASSOCIATION_OR)
	{
		if ($association != self::ASSOCIATION_AND || $association != self::ASSOCIATION_OR)
		{
			throw new \framework\orm\utils\CriteriaException('Bad association type between criterias');
		}

		return $this->_addConstraint(self::CRITERIA, array($association, $criteria));
	}

	/**
	 * Add a logical AND operation between this Criteria and another.
	 * @param \framework\orm\utils\Criteria $criteria
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function andCriteria (\framework\orm\utils\Criteria $criteria)
	{
		return $this->criteria($criteria, self::ASSOCIATION_AND);
	}

	/**
	 * Add a logical OR operation between this Criteria and another.
	 * @param \framework\orm\utils\Criteria $criteria
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function orCriteria (\framework\orm\utils\Criteria $criteria)
	{
		return $this->criteria($criteria, self::ASSOCIATION_OR);
	}

	/**
	 * Check if a field equals a value.
	 * @param type $field
	 * @param type $value
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function equals ($field, $value)
	{
		return $this->_addConstraint(self::EQUALS, array($field, $value));
	}

	/**
	 * Check if a field is greater than a value.
	 * @param type $field
	 * @param type $value
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function greaterThan ($field, $value)
	{
		return $this->_addConstraint(self::GREATER_THAN, array($field, $value));
	}

	/**
	 * Check if a field is less than a value.
	 * @param type $field
	 * @param type $value
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function lessThan ($field, $value)
	{
		return $this->_addConstraint(self::LESS_THAN, array($field, $value));
	}

	/**
	 * CHeck if a field is greater than or equal to a value
	 * @param type $field
	 * @param type $value
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function greaterThanOrEqual ($field, $value)
	{
		return $this->_addConstraint(self::GREATER_THAN_OR_EQUAL, array($field, $value));
	}

	/**
	 * Check if a field is less than or equal to a value.
	 * @param type $field
	 * @param type $value
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function lessThanOrEqual ($field, $value)
	{
		return $this->_addConstraint(self::LESS_THAN_OR_EQUAL, array($field, $value));
	}

	/**
	 * Check if a field isn't equal to a value.
	 * @param type $field
	 * @param type $value
	 * @return \framework\orm\utils\Criteria $this  
	 */
	public function notEquals ($field, $value)
	{
		return $this->_addConstraint(self::NOT_EQUALS, array($field, $value));
	}

	/**
	 * Check if a field's value is one of a set of values.
	 * @param type $field
	 * @param array $values
	 * @return \framework\orm\utils\Criteria $this  
	 */
	public function in ($field, array $values)
	{
		return $this->_addConstraint(self::IN, array($field, $values));
	}

	/**
	 * Check if a field's value isn't any of a set of values.
	 * @param type $field
	 * @param array $values
	 * @return \framework\orm\utils\Criteria $this
	 */
	public function notIn ($field, array $values)
	{
		return $this->_addConstraint(self::NOT_IN, array($field, $values));
	}

	/**
	 * Check if a field is NULL.
	 * @param type $field
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function isNull ($field)
	{
		return $this->_addConstraint(self::IS_NULL, $field);
	}

	/**
	 * Check if a field is not NULL.
	 * @param type $field
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function isNotNull ($field)
	{
		return $this->_addConstraint(self::IS_NOT_NULL, $field);
	}

	/**
	 * Check if a field is like a mask.
	 * Use the wilcard '%' to mean 'any character'.
	 * @param type $field
	 * @param type $mask
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function like ($field, $mask)
	{
		return $this->_addConstraint(self::LIKE, array($field, $mask));
	}

	/**
	 * Check if a field is not like a mask.
	 * Use the wilcard '%' to mean 'any character'.
	 * @param string $field
	 * @param string $mask
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function notLike ($field, $mask)
	{
		return $this->_addConstraint(self::NOT_LIKE, array($field, $mask));
	}

	/**
	 * Set a limit in the number of items to retrieve.
	 * @param int $count 
	 * @param int $start The start position from which to count.
	 * @return \framework\orm\utils\Criteria $this 
	 */
	public function limit ($count, $start = 0)
	{
		return $this->_addConstraint(self::LIMIT, array($start, $count));
	}

	/**
	 * Get the constraints of this criteria
	 * @return array 
	 */
	public function getConstraints ()
	{
		return $this->constraints;
	}

}