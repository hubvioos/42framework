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
define('VENDORS_DIR', dirname(APPLICATION_DIR).DS.'vendors');


require FRAMEWORK_DIR.DS.'libs'.DS.'ClassLoader.php';
require FRAMEWORK_DIR.DS.'libs'.DS.'StaticClassLoader.php';

$loader = new \framework\libs\StaticClassLoader(APPLICATION_DIR.DS.'build'.DS.'autoload.php');
$loader->register();
$loader = new \framework\libs\StaticClassLoader(FRAMEWORK_DIR.DS.'config'.DS.'autoload.php');
$loader->register();
$loader = new \framework\libs\ClassLoader('framework', FRAMEWORK_DIR);
$loader->register();
$loader = new \framework\libs\ClassLoader('application', APPLICATION_DIR);
$loader->register();


$config = array();
if (file_exists(APPLICATION_DIR.DS.'build'.DS.'config.php'))
{
	include APPLICATION_DIR.DS.'build'.DS.'config.php';
}

$container = new \framework\core\ApplicationContainer($config);

$container->getCore()
				->bootstrap()
				->run();
