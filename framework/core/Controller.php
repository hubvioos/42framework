<?php
/**
 * Copyright (C) 2010 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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

defined('FRAMEWORK_DIR') or die('Invalid script access');

class ControllerException extends \Exception { }

class Controller extends \framework\core\FrameworkObject
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
	
	protected $_httpRequest = null;
	
	protected $_httpResponse = null;
	
	/**
	 * Executes the action corresponding to the current request
	 * 
	 * @param Framework\Request $request
	 */
	public function execute(HttpRequest &$httpRequest, HttpResponse &$httpResponse)
	{
		$this->_httpRequest = $httpRequest;
		$this->_httpResponse = $httpResponse;
		$this->_request = $httpRequest->getRequest();
		$this->_response = $this->getContainer()->getResponse();
		$this->_before($this->_request, $this->_response);
		if ($this->_response->getStatus() !== \framework\core\Response::ERROR)
		{
			call_user_func_array(array($this, 'processAction'), $this->_request->getParams());
			$this->_after($this->_request, $this->_response);
			
			if ($this->_view !== false)
			{
				if ($this->_view === null)
				{
					$this->setView($this->_request->getAction());
				}
				$this->_response->set($this->getContainer()->getNewView($this->_request->getModule(), $this->_view, $this->_vars));
			}
		}
		return $this->_response;
	}

	/**
	 * Sets the view of the current action
	 * 
	 * @param mixed $view (string or false)
	 */
	public function setView($view)
	{
		$this->_view = $view;
		return $this;
	}
	
	public function setLayout($layout)
	{
		$view = $this->viewSetGlobal('layout', $layout);
		return $this;
	}
	
	public function setMessage($message, $category = 'notice')
	{
		$this->getContainer()->getMessage()->set($message, $category);
	}

	/**
	 * Sets a variable for the view
	 * 
	 * @param mixed $var (array or string)
	 * @param mixed $value
	 */
	public function set($var, $value = false)
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
	protected function _before(Request &$request, Response &$response) { }
	
	/**
	 * Filter executed after the action
	 * 
	 * @param Framework\Request $request
	 * @param mixed $actionResponse
	 * @return mixed
	 */
	protected function _after(Request &$request, Response &$response) { }
	
	public function getHttpRequest()
	{
		return $this->_httpRequest;
	}
	
	public function getHttpResponse()
	{
		return $this->_httpResponse;
	}
}