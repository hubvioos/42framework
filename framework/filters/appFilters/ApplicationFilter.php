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

class ApplicationFilter extends \framework\filters\Filter
{	
	public function duplicateContentPolicy ($url, $path, $params)
	{
		// Redirect to root if we use the default module and action.
		if ($url != '' 
		    && $params['module'] == $this->getConfig('defaultModule')
		    && $params['action'] == $this->getConfig('defaultAction')
		    && empty($params['params'])
		    )
		{
		    $this->getComponent('httpResponse')->redirect($this->getConfig('siteUrl'), 301, true);
		}
		// Avoid duplicate content of the routes.
		else if ($url != $this->getComponent('route')->pathToUrl($path)
			&& $url != '')
		{
		    $this->getComponent('httpResponse')
		    		->redirect($this->getConfig('siteUrl') . $this->getComponent('route')->pathToUrl($path), 301, true);
		}
						
		// Avoid duplicate content with just a "/" after the URL
		if(strrchr($url, '/') === '/')
		{
		    $this->getComponent('httpResponse')->redirect($this->getConfig('siteUrl') . rtrim($url, '/'), 301, true);  
		}
	}
	
	/**
	 * Main execution method
	 * 
	 * @param \Framework\core\HttpRequest $request
	 * @param \Framework\core\HttpResponse $response
	 * @return Framework\Core
	 */
	public function _before(&$request, &$response)
	{
		$config = $this->getConfig();
		if (PHP_SAPI === 'cli')
		{
			$request->setCli(true);
			$params = \application\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';
			
			$state = \framework\core\Request::CLI_STATE;
		}
		else
		{
			$url = $request->getUrl();
			
			$path = $this->getComponent('route')->urlToPath($url, $config['defaultModule'], $config['defaultAction']);
			
			$params = $this->getComponent('route')->pathToParams($path);
			
			$this->duplicateContentPolicy($url, $path, $params);
			
			$state = \framework\core\Request::FIRST_REQUEST;
			
			// Views variables
			$this->viewSetGlobal('messages', $this->getComponent('message')->getAll());
			
			if (!class_exists('application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				$params['module'] = 'errors';
				$params['action'] = 'error404';
				$params['params'] = array();
			}
		}
		$request->setRequest($this->createRequest($params['module'], $params['action'], $params['params'], $state));
	}
	
	/**
	 * Render the request (send headers and display the response)
	 * 
	 * @param \Framework\core\HttpRequest $request
	 * @param \Framework\core\HttpResponse $response
	 */
	public function _after(&$request, &$response)
    {
    	$config = $this->getConfig();
    	
    	if ($this->viewGetGlobal('layout') === null)
		{
			$this->viewSetGlobal('layout', $config['defaultLayout']);
		}
		
    	if ($this->viewGetGlobal('layout') !== false)
		{
			$this->viewSetGlobal('contentForLayout', $response->get());
			$response->clear();
			
			$response->set($this->createView($config['defaultModule'], $this->viewGetGlobal('layout')));
		}
		
		if (isset($config['viewFilters']))
		{
			$filterChain = $this->getComponent('filterChain');

			foreach ($config['viewFilters'] as $filter)
			{
				$filterChain->addFilter(new $filter);
			}

			$filterChain->addFilter(new \framework\filters\viewFilters\RenderFilter());

			$filterChain->execute($request, $response);
		}
		$render = $response->render();

		$response->send();
		
		echo $render;
		
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
