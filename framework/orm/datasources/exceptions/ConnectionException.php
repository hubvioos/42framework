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

namespace framework\orm\datasources\exceptions;

/**
 * ConnectionException
 * Exception to throw on a connection error
 *
 * @author mickael
 */
class ConnectionException extends \framework\orm\datasources\exceptions\DatasourceException
{
	const DATABASE = 1;
	
	const HOST = 2;

    /**
     * @param string $target
     * @param int $targetType
     * @param \Exception $previous
     */
	public function __construct ($target, $targetType = self::HOST, $previous = NULL)
	{
		if($targetType == self::DATABASE)
		{
			parent::__construct('Unable to connect to database '.$target, $previous);
		}
		else
		{
			parent::__construct('Unable to connect to host '.$target, $previous);
		}
	}
}