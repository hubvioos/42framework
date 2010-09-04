<?php
use Framework\Utils;
define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT', dirname(__FILE__));
define('APPLICATION_DIR', dirname(WEBROOT));
define('FRAMEWORK_DIR', dirname(APPLICATION_DIR).DS.'42framework');
define('MODULES_DIR', dirname(APPLICATION_DIR).DS.'modules');
define('VENDORS_DIR', dirname(APPLICATION_DIR).DS.'vendors');

$autoload = array();
$config = array();
include APPLICATION_DIR.DS.'build'.DS.'autoload.php';
include APPLICATION_DIR.DS.'build'.DS.'config.php';
require FRAMEWORK_DIR.DS.'Core.php';
require FRAMEWORK_DIR.DS.'utils'.DS.'ClassLoader.php';

$core = \Framework\Core::getInstance()
			->init($autoload, $config)
			->setRequest(\Framework\Request::getInstance())
			->setResponse(\Framework\Response::getInstance())
			->execute()
			->render();