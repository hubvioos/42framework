<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ControllerException extends \Exception { }

class Controller
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
	
	/**
	 * Executes the action corresponding to the current request
	 * 
	 * @param Framework\Request $request
	 */
	public function execute(Request $request)
	{
		$this->_request = $request;
		$beforeResponse = $this->_before($this->_request);
		if ($beforeResponse === true)
		{
			$actionResponse = null;
			$actionResponse = call_user_func_array(array($this, 'processAction'), $this->_request->getParams());
			$actionResponse = $this->_after($this->_request, $actionResponse);
			
			if ($this->_view !== false)
			{
				if ($this->_view === null)
				{
					$this->setView($this->_request->getAction());
				}
				$this->_response = View::factory($this->_request->getModule(), $this->_view, $this->_vars);
			}
			else 
			{
				if ($actionResponse === null)
				{
					$actionResponse = '';
				}
				$this->_response = $actionResponse;
			}
		}
		elseif ($this->_response === null)
		{
			$this->_response = '';
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
		View::setGlobal('layout', $layout);
		return $this;
	}
	
	public function setMessage($message, $category = 'notice')
	{
		$session = Session::getInstance('message');
		Utils\Message::add($session, $category, $message);
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
	 * Sets a global variable for the view. Shortcut for View::setGlobal()
	 * 
	 * @param mixed $var
	 * @param mixed $value
	 */
	public function setGlobal($var, $value)
	{
		View::setGlobal($var, $value);
		return $this;
	}
	
	/**
	 * Filter executed before the action
	 * 
	 * @param Framework\Request $request
	 * @return mixed (boolean or Framework\Response)
	 */
	protected function _before(Request $request)
	{
		return true;
	}
	
	/**
	 * Filter executed after the action
	 * 
	 * @param Framework\Request $request
	 * @param mixed $actionResponse
	 * @return mixed
	 */
	protected function _after(Request $request, $actionResponse)
	{
		return $actionResponse;
	}
}