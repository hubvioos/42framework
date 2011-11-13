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

class Dispatcher extends \framework\core\FrameworkObject
{

	public function dispatch (\framework\core\Request $request)
	{
		$this->raiseEvent('framework.beforeDispatch', $request);
		
		$classname = $this->getAction($request->getModule(), $request->getAction(), $request);
		
		if (!$classname)
		{
			return $this->createRequest(array('module' => 'errors', 'action' => 'error404'), $request->getState())->execute();
		}

		$module = $this->getComponent('action', $classname);
		$response = $this->getComponent('response');
		$response->setFormat($request->getFormat());
		
		return $module->execute($request, $response);
	}

	public function getModulePath ($module)
	{
		$config = $this->getConfig('modulesLocation');

		$viewsPath = null;

		switch ($config[$module])
		{
			case 'framework':
				$viewsPath = \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'modules' . \DIRECTORY_SEPARATOR . $module;
				break;

			case 'modules':
				$viewsPath = \MODULES_DIR . \DIRECTORY_SEPARATOR . $module;
				break;

			case 'application':
				$viewsPath = \APP_DIR . \DIRECTORY_SEPARATOR . 'modules' . \DIRECTORY_SEPARATOR . $module;
				break;
		}

		return $viewsPath;
	}

	public function getModuleNamespace ($module)
	{
		$config = $this->getConfig('modulesLocation');

		$namespace = null;

		switch ($config[$module])
		{
			case 'framework':
				$namespace = '\\framework\\modules\\' . $module;
				break;

			case 'modules':
				$namespace = '\\modules\\' . $module;
				break;

			case 'application':
				$namespace = '\\application\\modules\\' . $module;
				break;
		}

		return $namespace;
	}

	protected function getAction ($module, $action, \framework\core\Request $request)
	{
		$modulesLocation = $this->getConfig('modulesLocation');

		$classname = false;

		if (isset($modulesLocation[$module]))
		{
			$classname = $this->getModuleNamespace($module) . '\\controllers\\' . $action;

			if (!\class_exists($classname))
			{
				$moduleConfig = $this->getConfig('modules.' . $module);

				if (isset($moduleConfig['extends']))
				{
					$classname = $this->getAction($moduleConfig['extends'], $action, $request);
					
					if ($classname)
					{
						$request->set('module', $moduleConfig['extends']);
					}
				}
			}
		}

		return $classname;
	}

}
