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
 * CollectionException 
 */
class CollectionException extends \Exception
{
	
}

/**
 * Description of Collection
 *
 * @author mickael
 */
class Collection implements \ArrayAccess, \Iterator, \Countable
{

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    protected $storage = array();

    protected $property = '';

    protected $order = '';

    protected $ignoreStringCase = true;

    protected $index = 0;

    protected $sorted = false;

	public function __construct ($object = array())
	{
		if(is_array($object) || $object instanceof \Traversable)
		{
			foreach ($object as $element)
			{
				$this->add($element);
			}
		}
        else
        {
            $this->add($object);
        }
	}

    /**
     * ArrayAccess methods
     */

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean Returns true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->storage);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->storage[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if($offset == '')
        {
            $this->storage[] = $value;

        }
        else
        {
            $this->storage[$offset] = $value;
        }

        if($this->sorted)
        {
            $this->sorted = false;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->storage[$offset]);
    }

    /**
     * Iterator methods
     */

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->storage[$this->index];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->index += 1;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return ($this->index < \count($this->storage));
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * Countable methods
     */

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return \count($this->storage);
    }

    /**
     * Custom methods
     */

    /**
     * @param $element
     * @return \framework\orm\utils\Collection
     */
    public function add($element)
    {
        $this->storage[] = $element;

        if($this->sorted)
        {
            $this->sorted = false;
        }

        return $this;
    }

    /**
     * The function used to provide the Collection->sort() method
     * @param mixed $a
     * @param mixed $b
     * @return int
     * @throws CollectionException
     */
    protected function _sortAlgorithm($a, $b)
    {
        $getter = 'get' . $this->property;

        $aOffset = $a->{$getter}();
        $bOffset = $b->{$getter}();

        // swap the variables if the sorting must be done in reverse
        if($this->order == self::SORT_DESC)
        {
            $tmp = $aOffset;
            $aOffset = $bOffset;
            $bOffset = $tmp;
        }

        if(\is_scalar($aOffset) && \is_scalar($bOffset))
        {
            if(\is_numeric($aOffset) && \is_numeric($bOffset))
            {
                $aOffset = $aOffset + 0;
                $bOffset = $bOffset + 0;

                if($aOffset == $bOffset)
                {
                    return 0;
                }

                return ($aOffset < $bOffset) ? -1 : 1 ;
            }
            elseif(\is_string($aOffset) && \is_string($bOffset))
            {
                return ($this->ignoreStringCase) ? \strcmp($aOffset, $bOffset) : \strcasecmp($aOffset, $bOffset);
            }
        }
        elseif((\is_array($aOffset) && \is_array($bOffset))
            || ($aOffset instanceof \Countable && $bOffset instanceof \Countable))
        {
            if(\count($aOffset) == \count($bOffset))
            {
                return 0;
            }

            return (\count($aOffset) < \count($bOffset)) ? -1 : 1 ;
        }

        throw new \framework\orm\utils\CollectionException('A Collection can anly be sorted using arrays or scalar values.');
    }


    /**
     * Sort the Collection's elements according to a property
     * If the property is a string, it's sorted in alphabetical order, if it's countable, its length is used
     * @param $property
     * @param string $order
     * @param bool $ignoreStringCase
     * @return \framework\orm\utils\Collection
     */
    public function sort($property, $order = self::SORT_ASC, $ignoreStringCase = true)
    {
        $this->property = ucfirst($property);
        $this->order = $order;
        $this->ignoreStringCase = $ignoreStringCase;

        if($this->order != self::SORT_ASC && $this->order != self::SORT_DESC)
        {
            throw new \framework\orm\utils\CollectionException('Wrong sort order <strong>'.$this->order.'</strong>');
        }

        if(!$this->isEmpty())
        {
            usort($this->storage, array($this, '_sortAlgorithm'));
        }

        $this->sorted = true;

        return $this;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return $this->storage;
    }


    /**
	 * Merge with an array or another Collection
	 * @param array|\framework\orm\utils\Collection $collection
	 * @return \framework\orm\utils\Collection $this
	 * @throws \framework\orm\utils\CollectionException 
	 */
	public function merge($collection)
	{
		if(!\is_array($collection) && !($collection instanceof self))
		{
			throw new \framework\orm\utils\CollectionException('A Collection can only be merged with an array or another Collection');
		}

        if($collection instanceof self)
        {
            $this->storage = \array_merge($this->storage, $collection->storage);
        }
        else
        {
            $this->storage = \array_merge($this->storage, $collection);
        }

        if($this->sorted)
        {
            $this->sorted = false;
        }

		return $this;
	}
	
	/**
	 * Find whether the Collection is empty or not.
	 * @return bool
	 */
	public function isEmpty()
	{
		return \count($this->storage) == 0;
	}

    /**
     * Get the first value of the Collection or a default value if the Collection is empty
     * @param mixed $default A default value to return if there is no first element
     * @return mixed
     */
    public function first($default = NULL)
    {
        return $this->isEmpty() ? $default : $this->storage[0];
    }

    /**
     * Get the last element of the Collection or a default value if the Collection is empty
     * @param mixed $default A default value to return if there is no last element
     * @return mixed
     */
    public function last($default = NULL)
    {
        return $this->isEmpty() ? $default : $this->storage[$this->count() - 1];
    }

    /**
     * Check if the Collection is sorted. This is reset to false anytime a new element is added to the Collection.
     * @return bool
     */
    public function isSorted()
    {
        return $this->sorted;
    }

    /**
     * Get the last used sorting order.
     * @return string
     */
    public function getSortingOrder()
    {
        return $this->order;
    }

    /**
     * Reverse the collection
     * @return Collection
     */
    public function reverse()
    {
        $this->storage = \array_reverse($this->storage, false);
        return $this;
    }

    /**
     * Find an element in the collection based on the value of one of its properties.
     * @param $key string The property name. Can be a method name if $method param is set to true or an array index if the $method param is set to false.
     * @param $value mixed The value againt which the tests are made
     * @param bool $method Set to true if the $key param is a method name.
     * @return mixed|null
     */
    public function find($key, $value, $method = true)
    {
        if($method === true)
        {
            foreach($this->storage as $object)
            {
                if($object->{$key}() === $value)
                {
                    return $object;
                }
            }
        }
        else
        {
            foreach($this->storage as $object)
            {
                if($object[$key] === $value)
                {
                    return $object;
                }
            }
        }

        return NULL;
    }
}