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
 * Library LightRedisHandler
 * 
 * This library requires Redis to be installed on your server.
 * See https://github.com/nicolasff/phpredis for installation and documentation.
 * 
 * This is a minimal session handler using Redis.
 * It does nothing but using PHP's default parameters for session handling using Redis.
 * @link https://github.com/nicolasff/phpredis
 * @author mickael
 */
namespace framework\libs\session;

class LightRedisHandler implements \framework\libs\session\LightSessionHandler
{
	protected $_savePath = '';
	
	/**
	 * Constructor
	 * @param array|string $servers The servers' list
	 */
	public function __construct ($servers)
	{
		$this->setSavePath($servers);
		
		$this->_savePath = \rtrim($this->_savePath, ', ');
	}
	
	
	public function getSavePath ()
	{
		return $this->_savePath;
	}

	/**
	 * Set the session's save path from a list of servers
	 * @param array|string $servers The list of servers. Can be an array or a comma-separated string.
	 * @return LightMemecachedHandler 
	 */
	public function setSavePath ($servers)
	{
		if(is_array($servers))
		{
			foreach($servers as $server)
			{
				$this->_savePath .= $server.', ';
			}
		}
		else
		{
			$this->_savePath = $servers;
		}
		
		return $this;
	}
	
	public function setAsSessionHandler ()
	{
		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', $this->_savePath);
	}


}