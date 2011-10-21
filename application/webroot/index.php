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

/**
 * Defines the path to the server root folder
 */
\define('WEBROOT', __DIR__);

/**
 * Defines the path to the application folder
 */
\define('APP_DIR', \dirname(\WEBROOT));

/**
 * Defines the path to the framework folder
 */
\define('FRAMEWORK_DIR', \dirname(\APP_DIR).\DIRECTORY_SEPARATOR.'framework');

/**
 * Defines the path to the folder containing build files
 */
\define('BUILD_DIR', \APP_DIR.\DIRECTORY_SEPARATOR.'build');

/**
 * Defines the path to the folder containing log files
 */
\define('LOG_DIR', \APP_DIR.\DIRECTORY_SEPARATOR.'log');

/**
 * Defines the path to modules folder
 */
\define('MODULES_DIR', \dirname(\APP_DIR).\DIRECTORY_SEPARATOR.'modules');

/**
 * Definess the path to vendors folder
 */
\define('VENDORS_DIR', \dirname(\APP_DIR).\DIRECTORY_SEPARATOR.'vendors');

\define('DS', \DIRECTORY_SEPARATOR);

$autoload = array();
$config = array();

require \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'core'.\DIRECTORY_SEPARATOR.'FrameworkObject.php';
require \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'core'.\DIRECTORY_SEPARATOR.'Core.php';

if (\file_exists(\BUILD_DIR.\DIRECTORY_SEPARATOR.'config.php'))
{
	include \BUILD_DIR.\DIRECTORY_SEPARATOR.'config.php';
}

if (!isset ($config['environment']))
{
	$config['environment'] = \framework\core\Core::DEV;
}

if ($config['environment'] == \framework\core\Core::DEV)
{
    require \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'libs'.\DIRECTORY_SEPARATOR.'ConfigBuilder.php';

	$modulesDirectories = array();
	$modulesDirectories['framework'] = \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'modules';
	$modulesDirectories['modules'] = \MODULES_DIR;
	$modulesDirectories['application'] = \APP_DIR . \DIRECTORY_SEPARATOR . 'modules';
	
	$variablesNames = array(
		'framework' => array('config' => 'frameworkConfig', 'routes' => 'routes', 'events' => 'events', 'components' => 'fcomponents'),
		'modules' => array('config' => 'config', 'events' => 'events', 'components' => 'components'),
		'application' => array('config' => 'appConfig', 'routes' => 'routes', 'events' => 'events', 'components' => 'components')
	);

	// get the full config, i.e. framework + app + modules
	$configBuilder = new \framework\libs\ConfigBuilder();
	$configBuilder->setModulesDirectories($modulesDirectories)
			->setVariablesNames($variablesNames)
			->buildConfig();
	
	$config = $configBuilder->getConfig();
}

/**
 * Defines the execution mode
 */
\define('ENV', $config['environment']);

if (\file_exists(\BUILD_DIR.\DIRECTORY_SEPARATOR.'autoload.php'))
{
	include \BUILD_DIR.\DIRECTORY_SEPARATOR.'autoload.php';
}

require \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'libs'.\DIRECTORY_SEPARATOR.'ClassLoader.php';
require \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'libs'.\DIRECTORY_SEPARATOR.'StaticClassLoader.php';

if (\ENV != \framework\core\Core::DEV)
{
	$loader = new \framework\libs\StaticClassLoader($autoload);
	$loader->register();
}
else
{
	$loader = new \framework\libs\ClassLoader('framework', \FRAMEWORK_DIR);
	$loader->register();
	
	$loader = new \framework\libs\ClassLoader('modules', \MODULES_DIR);
	$loader->register();
	
	$loader = new \framework\libs\ClassLoader('application', \APP_DIR);
	$loader->register();

	$loader = new \framework\libs\StaticClassLoader($autoload);
	$loader->register();

	$vendorsAutoload = array();
	
	include \FRAMEWORK_DIR.\DIRECTORY_SEPARATOR.'config'.\DIRECTORY_SEPARATOR.'vendorsAutoload.php';
	
	$loader = new \framework\libs\StaticClassLoader($vendorsAutoload);
	$loader->register();
}

$registry = new \framework\libs\Registry($config);

$container = new \framework\libs\ComponentsContainer($registry);
$core = $container->getCore();

$core->bootstrap()
		->run();

