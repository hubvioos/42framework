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
	/**
	 * @var \framework\filters\FilterChain
	 */
	protected $_filterChain = null;
	
	
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
		
		// Timezone
		date_default_timezone_set($this->getConfig('defaultTimezone'));
		
		$this->_filterChain = $this->getComponent('filterChain');
		
		$this->_filterChain->addFilter(new \framework\filters\appFilters\ApplicationFilter());
		$this->_filterChain->addFilter(new \framework\filters\appFilters\SecurityFilter());
		$this->_filterChain->addFilter(new \framework\filters\appFilters\DoctrineFilter());
		
		foreach ($this->getConfig('applicationFilters') as $filter)
		{
			$this->_filterChain->addFilter(new $filter);
		}
		
		$this->_filterChain->addFilter(new \framework\filters\appFilters\ExecFilter());
		
		$this->_filterChain->init();
		
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
		$this->_filterChain->execute($request, $response);
		
		return $this;
	}
}
