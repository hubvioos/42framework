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

define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT', __DIR__);
define('APPLICATION_DIR', dirname(WEBROOT));
define('FRAMEWORK_DIR', dirname(APPLICATION_DIR).DS.'framework');
define('MODULES_DIR', APPLICATION_DIR.DS.'modules');
define('CACHE_DIR', APPLICATION_DIR.DS.'cache');
define('LOG_DIR', APPLICATION_DIR.DS.'log');
define('VENDORS_DIR', dirname(APPLICATION_DIR).DS.'vendors');

$autoload = array();
$config = array();

if (file_exists(CACHE_DIR.DS.'autoload.php'))
{
	include CACHE_DIR.DS.'autoload.php';
}

if (file_exists(CACHE_DIR.DS.'config.php'))
{
	include CACHE_DIR.DS.'config.php';
}
else
{
	include FRAMEWORK_DIR.DS.'config'.DS.'config.php';
}

require FRAMEWORK_DIR.DS.'libs'.DS.'ClassLoader.php';
require FRAMEWORK_DIR.DS.'libs'.DS.'StaticClassLoader.php';

if ($config['environment'] == 'prod')
{
	$loader = new \framework\libs\StaticClassLoader($autoload);
	$loader->register();
}

$loader = new \framework\libs\ClassLoader('framework', FRAMEWORK_DIR);
$loader->register();
$loader = new \framework\libs\ClassLoader('application', APPLICATION_DIR);
$loader->register();

if ($config['environment'] == 'test' || $config['environment'] == 'dev')
{
	$loader = new \framework\libs\StaticClassLoader($autoload);
	$loader->register();
	
	$vendorsAutoload = array();
	
	include FRAMEWORK_DIR.DS.'config'.DS.'vendorsAutoload.php';
	
	$loader = new \framework\libs\StaticClassLoader($vendorsAutoload);
	$loader->register();
}

$config = new \framework\libs\Registry($config);
$container = new \framework\core\ComponentsContainer($config);

$core = $container->get('core');

$core	->bootstrap()
		->run();
