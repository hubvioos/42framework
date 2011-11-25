<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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
class Collection extends \ArrayObject
{
	const SORT_ASC = 'asc';
	const SORT_DESC = 'desc';
	
	protected $collection = array();
	
	public function __construct ($array = array())
	{
		if(\count($array) > 0)
		{
			foreach ($array as $element)
			{
				$this[] = $element;
			}
		}
	}

	
	public function sort($property, $order = self::SORT_ASC)
	{
		// nope !
	}
}