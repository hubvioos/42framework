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

namespace framework\orm\types\adapters;

/**
 * Description of OrientDBDateTimeAdapter
 *
 * @author mickael
 */
class OrientDBDateTimeAdapter implements \framework\orm\types\adapters\IAdapter
{
	
	const ORIENTDB_DATE_FORMAT = 'Y-m-d H:i:s:u';
	
	public function __construct ()
	{
		
	}

	
	public function convertToPHP ($value)
	{
		if($value instanceof \DateTime)
		{
			return $value;
		}
		else
		{
			try
			{
				$date = new \DateTime();
				$date->setTimestamp($value);
				
				return $date;
			}
			catch(\Exception $e)
			{
				throw new \framework\orm\types\adapters\AdapterException('Invalid timestamp.');
			}
		}
		
		throw new \framework\orm\types\adapters\AdapterException('Unable to convert value to PHP type.');
	
	}

	public function convertToStorage ($value)
	{
		if($value instanceof \DateTime)
		{
			return $value->format(self::ORIENTDB_DATE_FORMAT);
		}
		else
		{
			if(\is_numeric($value) && \strlen($value) == 9)
			{
				return \date(self::ORIENTDB_DATE_FORMAT, $value);
			}
				
			if(\is_string($value) && \preg_match("#^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}:[0-9]{3}#", $value))
			{
				return $value;
			}
		}
		
		throw new \framework\orm\types\adapters\AdapterException('Unable to convert value to OrientDBDateTime.');
	}

	
	
	
	
}

