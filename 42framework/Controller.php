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
	 * Contains the main response
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
		$this->before();
		$actionResponse = call_user_func_array(array($this, $request->action), $request->params);
		$this->after();
		
		if ($this->view !== false)
		{
			if ($this->view === null)
			{
				$this->setView($request->action);
			}
			$response = Response::factory(View::factory($this->view, $this->vars));
		}
		else 
		{
			$response = Response::factory($actionResponse);
		}
		
		$this->view = null;
		$this->vars = array();
		
		return $response;
	}

	/**
	 * Sets the view of the current action
	 * 
	 * @param mixed $view (string or false)
	 */
	public function setView ($view)
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
	public function set ($var, $value = false)
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
	
	public function before ()
	{
		
	}
	
	public function after ()
	{
		
	}
}