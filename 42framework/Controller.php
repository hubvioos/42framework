<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class ControllerException extends Exception { }

class Controller
{	
	protected $view = null;

	protected $vars = array();

	public function __construct ()
	{
		
	}

	public function execute (Request $request)
	{
		$response = Response::factory();
		$response = call_user_func_array(array($this, $request->action), $request->params);
		
		if ($this->view !== false)
		{
			if ($this->view == null)
			{
				$this->setView(Config::$config['defaultView']);
			}
			Response::factory($response);
		}
	}

	public function setView ($view)
	{
		$this->view = $view;
	}

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