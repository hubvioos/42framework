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
 */

namespace framework\libs\session;

class Session
{
	protected static $_isStarted = false;
	
	protected $_handler = null;
	
	public function __construct (\framework\libs\session\SessionHandler $handler)
	{	
		$this->_handler = $handler;
		
		/*
		\session_set_save_handler(array($this->_handler, 'open'), array($this->_handler, 'close'), 
				array($this->_handler, 'read'), array($this->_handler, 'write'), 
				array($this->_handler, 'destroy'), array($this->_handler, 'gc'));
		 */
	}
	
	public function init ()
	{
		if (!self::$_isStarted)
		{
			$this->_handler->setAsSessionHandler();
			
			session_start();
			self::$_isStarted = true;
		}
		
		return $this;
	}

	public function destroy ($namespace = null)
	{	
		if ($namespace !== null)
		{
			unset($_SESSION[$namespace]);
		}
		else
		{
			$this->destroyAll();
		}
	}
	
	public function destroyAll ()
	{
		session_unset();
		session_destroy();
		self::$_isStarted = false;
		
		return $this;
	}
}