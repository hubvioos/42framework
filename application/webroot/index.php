<?php
define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT', dirname(__FILE__));
define('APPLICATION_DIR', dirname(WEBROOT));
define('FRAMEWORK_DIR', dirname(APPLICATION_DIR).DS.'42framework');
define('VENDORS_DIR', dirname(APPLICATION_DIR).DS.'vendors');

$autoload = array();
include APPLICATION_DIR.DS.'build'.DS.'autoload.php';
require FRAMEWORK_DIR.DS.'utils'.DS.'ClassLoader.php';

spl_autoload_register(array(new \Framework\Utils\ClassLoader($autoload), 'load'));
date_default_timezone_set('Europe/Paris');

$config = array();
include APPLICATION_DIR.DS.'config'.DS.'config.php';
$test = new \Application\modules\cli\controllers\CliAutoload();
$test->compileActionsMap();
/*
$core = \Framework\Core::getInstance(\Framework\Request::getInstance(), \Framework\Response::getInstance())->init($config);

$core->execute();
$response = $core->getResponse();

$response->send();
echo $response;*/