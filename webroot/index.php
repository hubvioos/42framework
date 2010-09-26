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
define('FRAMEWORK_DIR', APPLICATION_DIR.DS.'42framework');
define('MODULES_DIR', APPLICATION_DIR.DS.'modules');
define('VENDORS_DIR', APPLICATION_DIR.DS.'vendors');

$autoload = array();
$config = array();
if (file_exists(APPLICATION_DIR.DS.'build'.DS.'autoload.php'))
{
	include APPLICATION_DIR.DS.'build'.DS.'autoload.php';
}
if (file_exists(APPLICATION_DIR.DS.'build'.DS.'config.php'))
{
	include APPLICATION_DIR.DS.'build'.DS.'config.php';
}
/*require FRAMEWORK_DIR.DS.'Config.php';
require FRAMEWORK_DIR.DS.'BaseContainer.php';
require FRAMEWORK_DIR.DS.'ApplicationContainer.php';
require FRAMEWORK_DIR.DS.'FrameworkObject.php';
require FRAMEWORK_DIR.DS.'Application.php';
require FRAMEWORK_DIR.DS.'libs'.DS.'ClassLoader.php';
*/
require VENDORS_DIR.DS.'Doctrine'.DS.'Common'.DS.'ClassLoader.php';
require FRAMEWORK_DIR.DS.'libs'.DS.'StaticClassLoader.php';

$loader = new \Framework\Libs\StaticClassLoader($autoload, FRAMEWORK_DIR.DS.'config'.DS.'autoload.php');
$loader->register();

$container = new \Framework\ApplicationContainer($config);

$container->getApplication()
				->bootstrap()
				->run()
				->render();