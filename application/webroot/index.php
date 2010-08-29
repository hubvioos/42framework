<?php
define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT', dirname(__FILE__));
define('APPLICATION_DIR', dirname(WEBROOT));
define('FRAMEWORK_DIR', dirname(APPLICATION_DIR).DS.'42framework');
define('VENDORS_DIR', dirname(APPLICATION_DIR).DS.'vendors');

$autoload = array();
$actionsMap = array();
include APPLICATION_DIR.DS.'build'.DS.'autoload.php';
include APPLICATION_DIR.DS.'build'.DS.'actionsMap.php';
require FRAMEWORK_DIR.DS.'utils'.DS.'ClassLoader.php';

spl_autoload_register(array(new \Framework\Utils\ClassLoader($autoload, $actionsMap), 'load'));
date_default_timezone_set('Europe/Paris');

$config = array();
include APPLICATION_DIR.DS.'build'.DS.'config.php';

$core = \Framework\Core::getInstance()
			->init($config)
			->setRequest(\Framework\Request::getInstance())
			->setResponse(\Framework\Response::getInstance())
			->execute()
			->render();