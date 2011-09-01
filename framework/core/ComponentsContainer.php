<?php 
/**
 * Copyright (C) 2011 - Kévin O'NEILL, François KLINGLER - <contact@42framework.com>
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

/**
 * @method \framework\libs\Config getConfig()
 * @method \framework\errorHandler\ErrorHandler getErrorHandler()
 * @method \framework\libs\Route getRoute()
 * @method \framework\core\HttpRequest getHttpRequest()
 * @method \framework\libs\History getHistory()
 * @method \framework\core\HttpResponse getHttpResponse()
 * @method \framework\core\Response getResponse()
 * @method string getViewClass()
 * @method \framework\events\EventManager getEventManager()
 * @method \framework\filters\FilterChain getFilterChain()
 */
class ComponentsContainer extends \framework\libs\BaseContainer
{
	public function __construct (\framework\libs\Registry $config)
	{
		$this->config = $config;
		
		/*
		 * Core
		 *
		 */
		
		$this->core = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ComponentsContainer */
				return new \framework\core\Core($c);
			}
		);
				
		$this->filterChain = function ($c, $args)
		{
			return new \framework\filters\FilterChain();
		};
		
		$this->httpRequest = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ApplicationContainer */
				return new \framework\core\HttpRequest($c->getHistory());
			}
		);
		
		$this->httpResponse = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ApplicationContainer */
				return new \framework\core\HttpResponse();
			}
		);
		
		$this->request = function ($c, $args)
		{
			$module = $args[0];
			$action = $args[1];
			$params = isset($args[2]) ? $args[2] : array();
			$state = isset($args[3]) ? $args[3] : null;
			
			return new \framework\core\Request($module,$action,$params,$state);	
		};
		
		$this->response = function ($c, $args)
		{
			return new \framework\core\Response();
		};
		
		$this->action = function ($c, $args)
		{
			$module = $args[0];
			$action = $args[1];
			
			$controller = 'application\\modules\\'.$module.'\\controllers\\'.$action;
			return new $controller;
		};
		
		$this->model = function ($c, $args)
		{
			$module = $args[0];
			$action = $args[1];
			
			$model = 'application\\modules\\'.$module.'\\models\\'.$model;
			return new $model;	
		};
		
		$this->view = function ($c, $args)
		{
			$module = $args[0];
			$action = $args[1];
			$vars = isset($args[2]) ? $args[2] : false;
			
			return new \framework\core\View($module,$action,$vars);
		};
		
		$this->eventManager = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ApplicationContainer */
				return new \framework\libs\EventManager($c->config->get('events'));
			}
		);
		
		
		/*
		 * Libs
		 *
		 */
		
		$this->errorHandler = $this->asUniqueInstance(
			function ($c, $args)
			{
				$errorHandler = new \framework\errorHandler\ErrorHandler();
				foreach ($c->config['errorHandlerListeners'] as $lis)
				{
					$errorHandler->attach(new $lis());
				}
				$errorHandler->init($c->config['errorReporting'], $c->config['displayErrors']);
				return $errorHandler;
			}
		);
		
		$this->history = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ApplicationContainer */
				return new \framework\libs\History($c->getSession('history'), $c->config['historySize']);
			}
		);
		
		$this->route = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ApplicationContainer */
				return new \framework\libs\Route($c->config['routes']->toArray());
			}
		);
		
		$this->message = $this->asUniqueInstance(
			function ($c, $args)
			{
				/* @var $c ComponentsContainer */
				return new \framework\libs\Message($c->getSession('flash'));
			}
		);
		
		$this->session = function ($c, $args)
		{
			$namespace = isset($args[0]) ? $args[0] : 'default';
			
			return new \framework\libs\Session($namespace);
		};
		
		
		/*
		 * Doctrine
		 * 
		 */
		
		$this->entityManager = $this->asUniqueInstance(
			function ($c, $args)
			{
				if ($c->config['environment'] == 'development')
				{
					$cache = new \Doctrine\Common\Cache\ArrayCache;
				}
				else
				{
					$cache = new \Doctrine\Common\Cache\ApcCache;
				}

				$config = new \Doctrine\ORM\Configuration;
				
				$config->setMetadataCacheImpl($cache);
				$config->setQueryCacheImpl($cache);
				
				$driverImpl = $config->newDefaultAnnotationDriver(MODULES_DIR);
				$config->setMetadataDriverImpl($driverImpl);
				
				$config->setProxyDir(BUILD_DIR);
				$config->setProxyNamespace('application\proxies');

				if ($c->config['environment'] == 'development')
				{
					$config->setAutoGenerateProxyClasses(true);
				}
				else
				{
					$config->setAutoGenerateProxyClasses(false);
				}

				return \Doctrine\ORM\EntityManager::create($c->config->get('dbConnectionParams', true), $config);
			}
		);
	}
}