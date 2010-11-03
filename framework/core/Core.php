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

class Core extends \framework\core\FrameworkObject
{	
	/**
	 * @var \framework\filters\FilterChain
	 */
	protected $_filterChain = null;
	
	/**
	 * @param \framework\ApplicationContainer $container
	 */
	public function __construct(ApplicationContainer $container)
	{
		$this->setContainer($container);
	}

	/**
	 * @return \framework\core\Core
	 */
	public function bootstrap ()
	{
		$this->getContainer()->getErrorHandler();
		$this->getContainer()->getEventManager();
		$this->getContainer()->getRoute();
		// Timezone
		date_default_timezone_set($this->getContainer()->config['defaultTimezone']);
		$appFilters = array();
		foreach ($this->getContainer()->config['applicationFilters'] as $filter)
		{
			$appFilters[] = new $filter;
		}
		$this->_filterChain = $this->getContainer()->getFilterChain($appFilters);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return \framework\core\Core
	 */
	public function viewSetGlobal($key, $value)
	{
		$view = $this->getContainer()->getViewClass();
		/* @var $view View */
		$view::setGlobal($key, $value);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function viewGetGlobal($key)
	{
		$view = $this->getContainer()->getViewClass();
		/* @var $view View */
		return $view::getGlobal($key);
	}
	
	/**
	 * Main execution method
	 * 
	 * @return \framework\core\Core
	 */
	public function run()
	{
		$request = $this->getContainer()->getHttpRequest();
		$response = $this->getContainer()->getHttpResponse();
		$this->_filterChain->execute($request, $response);
		return $this;
	}
	
	/**
	 * @param \framework\core\HttpResponse $response
	 */
	public function render(HttpResponse $response)
	{
		$appFilter = $this->getContainer()->getApplicationFilter();
		$request = $this->getContainer()->getHttpRequest();
		$appFilter->_after($request, $response);
	}
}
