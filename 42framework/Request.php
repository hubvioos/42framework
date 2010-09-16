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
