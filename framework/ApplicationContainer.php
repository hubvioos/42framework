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
 * @method \Framework\Libs\Message getMessage()
 * @method \Framework\Libs\Route getRoute()
 * @method \Framework\HttpRequest getHttpRequest()
 * @method \Framework\History getHistory()
 * @method \Framework\HttpResponse getHttpResponse()
 * @method \Framework\Response getResponse()
 * @method string getViewClass()
 * @method \Framework\Core getCore()
 * @method \Framework\filters\ApplicationFilter getApplicationFilter()
 */
class ApplicationContainer extends BaseContainer
{
	public function __construct (array $config = array())
	{
		$this->config = new Config($config, FRAMEWORK_DIR.DS.'config'.DS.'config.php');
		
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
		
		$this->httpRequest = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \Framework\HttpRequest($c->getHistory());
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
		
		$this->httpResponse = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new \Framework\HttpResponse();
			}
		);
		
		$this->response = function ($c)
		{
			return new \Framework\Response();
		};
		
		$this->viewClass = function ($c)
		{
			return 'Framework\\View';
		};
		
		$this->core = $this->asUniqueInstance(
			function ($c)
			{
				/* @var $c ApplicationContainer */
				return new Core($c);
			}
		);
		
		$this->applicationFilter = function ($c)
		{
			return new \Framework\filters\ApplicationFilter();
		};
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
	
	public function getDoctrineClassLoader($ns = null, $includePath = null)
	{
		return new \Doctrine\Common\ClassLoader($ns, $includePath);
	}
	
	public function getStaticClassLoader($autoload = array(), $autoloadPath = null)
	{
		return new Libs\StaticClassLoader($autoload, $autoloadPath);
	}
	
	public function classExists($className)
	{
		return \Doctrine\Common\ClassLoader::classExists($className);
	}
	
	public function getClassLoader($className)
	{
		return \Doctrine\Common\ClassLoader::getClassLoader($className);
	}
	
	/**
	 * @return \Framework\AppFilterChain
	 */
	public function getAppFilterChain($filters = array())
	{
		return new AppFilterChain($filters);
	}
	
	/**
	 * @return \Framework\ViewFilterChain
	 */
	public function getViewFilterChain($filters = array())
	{
		return new ViewFilterChain($filters);
	}
	
	public function getNewView($module, $file, $vars = false)
	{
		return new View($module, $file, $vars);
	}
	
	/**
	 * Load the action $action, from the module $module. Shortcut for ClassLoader::loadController()
	 * 
	 * @param string $module
	 * @param string $action
	 * @return Framework\Controller
	 */
	public function getAction($module, $action)
	{
		$controller = 'Application\\modules\\'.$module.'\\controllers\\'.$action;
		return new $controller;
	}
	
	/**
	 * Load the model $model, from the module $module. Shortcut for ClassLoader::loadModel()
	 * 
	 * @param string $module
	 * @param string $model
	 * @return Framework\Model
	 */
	public function getModel($module, $model)
	{	
		$model = 'Application\\modules\\'.$module.'\\models\\'.$model;
		
		static $models = array();
		
		if (!isset($models[$model]))
		{
			$models[$model] = new $model;
		}
		return $models[$model];
	}
}