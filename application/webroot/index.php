<?php
use \Framework;
define('DS', DIRECTORY_SEPARATOR);
define('WEBROOT', dirname(__FILE__));
define('APPLICATION_DIR', dirname(WEBROOT));
define('FRAMEWORK_DIR', dirname(APPLICATION_DIR).DS.'42framework');

require APPLICATION_DIR.DS.'config'.DS.'autoload.php';
require APPLICATION_DIR.DS.'config'.DS.'config.php';
require FRAMEWORK_DIR.DS.'utils'.DS.'ClassLoader.php';

spl_autoload_register(array(new Framework\Utils\ClassLoader($autoload), 'load'));

$core = Framework\Core::getInstance(Framework\Request::getInstance(), Framework\Response::getInstance())->init($config);

$core->execute();
$response = $core->getResponse();

$response->send();
echo $response;