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

		$classname = $this->getAction($request->getModule(), $request->getAction());

		if (!$classname)
		{
			throw new \framework\core\http\exception\NotFoundException();
		}

		$module = $this->getComponent('action', $classname);
		$response = $this->getComponent('response');
		$response->setFormat($request->getFormat());

		return $module->execute($request, $response);
	}

	public function getModulePath ($module)
	{
		return \MODULES_DIR . \DIRECTORY_SEPARATOR . $module;
	}

	public function getModuleNamespace ($module)
	{
		return '\\modules\\' . $module;
	}

	protected function getAction ($module, $action)
	{
		$modulesLocation = $this->getConfig('modules');

		$classname = false;

		if (isset($modulesLocation[$module]))
		{
			$classname = $this->getModuleNamespace($module) . '\\controllers\\' . $action;

			if (!\class_exists($classname))
			{
				$moduleConfig = $this->getConfig('modules.' . $module);

				if (isset($moduleConfig['extends']))
				{
					$classname = $this->getAction($moduleConfig['extends'], $action);
				}
			}
		}

		return $classname;
	}

	public function getViewPath ($module, $file, $extension, $format = null)
	{
		$modulesLocation = $this->getConfig('modules');

		$filepath = false;
		
		if (isset($modulesLocation[$module]))
		{
			$filepath = $this->getModulePath($module) . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR;
			
			if ($format !== null)
			{
				$filepath .= $format . \DIRECTORY_SEPARATOR . $file . $extension;
			}
			else
			{
				$filepath .= $file . $extension;
			}

			if (!\file_exists($filepath))
			{
				$filepath = false;
				
				$moduleConfig = $this->getConfig('modules.' . $module);

				if (isset($moduleConfig['extends']))
				{
					$filepath = $this->getView($moduleConfig['extends'], $file, $extension);
				}
			}
		}

		return $filepath;
	}

}
