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
 * Defines the area's name
 */
\define('AREA_NAME', 'web');

/**
 * Defines the path to the server root folder
 */
\define('WEBROOT', __DIR__);

require \dirname(\dirname(\dirname(\WEBROOT))) . \DIRECTORY_SEPARATOR . 'common' . \DIRECTORY_SEPARATOR . 'constants.php';

$autoload = array();
$config = array();

require \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'libs' . \DIRECTORY_SEPARATOR . 'StaticClassLoader.php';

if (\ENV == 'dev') // dynamic autoload and config
{
	require \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'libs' . \DIRECTORY_SEPARATOR . 'ClassLoader.php';
	
	/*
	 * Autoload
	 */
	$loader = new \framework\libs\ClassLoader(\FRAMEWORK_DIR);
	$loader->addNamespace('framework');
	$loader->addNamespace('modules', \MODULES_DIR);
	$loader->addNamespace('application', \AREA_DIR);
	$loader->addNamespace('Monolog', \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'vendors' . \DIRECTORY_SEPARATOR . 'Monolog');
	$loader->register();

	$vendorsAutoload = array();
	
	if (\file_exists(\BUILD_DIR . \DIRECTORY_SEPARATOR . 'autoload.php'))
	{
		include \BUILD_DIR . \DIRECTORY_SEPARATOR . 'autoload.php';
	}
	
	include \FRAMEWORK_DIR . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'vendorsAutoload.php';
	
	$loader = new \framework\libs\StaticClassLoader();
	$loader->addMap('build', $autoload);
	$loader->addMap('vendors', $vendorsAutoload);
	$loader->register();
	
	/*
	 * Config
	 */
	$variablesNames = array(
		'framework' => array(
			'config' => 'config', 
			'routes' => 'routes', 
			'events' => 'events', 
			'components' => 'components'),
		'modules' => array(
			'config' => 'config', 
			'routes' => 'routes', 
			'events' => 'events', 
			'components' => 'components'),
		'application' => array(
			'config' => 'config', 
			'routes' => 'routes', 
			'events' => 'events', 
			'components' => 'components'),
		'area' => array(
			'config' => 'config', 
			'routes' => 'routes', 
			'events' => 'events', 
			'components' => 'components')
	);

	// get the full config, i.e. framework + app + modules
	$configBuilder = new \framework\libs\ConfigBuilder();
	$configBuilder->setVariablesNames($variablesNames)
				->buildConfig();

	$config = $configBuilder->getConfig();
}
else // static autoload and config
{
	/*
	 * Autoload
	 */
	require \BUILD_DIR . \DIRECTORY_SEPARATOR . 'autoload.php';
	
	$loader = new \framework\libs\StaticClassLoader();
	$loader->addMap('build', $autoload);
	$loader->register();

	/*
	 * Config
	 */
	require \BUILD_DIR . \DIRECTORY_SEPARATOR . 'config.php';
}

$container = new \framework\libs\ComponentsContainer($config);

$core = $container->getCore();

$core->bootstrap()
		->run();