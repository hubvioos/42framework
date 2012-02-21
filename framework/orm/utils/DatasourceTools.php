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
 * Class DatasourceTools.
 * This class aims to hold the various operations that might be used in several datasources (DRY).
 *
 * @author mickael
 */

namespace framework\orm\utils;

class DatasourceTools
{
	public function __construct()
	{
		
	}
    
	/**
	 * Be sure to escape the parameters if necessary.
	 * @param mixed $param
	 * @return mixed
	 */
    public function quoteParameter($param)
	{
		return ((\is_string($param) && \is_numeric($param))
				|| \is_float($param) || \is_int($param)) ? $param : $this->quoteString($param);
	}
    
	/**
	 * Properly quote a string (i.e. escape the '"' character since it's the one we use to enclose string in requests).
	 * @param string $string The string to quote
	 * @return string 
	 */
	public function quoteString ($string)
	{
		return '"' . \str_replace(array('"'/*, '\\'*/), array('\\"'/*, '\\\\'*/), $string) . '"';
	}

}