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

namespace modules\cli\controllers;

class CompileConfig extends \modules\cli\controllers\CliCommand
{
	public function processAction ()
	{
		/*$config = array();
		require \FRAMEWORK_DIR.DS.'config'.DS.'config.php';
		$frameworkConfig = $config;
		
		require APP_DIR.DS.'config'.DS.'config.php';
		$appConfig = $config;
		
		$config = array_merge($frameworkConfig, $appConfig);
		
		
		// HERE WE GOOOOOOOO
		$configBuilder->setModulesDirectories($modulesDirectories)
				->buildConfig();
		$config = $configBuilder->getConfig();
		 */
		
		$config = $this->getConfig()->toArray();
		
		$ab = new \modules\cli\ConfigBuilder($config);
		$ab->setTemplateFile(\FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'modules' . \DIRECTORY_SEPARATOR . 'cli' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . 'configTemplate.php');
		$ab->save(\APP_DIR . \DIRECTORY_SEPARATOR . 'build' . \DIRECTORY_SEPARATOR . 'config.php');
		
		echo "Configuration générée !\n";
	}
}
