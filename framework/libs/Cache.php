<?php
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
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
 *
 * These lib is an adaptation CakePHP's cache system.
 * Original License : 
 *
	 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
	 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 *
	 * copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
	 * link          http://cakephp.org CakePHP(tm) Project
	 * package       cake.libs
	 * since         CakePHP(tm) v 1.2.0.4933
	 * license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 */
namespace framework\libs;


/**
 * Cache provides a consistent interface to Caching in your application. It allows you
 * to use several different Cache engines, without coupling your application to a specific 
 * implementation.  It also allows you to change out cache storage or configuration without effecting 
 * the rest of your application.
 *
 * You can configure Cache engines in your general configuration file > 'application\config.php'
 * You can check an exemple in the framework default config file
 *
 * In general all Cache operations are supported by all cache engines.  However, increment() 
 * and decrement() are not supported by File caching.
 *
 */
class Cache {
	
	/**
	 * Name of the current config engine
	 *
	 * @var string
	 */
	protected $_name = '';
	
	/**
	 * Cache configuration stack
	 * Keeps the permanent/default settings for each cache engine.
	 * These settings are used to reset the engines after temporary modification.
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Whether to reset the settings with the next call to set();
	 *
	 * @var array
	 */
	protected $_reset = false;

	/**
	 * Cache configuration stack
	 * Keeps the permanent/default settings for each cache engine.
	 * These settings are used to reset the engines after temporary modification.
	 *
	 * @var \framework\libs\cache\CacheEngineInterface
	 */
	protected $_cacheEngine = null;
	
	/**
	 * Finds and builds the instance of the required engine class.
	 * @param string $name Name of the config array that needs an engine instance built
	 * @param array $config Configuration of these engine
	 */
	public function __construct ($name, $config)
	{
		$this->_name = $name;
		$this->_config = $config;
		
		//Check if the engine is specified
		if(!isset($this->_config['engine']))
		{
			throw new \InvalidArgumentException('You have to specify a cache engine');
		}
		
		//Check if the engine exists
		$classPath = '\\framework\\libs\\cache\\'.$this->_config['engine'].'Engine';
		 if(!\class_exists($classPath) && \in_array('CacheEngineInterface', \class_parents($classPath)))
		{
			throw new \InvalidArgumentException('The cache engine you specified does\'t exists');
		}
		
		//Init the cache engine with checking errors
		$this->_cacheEngine = new $classPath();
		
		if(!$this->_cacheEngine->init($this->_config))
		{
			throw new \InvalidArgumentException('The initialization of the cache engine has failed');
		}
	}

	/**
	* Returns an array containing the currently configured Cache settings.
	*
	* @return array Array of configured Cache config names.
	*/
	public function getConfig() 
	{
		return $this->_config;
	}

	/**
	* Garbage collection
	*
	* Permanently remove all expired and deleted data
	*
	* @return void
	*/
	public function gc() 
	{
		$this->_cacheEngine->gc();
	}

/**
 * Write data for key into cache. Will automatically use the currently
 * active cache configuration. 
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached - anything except a resource
 * @return boolean True if the data was successfully cached, false on failure
 */
public  function write($key, $value) 
{
	
	$key = $this->_cacheEngine->key($key);

	if (!$key || is_resource($value)) 
	{
		return false;
	}

	$success = $this->_cacheEngine->write($this->_config['prefix'] . $key, $value, $this->_config['duration']);

	if ($success === false && $value !== '') 
	{
		throw new \InvalidArgumentException('Cannot write to cache');
	}
	return $success;
}

/**
 * Read a key from the cache. 
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 */
	public function read($key) {
		
		$key = $this->_cacheEngine->key($key);
		
		if (!$key) 
		{
			return false;
		}
		
		return $this->_cacheEngine->read($this->_config['prefix'] . $key);
	}

/**
 * Increment a number under the key and return incremented value.
 *
 * @param string $key Identifier for the data
 * @param integer $offset How much to add
 * @return mixed new value, or false if the data doesn't exist, is not integer,
 *    or if there was an error fetching it.
 */
	public function increment($key, $offset = 1) 
	{
		$key = $this->_cacheEngine>key($key);

		if (!$key || !is_integer($offset) || $offset < 0) 
		{
			return false;
		}
		
		$success = $this->_cacheEngine->increment($this->_config['prefix'] . $key, $offset);
		
		return $success;
	}
/**
 * Decrement a number under the key and return decremented value.
 *
 * @param string $key Identifier for the data
 * @param integer $offset How much to substract
 * @return mixed new value, or false if the data doesn't exist, is not integer,
 *   or if there was an error fetching it
 */
	public function decrement($key, $offset = 1) {

		$key = $this->_cacheEngine->key($key);

		if (!$key || !is_integer($offset) || $offset < 0) 
		{
			return false;
		}
		
		$success = $this->_cacheEngine->decrement($this->_config['prefix'].$key, $offset);

		return $success;
	}
/**
 * Delete a key from the cache.
 * @param string $key Identifier for the data
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 */
	public function delete($key) 
	{
		$key = $this->_cacheEngine>key($key);
		
		if (!$key) 
		{
			return false;
		}

		$success = $this->_cacheEngine->delete($settings['prefix'] . $key);

		return $success;
	}

/**
 * Delete all keys from the cache.
 *
 * @param boolean $check if true will check expiration, otherwise delete all
 * @param string $config name of the configuration to use. Defaults to 'default'
 * @return boolean True if the cache was succesfully cleared, false otherwise
 */
	public function clear($check = false) 
	{
		$success = $this->_cacheEngine->clear($check);
		return $success;
	}
}
