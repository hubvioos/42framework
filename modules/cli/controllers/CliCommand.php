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
namespace Application\modules\cli\controllers;
defined('FRAMEWORK_DIR') or die('Invalid script access');

class CliException extends \Exception { }

class CliCommand extends \Framework\Controller
{
	/**
	 * Executes the action corresponding to the current request
	 * 
	 * @param Framework\Request $request
	 */
	protected function _before(\Framework\Request $request)
	{
		if ($request->getState() == \Framework\Request::CLI_STATE)
		{
			$this->_response = \Framework\Request::factory('errors', 'error403', array($request))->execute();
			return false;
		}
		return true;
	}
	
	protected function _after(\Framework\Request $request, $actionResponse)
	{
		$this->setView(false);
		\Framework\View::setGlobal('layout', false);
	}
}