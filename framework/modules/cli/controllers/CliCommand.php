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
namespace application\modules\cli\controllers;

class CliCommand extends \framework\core\Controller
{
	protected $usesView = false;
	protected $usesLayout = false;

	protected function _before(\framework\core\Request &$request, \framework\core\Response &$response)
	{
		if ($request->getState() != \framework\core\Request::CLI_STATE)
		{
			//$this->getContainer()->getNewRequest('errors', 'error403', array($request))->execute();
		}
	}
}