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
		'failure_callback' => '\\framework\\libs\\session\\MemcacheHandler::defaultCallback', 
		'timeoutms' => 1500
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
	 * @param string $sessionName The session name
	 * @param number $lifetime The session's lifetime (seconds). Should not be more than 2592000 (30 days)
	 * @param array $defaultServerParams The default parameters for the memcache servers. Will be ignored of the first argument is an already configured Memcache object
	 * @throws \framework\libs\session\MemcacheSessionException
	 */
	public function __construct ($memcache, $sessionName = '', $lifetime = 0, array $defaultServerParams = array())
	{
		if($sessionName !== '')
		{
			$this->_sessionName = $sessionName;
		}
		else
		{
			throw new \framework\libs\session\MemcacheSessionException('The session name cannot be empty');
			return;
		}
		
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
				$this->_servers = $server;
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
	 */
	public function open ($savePath = '', $sessionName = '')
	{
		if($this->_hasAlreadyConfiguredMemcacheObject === false)
		{
			foreach($this->_servers as $server)
			{
				// add the server to the Memcache object
				$this->_memcache->addserver($server['host'], $server['port'], $server['persistent'], 
							$server['weight'], $server['timeout'], $server['retry_interval'], 
							$server['status'], $server['failure_callback'], $server['timeoutms']);
			}
		}
	}
	
	/**
	 * Close the session.
	 * Executed at the end of the script.
	 */
	public function close ()
	{
		$this->_memcache->close();
	}

	/**
	 * Destroy a session.
	 * Expects a session id.
	 * @param string $sessionId
	 */
	public function destroy ($sessionId = '')
	{
		if($sessionId !== '')
		{
			$this->_memcache->delete($this->_sessionName.$sessionId);
		}
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
		$data = $this->_memcache->get($this->_sessionName.$sessionId);
		
		if($data !== false)
		{
			return $data;
		}
		else
		{
			return '';
		}
	}

	/**
	 * Store some data in session.
	 * Expects a session id and the data to write.
	 * @param string $sessionId
	 * @param mixed $data
	 */
	public function write ($sessionId = '', $data = '')
	{
		if($sessionId !== '')
		{
			if($this->_memcache->replace($this->_sessionName.$sessionId, $data, null, $this->_lifetime) === false)
			{
				$this->_memcache->add($this->_sessionName.$sessionId, $data, null, $this->_lifetime);
			}
		}
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
	
	public function __destruct ()
	{
		$this->_memcache->close();
	}
	
	/**
	 * Use the handler as session handler
	 */
	public function setAsSessionHandler ()
	{
		\session_set_save_handler(
				array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), 
				array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc')
		);
	}
	
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
					if($param == 'host')
					{
						unset($this->_servers[$index]);
					}
					// or set the parameter to its default value
					else
					{	
						$server[$param] = $defaultValue;
					}
				}
			}
		}
	}
	
	public static function defaultCallback($host, $port)
	{
		throw new \framework\libs\session\MemcacheErrorException(
				'The memcache host '.$host.':'.$port.' reported an error.');
	}
}