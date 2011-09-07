<?php 
/**
 * Copyright (C) 2011 - K√©vin O'NEILL, Fran√ßois KLINGLER - <contact@42framework.com>
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

class Core extends \framework\core\FrameworkObject
{	
	const DEV = 'dev';
	
	const PROD = 'prod';
	
	const STAGE = 'stage';
	
	
	public function __construct (\framework\core\ComponentsContainer $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Initializes the framework
	 * 
	 * @return \framework\core\Core
	 */
	public function bootstrap ()
	{
		$this->getComponent('errorHandler');
		
		$this->getComponent('session')->init();
		
		// Timezone
		\date_default_timezone_set($this->getConfig('defaultTimezone'));
		
		return $this;
	}
	
	/**
	 * Main execution method
	 * 
	 * @return \framework\core\Core
	 */
	public function run()
	{
		$request = $this->getComponent('httpRequest');
		$response = $this->getComponent('httpResponse');
		
		if (\PHP_SAPI === 'cli')
		{
			$request->setCli(true);
			$params = \framework\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';
			
			$state = \framework\core\Request::CLI_STATE;
		}
		else
		{
			$url = $request->getUrl();
			
			// $path = $this->getComponent('route')->urlToPath($url, $config['defaultModule'], $config['defaultAction']);
			$path = $this->getComponent('route')->urlToPath($url);
			
			$params = $this->getComponent('route')->pathToParams($path);
			
			$this->duplicateContentPolicy($url, $path, $params);
			
			$state = \framework\core\Request::FIRST_REQUEST;
			
			// Views variables
			$this->viewSetGlobal('messages', $this->getComponent('message')->getAll());
			$this->getComponent('message')->clearAll();
			
			if (!class_exists('application\\modules\\'.$params['module'].'\\controllers\\'.$params['action']))
			{
				$params['module'] = 'errors';
				$params['action'] = 'error404';
				$params['params'] = array();
			}
		}
		$execute = $this->createRequest($params['module'], $params['action'], $params['params'], $state);
		
		$this->raiseEvent('beforeApp');
		
		$executeResponse = $execute->execute();
		
		if ($executeResponse->getStatus() == \framework\core\Response::SUCCESS)
		{
			$response->set($executeResponse->get());
		}
		else
		{
			$this->createRequest('errors', 'error404')->execute();
		}
		
		if (!$request->isCli())
		{
			$previousIpAddress = $request->getPreviousIpAddress();
			$previousUserAgent = $request->getPreviousUserAgent();
						
			if ($previousIpAddress !== null 
				&& $previousIpAddress != $request->getIpAddress()
				&& $previousUserAgent !== null
				&& $previousUserAgent != $request->getUserAgent()
				)
			{
				$this->getComponent('session')->destroyAll();
				$this->getComponent('message')
					->set('It seems that your session has been stolen, we destroyed it for security reasons. 
						Check your environment security.', 'warning');
				$this->getResponse()->redirect($this->getConfig('siteUrl'), 301, true);
			}
		}
		
		$this->raiseEvent('afterApp');
    	
    	if ($this->viewGetGlobal('layout') === null)
		{
			$this->viewSetGlobal('layout', $this->getConfig('defaultLayout'));
		}
		
    	if ($this->viewGetGlobal('layout') !== false)
		{
			$this->viewSetGlobal('contentForLayout', $response->get());
			$response->clear();
			
			$response->set($this->createView($this->getConfig('defaultModule'), $this->viewGetGlobal('layout')));
		}
		
		$this->raiseEvent('beforeView');
		
		$render = $response->render();
		
		$this->raiseEvent('afterView', $render);
		
		$response->send();
		
		echo $render;
		
		if ($response->getStatus() == 200)
		{
			$request->updateHistory();
		}
		
		//exit();
		
		return $this;
	}
	
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
}
