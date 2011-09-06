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

class CompileConfig extends \application\modules\cli\controllers\CliCommand
{
	public function processAction ($configFileName = 'config', $frameworkConfigVariableName = 'frameworkConfig',  $appConfigVariableName= 'appConfig', $moduleConfigVariableName = 'config')
	{
		$variablesNames = array(   'framework' => $frameworkConfigVariableName,
												 'app' => $appConfigVariableName,
												 'module' => $moduleConfigVariableName);
		$configBuilder = new \framework\libs\ConfigBuilder($configFileName, $variablesNames);
		$configBuilder->setModulesDirectory(\MODULES_DIR)
							->buildConfig();
		$config = $configBuilder->getConfig();

		
		$ab = new \application\modules\cli\ConfigBuilder($config);
		$ab->setTemplateFile(MODULES_DIR.DS.'cli'.DS.'views'.DS.'configTemplate.php');
		$ab->save(APPLICATION_DIR.DS.'build'.DS.$configFileName.'.php');
	}
}