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

namespace framework\core;

class EventManager extends \framework\core\FrameworkObject
{
    /**
     *  All listeners
     * @var array
     */
    protected $_listeners = null;

    /**
     * @param array $events
     */
    public function __construct($listenersConfig)
    {
		$this->_listeners = $listenersConfig;	
    }


    /**
     * @param string $eventName
     * @param array $listener
     * @return \framework\events\EventManager
     */
    public function addListener($eventName, Array $listener)
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
     * @param array $listener
     * @return \framework\events\EventManager
     */
    public function removeListener($eventName, Array $listener = array())
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
     * @param \framework\libs\Event $event
     * @return mixed
     */
    public function dispatchEvent(\framework\libs\Event $event)
    {
	$eventName = $event->getName();
	$params = $event->getParamaters();
        $returnValue = null;
        
        if(isset($this->_listeners[$eventName]))
        {
            foreach ($this->_listeners[$eventName] as $listener)
            {
		//Check if it's directly callable
		if(\is_callable($listener['callable']))
		{		
			$returnValue= call_user_func_array($listener['callable'], $params);
		}
		elseif(\is_a($listener['callable'], '\\framework\\libs\\Registry') && !empty($listener['callable']))
		{
			//Check if it's an callable array
			if(\is_callable(array($listener['callable'][0], $listener['callable'][1])))
			{
				$returnValue = \call_user_func_array(array($listener['callable'][0], $listener['callable'][1]), $params);
			}
			//Check if it's a component
			else
			{
				//Get the component...
				$component = null;
				if(isset($listener['params']))
				{
					$component = $this->getComponent($listener['callable'][0] , $listener['params']);
				}
				else
				{
					$component = $this->getComponent($listener['callable'][0]);
				}

				//...and call the specified method
				$returnValue = \call_user_func_array(array($component, $listener['callable'][1]) , $params);
			}
		}
		else
		{
			throw new \InvalidArgumentException('The argument is not a callable.');
		}
   
                if ($returnValue)
                {
                    break;
                }
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
