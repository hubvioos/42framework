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
namespace framework\core;
defined('FRAMEWORK_DIR') or die('Invalid script access');

/**
 * @method \framework\libs\Config getConfig()
 * @method \framework\errorHandler\ErrorHandler getErrorHandler()
 * @method \framework\libs\Message getMessage()
 * @method \framework\libs\Route getRoute()
 * @method \framework\core\HttpRequest getHttpRequest()
 * @method \framework\libs\History getHistory()
 * @method \framework\core\HttpResponse getHttpResponse()
 * @method \framework\core\Response getResponse()
 * @method string getViewClass()
 * @method \framework\core\Core getCore()
 * @method \framework\filters\appFilters\ApplicationFilter getApplicationFilter()
 * @method \framework\events\EventManager getEventManager()
 */
class ApplicationContainer extends \framework\libs\BaseContainer
{
	public function __construct (array $config = array())
	{
		$this->config = new \framework\libs\Config($config, FRAMEWORK_DIR.DS.'config'.DS.'config.php');
		
		$this->errorHandler = $this->asUniqueInstance(
			function ($c)
			{
				$errorHandler = new \framework\errorHandler\ErrorHandler();
				foreach ($c->config['errorHandlerListeners'] as $lis)
				{
					$errorHandler->attach(new $lis());
				}
				$errorHandler->start($c->config['errorReporting'], $c->config['displayErrors']);
				return $errorHandler;
			}
		);
		
		$this->httpRequest = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\core\HttpRequest($c->getHistory());
			}
		);
		
		$this->history = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\libs\History($c->getSession('history'), $c->config['historySize']);
			}
		);
		
		$this->message = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\libs\Message($c->getSession('message'));
			}
		);
		
		$this->route = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\libs\Route($c->config['routes']);
			}
		);
		
		$this->httpResponse = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\core\HttpResponse();
			}
		);
		
		$this->response = function ($c)
		{
			return new \framework\core\Response();
		};
		
		$this->viewClass = function ($c)
		{
			return 'framework\\core\\View';
		};
		
		$this->core = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\core\Core($c);
			}
		);
		
		$this->applicationFilter = function ($c)
		{
			return new \framework\filters\appFilters\ApplicationFilter();
		};
		
		$this->eventManager = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \framework\events\EventManager($c->config['events']);
			}
		);
	}
	
	/**
	 * @param string $namespace
	 * @return \framework\libs\Session
	 */
	public function getSession($namespace = 'default')
	{
		static $session = array();
		
		if (!isset($session[$namespace]) || !$session[$namespace] instanceof \framework\libs\Session)
		{
			$session[$namespace] = new \framework\libs\Session($namespace);
		}
		
		return $session[$namespace];
	}
	
	/**
	 * @param string $module
	 * @param string $action
	 * @param array $params
	 * @param string $state
	 * @return \Framework\core\Request
	 */
	public function getNewRequest($module, $action, $params = array(), $state = null)
	{
		return new \framework\core\Request($module, $action, $params, $state);
	}
	
	/**
	 * @return \Framework\core\Request
	 */
	public function getCurrentRequest()
	{
		return \framework\core\Request::getCurrent();
	}
	
	/**
	 * @param string $namespace
	 * @param string $folder
	 * @param string $separator
	 * @param string $extension
	 * @return \framework\libs\ClassLoader
	 */
	public function getClassLoader($namespace, $folder, $separator = null, $extension = null)
	{
		return new \framework\libs\ClassLoader($namespace, $folder, $separator, $extension);
	}
	
	/**
	 * @param array $autoload
	 * @param string $autoloadPath
	 * @return \framework\libs\StaticClassLoader
	 */
	public function getStaticClassLoader($autoload = array(), $autoloadPath = null)
	{
		return new \framework\libs\StaticClassLoader($autoload, $autoloadPath);
	}
	
	/**
	 * @param array $filters
	 * @return \framework\filters\FilterChain
	 */
	public function getFilterChain($filters = array())
	{
		return new \framework\filters\FilterChain($filters);
	}
	
	/**
	 * @param string $module
	 * @param string $file
	 * @param mixed $vars
	 * @return \framework\core\View
	 */
	public function getNewView($module, $file, $vars = false)
	{
		return new \framework\core\View($module, $file, $vars);
	}
	
	/**
	 * Load the action $action, from the module $module. Shortcut for ClassLoader::loadController()
	 * 
	 * @param string $module
	 * @param string $action
	 * @return Framework\core\Controller
	 */
	public function getAction($module, $action)
	{
		$controller = 'application\\modules\\'.$module.'\\controllers\\'.$action;
		return new $controller;
	}
	
	/**
	 * Load the model $model, from the module $module. Shortcut for ClassLoader::loadModel()
	 * 
	 * @param string $module
	 * @param string $model
	 * @return Framework\core\Model
	 */
	public function getModel($module, $model)
	{	
		$model = 'application\\modules\\'.$module.'\\models\\'.$model;
		
		static $models = array();
		
		if (!isset($models[$model]))
		{
			$models[$model] = new $model;
		}
		return $models[$model];
	}
}