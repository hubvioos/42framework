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
namespace framework\libs\cache;

/**
 * Storage engine for 42framework caching
 *
 */
abstract class CacheEngineInterface {

/**
 * Settings of current engine instance
 *
 * @var int
 * @access public
 */

/**
 * Garbage collection
 *
 * Permanently remove all expired and deleted data
 * @return void
 */
	public function gc() { }

/**
 * Write value for a key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached
 * @param mixed $duration How long to cache for.
 * @return boolean True if the data was succesfully cached, false on failure
 */
	abstract public function write($key, $value, $duration);

/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 */
	abstract public function read($key);

/**
 * Increment a number under the key and return incremented value
 *
 * @param string $key Identifier for the data
 * @param integer $offset How much to add
 * @return New incremented value, false otherwise
 */
	abstract public function increment($key, $offset = 1);

/**
 * Decrement a number under the key and return decremented value
 *
 * @param string $key Identifier for the data
 * @param integer $value How much to substract
 * @return New incremented value, false otherwise
 */
	abstract public function decrement($key, $offset = 1);

/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 */
	abstract public function delete($key);

/**
 * Delete all keys from the cache
 *
 * @param boolean $check if true will check expiration, otherwise delete all
 * @return boolean True if the cache was succesfully cleared, false otherwise
 */
	abstract public function clear($check);

/**
 * Cache Engine settings
 *
 * @return array settings
 */
	public function settings() {
		return $this->settings;
	}

/**
 * Generates a safe key for use with cache engine storage engines.
 *
 * @param string $key the key passed over
 * @return mixed string $key or false
 */
	public function key($key) {
		if (empty($key)) {
			return false;
		}
		$key = \framework\libs\Inflector::underscore(str_replace(array(DS, '/', '.'), '_', strval($key)));
		return $key;
	}
}
