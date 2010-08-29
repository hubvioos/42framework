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
	protected $view = null;

	/**
	 * Contains vars for the view
	 * 
	 * @var array
	 */
	protected $vars = array();
	
	/**
	 * Contains the current request
	 * 
	 * @var Framework\Request
	 */
	protected $request = null;
	
	/**
	 * Contains, eventually, a response
	 * 
	 * @var Framework\Response
	 */
	protected $response = null;
	

	public function __construct ()
	{
		
	}

	/**
	 * Executes the action corresponding to the current request
	 * 
	 * @param Framework\Request $request
	 */
	public function execute (Request $request)
	{
		$this->request = $request;
		$beforeResponse = $this->before($this->request);
		if ($beforeResponse === true)
		{
			$actionResponse = call_user_func_array(array($this, $this->request->action), $this->request->params);
			$actionResponse = $this->after($this->request, $actionResponse);
			
			if ($this->view !== false)
			{
				if ($this->view === null)
				{
					$this->setView($this->request->action);
				}
				$response = View::factory($this->view, $this->vars);
			}
			else 
			{
				if ($actionResponse === null)
				{
					$actionResponse = '';
				}
				$response = $actionResponse;
			}
		}
		else
		{
			if ($this->response !== null)
			{
				$response = $this->response;
			}
			else 
			{
				$response = '';
			}
		}
		
		$this->request = null;
		$this->response = null;
		$this->view = null;
		$this->vars = array();
		
		return $response;
	}

	/**
	 * Sets the view of the current action
	 * 
	 * @param mixed $view (string or false)
	 */
	protected function setView ($view)
	{
		$this->view = $view;
		return $this;
	}

	/**
	 * Sets a variable for the view
	 * 
	 * @param mixed $var (array or string)
	 * @param mixed $value
	 */
	protected function set ($var, $value = false)
	{
		if (is_array($var))
		{
			array_merge($this->vars, $var);
		}
		else
		{
			$this->vars[$var] = $value;
		}
		return $this;
	}
	
	/**
	 * Filter executed before the action
	 * 
	 * @param Framework\Request $request
	 * @return mixed (boolean or Framework\Response)
	 */
	protected function before (Request $request)
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
	protected function after (Request $request, $actionResponse)
	{
		return $actionResponse;
	}
}