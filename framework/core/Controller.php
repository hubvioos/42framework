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

class ControllerException extends \Exception
{
	
}

class ControllerDependencyException extends \Exception
{
	
	protected static $_SATISFIED = '<span style="color:green; font-weight:bold;">SATISFIED</span>';
	protected static $_MISSING = '<span style="color:red; font-weight:bold;">UNSATISFIED (not installed)</span>';
	protected static $_UNSATISFIED = '<span style="color:red; font-weight:bold;">UNSATISFIED (version %f installed)</span>';
	protected static $_SCHRODINGER = '<span style="color:orange; font-weight:bold;">UNABLE TO CHECK THE VERSION NUMBER</span>';

		
	/**
	 * Constructor
	 * @param string $unsatisfiedModule The name of the unsatisfied
	 * @param array $modulesConfig The config options for the modules i.e. $config['modules']
	 */
	public function __construct($unsatisfiedModule, array $modulesConfig)
	{
		echo 'exception for '.$unsatisfiedModule;
		
		// write a kickass message
		$this->message = '<p>The module "<em>'.$unsatisfiedModule
			.'</em>" couldn\'t be properly requested because '
			.'one or more of its dependencies are not satified.</p>'
			.'<p>List of the dependencies: </p><ul>';
		
		foreach($modulesConfig[$unsatisfiedModule]['dependencies'] as $dependency => $version)
		{
			$this->message .= '<li><span style="font-style:italic">'.$dependency
					.'</span> version <span style="font-weight:bold;">'.$version.'</span> : ';
			
			if(!isset($modulesConfig[$dependency]))
			{
				$this->message .= self::$_MISSING;
			}
			else if(!isset($modulesConfig[$dependency]['version']))
			{
				$this->message .= self::$_SCHRODINGER;
			}
			else if($modulesConfig[$dependency]['version'] < $version)
			{
				$this->message .= sprintf(self::$_UNSATISFIED, $modulesConfig[$dependency]['version']);
			}
			else if($modulesConfig[$dependency]['version'] >= $version)
			{
				$this->message .= self::$_SATISFIED;
			}
			
			$this->message .= '</li>';
		}
		
		$this->message .= '</ul>';
	}
}

abstract class Controller extends \framework\core\FrameworkObject
{

	/**
	 * the view corresponding to the current action
	 *
	 * @var mixed (null, string or false)
	 */
	protected $_view = null;

	/**
	 * Contains vars for the view
	 *
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * Contains the current request
	 *
	 * @var Framework\Request
	 */
	protected $_request = null;

	/**
	 * Contains the response
	 *
	 * @var mixed (View, string or null)
	 */
	protected $_response = null;


	/* Controller parameters */
	protected $usesView = true;
	protected $usesLayout = true;
	protected $isInternal = false;

	/**
	 * Executes the action corresponding to the current request
	 *
	 * @param framework\core\Request $request
	 */
	public function execute (Request &$request, Response &$response)
	{
		$this->_request = $request;
		$this->_response = $response;

		if ($this->isInternal && $this->_request->getState() == \framework\core\Request::FIRST_REQUEST)
		{
			$this->_response->setStatus(\framework\core\Response::OUTSIDE_ACCESS_FORBIDDEN);
		}
		else
		{
			// check if the module's dependencies are not unsatisfied (they can be SCHRODINGER !)
			if ($this->getConfig('modules.'.$request->getModule().'.dependenciesSatisfied') !== \framework\libs\ConfigBuilder::DEPENDENCIES_UNSATISFIED)
			{
				//Preparation to "before" and "after" events lauching
				//$classPath = 'application\\modules\\' . $request->getModule() . '\\controllers\\' . $request->getAction();
				$beforeName = $request->getModule().'.before' . \ucfirst($request->getAction());
				$afterName = $request->getModule().'.after' . \ucfirst($request->getAction());
				
				$methodName = 'process'.\ucfirst($this->getComponent('httpRequest')->getMethod());
				
				if (!\method_exists($this, $methodName))
				{
					$methodName = 'processAction';
				}
				
				//Launch Before event
				$this->raiseEvent($beforeName);
				
				if ($this->_before() !== false
						&& $this->{$methodName}($this->_request) !== false
						&& $this->_after() !== false
				)
				{
					//Lauch After event
					$this->raiseEvent($afterName);

					if ($this->usesView)
					{
						if ($this->usesLayout == false)
						{
							$this->setLayout(false);
						}

						if ($this->_view === null)
						{
							$this->setView($this->_request->getAction());
						}
						$this->_response->setContent($this->createView($this->_request->getModule(), $this->_view, $this->_vars, $this->_response->getFormat()));
					}

					$this->_response->setStatus(\framework\core\Response::SUCCESS);
				}
				else
				{
					$this->_response->setStatus(\framework\core\Response::ERROR);
				}
			}
			else
			{
				throw new \framework\core\ControllerDependencyException($request->getModule(), $this->getConfig('modules'));
			}
		}

		return $this->_response;
	}
	
	public function forward ($module, $action)
	{
		$this->_request->set('module', $module);
		$this->_request->set('action', $action);
		$this->setResponse($this->_request->execute());
	}

	/**
	 * Sets the view of the current action
	 *
	 * @param mixed $view (string or false)
	 */
	public function setView ($view)
	{
		if ($view === false)
		{
			$this->usesView = false;
		}
		else
		{
			$this->_view = $view;
		}

		return $this;
	}

	public function setLayout ($layout)
	{
		if ($layout === false)
		{
			$this->usesLayout = false;
		}
		
		$this->viewSetGlobal('layout', $layout);
		return $this;
	}

	public function setMessage ($message, $category = 'notice')
	{
		$this->getComponent('message')->set($message, $category);
	}

	/**
	 * Sets a variable for the view
	 *
	 * @param mixed $var (array or string)
	 * @param mixed $value
	 */
	public function set ($var, $value = false)
	{
		if (is_array($var))
		{
			$this->_vars = array_merge_recursive($this->_vars, $var);
		}
		else
		{
			$this->_vars[$var] = $value;
		}
		return $this;
	}

	/**
	 * Filter executed before the action
	 *
	 * @param Framework\Request $request
	 * @return mixed (boolean or Framework\Response)
	 */
	protected function _before ()
	{
		
	}

	/**
	 * Filter executed after the action
	 *
	 * @param Framework\Request $request
	 * @param mixed $actionResponse
	 * @return mixed
	 */
	protected function _after ()
	{
		
	}

	public function getRequest ()
	{
		return $this->_request;
	}

	public function setResponse (Response $response)
	{
		$this->_response = $response;
	}

	public function getResponse ()
	{
		return $this->_response;
	}

}
