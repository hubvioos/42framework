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

class ApplicationContainerException extends \Exception { }

class ApplicationContainer extends BaseContainer
{
	public function __construct (array $config = array(), array $autoload = array())
	{		
		if (empty($config))
		{
			require FRAMEWORK_DIR.DS.'config'.DS.'config.php';
		}
		$this->config = new \ArrayIterator($config);
		$this->autoload = $autoload;
		$this->classLoader = function ($c) {
			static $loader = null;
			if ($loader === null)
			{
				/* @var $loader Libs\ClassLoader */
				$loader = '\\Framework\\Libs\\ClassLoader';
				$loader::init($c->getAutoload(), FRAMEWORK_DIR.DS.'config'.DS.'autoload.php');
			}
			return $loader;
		};
		$this->errorHandler = function ($c) {
			static $errorHandler = null;
			if ($errorHandler === null)
			{
				$errorHandler = \Framework\ErrorHandler::getInstance();
				foreach ($c->config['errorHandlerListeners'] as $lis)
				{
					$errorHandler->attach(new $lis());
				}
				$errorHandler->start($c->config['errorReporting'], $c->config['displayErrors']);
			}
			return $errorHandler;
		};
		$this->routerClass = function ($c) {
			return 'Framework\\Libs\\Route';
		};
		$this->context = function ($c) {
			return \Framework\Context::getInstance($c->history);
		};
		$this->history = function ($c) {
			/* @var $c ApplicationContainer */
			return \Framework\History::getInstance($c->getSession('history'), $c->config['historySize']);
		};
		$this->sessionClass = function ($c) {
			return 'Framework\\Libs\\Session';
		};
		$this->requestClass = function ($c) {
			return 'Framework\\Request';
		};
		$this->response = function ($c) {
			return \Framework\Response::getInstance();
		};
		$this->viewClass = function ($c) {
			return 'Framework\\View';
		};
		
		return $this;
	}
	
	public function getSession($namespace = 'default')
	{
		/* @var $session Libs\Session */
		$session = $this->sessionClass;
		return $session::getInstance($namespace);
	}
	
	public function getRequest($module, $action, $params = array(), $state = null)
	{
		/* @var $request Request */
		$request = $this->requestClass;
		return $request::factory($module, $action, $params, $state);
	}
}