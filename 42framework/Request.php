<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RequestException extends \Exception { }

class Request
{
	protected $_module = null;

	protected $_action = null;

	protected $_params = array();
	
	protected $_isInternal = true;
	
	protected $_method = 'GET';
	
    protected static $_current = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($_module, $_action, $_params, $_internal = true, $_method = 'GET')
	{
		$this->_module = $_module;
		$this->_action = $_action;
		$this->_params = $_params;
		$this->_method = $_method;
		$this->_isInternal = $_internal;
		Request::$_current = $this;
	}

	protected function __clone () { }

	public static function factory ()
	{
		$params = array();
		switch (func_num_args())
		{
			case 1:
				$params = Utils\Route::pathToParams(func_get_arg(0));
				$params['internal'] = true;
				break;
			case 2:
				$params = Utils\Route::pathToParams(func_get_arg(0));
				$params['internal'] = func_get_arg(1);
				break;
			case 3:
				list($params['module'], $params['action'], $params['params']) = func_get_args();
				$params['internal'] = true;
				break;
			case 4:
				list($params['module'], $params['action'], $params['params'], $params['internal']) = func_get_args();
				break;
			default:
				throw new RequestException('Request::factory : invalid arguments');
		}
		return new Request($params['module'], $params['action'], $params['params'], $params['internal']);
	}
	
	public function getCurrent ()
	{
		return Request::$_current;
	}
	
	public function execute ()
	{
		$module = Core::loadAction(Request::$_current->_module, Request::$_current->_action);
		return $module->execute(Request::$_current);
	}
	
	/**
	 * @return the $_module
	 */
	public function getModule ()
	{
		return $this->_module;
	}

	/**
	 * @return the $_action
	 */
	public function getAction ()
	{
		return $this->_action;
	}

	/**
	 * @return the $_params
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	/**
	 * @return the $_method
	 */
	public function getMethod ()
	{
		return $this->_method;
	}

	public function isInternal ()
	{
		return $this->_isInternal;
	}
}
