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

namespace framework\filters\appFilters;

defined('FRAMEWORK_DIR') or die('Invalid script access');

class ApplicationFilter extends \framework\filters\Filter
{	
	public function duplicateContentPolicy ($url, $path, $params)
	{
		// Redirect to root if we use the default module and action.
		if ($url != '' 
		    && $params['module'] == $this->getContainer()->config['defaultModule']
		    && $params['action'] == $this->getContainer()->config['defaultAction']
		    && empty($params['params'])
		    )
		{
		    $this->getContainer()->getHttpResponse()->redirect($this->getContainer()->config['siteUrl'], 301, true);
		}
		// Avoid duplicate content of the routes.
		else if ($url != $this->getContainer()->getRoute()->pathToUrl($path)
			&& $url != '')
		{
		    $this->getContainer()->getHttpResponse()
		    		->redirect($this->getContainer()->config['siteUrl'] . $this->getContainer()->getRoute()->pathToUrl($path), 301, true);
		}
						
		// Avoid duplicate content with just a "/" after the URL
		if(strrchr($url, '/') === '/')
		{
		    $this->getContainer()->getHttpResponse()->redirect($this->getContainer()->config['siteUrl'] . rtrim($url, '/'), 301, true);  
		}
	}
	
	/**
	 * Main execution method
	 * 
	 * @return Framework\Core
	 */
	public function _before(&$request, &$response)
	{
		$config = $this->getContainer()->getConfig();
		if (PHP_SAPI === 'cli')
		{
			$request->setCli(true);
			$params = \Application\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';
			
			$state = \Framework\core\Request::CLI_STATE;
		}
		else
		{
			$url = $request->getUrl();
			
			$path = $this->getContainer()->getRoute()->urlToPath($url, $config['defaultModule'], $config['defaultAction']);
			
			$params = $this->getContainer()->getRoute()->pathToParams($path);
			
			$this->duplicateContentPolicy($url, $path, $params);
			
			$state = \Framework\core\Request::FIRST_REQUEST;
			
			// Views variables
			/* @var $view View */
			$view = $this->getContainer()->getViewClass();
			$this->getContainer()->getCore()->viewSetGlobal('layout', $config['defaultLayout']);
			$this->getContainer()->getCore()->viewSetGlobal('messages', $this->getContainer()->getMessage()->getAll());
			
			/*if (!$this->getContainer()->getCore()->classExists('Application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				$params['module'] = 'errors';
				$params['action'] = 'error404';
				$params['params'] = array();
			}*/
			if (!class_exists('application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				$params['module'] = 'errors';
				$params['action'] = 'error404';
				$params['params'] = array();
			}
		}
		$request->setRequest($this->getContainer()->getNewRequest($params['module'], $params['action'], $params['params'], $state));
	}
	
	/**
	 * Render the request (send headers and display the response)
	 * 
	 * @param Framework\Response $response (optional)
	 */
	public function _after(&$request, &$response)
    {
    	$config = $this->getContainer()->getConfig();
    	if ($this->getContainer()->getCore()->viewGetGlobal('layout') !== false)
		{
			if ($this->getContainer()->getCore()->viewGetGlobal('layout') === null)
			{
				$this->getContainer()->getCore()->viewSetGlobal('layout', $config['defaultLayout']);
			}
			$this->getContainer()->getCore()->viewSetGlobal('contentForLayout', $response->get());
			$response->clear();
			
			$response->set($this->getContainer()->getNewView($config['defaultModule'], $this->getContainer()->getCore()->viewGetGlobal('layout')));
		}
		
		$response->send();
		
		if (isset($config['viewFilters']))
		{
			$viewFilters = array();
			foreach ($config['viewFilters'] as $filter)
			{
				$viewFilters[] = new $filter;
			}
			
			$this->getContainer()->getFilterChain($viewFilters)->execute($request, $response);
		}
		echo $response;
		
		if ($response->getStatus() == 200)
		{
			$request->updateHistory(array(
				'url' => $request->getUrl(),
				'ipAddress' => $request->getIpAddress(),
				'userAgent' => $request->getUserAgent()
				));
		}
		exit();
	}
}
