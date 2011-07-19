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

namespace framework\libs;

class History
{	
	/**
	 * @var $_history \Framework\Libs\Session
	 */
	protected $_history = null;
	
	protected $_historySize = null;
	
	/**
	 * @param \Framework\Libs\Session $session
	 * @param integer $historySize
	 */
	public function __construct (Session $session, $historySize)
	{
		$this->_history = $session;	
		$this->_historySize = $historySize;
	}
	
	public function update (Array $values = array())
	{
		$size = sizeof($this->_history);
		
		foreach ($this->_history as $key => $value)
		{
			if ($key != 0 && $size >= $this->_historySize)
			{				
				$this->_history[$size-$key] = $this->_history[$size-$key-1];
			}
		}
		$this->_history[0] = $values;
		$this->_history->save();
	}
	
	public function get ($offset = null)
	{
		if ($offset === null)
		{
			return $this->_history;
		}
		
		if (isset ($this->_history[$offset]))
		{
			return $this->_history[$offset];
		}
		
		return null;
	}
	
	public function getPrevious ()
	{
		return (isset($this->_history[0])) ? $this->_history[0] : null;
	}
}