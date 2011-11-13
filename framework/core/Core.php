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

	public function __construct (\framework\libs\ComponentsContainer $container)
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

		$this->getComponent('router')->init($this->getConfig('routes', true));

		// Timezone
		\date_default_timezone_set($this->getConfig('defaultTimezone'));

		$this->raiseEvent('framework.bootstrap');

		return $this;
	}

	/**
	 * Main execution method
	 * 
	 * @return \framework\core\Core
	 */
	public function run ()
	{
		$this->raiseEvent('framework.beforeApp');
		
		$request = $this->getComponent('httpRequest');
		$response = $this->getComponent('httpResponse');

		$params = array();
		$state = null;

		if ($request->isCli())
		{
			$params = \framework\modules\cli\CliUtils::extractParams();
			$params['module'] = 'cli';

			$state = \framework\core\Request::CLI_STATE;
		}
		else
		{
			$previousIpAddress = $request->getPreviousIp();
			$previousUserAgent = $request->getPreviousUserAgent();

			if ($previousIpAddress !== null
					&& $previousIpAddress != $request->getIp()
					&& $previousUserAgent !== null
					&& $previousUserAgent != $request->getUserAgent()
			)
			{
				$this->getComponent('session')->destroyAll();
				$this->getComponent('message')
						->set('It seems that your session has been stolen, we destroyed it for security reasons. 
						Check your environment security.', 'warning');
				$this->getResponse()->redirect($this->getConfig('siteUrl'), 302, true);
			}
			
			// Views variables
			$this->viewSetGlobal('messages', $this->getComponent('message')->getAll());
			$this->getComponent('message')->clearAll();

			$url = $request->getUrl();

			$params = $this->getComponent('router')->match($request->getMethod(), $url);

			$this->duplicateContentPolicy($url, $params);

			$state = \framework\core\Request::FIRST_REQUEST;
		}
		
		$execute = $this->createRequest($params, $state);

		$this->raiseEvent('framework.beforeExecute', $execute);

		$executeResponse = $execute->execute();
		
		$this->raiseEvent('framework.afterExecute', $executeResponse);

		if ($executeResponse->getStatus() == \framework\core\Response::SUCCESS)
		{
			$response->setContent($executeResponse->getContent());
		}
		else
		{
			$this->createRequest(array('module' => 'errors', 'action' => 'error404'))->execute();
		}

		$this->render();
	}

	public function render ($stop = false)
	{
		$this->raiseEvent('framework.beforeRender');
		
		$request = $this->getComponent('httpRequest');
		$response = $this->getComponent('httpResponse');

		if ($this->viewGetGlobal('layout') === null)
		{
			$this->viewSetGlobal('layout', $this->getConfig('defaultLayout'));
		}

		if ($this->viewGetGlobal('layout') !== false)
		{
			$this->viewSetGlobal('contentForLayout', $response->getContent());
			$response->resetContent();

			$response->setContent($this->createView($this->getConfig('defaultModule'), $this->viewGetGlobal('layout')));
		}

		$content = $response->getContent();

		if ($content !== null)
		{
			if ($content instanceof \framework\core\View)
			{
				$content = $content->render();
			}
		}
		else
		{
			$content = '';
		}

		$response->setContent($content);

		$this->raiseEvent('framework.afterRender', $content);

		$response->send();

		if ($response->getStatus() == 200)
		{
			$request->updateHistory();
		}
		
		$this->raiseEvent('framework.afterApp');

		if ($stop)
		{
			exit();
		}

		return $this;
	}

	public function duplicateContentPolicy ($url, $params)
	{
		$router = $this->getComponent('router');
		
		// Redirect to root if we use the default module and action.
		if ($params['module'] == $this->getConfig('defaultModule')
			&& $params['action'] == $this->getConfig('defaultAction')
			&& $url != '' 
			&& $router->matchedRoute()->name() != 'default')
		{
			$this->getComponent('httpResponse')->redirect($this->getConfig('siteUrl'), 301, true);
		}

		// Avoid duplicate content with just a "/" after the URL
		if (strrchr($url, '/') === '/')
		{
			$this->getComponent('httpResponse')->redirect($this->getConfig('siteUrl') . rtrim($url, '/'), 301, true);
		}
	}

}
