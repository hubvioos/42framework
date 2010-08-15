<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ControllerException extends Exception { }

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
		$actionResponse = call_user_func_array(array($this, $request->action), $request->params);
		
		if ($this->view !== false)
		{
			if ($this->view == null)
			{
				$this->setView(Config::$config['defaultView']);
			}
			return Response::factory(View::factory($this->view, $this->vars)->render());
		}
		else 
		{
			return Response::factory($actionResponse);
		}
	}

	/**
	 * Sets the view of the current action
	 * 
	 * @param mixed $view (string or false)
	 */
	public function setView ($view)
	{
		$this->view = $view;
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
	}
}