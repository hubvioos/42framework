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

class Application extends FrameworkObject
{	
	protected $filterChain = null;
	
	public function __construct(ApplicationContainer $container)
	{
		$this->setContainer($container);
	}

	public function bootstrap ()
	{
		$this->getContainer()->getErrorHandler();
		$this->getContainer()->getRoute();
		$this->filterChain = $this->getContainer()->getFilterChain($this->getContainer()->config['applicationFilters']);
		return $this;
	}
	
	public function duplicateContentPolicy ($url, $path, $params)
	{
		// Redirect to root if we use the default module and action.
		if ($url != '' 
		    && $params['module'] == $this->getContainer()->config['defaultModule']
		    && $params['action'] == $this->getContainer()->config['defaultAction']
		    && empty($params['params'])
		    )
		{
		    $this->getContainer()->getNewResponse()->redirect($this->getContainer()->config['siteUrl'], 301, true);
		}
		// Avoid duplicate content of the routes.
		else if ($url != $this->getContainer()->getRoute()->pathToUrl($path)
			&& $url != '')
		{
		    $this->getContainer()->getNewResponse()
		    		->redirect($this->getContainer()->config['siteUrl'] . $this->getContainer()->getRoute()->pathToUrl($path), 301, true);
		}
						
		// Avoid duplicate content with just a "/" after the URL
		if(strrchr($url, '/') === '/')
		{
		    $this->getContainer()->getNewResponse()->redirect($this->getContainer()->config['siteUrl'] . rtrim($url, '/'), 301, true);  
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
			$this->getContainer()->getSession()->destroyAll();
			$this->getContainer()->getMessage()
				->set('It seems that your session has been stolen, we destroyed it for security reasons. Check your environment security.', 'warning');
			$this->getContainer()->getNewResponse()->redirect($this->getContainer()->config['siteUrl'], 301, true);
		}
	}
	
	public function viewSetGlobal($key, $value)
	{
		$view = $this->getContainer()->getViewClass();
		/* @var $view View */
		$view::setGlobal($key, $value);
	}
	
	public function viewGetGlobal($key)
	{
		$view = $this->getContainer()->getViewClass();
		/* @var $view View */
		return $view::getGlobal($key);
	}
	
	/**
	 * Main execution method
	 * 
	 * @return Framework\Core
	 */
	public function run()
	{
		$config = $this->getContainer()->getConfig();
		if (PHP_SAPI === 'cli')
		{
			$params = \Application\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';
			
			$state = Request::CLI_STATE;
		}
		else
		{
			$url = $this->getContainer()->getContext()->getUrl();
			
			$path = $this->getContainer()->getRoute()->urlToPath($url, $config['defaultModule'], $config['defaultAction']);
			$params = $this->getContainer()->getRoute()->pathToParams($path);
			
			$state = Request::FIRST_REQUEST;
			
			// Views variables
			/* @var $view View */
			$view = $this->getContainer()->getViewClass();
			$this->viewSetGlobal('layout', $config['defaultLayout']);
			$this->viewSetGlobal('messages', $this->getContainer()->getMessage()->getAll());
			
			if (!$this->getContainer()->classExists('Application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				$this->getContainer()->getNewRequest('errors', 'error404', array(), Request::FIRST_REQUEST)->execute();
			}
			
			$this->duplicateContentPolicy($url, $path, $params);
			$this->requestSecurityPolicy($this->getContainer()->getContext());
		}
		// Timezone
		date_default_timezone_set($config['defaultTimezone']);
		
		$request = $this->getContainer()->getNewRequest($params['module'], $params['action'], $params['params'], $state);
		
		$this->getContainer()->getResponse()->setBody($request->execute());
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
			$this->getContainer()->response = $response;
		}
		
		/* @var $response Response */
		$response = $this->getContainer()->getResponse();
		
		if ($this->viewGetGlobal('layout') !== false)
		{
			$config = $this->getContainer()->getConfig();
			if ($this->viewGetGlobal('layout') === null)
			{
				$this->viewSetGlobal('layout', $config['defaultLayout']);
			}
			$this->viewSetGlobal('contentForLayout', $response->getBody());
			$response->clearResponse();
			
			$vue = $this->getContainer()->getNewView($config['defaultModule'], $this->viewGetGlobal('layout'));
			
			$response->setBody($vue);
		}
		$response->send();
		echo $response;
		
		if ($response->getStatus() == 200)
		{
			/* @var $context Context */
			$context = $this->getContainer()->getContext();
			$context->updateHistory(array(
				'url' => $context->getUrl(),
				'ipAddress' => $context->getIpAddress(),
				'userAgent' => $context->getUserAgent()
				));
		}
		exit();
	}
}
