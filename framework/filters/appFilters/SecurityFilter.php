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
namespace framework\filters\appFilters;

class SecurityFilter extends \framework\filters\Filter
{	
	/**
	 * Main execution method
	 * 
	 * @param \Framework\core\HttpRequest $request
	 * @param \Framework\core\HttpResponse $response
	 * @return Framework\Core
	 */
	public function _before(&$request, &$response)
	{
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
	}	
}
