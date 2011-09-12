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
 * Library CompleteSessionHandler
 *
 * @author mickael
 */
namespace framework\libs\session;

interface CompleteSessionHandler extends \framework\libs\session\SessionHandler
{

	/**
	 * Open a session
	 * Expects a save path and a session name
	 */
	public function open($savePath = '', $sessionName = '');
	
	/**
	 * Close the session
	 * Executed at the end of the script
	 */
	public function close();
	
	/**
	 * Read some data stored in session
	 * Must return a string (empty if no data could have been read)
	 * @return string
	 */
	public function read();
	
	/**
	 * Store some data in session
	 * Expects a session id and the data to write
	 */
	public function write($sessionId = '', $data = '');
	
	/**
	 * Destroy a session
	 * Expects a session id
	 */
	public function destroy($sessionId = '');
	
	/**
	 * Garbage collector. Erase the session when it's expired
	 * Expects the the session's max life time
	 */
	public function gc($maxLifeTime = 0);

}