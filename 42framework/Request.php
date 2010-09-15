<?php
namespace Framework;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class RequestException extends \Exception { }

class Request
{
	protected $_module = null;

	protected $_action = null;

	protected $_params = array();
	
	protected $_state = null;
	
	const DEFAULT_STATE = -1;
	const CLI_STATE = -50;
	const FIRST_REQUEST = -100;
	
    protected static $_current = null;

	/*
		Constructeur de la classe, partie importante pour l'exécution de la page.
		Cette méthode s'occupe de déterminer le module et l'action à appeler, en faisant appel à Route.
	*/
	protected function __construct ($module, $action, $params, $state)
	{
		$this->_module = $module;
		$this->_action = $action;
		$this->_params = $params;
		$this->_state = $state;
		self::$_current = $this;
	}

	protected function __clone () { }

	public static function factory ($module, $action, Array $params = array(), $state = self::DEFAULT_STATE)
	{
		return new self($module, $action, $params, $state);
	}
	
	public function getCurrent ()
	{
		return self::$_current;
	}
	
	public function execute ()
	{
		$module = Core::loadAction($this->_module, $this->_action);
		return $module->execute($this);
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

	public function getState ()
	{
		return $this->_state;
	}
}
