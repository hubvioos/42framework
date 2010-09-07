<?php 
define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT', dirname(__FILE__));
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
require FRAMEWORK_DIR.DS.'Core.php';
require FRAMEWORK_DIR.DS.'utils'.DS.'ClassLoader.php';

$core = \Framework\Core::getInstance()
			->init($autoload, $config)
			->bootstrap(
				\Framework\Context::getInstance(
					\Framework\History::getInstance(
						\Framework\Utils\Session::getInstance('history'), 
						\Framework\Utils\Config::$config['historySize']
						)
					),
				\Framework\Request::getInstance(),
				\Framework\Response::getInstance()
				)
			->execute()
			->render();