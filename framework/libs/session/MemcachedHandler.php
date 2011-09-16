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
 * Library MemcachedHandler
 *
 * This library requires Memcached to be installed on your server.
 * See http://uk3.php.net/manual/en/book.memcached.php for installation and documentation.
 * @author mickael
 */
namespace framework\libs\session;


class MemcachedSessionException extends \Exception
{
	
}

class MemcachedHandler implements \framework\libs\session\CompleteSessionHandler
{
	/**
	 * Whether or not the handler has been constructed from an already configured Memcachedd object
	 * @var boolean 
	 */
	protected $_hasAlreadyConfiguredMemcachedObject = false;

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
	 * The memcached instance
	 * @var \Memcached 
	 */
	protected $_memcached = null;
	
	/**
	 * The session name
	 * @var string 
	 */
	protected $_sessionName = '';
	
	/**
	 * Constructor
	 * @param \Memcached|array $memcached An already configured Memcached object or a array containing the servers info
	 * @param number $lifetime The session's lifetime (seconds). Should not be more than 2592000 (30 days)
	 * @param array $defaultServerParams The default parameters for the memcached servers. Will be ignored of the first argument is an already configured Memcached object
	 */
	public function __construct ($memcached, $lifetime = 0)
	{
		if(\extension_loaded('memcached') === false)
		{
			throw new \framework\libs\session\MemcachedSessionException('Memcached must be loaded');
		}
		elseif (\class_exists('\\Memcached') === false)
		{
			throw new \framework\libs\session\MemcachedSessionException('Unable to find class "Memcached"');
		}
		else
		{
		
			$this->setLifetime($lifetime);

			if ($memcached instanceof \Memcached)
			{
				$this->_hasAlreadyConfiguredMemcachedObject = true;
				$this->_memcached = $memcached;
				return; 
			}
			elseif(\is_array($memcached))
			{
				$this->_memcached = new \Memcached();

				foreach($memcached as $server)
				{
					$this->_servers[] = $server;
				}
			}
			else
			{
				throw new \framework\libs\session\MemcachedSessionException(
						'Wrong first parameter "'. \gettype($memcached) 
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
			if($this->_hasAlreadyConfiguredMemcachedObject === false)
			{
				// connect to the Memcached servers
				$this->_memcached->addServers($this->_servers);
			}
			if($sessionName !== '')
			{
				$this->_sessionName = $sessionName;
			}
			else
			{
				throw new \framework\libs\session\MemcachedSessionException('The session name cannot be empty');
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
		// the connection is automatically closed
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
			$this->_memcached->delete($this->_key($sessionId));
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
		$data = $this->_memcached->get($this->_key($sessionId));
		
		if($this->_memcached->getResultCode() === \Memcached::RES_SUCCESS)
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
			$this->_memcached->set($this->_key($sessionId), $data, $this->_lifetime);
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
		// memcached has its own GC
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
	 * Compute a unique key
	 * @param mixed $key
	 * @return string 
	 */
	private function _key($key)
	{
		return '_session.'.$this->_sessionName.'.'.$key;
	}
}