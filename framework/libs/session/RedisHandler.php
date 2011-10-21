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
 * Library RedisHandler
 * 
 * This library requires Redis to be installed on your server.
 * See https://github.com/nicolasff/phpredis for installation and documentation
 * 
 * @author mickael
 */

namespace framework\libs\session;


class RedisSessionException extends \Exception
{
	
}


class RedisHandler implements \framework\libs\session\CompleteSessionHandler
{

	/**
	 * Whether or not the handler has been constructed from an already configured Redis object
	 * @var boolean 
	 */
	protected $_hasAlreadyConfiguredRedisObject = false;

	/**
	 * The severs' list
	 * @var array
	 */
	protected $_servers = array();
	
	/**
	 * The session's lifetime
	 * @var number
	 */
	protected $_lifetime = 0;
	
	/**
	 * The default parameters for the servers used by the Redis object
	 * @var array 
	 */
	protected $_defaultServerParams = array(
		'host' => '127.0.0.1', 
		'port' => 6379, 
		'timeout' => 0
	);
	
	/**
	 * The redis instance
	 * @var \Redis 
	 */
	protected $_redis = null;
	
	/**
	 * The session name
	 * @var string 
	 */
	protected $_sessionName = '';

	/**
	 * Constructor
	 * @param Redis|array $redis An already configured Redis object or a array containing the servers info
	 * @param number $lifetime The session's lifetime (seconds). Should not be more than 2592000 (30 days)
	 * @param array $defaultServerParams The default parameters for the redis servers. Will be ignored of the first argument is an already configured Redis object
	 */
	public function __construct ($redis, $lifetime = 0, array $defaultServerParams = array())
	{		
		$this->setLifetime($lifetime);
		
		if(\class_exists('\\Redis') === false)
		{
			throw new \framework\libs\session\RedisSessionException('Unable to find class "Redis"');
		}
		else
		{
			if ($redis instanceof \Redis)
			{
				$this->_hasAlreadyConfiguredRedisObject = true;
				$this->_redis = $redis;
				return; 
			}
			elseif(\is_array($redis))
			{
				if(count($defaultServerParams) > 0)
				{
					$this->setDefaultServerParams($defaultServerParams);
				}

				$this->_redis = new \Redis;

				foreach($redis as $server)
				{
					$this->_servers[] = $server;
				}

				$this->_configureServers();
			}
			else
			{
				throw new \framework\libs\session\RedisSessionException(
						'Wrong first parameter "'. \gettype($redis) 
						.'" for session handler, session cannot be initilised');
			}
		}
		
	}
	
	/**
	 * Set the session's lifetime (seconds). Should not be more than 2592000 (30 days).
	 * @param number $lifetime 
	 */
	public function setLifetime ($lifetime)
	{
		if(\is_numeric($lifetime))
		{
			$this->_lifetime = \intval($lifetime);
		}
		else
		{
			$this->_lifetime = 0;
		}
	}
	
	/**
	 * Set the session name
	 * @param string $sessionName 
	 */
	public function setSessionName ($sessionName)
	{
		$this->_sessionName = $sessionName;
	}
	
	/**
	 * Set the default parameters for the Redis servers.
	 * WARNING : will be inefficient if the RedisHandler has be constructed with an already configured Redis object!
	 * Keys can be : 'host', 'port', or 'timeout' 
	 * @param array $defaultServerParams
	 * @return \framework\libs\session\RedisHandler
	 */
	public function setDefaultServerParams (array $defaultServerParams)
	{
		if($this->_hasAlreadyConfiguredRedisObject === false)
		{
			$this->_defaultServerParams = array_merge($this->_defaultServerParams, $defaultServerParams);

			$this->_redis = new \Redis;
			// apply these default parameters
			$this->_configureServers();
		}
		
		return $this;
	}

	/**
	 * Open a session.
	 * Expects a save path and a session name.
	 * @param string $savePath
	 * @param string $sessionName
	 * @return boolean
	 */
	public function open ($savePath = '', $sessionName = '')
	{
		try
		{
			if($this->_hasAlreadyConfiguredRedisObject === false)
			{
				foreach($this->_servers as $server)
				{
					// connect to the Redis servers
					$this->_redis->connect($server['host'], $server['port'], $server['timeout']);
				}
			}
			if($sessionName !== '')
			{
				$this->_sessionName = $sessionName;
			}
			else
			{
				throw new \framework\libs\session\RedisSessionException('The session name cannot be empty');
			}
		}
		catch(\Exception $e)
		{
			throw new \framework\libs\session\RedisSessionException('The session could not be opened.', $e->getCode(), $e);
		}
		
		return true;
	}
	
	/**
	 * Close the session.
	 * Executed at the end of the script.
	 * @return boolean
	 */
	public function close ()
	{
		$this->_redis->close();
		return true;
	}

	/**
	 * Destroy a session.
	 * Expects a session id.
	 * @param string $sessionId
	 * @return boolean
	 */
	public function destroy ($sessionId = '')
	{
		if($sessionId !== '' && $this->_redis->exists($this->_key($sessionId)))
		{
			$this->_redis->del($this->_key($sessionId));
		}
		
		session_destroy();
		return true;
	}	

	/**
	 * Read some data stored in session.
	 * Expects a session id.
	 * Must return a string (empty if no data could have been read).
	 * @param string $sessionId
	 * @return string
	 */
	public function read ($sessionId = '')
	{
		$data = $this->_redis->get($this->_key($sessionId));
		
		if($data !== false)
		{
			return $data;
		}
		
		return '';
	}

	/**
	 * Store some data in session.
	 * Expects a session id and the data to write.
	 * @param string $sessionId
	 * @param mixed $data
	 * @return boolean
	 */
	public function write ($sessionId = '', $data = '')
	{
		if($sessionId !== '')
		{
			$this->_redis->setex($this->_key($sessionId), $this->_lifetime, $data);
		}
		
		return true;
	}

	/**
	 * Garbage collector. Erase the session when it's expired.
	 * Expects the the session's max life time.
	 * @param number $maxLifetime
	 */
	public function gc ($maxLifetime = 0)
	{
		// redis has its own GC
	}
	
	/**
	 * Configure the servers
	 */
	private function _configureServers()
	{		
		// check every server
		foreach ($this->_servers as $index => $server)
		{
			// make sure each server has all the params configured
			foreach($this->_defaultServerParams as $param => $defaultValue)
			{
				// if a parameter is missing
				if(!isset($server[$param]))
				{
					// remove the server if it has no host
					if($param === 'host')
					{
						unset($this->_servers[$index]);
						continue 2;
					}
					// or set the parameter to its default value
					else
					{
						$this->_servers[$index][$param] = $defaultValue;
					}
				}
			}
		}
	}

	public function setAsSessionHandler ()
	{
		ini_set('session.gc_probability', 0);
		ini_set('session.gc_divisor', 0);
		
		\session_set_save_handler(
				array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), 
				array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc')
		);
	}


	/**
	 * Compute a unique key
	 * @param mixed $key
	 * @return string 
	 */
	private function _key($key)
	{
		return '_session.'.$this->_sessionName.'.'.$key;
	}
	
}