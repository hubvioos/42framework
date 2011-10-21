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
 * Library MemcacheHandler
 *
 * This library requires Memcache to be installed on your server.
 * See http://uk3.php.net/manual/en/book.memcache.php for installation and documentation.
 * @author mickael
 */
namespace framework\libs\session;


class MemcacheSessionException extends \Exception
{
	
}

class MemcacheHandler implements \framework\libs\session\CompleteSessionHandler
{
	/**
	 * Whether or not the handler has been constructed from an already configured Memcached object
	 * @var boolean 
	 */
	protected $_hasAlreadyConfiguredMemcacheObject = false;

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
	 * The default parameters for the servers used by the Memcache object
	 * @var array 
	 */
	protected $_defaultServerParams = array(
		'host' => '127.0.0.1', 
		'port' => 11211, 
		'persistent' => true, 
		'weight' => 1, 
		'timeout' => 1, 
		'retry_interval' => 15, 
		'status' => true, 
		'failure_callback' => '\\framework\\libs\\session\\MemcacheHandler::defaultCallback'
	);
	
	/**
	 * The memcache instance
	 * @var \Memcache 
	 */
	protected $_memcache = null;
	
	/**
	 * The session name
	 * @var string 
	 */
	protected $_sessionName = '';
	
	/**
	 * Constructor
	 * @param Memcache|array $memcache An already configured Memcache object or a array containing the servers info
	 * @param number $lifetime The session's lifetime (seconds). Should not be more than 2592000 (30 days)
	 * @param array $defaultServerParams The default parameters for the memcache servers. Will be ignored of the first argument is an already configured Memcache object
	 */
	public function __construct ($memcache, $lifetime = 0, array $defaultServerParams = array())
	{
		if(\extension_loaded('memcache') === false)
		{
			throw new \framework\libs\session\MemcacheSessionException('Memcache must be loaded');
		}
		elseif(\class_exists('Memcache') === false)
		{
			throw new \framework\libs\session\MemcacheSessionException('Unable to find class "Memcache"');
		}
		else
		{
			$this->setLifetime($lifetime);

			if ($memcache instanceof \Memcache)
			{
				$this->_hasAlreadyConfiguredMemcacheObject = true;
				$this->_memcache = $memcache;
				return; 
			}
			elseif(\is_array($memcache))
			{
				if(count($defaultServerParams) > 0)
				{
					$this->setDefaultServerParams($defaultServerParams);
				}

				$this->_memcache = new \Memcache;

				foreach($memcache as $server)
				{
					$this->_servers[] = $server;
				}
				
				$this->_configureServers();
			}
			else
			{
				throw new \framework\libs\session\MemcacheSessionException(
						'Wrong first parameter "'. \gettype($memcache) 
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
	 * Set the default parameters for the Memcache servers.
	 * WARNING : will be inefficient if the MemcacheHandler has be constructed with an already configured Memcache object!
	 * Keys can be : 'host', 'port', 'persistent', 'weight', 'timeout', 
	 * 'retry_interval', 'status', 'failure_callback'  and 'timeoutms'.
	 * See http://uk3.php.net/manual/en/memcache.addserver.php for the meaning of each one.
	 * @link http://fr.php.net/manual/en/memcache.addserver.php
	 * @param array $defaultServerParams
	 * @return MemcacheHandler 
	 */
	public function setDefaultServerParams (array $defaultServerParams)
	{
		if($this->_hasAlreadyConfiguredMemcacheObject === false)
		{
			$this->_defaultServerParams = array_merge($this->_defaultServerParams, $defaultServerParams);

			$this->_memcache = new \Memcache;
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
			if($this->_hasAlreadyConfiguredMemcacheObject === false)
			{
				foreach($this->_servers as $server)
				{
					// connect to the Memcache servers
					$this->_memcache->addServer($server['host'], $server['port'], $server['persistent'], 
								$server['weight'], $server['timeout'], $server['retry_interval'], 
								$server['status'], $server['failure_callback']);
				}
			}
			if($sessionName !== '')
			{
				$this->_sessionName = $sessionName;
			}
			else
			{
				throw new \framework\libs\session\MemcacheSessionException('The session name cannot be empty');
			}
		}
		catch (Exception $e)
		{
			throw new \framework\libs\session\RedisSessionException('The session could not be opened', $e->getCode(), $e);
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
		$this->_memcache->close();
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
		if($sessionId !== '')
		{
			$this->_memcache->delete($this->_key($sessionId));
		}
		
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
		$data = $this->_memcache->get($this->_key($sessionId));
		
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
			if($this->_memcache->replace($this->_key($sessionId), $data, null, $this->_lifetime) === false)
			{
				// add the item if we couldn't have replaced it (i.e. if it doesn't already exists)
				$this->_memcache->add($this->_key($sessionId), $data, null, $this->_lifetime);
			}
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
		// memcache has its own GC
	}
	
	/**
	 * Use the handler as session handler
	 */
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
	
	public static function defaultCallback($host, $port)
	{
		throw new \framework\libs\session\MemcacheSessionException(
				'The memcache host '.$host.':'.$port.' reported an error.');
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