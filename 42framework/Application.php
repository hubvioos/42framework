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

class ApplicationException extends \Exception { }

class Application
{
	/**
	 * @var Framework\Application
	 */
	protected static $_instance = null;
	
	protected $_container = null;
	
	protected function __construct(ApplicationContainer $container)
	{
		$this->_container = $container;
	}
	
	/**
	 * Returns the unique instance of Framework\Application
	 * 
	 * @return Framework\Application
	 */
	public static function getInstance (ApplicationContainer $container = null)
	{
		if (self::$_instance === null)
		{
			if ($container === null)
			{
				throw new ApplicationException('Invalid argument : $container is null');
			}
			self::$_instance = new self($container);
		}
		return self::$_instance;
	}
	
	protected function __clone () { }


	public function bootstrap ()
	{
		$this->_container->getClassLoader();
		$this->_container->getErrorHandler();
		
		/* @var $router Libs\Route */
		$router = $this->_container->getRouterClass();
		$config = $this->_container->getConfig();
		$router::init($config['routes']);
		
		return $this;
	}
	
	public function duplicateContentPolicy ($url, $path, $params)
	{
		// Redirect to root if we use the default module and action.
		if ($url != '' 
		    && $params['module'] == $this->_container->config['defaultModule']
		    && $params['action'] == $this->_container->config['defaultAction']
		    && empty($params['params'])
		    )
		{
		    Response::getInstance()->redirect($this->_container->config['siteUrl'], 301, true);
		}
		// Avoid duplicate content of the routes.
		else if ($url != Libs\Route::pathToUrl($path)
			&& $url != '')
		{
		    Response::getInstance()->redirect($this->_container->config['siteUrl'] . Libs\Route::pathToUrl($path), 301, true);
		}
						
		// Avoid duplicate content with just a "/" after the URL
		if(strrchr($url, '/') === '/')
		{
		    Response::getInstance()->redirect($this->_container->config['siteUrl'] . rtrim($url, '/'), 301, true);  
		}
	}
	
	/**
	 * @param \Framework\Context $context
	 */
	public function requestSecurityPolicy ($context)
	{
		$previousIpAddress = $context->getPreviousIpAddress();
		$previousUserAgent = $context->getPreviousUserAgent();
					
		if ($previousIpAddress !== null 
			&& $previousIpAddress != $context->getIpAddress()
			&& $previousUserAgent !== null
			&& $previousUserAgent != $context->getUserAgent()
			)
		{
			Libs\Session::destroyAll();
			
			Libs\Message::add(Libs\Session::getInstance('message'),'warning',
				'It seems that your session has been stolen, we destroyed it for security reasons. Check your environment security.');
			
			Response::getInstance()->redirect($this->_container->config['siteUrl'], 301, true);
		}
	}
	
	/**
	 * @return \Framework\ApplicationContainer
	 */
	public function getContainer()
	{
		return $this->_container;
	}
	
	/**
	 * Load the action $action, from the module $module. Shortcut for ClassLoader::loadController()
	 * 
	 * @param string $module
	 * @param string $action
	 * @return Framework\Controller
	 */
	public function loadAction($module, $action)
	{
		/* @var $loader Libs\ClassLoader */
		$loader = $this->_container->getClassLoader();
		return $loader::loadController($module, $action);
	}
	
	/**
	 * Load the model $model, from the module $module. Shortcut for ClassLoader::loadModel()
	 * 
	 * @param string $module
	 * @param string $model
	 * @return Framework\Model
	 */
	public function loadModel($module, $model)
	{
		/* @var $loader Libs\ClassLoader */
		$loader = $this->_container->getClassLoader();
		return $loader::loadModel($module, $model);
	}
	
	/**
	 * Main execution method
	 * 
	 * @return Framework\Core
	 */
	public function run()
	{
		$config = $this->_container->getConfig();
		if (PHP_SAPI === 'cli')
		{
			$params = \Application\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';
			
			$state = Request::CLI_STATE;
		}
		else
		{
			$url = $this->_container->getContext()->getUrl();
			/* @var $router Libs\Route */
			$router = $this->_container->getRouterClass();
			$path = $router::urlToPath($url, $config['defaultModule'], $config['defaultAction']);
			$params = Libs\Route::pathToParams($path);
			
			$state = Request::FIRST_REQUEST;
			
			// Views variables
			/* @var $view View */
			$view = $this->_container->getViewClass();
			$view::setGlobal('layout', $config['defaultLayout']);
			$view::setGlobal('message', $this->_container->getSession('message'));
			
			/* @var $loader Libs\ClassLoader */
			$loader = $this->_container->getClassLoader();
			if (!$loader::canLoadClass('Application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				$this->_container->getRequest('errors', 'error404', array(), Request::FIRST_REQUEST)->execute();
			}
			
			$this->duplicateContentPolicy($url, $path, $params);
			$this->requestSecurityPolicy($this->_container->getContext());
		}
		// Timezone
		date_default_timezone_set($config['defaultTimezone']);
		
		$this->_container->request = $this->_container->getRequest($params['module'], $params['action'], $params['params'], $state);
		
		$this->_container->response->setBody($this->_container->request->execute());
		return $this;
	}
	
	/**
	 * Render the request (send headers and display the response)
	 * 
	 * @param Framework\Response $response (optional)
	 */
	public function render($response = null)
	{
		if ($response !== null)
		{
			$this->_container->response = $response;
		}
		
		/* @var $response Response */
		$response = $this->_container->getResponse();
		/* @var $view View */
		$view = $this->_container->getViewClass();
		if ($view::getGlobal('layout') !== false)
		{
			$config = $this->_container->getConfig();
			if ($view::getGlobal('layout') === null)
			{
				$view::setGlobal('layout', $config['defaultLayout']);
			}
			$view::setGlobal('contentForLayout', $response->getBody());
			$response->clearResponse();
			$response->setBody($view::factory($config['defaultModule'], $view::getGlobal('layout')));
		}
		$response->send();
		echo $response;
		
		if ($response->getStatus() == 200)
		{
			/* @var $context Context */
			$context = $this->_container->getContext();
			$context->updateHistory(array(
				'url' => $context->getUrl(),
				'ipAddress' => $context->getIpAddress(),
				'userAgent' => $context->getUserAgent()
				));
		}
		exit();
	}
}