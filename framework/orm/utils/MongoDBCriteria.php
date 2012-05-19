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

class MongoDBCriteria extends Criteria
{
    const ALL = 'all';

    const MODULO = 'modulo';

    const EXISTS = 'exists';

    const TYPE = 'type';

    const SIZE = 'size';

    /**
     * @var array
     */
    protected $types = array(
        'double' => 1,
        'string' => 2,
        'object' => 3,
        'array' => 4,
        'binary' => 5,
        'id' => 7,
        'boolean' => 8,
        'date' => 9,
        'null' => 10,
        'regex' => 11,
        'js' => 13,
        'symbol' => 14,
        'scoped_js' => 15,
        'int32' => 16,
        'timestamp' => 17,
        'int64' => 18,
        'min-key' => 255,
        'max-key' => 127
    );

    public function like ($field, $regex)
    {
        //TODO escape regex and check options
        return $this->_addConstraint(self::LIKE, array($field, $regex));
    }

    public function all($field, array $values)
    {
        return $this->_addConstraint(self::ALL, array($field, $values));
    }

    public function mod($field, $divisor, $remainder = 0)
    {
        if(!\is_numeric($divisor) || !\is_numeric($remainder))
        {
            throw new CriteriaException('MODULO operator expects numeric values.');
        }

        return $this->_addConstraint(self::MODULO, array($field, array(\intval($divisor), \intval($remainder))));
    }

    public function exists($field, $exists = true)
    {
        return $this->_addConstraint(self::EXISTS, array($field, (bool) $exists));
    }

    public function type($field, $type)
    {
        if(\is_numeric($type) && \in_array($type, $this->types))
        {
            return $this->_addConstraint(self::TYPE, array($field, $type));
        }
        elseif(\is_string($type) && \in_array($type, \array_keys($this->types)))
        {
            return $this->_addConstraint(self::TYPE, array($field, $this->types[$type]));
        }

        throw new CriteriaException('Wrong type <strong>'.$type.'</strong>.');
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return $this->types;
    }

    public function size ($field, $size)
    {
        if(!\is_numeric($size))
        {
            throw new CriteriaException('SIZE operator expects a numeric value.');
        }

        return $this->_addConstraint(self::SIZE, array($field, \intval($size)));

    }
}
