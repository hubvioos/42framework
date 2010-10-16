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

class CoreException extends \Exception { }

class Core extends FrameworkObject
{	
	protected $_filterChain = null;
	
	public function __construct(ApplicationContainer $container)
	{
		$this->setContainer($container);
	}

	public function bootstrap ()
	{
		$this->getContainer()->getErrorHandler();
		$this->getContainer()->getRoute();
		// Timezone
		date_default_timezone_set($this->getContainer()->config['defaultTimezone']);
		$appFilters = array();
		foreach ($this->getContainer()->config['applicationFilters'] as $filter)
		{
			$appFilters[] = new $filter;
		}
		$this->_filterChain = $this->getContainer()->getAppFilterChain($appFilters);
		return $this;
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
	
	public function classExists($className)
	{
		return \Doctrine\Common\ClassLoader::classExists($className);
	}
	
	/**
	 * Main execution method
	 * 
	 * @return Framework\Core
	 */
	public function run()
	{
		$request = $this->getContainer()->getHttpRequest();
		$response = $this->getContainer()->getHttpResponse();
		$this->_filterChain->execute($request, $response);
		return $this;
	}
	
	public function render(HttpResponse $response)
	{
		$appFilter = $this->getContainer()->getApplicationFilter();
		$request = $this->getContainer()->getHttpRequest();
		$appFilter->_after($request, $response);
	}
}
