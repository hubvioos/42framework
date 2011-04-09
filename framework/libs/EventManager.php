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

class EventManager
{
    /**
     * @var array
     */
	protected $_listeners = array();

    /**
     * @param array $events
     */
	public function __construct(Array $events = array())
    {
    	$this->_listeners = $events;
    }
    
    /**
     * @param string $eventName
     * @param string $listener
     * @return \framework\events\EventManager
     */
    public function addListener($eventName, $listener)
    {
        if (!isset($this->_listeners[$eventName]))
        {
            $this->_listeners[$eventName] = array();
        }
        $this->_listeners[$eventName][] = $listener;
        return $this;
    }

    /**
     * @param string $eventName
     * @param string $listener
     * @return \framework\events\EventManager
     */
    public function removeListener($eventName, $listener = null)
    {
        if (!isset($this->_listeners[$eventName]))
        {
            return false;
        }
        if ($listener === null)
        {
            unset($this->_listeners[$eventName]);
            return true;
        }
        
		$ok = false;
		
		foreach ($this->_listeners[$eventName] as $id => $lis)
        {
        	if ($listener === $lis)
        	{
        		unset($this->_listeners[$eventName][$id]);
				$ok = true;
        	}
        }
        return $ok;
    }

    /**
     * @param string $eventName
     * @param mixed $params
     * @return mixed
     */
    public function dispatchEvent($eventName, $params = null)
    {
        $returnValue = null;
    	foreach ($this->_listeners[$eventName] as $listener)
        {
            $returnValue = $listener::$eventName($params);
            if ($returnValue)
            {
            	break;
            }
        }
        return $returnValue;
    }

    /**
     * @param string $eventName
     * @return boolean
     */
    public function hasListeners($eventName)
    {
        return (Boolean) count($this->_listeners[$eventName]);
    }

    /**
     * @param string $eventName
     * @return array
     */
    public function getListeners($eventName)
    {
        if (!isset($this->_listeners[$eventName]))
        {
            return array();
        }
        return $this->_listeners[$eventName];
    }
}
