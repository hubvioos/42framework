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

/**
 * @method \Framework\Config getConfig()
 * @method \Framework\ErrorHandler getErrorHandler()
 * @method \Framework\Libs\ClassLoader getClassLoader()
 * @method \Framework\Libs\Message getMessage()
 * @method \Framework\Libs\Route getRoute()
 * @method array getAutoload()
 * @method \Framework\Context getContext()
 * @method \Framework\History getHistory()
 * @method \Framework\Response getResponse() Returns the main instance of Response
 * @method \Framework\Response getNewResponse()
 * @method string getViewClass()
 * @method \Framework\Application getApplication()
 */
class ApplicationContainer extends BaseContainer
{
	public function __construct (array $config = array(), array $autoload = array())
	{
		$this->config = new Config($config, FRAMEWORK_DIR.DS.'config'.DS.'config.php');
		$this->autoload = $autoload;
		
		$this->classLoader = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $loader Libs\ClassLoader */
				$loader = '\\Framework\\Libs\\ClassLoader';
				/* @var $c ApplicationContainer */
				$loader::init($c->getAutoload(), FRAMEWORK_DIR.DS.'config'.DS.'autoload.php');
				return $loader;
			}
		);
		
		$this->errorHandler = $this->asUniqueInstance(
			function ($c)
			{
				$errorHandler = new \Framework\ErrorHandler();
				foreach ($c->config['errorHandlerListeners'] as $lis)
				{
					$errorHandler->attach(new $lis());
				}
				$errorHandler->start($c->config['errorReporting'], $c->config['displayErrors']);
				return $errorHandler;
			}
		);
		
		$this->context = $this->asUniqueInstance(
			function ($c)
			{
				return new \Framework\Context($c->getHistory());
			}
		);
		
		$this->history = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \Framework\History($c->getSession('history'), $c->config['historySize']);
			}
		);
		
		$this->message = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \Framework\Libs\Message($c->getSession('message'));
			}
		);
		
		$this->route = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \Framework\Libs\Route($c->config['routes']);
			}
		);
		
		$responseFunc = function ($c)
		{
			return new \Framework\Response();
		};
		$this->response = $this->asUniqueInstance($responseFunc);
		$this->newResponse = $responseFunc;
		
		$this->viewClass = function ($c)
		{
			return 'Framework\\View';
		};
		
		$this->application = $this->asUniqueInstance(
			function ($c)
			{
				return new \Framework\Application($c);
			}
		);
	}
	
	public function getSession($namespace = 'default')
	{
		static $session = array();
		
		if (!isset($session[$namespace]) || !$session[$namespace] instanceof \Framework\libs\Session)
		{
			$session[$namespace] = new \Framework\libs\Session($namespace);
		}
		
		return $session[$namespace];
	}
	
	/**
	 * @param string $module
	 * @param string $action
	 * @param array $params
	 * @param string $state
	 * @return \Framework\Request
	 */
	public function getNewRequest($module, $action, $params = array(), $state = null)
	{
		return new Request($module, $action, $params, $state);
	}
	
	public function getCurrentRequest()
	{
		return Request::getCurrent();
	}
	
	public function getNewView($module, $file, $vars = false)
	{
		return new View($module, $file, $vars);
	}
}